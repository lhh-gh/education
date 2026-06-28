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

use App\Model\Education\Academic\EducationClassroom;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationClassroom>
 */
final class ClassroomRepository extends IRepository
{
    public function __construct(
        protected readonly EducationClassroom $model
    ) {}

    public function pageByContext(array $filters, int $page, int $pageSize, EducationUserContext $context): array
    {
        $query = $this->applyContext($this->getQuery(), $context, $filters);
        $this->applyFilters($query, $filters, ['code', 'name', 'location']);
        $query->orderBy('sort_order')->orderByDesc('id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function findVisibleById(int $id, EducationUserContext $context): ?EducationClassroom
    {
        $classroom = $this->applyContext($this->getQuery(), $context, [])
            ->whereKey($id)
            ->first();

        return $classroom instanceof EducationClassroom ? $classroom : null;
    }

    public function existsCode(int $tenantId, int $campusId, string $code, ?int $exceptId = null): bool
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('code', $code)
            ->when($exceptId !== null, static fn (Builder $query) => $query->where('id', '<>', $exceptId))
            ->exists();
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
