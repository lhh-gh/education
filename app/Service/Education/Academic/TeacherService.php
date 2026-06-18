<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace App\Service\Education\Academic;

use App\Event\Education\Foundation\EducationAuditEvent;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Academic\AcademicRecordStatus;
use App\Model\Enums\Education\Academic\Gender;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Model\Enums\Education\Foundation\UserProfileStatus;
use App\Repository\Education\Academic\TeacherRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class TeacherService
{
    public function __construct(
        private readonly TeacherRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        [$page, $pageSize, $filters] = $this->extractPage($filters);

        return $this->repository->pageByContext($filters, $page, $pageSize, $context);
    }

    public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationTeacher
    {
        $tenantId = $this->tenantIdForWrite($data, $context);
        $campusId = $this->campusIdForWrite($tenantId, $data['campus_id'] ?? null, $context);
        $teacherNo = trim((string) ($data['teacher_no'] ?? ''));
        $this->assertUniqueTeacherNo($tenantId, $teacherNo);
        $this->assertTeacherProfile($tenantId, $data['user_profile_id'] ?? null);

        $data['tenant_id'] = $tenantId;
        $data['campus_id'] = $campusId;
        $data['teacher_no'] = $teacherNo;
        $data['gender'] = $this->normalizeGender((string) ($data['gender'] ?? Gender::Unknown->value));
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? AcademicRecordStatus::Enabled->value));
        $data['created_by'] = $operatorId;
        $data['updated_by'] = $operatorId;

        return Db::transaction(function () use ($data, $context): EducationTeacher {
            $teacher = $this->repository->create($data)->refresh();
            $this->dispatchAudit('created', $teacher, $context, [], $teacher->toArray());

            return $teacher;
        });
    }

    public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationTeacher
    {
        $teacher = $this->findForWrite($id, $context);
        $tenantId = (int) $teacher->tenant_id;
        $campusId = $this->campusIdForWrite($tenantId, $data['campus_id'] ?? $teacher->campus_id, $context);
        $teacherNo = trim((string) ($data['teacher_no'] ?? $teacher->teacher_no));
        $this->assertUniqueTeacherNo($tenantId, $teacherNo, $id);
        $this->assertTeacherProfile($tenantId, $data['user_profile_id'] ?? $teacher->user_profile_id, $id);

        $data['tenant_id'] = $tenantId;
        $data['campus_id'] = $campusId;
        $data['teacher_no'] = $teacherNo;
        $data['gender'] = $this->normalizeGender((string) ($data['gender'] ?? $teacher->gender));
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? $teacher->status));
        $data['updated_by'] = $operatorId;

        return Db::transaction(function () use ($teacher, $data, $context): EducationTeacher {
            $before = $teacher->toArray();
            $teacher->fill($data);
            $teacher->save();
            $teacher = $teacher->refresh();
            $this->dispatchAudit('updated', $teacher, $context, $before, $teacher->toArray());

            return $teacher;
        });
    }

    public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationTeacher
    {
        $teacher = $this->findForWrite($id, $context);
        $data = [
            'status' => $this->normalizeStatus($status),
            'updated_by' => $operatorId,
        ];

        return Db::transaction(function () use ($teacher, $data, $context): EducationTeacher {
            $before = $teacher->toArray();
            $teacher->fill($data);
            $teacher->save();
            $teacher = $teacher->refresh();
            $this->dispatchAudit('status_changed', $teacher, $context, $before, $teacher->toArray());

            return $teacher;
        });
    }

    public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
    {
        $teacher = $this->findForWrite($id, $context);

        return Db::transaction(function () use ($teacher, $context, $operatorId): bool {
            $before = $teacher->toArray();
            $teacher->updated_by = $operatorId;
            $teacher->save();
            $deleted = (bool) $teacher->delete();
            $this->dispatchAudit('deleted', $teacher, $context, $before, ['id' => (int) $teacher->id, 'deleted' => $deleted]);

            return $deleted;
        });
    }

    private function findForWrite(int $id, EducationUserContext $context): EducationTeacher
    {
        $teacher = $this->repository->findById($id);
        if (! $teacher instanceof EducationTeacher) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }

        $this->assertTenantVisible((int) $teacher->tenant_id, $context);
        $this->assertCampusWritable((int) $teacher->tenant_id, (int) $teacher->campus_id, $context);

        return $teacher;
    }

    private function tenantIdForWrite(array $data, EducationUserContext $context): int
    {
        if ($context->platformAccess) {
            $tenantId = isset($data['tenant_id']) && $data['tenant_id'] !== '' ? (int) $data['tenant_id'] : 0;
        } else {
            if ($context->tenantId === null) {
                throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope');
            }
            if (isset($data['tenant_id']) && $data['tenant_id'] !== '' && (int) $data['tenant_id'] !== $context->tenantId) {
                throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope', ['tenant_id' => (int) $data['tenant_id']]);
            }
            $tenantId = $context->tenantId;
        }

        if (! EducationTenant::query()->whereKey($tenantId)->exists()) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'education tenant not found', ['tenant_id' => $tenantId]);
        }

        return $tenantId;
    }

    private function campusIdForWrite(int $tenantId, mixed $campusId, EducationUserContext $context): int
    {
        if ($campusId === null || $campusId === '') {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'campus_id is required');
        }

        $campusId = (int) $campusId;
        $this->assertCampusWritable($tenantId, $campusId, $context);

        return $campusId;
    }

    private function assertTenantVisible(int $tenantId, EducationUserContext $context): void
    {
        if (! $context->platformAccess && $context->tenantId !== $tenantId) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope', ['tenant_id' => $tenantId]);
        }
    }

    private function assertCampusWritable(int $tenantId, int $campusId, EducationUserContext $context): void
    {
        $exists = EducationCampus::query()
            ->where('tenant_id', $tenantId)
            ->whereKey($campusId)
            ->exists();
        if (! $exists) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current tenant', ['campus_id' => $campusId]);
        }

        if ($context->platformAccess || $context->roleCode === EducationRoleCode::TenantAdmin) {
            return;
        }

        if (! $context->canAccessCampus($campusId)) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current user scope', ['campus_id' => $campusId]);
        }
    }

    private function assertUniqueTeacherNo(int $tenantId, string $teacherNo, ?int $exceptId = null): void
    {
        if ($this->repository->existsTeacherNo($tenantId, $teacherNo, $exceptId)) {
            throw new BusinessException(ResultCode::CONFLICT, 'teacher_no already exists', ['teacher_no' => $teacherNo]);
        }
    }

    private function assertTeacherProfile(int $tenantId, mixed $profileId, ?int $exceptId = null): void
    {
        if ($profileId === null || $profileId === '') {
            return;
        }

        $profileId = (int) $profileId;
        $profile = EducationUserProfile::query()->whereKey($profileId)->first();
        if (! $profile instanceof EducationUserProfile || $profile->status !== UserProfileStatus::Enabled) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'teacher profile must be enabled');
        }

        if ((int) $profile->tenant_id !== $tenantId) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'teacher profile is outside current tenant', ['user_profile_id' => $profileId]);
        }

        if ($profile->role_code !== EducationRoleCode::Teacher) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'teacher profile must have teacher role');
        }

        if ($this->repository->existsUserProfile($profileId, $exceptId)) {
            throw new BusinessException(ResultCode::CONFLICT, 'teacher profile already linked', ['user_profile_id' => $profileId]);
        }
    }

    private function normalizeGender(string $gender): string
    {
        if (Gender::tryFrom($gender) === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid gender');
        }

        return $gender;
    }

    private function normalizeStatus(string $status): string
    {
        if (AcademicRecordStatus::tryFrom($status) === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid academic record status');
        }

        return $status;
    }

    private function extractPage(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, (int) ($filters['pageSize'] ?? $filters['page_size'] ?? $filters['per_page'] ?? 15));
        unset($filters['page'], $filters['pageSize'], $filters['page_size'], $filters['per_page']);

        return [$page, $pageSize, $filters];
    }

    private function dispatchAudit(string $action, EducationTeacher $teacher, EducationUserContext $context, array $before, array $after): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'teacher',
            action: 'education.academic.teacher.' . $action,
            businessType: 'teacher',
            businessId: (int) $teacher->id,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: ['tenant_id' => (int) $teacher->tenant_id, 'campus_id' => (int) $teacher->campus_id],
            summary: \sprintf('Teacher %s %s', $teacher->name, $action)
        ));
    }
}
