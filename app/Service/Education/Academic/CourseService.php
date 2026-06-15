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
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Academic\AcademicRecordStatus;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\CourseRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class CourseService
{
    public function __construct(
        private readonly CourseRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        [$page, $pageSize, $filters] = $this->extractPage($filters);

        return $this->repository->pageByContext($filters, $page, $pageSize, $context);
    }

    public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationCourse
    {
        $tenantId = $this->tenantIdForWrite($data, $context);
        $campusId = $this->campusIdForWrite($tenantId, $data['campus_id'] ?? null, $context);
        $code = trim((string) ($data['code'] ?? ''));
        $this->assertUniqueCode($tenantId, $campusId, $code);

        $data['tenant_id'] = $tenantId;
        $data['campus_id'] = $campusId;
        $data['code'] = $code;
        $data['unit_minutes'] = (int) ($data['unit_minutes'] ?? 60);
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? AcademicRecordStatus::Enabled->value));
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['created_by'] = $operatorId;
        $data['updated_by'] = $operatorId;

        return Db::transaction(function () use ($data, $context): EducationCourse {
            $course = $this->repository->create($data)->refresh();
            $this->dispatchAudit('created', $course, $context, [], $course->toArray());

            return $course;
        });
    }

    public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationCourse
    {
        $course = $this->findForWrite($id, $context);
        $tenantId = (int) $course->tenant_id;
        $campusId = $this->campusIdForWrite($tenantId, $data['campus_id'] ?? $course->campus_id, $context);
        $code = trim((string) ($data['code'] ?? $course->code));
        $this->assertUniqueCode($tenantId, $campusId, $code, $id);

        $data['tenant_id'] = $tenantId;
        $data['campus_id'] = $campusId;
        $data['code'] = $code;
        $data['unit_minutes'] = (int) ($data['unit_minutes'] ?? $course->unit_minutes ?? 60);
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? $course->status));
        $data['sort_order'] = (int) ($data['sort_order'] ?? $course->sort_order ?? 0);
        $data['updated_by'] = $operatorId;

        return Db::transaction(function () use ($course, $data, $context): EducationCourse {
            $before = $course->toArray();
            $course->fill($data);
            $course->save();
            $course = $course->refresh();
            $this->dispatchAudit('updated', $course, $context, $before, $course->toArray());

            return $course;
        });
    }

    public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationCourse
    {
        $course = $this->findForWrite($id, $context);
        $data = [
            'status' => $this->normalizeStatus($status),
            'updated_by' => $operatorId,
        ];

        return Db::transaction(function () use ($course, $data, $context): EducationCourse {
            $before = $course->toArray();
            $course->fill($data);
            $course->save();
            $course = $course->refresh();
            $this->dispatchAudit('status_changed', $course, $context, $before, $course->toArray());

            return $course;
        });
    }

    public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
    {
        $course = $this->findForWrite($id, $context);
        if ($this->repository->hasBusinessReferences((int) $course->id, (int) $course->tenant_id)) {
            throw new BusinessException(ResultCode::CONFLICT, 'course is referenced by packages or enrollments', ['id' => (int) $course->id]);
        }

        return Db::transaction(function () use ($course, $context, $operatorId): bool {
            $before = $course->toArray();
            $course->updated_by = $operatorId;
            $course->save();
            $deleted = (bool) $course->delete();
            $this->dispatchAudit('deleted', $course, $context, $before, ['id' => (int) $course->id, 'deleted' => $deleted]);

            return $deleted;
        });
    }

    public function options(array $filters, EducationUserContext $context): array
    {
        return $this->repository->options($filters, $context);
    }

    private function findForWrite(int $id, EducationUserContext $context): EducationCourse
    {
        $course = $this->repository->findById($id);
        if (! $course instanceof EducationCourse) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'course not found in current context', ['id' => $id]);
        }

        $this->assertTenantVisible((int) $course->tenant_id, $context);
        $this->assertCampusWritable((int) $course->tenant_id, (int) $course->campus_id, $context);

        return $course;
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

    private function assertUniqueCode(int $tenantId, int $campusId, string $code, ?int $excludeId = null): void
    {
        if ($this->repository->existsCode($tenantId, $campusId, $code, $excludeId)) {
            throw new BusinessException(ResultCode::CONFLICT, 'course code already exists', ['campus_id' => $campusId, 'code' => $code]);
        }
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
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? $filters['page_size'] ?? $filters['per_page'] ?? 15)));
        unset($filters['page'], $filters['pageSize'], $filters['page_size'], $filters['per_page']);

        return [$page, $pageSize, $filters];
    }

    private function dispatchAudit(string $action, EducationCourse $course, EducationUserContext $context, array $before, array $after): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'course',
            action: 'education.academic.course.' . $action,
            businessType: 'course',
            businessId: (int) $course->id,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: ['tenant_id' => (int) $course->tenant_id, 'campus_id' => (int) $course->campus_id],
            summary: \sprintf('Course %s %s', $course->name, $action)
        ));
    }
}
