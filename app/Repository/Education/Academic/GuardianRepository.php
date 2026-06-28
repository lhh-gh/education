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

namespace App\Repository\Education\Academic;

use App\Model\Education\Academic\EducationGuardian;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationGuardian>
 */
final class GuardianRepository extends IRepository
{
    public function __construct(
        protected readonly EducationGuardian $model
    ) {}

    public function pageByContext(array $filters, int $page, int $pageSize, EducationUserContext $context): array
    {
        $query = $this->applyContext($this->getQuery(), $context, $filters);
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['keyword']) && $filters['keyword'] !== '') {
            $keyword = '%' . $filters['keyword'] . '%';
            $query->where(static function (Builder $query) use ($keyword): void {
                $query->where('name', 'like', $keyword)
                    ->orWhere('mobile', 'like', $keyword);
            });
        }
        $query->orderByDesc('id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function findVisibleById(int $id, EducationUserContext $context): ?EducationGuardian
    {
        $guardian = $this->applyContext($this->getQuery(), $context, [])
            ->whereKey($id)
            ->first();

        return $guardian instanceof EducationGuardian ? $guardian : null;
    }

    public function existsMobile(int $tenantId, string $mobile, ?int $exceptId = null): bool
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('mobile', $mobile)
            ->when($exceptId !== null, static fn (Builder $query) => $query->where('id', '<>', $exceptId))
            ->exists();
    }

    /**
     * @param int[] $ids
     * @return array<int, EducationGuardian>
     */
    public function findManyVisible(array $ids, EducationUserContext $context): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }

        return $this->applyContext($this->getQuery(), $context, [])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id')
            ->all();
    }

    private function applyContext(Builder $query, EducationUserContext $context, array $filters): Builder
    {
        $scope = new EducationScopeQuery();
        $tenantId = $scope->tenantId($filters, $context);
        if ($tenantId === null && ! $context->platformAccess) {
            $query->whereRaw('1 = 0');

            return $query;
        }
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $campusId = $scope->campusId($filters, $context);
        if ($campusId !== null) {
            if (! $context->platformAccess && $context->roleCode !== EducationRoleCode::TenantAdmin && ! $context->canAccessCampus($campusId)) {
                $query->whereRaw('1 = 0');

                return $query;
            }

            return $this->whereStudentCampus($query, $tenantId, [$campusId]);
        }

        if ($context->platformAccess || $context->roleCode === EducationRoleCode::TenantAdmin) {
            return $query;
        }

        if ($context->campusIds === []) {
            $query->whereRaw('1 = 0');

            return $query;
        }

        return $this->whereStudentCampus($query, $tenantId, $context->campusIds);
    }

    /**
     * @param int[] $campusIds
     */
    private function whereStudentCampus(Builder $query, ?int $tenantId, array $campusIds): Builder
    {
        return $query->whereHas('studentRelations', static function (Builder $relationQuery) use ($tenantId, $campusIds): void {
            if ($tenantId !== null) {
                $relationQuery->where('tenant_id', $tenantId);
            }
            $relationQuery->whereHas('student', static function (Builder $studentQuery) use ($tenantId, $campusIds): void {
                if ($tenantId !== null) {
                    $studentQuery->where('tenant_id', $tenantId);
                }
                $studentQuery->whereIn('campus_id', $campusIds);
            });
        });
    }
}
