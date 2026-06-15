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
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Academic\AcademicRecordStatus;
use App\Model\Enums\Education\Academic\Gender;
use App\Model\Enums\Education\Academic\GuardianRelation;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\GuardianRepository;
use App\Repository\Education\Academic\StudentGuardianRepository;
use App\Repository\Education\Academic\StudentRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class StudentService
{
    public function __construct(
        private readonly StudentRepository $repository,
        private readonly GuardianRepository $guardianRepository,
        private readonly StudentGuardianRepository $studentGuardianRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        [$page, $pageSize, $filters] = $this->extractPage($filters);

        $result = $this->repository->pageByContext($filters, $page, $pageSize, $context);
        $ids = array_map(static fn (array $row): int => (int) $row['id'], $result['list']);
        $counts = $ids === [] ? [] : EducationStudentGuardian::query()
            ->selectRaw('student_id, COUNT(*) as aggregate')
            ->whereIn('student_id', $ids)
            ->groupBy('student_id')
            ->pluck('aggregate', 'student_id')
            ->all();
        foreach ($result['list'] as &$row) {
            $row['guardian_count'] = (int) ($counts[$row['id']] ?? 0);
        }
        unset($row);

        return $result;
    }

    public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationStudent
    {
        $tenantId = $this->tenantIdForWrite($data, $context);
        $campusId = $this->campusIdForWrite($tenantId, $data['campus_id'] ?? null, $context);
        $studentNo = trim((string) ($data['student_no'] ?? ''));
        $this->assertUniqueStudentNo($tenantId, $studentNo);

        $data['tenant_id'] = $tenantId;
        $data['campus_id'] = $campusId;
        $data['student_no'] = $studentNo;
        $data['gender'] = $this->normalizeGender((string) ($data['gender'] ?? Gender::Unknown->value));
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? AcademicRecordStatus::Enabled->value));
        $data['created_by'] = $operatorId;
        $data['updated_by'] = $operatorId;

        return Db::transaction(function () use ($data, $context): EducationStudent {
            $student = $this->repository->create($data)->refresh();
            $this->dispatchStudentAudit('created', $student, $context, [], $student->toArray());

            return $student;
        });
    }

    public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationStudent
    {
        $student = $this->findForWrite($id, $context);
        $tenantId = (int) $student->tenant_id;
        $campusId = $this->campusIdForWrite($tenantId, $data['campus_id'] ?? $student->campus_id, $context);
        $studentNo = trim((string) ($data['student_no'] ?? $student->student_no));
        $this->assertUniqueStudentNo($tenantId, $studentNo, $id);

        $data['tenant_id'] = $tenantId;
        $data['campus_id'] = $campusId;
        $data['student_no'] = $studentNo;
        $data['gender'] = $this->normalizeGender((string) ($data['gender'] ?? $student->gender));
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? $student->status));
        $data['updated_by'] = $operatorId;

        return Db::transaction(function () use ($student, $data, $context): EducationStudent {
            $before = $student->toArray();
            $student->fill($data);
            $student->save();
            $student = $student->refresh();
            $this->dispatchStudentAudit('updated', $student, $context, $before, $student->toArray());

            return $student;
        });
    }

    public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationStudent
    {
        $student = $this->findForWrite($id, $context);
        $data = [
            'status' => $this->normalizeStatus($status),
            'updated_by' => $operatorId,
        ];

        return Db::transaction(function () use ($student, $data, $context): EducationStudent {
            $before = $student->toArray();
            $student->fill($data);
            $student->save();
            $student = $student->refresh();
            $this->dispatchStudentAudit('status_changed', $student, $context, $before, $student->toArray());

            return $student;
        });
    }

    public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
    {
        $student = $this->findForWrite($id, $context);

        return Db::transaction(function () use ($student, $context, $operatorId): bool {
            $before = $student->toArray();
            $student->updated_by = $operatorId;
            $student->save();
            $deleted = (bool) $student->delete();
            $this->dispatchStudentAudit('deleted', $student, $context, $before, ['id' => (int) $student->id, 'deleted' => $deleted]);

            return $deleted;
        });
    }

    public function guardians(int $id, EducationUserContext $context): array
    {
        $student = $this->findForRead($id, $context);

        return $this->studentGuardianRepository->listByStudent((int) $student->id, $context);
    }

    public function saveGuardians(int $id, array $relations, EducationUserContext $context, ?int $operatorId): array
    {
        $student = $this->findForWrite($id, $context);
        $relations = $this->normalizeGuardianRelations($relations);
        $this->assertGuardiansVisible($relations, $context);

        return Db::transaction(function () use ($student, $relations, $context, $operatorId): array {
            $before = $this->studentGuardianRepository->listByStudent((int) $student->id, $context);
            $this->studentGuardianRepository->replaceForStudent((int) $student->tenant_id, (int) $student->id, $relations, $operatorId);
            $after = $this->studentGuardianRepository->listByStudent((int) $student->id, $context);
            $this->eventDispatcher->dispatch(new EducationAuditEvent(
                module: 'academic',
                resource: 'student_guardian',
                action: 'education.academic.student_guardian.saved',
                businessType: 'student',
                businessId: (int) $student->id,
                context: $context,
                beforeSnapshot: ['relations' => $before],
                afterSnapshot: ['relations' => $after],
                metadata: ['tenant_id' => (int) $student->tenant_id, 'campus_id' => (int) $student->campus_id],
                summary: \sprintf('Student %s guardians saved', $student->name)
            ));

            return $after;
        });
    }

    private function findForRead(int $id, EducationUserContext $context): EducationStudent
    {
        $student = $this->repository->findVisibleById($id, $context);
        if (! $student instanceof EducationStudent) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }

        return $student;
    }

    private function findForWrite(int $id, EducationUserContext $context): EducationStudent
    {
        $student = $this->repository->findById($id);
        if (! $student instanceof EducationStudent) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }

        $this->assertTenantVisible((int) $student->tenant_id, $context);
        $this->assertCampusWritable((int) $student->tenant_id, (int) $student->campus_id, $context);

        return $student;
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

    private function assertUniqueStudentNo(int $tenantId, string $studentNo, ?int $exceptId = null): void
    {
        if ($this->repository->existsStudentNo($tenantId, $studentNo, $exceptId)) {
            throw new BusinessException(ResultCode::CONFLICT, 'student_no already exists', ['student_no' => $studentNo]);
        }
    }

    private function assertGuardiansVisible(array $relations, EducationUserContext $context): void
    {
        $guardianIds = array_values(array_unique(array_map(static fn (array $relation): int => (int) $relation['guardian_id'], $relations)));
        $guardians = $this->guardianRepository->findManyVisible($guardianIds, $context);

        foreach ($guardianIds as $guardianId) {
            if (! isset($guardians[$guardianId])) {
                throw new BusinessException(ResultCode::FORBIDDEN, 'guardian is outside current tenant', ['guardian_id' => $guardianId]);
            }
        }
    }

    private function normalizeGuardianRelations(array $relations): array
    {
        $normalized = [];
        $primaryIndex = null;

        foreach (array_values($relations) as $index => $relation) {
            $relationValue = (string) ($relation['relation'] ?? GuardianRelation::Guardian->value);
            if (GuardianRelation::tryFrom($relationValue) === null) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid guardian relation');
            }

            $isPrimary = (bool) ($relation['is_primary'] ?? false);
            if ($isPrimary && $primaryIndex === null) {
                $primaryIndex = $index;
            }

            $normalized[] = [
                'guardian_id' => (int) $relation['guardian_id'],
                'relation' => $relationValue,
                'is_primary' => false,
                'can_receive_notice' => (bool) ($relation['can_receive_notice'] ?? true),
                'can_submit_leave' => (bool) ($relation['can_submit_leave'] ?? true),
                'remark' => $relation['remark'] ?? null,
            ];
        }

        if ($normalized !== []) {
            $normalized[$primaryIndex ?? 0]['is_primary'] = true;
        }

        return $normalized;
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

    private function dispatchStudentAudit(string $action, EducationStudent $student, EducationUserContext $context, array $before, array $after): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'student',
            action: 'education.academic.student.' . $action,
            businessType: 'student',
            businessId: (int) $student->id,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: ['tenant_id' => (int) $student->tenant_id, 'campus_id' => (int) $student->campus_id],
            summary: \sprintf('Student %s %s', $student->name, $action)
        ));
    }
}
