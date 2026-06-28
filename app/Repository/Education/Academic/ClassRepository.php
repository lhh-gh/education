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

use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationLesson;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationClass>
 */
final class ClassRepository extends IRepository
{
    public function __construct(
        protected readonly EducationClass $model
    ) {}

    public function pageByContext(array $filters, int $page, int $pageSize, EducationUserContext $context): array
    {
        $query = $this->applyContext($this->getQuery(), $context, $filters);
        $this->applyFilters($query, $filters);
        $query->orderByDesc('id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function findScoped(int $id, EducationUserContext $context): ?EducationClass
    {
        $class = $this->applyContext($this->getQuery(), $context, [])
            ->whereKey($id)
            ->first();

        return $class instanceof EducationClass ? $class : null;
    }

    public function findEnabledForScheduling(int $id, int $tenantId, int $campusId): ?EducationClass
    {
        $class = $this->getQuery()
            ->whereKey($id)
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('status', 'enabled')
            ->first();

        return $class instanceof EducationClass ? $class : null;
    }

    public function existsCode(int $tenantId, int $campusId, string $code, ?int $excludeId = null): bool
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('code', $code)
            ->when($excludeId !== null, static fn (Builder $query) => $query->where('id', '<>', $excludeId))
            ->exists();
    }

    public function hasLessonReferences(int $id, int $tenantId): bool
    {
        return EducationLesson::query()
            ->where('tenant_id', $tenantId)
            ->where('class_id', $id)
            ->exists();
    }

    public function options(array $filters, EducationUserContext $context): array
    {
        $query = $this->applyContext($this->getQuery(), $context, $filters);
        $this->applyFilters($query, $filters);

        return $query
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'tenant_id', 'campus_id', 'course_id', 'code', 'name', 'status'])
            ->toArray();
    }

    private function applyContext(Builder $query, EducationUserContext $context, array $filters): Builder
    {
        (new EducationScopeQuery())->applyTenantCampus($query, $filters, $context);

        return $query;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        foreach (['course_id', 'main_teacher_id', 'classroom_id'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, (int) $filters[$column]);
            }
        }
        foreach (['status', 'class_type'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }
        if (! isset($filters['keyword']) || $filters['keyword'] === '') {
            return;
        }

        $keyword = '%' . $filters['keyword'] . '%';
        $query->where(static function (Builder $query) use ($keyword): void {
            $query->where('code', 'like', $keyword)
                ->orWhere('name', 'like', $keyword);
        });
    }
}
