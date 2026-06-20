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

use App\Model\Education\Academic\EducationStudent;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationStudent>
 */
final class StudentRepository extends IRepository
{
    public function __construct(
        protected readonly EducationStudent $model
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

    public function findVisibleById(int $id, EducationUserContext $context): ?EducationStudent
    {
        $student = $this->applyContext($this->getQuery(), $context, [])
            ->whereKey($id)
            ->first();

        return $student instanceof EducationStudent ? $student : null;
    }

    public function existsStudentNo(int $tenantId, string $studentNo, ?int $exceptId = null): bool
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('student_no', $studentNo)
            ->when($exceptId !== null, static fn (Builder $query) => $query->where('id', '<>', $exceptId))
            ->exists();
    }

    private function applyContext(Builder $query, EducationUserContext $context, array $filters): Builder
    {
        (new EducationScopeQuery())->applyTenantCampus($query, $filters, $context);

        return $query;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (! isset($filters['keyword']) || $filters['keyword'] === '') {
            return;
        }

        $keyword = '%' . $filters['keyword'] . '%';
        $query->where(static function (Builder $query) use ($keyword): void {
            $query->where('student_no', 'like', $keyword)
                ->orWhere('name', 'like', $keyword)
                ->orWhere('mobile', 'like', $keyword);
        });
    }
}
