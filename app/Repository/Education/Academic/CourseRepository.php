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

use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationLessonPackage;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationCourse>
 */
final class CourseRepository extends IRepository
{
    public function __construct(
        protected readonly EducationCourse $model
    ) {}

    public function pageByContext(array $filters, int $page, int $pageSize, EducationUserContext $context): array
    {
        $query = $this->applyContext($this->getQuery(), $context, $filters);
        $this->applyFilters($query, $filters, ['code', 'name', 'category', 'subject']);
        $query->orderBy('sort_order')->orderByDesc('id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function findScoped(int $id, EducationUserContext $context): ?EducationCourse
    {
        $course = $this->applyContext($this->getQuery(), $context, [])
            ->whereKey($id)
            ->first();

        return $course instanceof EducationCourse ? $course : null;
    }

    public function findEnabledForEnrollment(int $id, int $tenantId, int $campusId): ?EducationCourse
    {
        $course = $this->getQuery()
            ->whereKey($id)
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('status', 'enabled')
            ->first();

        return $course instanceof EducationCourse ? $course : null;
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

    public function hasBusinessReferences(int $id, int $tenantId): bool
    {
        return EducationLessonPackage::query()
            ->where('tenant_id', $tenantId)
            ->where('course_id', $id)
            ->exists()
            || EducationEnrollment::query()
                ->where('tenant_id', $tenantId)
                ->where('course_id', $id)
                ->exists();
    }

    public function options(array $filters, EducationUserContext $context): array
    {
        $query = $this->applyContext($this->getQuery(), $context, $filters);
        $this->applyFilters($query, $filters, ['code', 'name']);

        return $query
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'tenant_id', 'campus_id', 'code', 'name', 'status'])
            ->toArray();
    }

    private function applyContext(Builder $query, EducationUserContext $context, array $filters): Builder
    {
        (new EducationScopeQuery())->applyTenantCampus($query, $filters, $context);

        return $query;
    }

    /**
     * @param list<string> $keywordColumns
     */
    private function applyFilters(Builder $query, array $filters, array $keywordColumns): void
    {
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (! isset($filters['keyword']) || $filters['keyword'] === '') {
            return;
        }

        $keyword = '%' . $filters['keyword'] . '%';
        $query->where(static function (Builder $query) use ($keyword, $keywordColumns): void {
            foreach ($keywordColumns as $index => $column) {
                $index === 0
                    ? $query->where($column, 'like', $keyword)
                    : $query->orWhere($column, 'like', $keyword);
            }
        });
    }
}
