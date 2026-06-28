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

use App\Model\Education\Academic\EducationTeacher;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationTeacher>
 */
final class TeacherRepository extends IRepository
{
    public function __construct(
        protected readonly EducationTeacher $model
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
                $query->where('teacher_no', 'like', $keyword)
                    ->orWhere('name', 'like', $keyword)
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

    public function findVisibleById(int $id, EducationUserContext $context): ?EducationTeacher
    {
        $teacher = $this->applyContext($this->getQuery(), $context, [])
            ->whereKey($id)
            ->first();

        return $teacher instanceof EducationTeacher ? $teacher : null;
    }

    public function existsTeacherNo(int $tenantId, string $teacherNo, ?int $exceptId = null): bool
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('teacher_no', $teacherNo)
            ->when($exceptId !== null, static fn (Builder $query) => $query->where('id', '<>', $exceptId))
            ->exists();
    }

    public function existsUserProfile(int $userProfileId, ?int $exceptId = null): bool
    {
        return $this->getQuery()
            ->where('user_profile_id', $userProfileId)
            ->when($exceptId !== null, static fn (Builder $query) => $query->where('id', '<>', $exceptId))
            ->exists();
    }

    private function applyContext(Builder $query, EducationUserContext $context, array $filters): Builder
    {
        (new EducationScopeQuery())->applyTenantCampus($query, $filters, $context);

        return $query;
    }
}
