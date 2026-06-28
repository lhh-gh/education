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
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Academic\AcademicRecordStatus;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\GuardianRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class GuardianService
{
    public function __construct(
        private readonly GuardianRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        [$page, $pageSize, $filters] = $this->extractPage($filters);

        $result = $this->repository->pageByContext($filters, $page, $pageSize, $context);
        $ids = array_map(static fn (array $row): int => (int) $row['id'], $result['list']);
        $counts = $this->studentCounts($ids, $filters, $context);
        foreach ($result['list'] as &$row) {
            $row['student_count'] = (int) ($counts[$row['id']] ?? 0);
        }
        unset($row);

        return $result;
    }

    public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationGuardian
    {
        $tenantId = $this->tenantIdForWrite($data, $context);
        $mobile = trim((string) ($data['mobile'] ?? ''));
        $this->assertUniqueMobile($tenantId, $mobile);

        $data['tenant_id'] = $tenantId;
        $data['mobile'] = $mobile;
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? AcademicRecordStatus::Enabled->value));
        $data['created_by'] = $operatorId;
        $data['updated_by'] = $operatorId;

        return Db::transaction(function () use ($data, $context): EducationGuardian {
            $guardian = $this->repository->create($data)->refresh();
            $this->dispatchAudit('created', $guardian, $context, [], $guardian->toArray());

            return $guardian;
        });
    }

    public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationGuardian
    {
        $guardian = $this->findForWrite($id, $context);
        $tenantId = (int) $guardian->tenant_id;
        $mobile = trim((string) ($data['mobile'] ?? $guardian->mobile));
        $this->assertUniqueMobile($tenantId, $mobile, $id);

        $data['tenant_id'] = $tenantId;
        $data['mobile'] = $mobile;
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? $guardian->status));
        $data['updated_by'] = $operatorId;

        return Db::transaction(function () use ($guardian, $data, $context): EducationGuardian {
            $before = $guardian->toArray();
            $guardian->fill($data);
            $guardian->save();
            $guardian = $guardian->refresh();
            $this->dispatchAudit('updated', $guardian, $context, $before, $guardian->toArray());

            return $guardian;
        });
    }

    public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationGuardian
    {
        $guardian = $this->findForWrite($id, $context);
        $data = [
            'status' => $this->normalizeStatus($status),
            'updated_by' => $operatorId,
        ];

        return Db::transaction(function () use ($guardian, $data, $context): EducationGuardian {
            $before = $guardian->toArray();
            $guardian->fill($data);
            $guardian->save();
            $guardian = $guardian->refresh();
            $this->dispatchAudit('status_changed', $guardian, $context, $before, $guardian->toArray());

            return $guardian;
        });
    }

    public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
    {
        $guardian = $this->findForWrite($id, $context);

        return Db::transaction(function () use ($guardian, $context, $operatorId): bool {
            $before = $guardian->toArray();
            $guardian->updated_by = $operatorId;
            $guardian->save();
            $deleted = (bool) $guardian->delete();
            $this->dispatchAudit('deleted', $guardian, $context, $before, ['id' => (int) $guardian->id, 'deleted' => $deleted]);

            return $deleted;
        });
    }

    /**
     * @param int[] $guardianIds
     * @return array<int, int|string>
     */
    private function studentCounts(array $guardianIds, array $filters, EducationUserContext $context): array
    {
        if ($guardianIds === []) {
            return [];
        }

        $query = EducationStudentGuardian::query()
            ->selectRaw('edu_student_guardians.guardian_id, COUNT(*) as aggregate')
            ->join('edu_students', 'edu_students.id', '=', 'edu_student_guardians.student_id')
            ->whereIn('edu_student_guardians.guardian_id', $guardianIds)
            ->whereNull('edu_students.deleted_at')
            ->groupBy('edu_student_guardians.guardian_id');

        $scope = new EducationScopeQuery();
        $tenantId = $scope->tenantId($filters, $context);
        if ($tenantId === null && ! $context->platformAccess) {
            $query->whereRaw('1 = 0');
        }
        if ($tenantId !== null) {
            $query->where('edu_student_guardians.tenant_id', $tenantId)
                ->where('edu_students.tenant_id', $tenantId);
        }

        $campusId = $scope->campusId($filters, $context);
        if ($campusId !== null) {
            if (! $context->platformAccess && $context->roleCode !== EducationRoleCode::TenantAdmin && ! $context->canAccessCampus($campusId)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('edu_students.campus_id', $campusId);
            }
        } elseif (! $context->platformAccess && $context->roleCode !== EducationRoleCode::TenantAdmin) {
            $context->campusIds === []
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('edu_students.campus_id', $context->campusIds);
        }

        return $query->pluck('aggregate', 'guardian_id')->all();
    }

    private function findForWrite(int $id, EducationUserContext $context): EducationGuardian
    {
        $guardian = $this->repository->findById($id);
        if (! $guardian instanceof EducationGuardian) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }

        if (! $context->platformAccess && $context->tenantId !== (int) $guardian->tenant_id) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope', ['tenant_id' => (int) $guardian->tenant_id]);
        }

        return $guardian;
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

    private function assertUniqueMobile(int $tenantId, string $mobile, ?int $exceptId = null): void
    {
        if ($this->repository->existsMobile($tenantId, $mobile, $exceptId)) {
            throw new BusinessException(ResultCode::CONFLICT, 'guardian mobile already exists', ['mobile' => $mobile]);
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
        $pageSize = max(1, (int) ($filters['pageSize'] ?? $filters['page_size'] ?? $filters['per_page'] ?? 15));
        unset($filters['page'], $filters['pageSize'], $filters['page_size'], $filters['per_page']);

        return [$page, $pageSize, $filters];
    }

    private function dispatchAudit(string $action, EducationGuardian $guardian, EducationUserContext $context, array $before, array $after): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'guardian',
            action: 'education.academic.guardian.' . $action,
            businessType: 'guardian',
            businessId: (int) $guardian->id,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: ['tenant_id' => (int) $guardian->tenant_id],
            summary: \sprintf('Guardian %s %s', $guardian->name, $action)
        ));
    }
}
