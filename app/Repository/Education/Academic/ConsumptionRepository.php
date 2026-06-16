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

use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationLessonConsumption>
 */
final class ConsumptionRepository extends IRepository
{
    public function __construct(
        protected readonly EducationLessonConsumption $model
    ) {}

    public function page(array $filters = [], mixed $page = null, ?int $pageSize = null): array
    {
        if (! $page instanceof EducationUserContext) {
            return parent::page($filters, \is_int($page) ? $page : null, $pageSize);
        }
        $context = $page;
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? $filters['page_size'] ?? 15)));
        unset($filters['page'], $filters['pageSize'], $filters['page_size']);
        $query = $this->applyContext($this->getQuery(), $context, $filters);
        $this->applyFilters($query, $filters);
        $query->orderByDesc('created_at')->orderByDesc('id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function findScoped(int $id, EducationUserContext $context): ?EducationLessonConsumption
    {
        $row = $this->applyContext($this->getQuery(), $context, [])->whereKey($id)->first();

        return $row instanceof EducationLessonConsumption ? $row : null;
    }

    public function findActiveForRollback(int $id, EducationUserContext $context): ?EducationLessonConsumption
    {
        $row = $this->applyContext($this->getQuery(), $context, [])
            ->whereKey($id)
            ->where('source_type', 'attendance')
            ->first();

        return $row instanceof EducationLessonConsumption ? $row : null;
    }

    public function createConsumption(array $data): EducationLessonConsumption
    {
        return $this->create($data);
    }

    public function createRollback(array $data): EducationLessonConsumption
    {
        return $this->create($data);
    }

    public function hasRollback(int $originalConsumptionId, int $tenantId): bool
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('original_consumption_id', $originalConsumptionId)
            ->where('source_type', 'rollback')
            ->exists();
    }

    public function nextConsumptionNo(int $tenantId, int $campusId): string
    {
        return 'CON' . date('YmdHis') . str_pad((string) $tenantId, 4, '0', \STR_PAD_LEFT) . str_pad((string) $campusId, 4, '0', \STR_PAD_LEFT) . random_int(1000, 9999);
    }

    private function applyContext(Builder $query, EducationUserContext $context, array $filters): Builder
    {
        if ($context->platformAccess) {
            if (isset($filters['tenant_id']) && $filters['tenant_id'] !== '') {
                $query->where('tenant_id', (int) $filters['tenant_id']);
            }
            if (isset($filters['campus_id']) && $filters['campus_id'] !== '') {
                $query->where('campus_id', (int) $filters['campus_id']);
            }

            return $query;
        }
        if ($context->tenantId === null) {
            $query->whereRaw('1 = 0');

            return $query;
        }
        $query->where('tenant_id', $context->tenantId);
        $campusId = isset($filters['campus_id']) && $filters['campus_id'] !== '' ? (int) $filters['campus_id'] : null;
        if ($context->roleCode === EducationRoleCode::TenantAdmin) {
            if ($campusId !== null) {
                $query->where('campus_id', $campusId);
            }

            return $query;
        }
        if ($campusId !== null) {
            $context->canAccessCampus($campusId) ? $query->where('campus_id', $campusId) : $query->whereRaw('1 = 0');

            return $query;
        }

        $context->campusIds === [] ? $query->whereRaw('1 = 0') : $query->whereIn('campus_id', $context->campusIds);

        return $query;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        foreach (['account_id', 'student_id', 'course_id', 'lesson_id'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, (int) $filters[$column]);
            }
        }
        foreach (['source_type', 'status'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }
        if (isset($filters['keyword']) && $filters['keyword'] !== '') {
            $keyword = '%' . $filters['keyword'] . '%';
            $query->where(static function (Builder $query) use ($keyword): void {
                $query->where('consumption_no', 'like', $keyword)
                    ->orWhere('reason', 'like', $keyword);
            });
        }
    }
}
