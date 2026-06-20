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
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
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
        (new EducationScopeQuery())->applyTenantCampus($query, $filters, $context);

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
