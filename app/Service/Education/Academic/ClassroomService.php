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
use App\Model\Education\Academic\EducationClassroom;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Academic\AcademicRecordStatus;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\ClassroomRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class ClassroomService
{
    public function __construct(
        private readonly ClassroomRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        [$page, $pageSize, $filters] = $this->extractPage($filters);

        return $this->repository->pageByContext($filters, $page, $pageSize, $context);
    }

    public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationClassroom
    {
        $tenantId = $this->tenantIdForWrite($data, $context);
        $campusId = $this->campusIdForWrite($tenantId, $data['campus_id'] ?? null, $context);
        $code = trim((string) ($data['code'] ?? ''));
        $this->assertUniqueCode($tenantId, $campusId, $code);

        $data['tenant_id'] = $tenantId;
        $data['campus_id'] = $campusId;
        $data['code'] = $code;
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? AcademicRecordStatus::Enabled->value));
        $data['created_by'] = $operatorId;
        $data['updated_by'] = $operatorId;

        return Db::transaction(function () use ($data, $context): EducationClassroom {
            $classroom = $this->repository->create($data)->refresh();
            $this->dispatchAudit('created', $classroom, $context, [], $classroom->toArray());

            return $classroom;
        });
    }

    public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationClassroom
    {
        $classroom = $this->findForWrite($id, $context);
        $tenantId = (int) $classroom->tenant_id;
        $campusId = $this->campusIdForWrite($tenantId, $data['campus_id'] ?? $classroom->campus_id, $context);
        $code = trim((string) ($data['code'] ?? $classroom->code));
        $this->assertUniqueCode($tenantId, $campusId, $code, $id);

        $data['tenant_id'] = $tenantId;
        $data['campus_id'] = $campusId;
        $data['code'] = $code;
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? $classroom->status));
        $data['updated_by'] = $operatorId;

        return Db::transaction(function () use ($classroom, $data, $context): EducationClassroom {
            $before = $classroom->toArray();
            $classroom->fill($data);
            $classroom->save();
            $classroom = $classroom->refresh();
            $this->dispatchAudit('updated', $classroom, $context, $before, $classroom->toArray());

            return $classroom;
        });
    }

    public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationClassroom
    {
        $classroom = $this->findForWrite($id, $context);
        $data = [
            'status' => $this->normalizeStatus($status),
            'updated_by' => $operatorId,
        ];

        return Db::transaction(function () use ($classroom, $data, $context): EducationClassroom {
            $before = $classroom->toArray();
            $classroom->fill($data);
            $classroom->save();
            $classroom = $classroom->refresh();
            $this->dispatchAudit('status_changed', $classroom, $context, $before, $classroom->toArray());

            return $classroom;
        });
    }

    public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
    {
        $classroom = $this->findForWrite($id, $context);

        return Db::transaction(function () use ($classroom, $context, $operatorId): bool {
            $before = $classroom->toArray();
            $classroom->updated_by = $operatorId;
            $classroom->save();
            $deleted = (bool) $classroom->delete();
            $this->dispatchAudit('deleted', $classroom, $context, $before, ['id' => (int) $classroom->id, 'deleted' => $deleted]);

            return $deleted;
        });
    }

    private function findForWrite(int $id, EducationUserContext $context): EducationClassroom
    {
        $classroom = $this->repository->findById($id);
        if (! $classroom instanceof EducationClassroom) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }

        $this->assertTenantVisible((int) $classroom->tenant_id, $context);
        $this->assertCampusWritable((int) $classroom->tenant_id, (int) $classroom->campus_id, $context);

        return $classroom;
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

    private function assertUniqueCode(int $tenantId, int $campusId, string $code, ?int $exceptId = null): void
    {
        if ($this->repository->existsCode($tenantId, $campusId, $code, $exceptId)) {
            throw new BusinessException(ResultCode::CONFLICT, 'classroom code already exists', ['code' => $code]);
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
        $pageSize = max(1, (int) ($filters['page_size'] ?? $filters['per_page'] ?? 15));
        unset($filters['page'], $filters['page_size'], $filters['per_page']);

        return [$page, $pageSize, $filters];
    }

    private function dispatchAudit(string $action, EducationClassroom $classroom, EducationUserContext $context, array $before, array $after): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'classroom',
            action: 'education.academic.classroom.' . $action,
            businessType: 'classroom',
            businessId: (int) $classroom->id,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: ['tenant_id' => (int) $classroom->tenant_id, 'campus_id' => (int) $classroom->campus_id],
            summary: \sprintf('Classroom %s %s', $classroom->name, $action)
        ));
    }
}
