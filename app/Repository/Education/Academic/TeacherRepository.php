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
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\IRepository;
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
            return $query->whereRaw('1 = 0');
        }

        $query->where('tenant_id', $context->tenantId);
        $campusId = isset($filters['campus_id']) && $filters['campus_id'] !== '' ? (int) $filters['campus_id'] : null;

        if ($context->roleCode === EducationRoleCode::TenantAdmin) {
            return $campusId === null ? $query : $query->where('campus_id', $campusId);
        }

        if ($campusId !== null) {
            return $context->canAccessCampus($campusId)
                ? $query->where('campus_id', $campusId)
                : $query->whereRaw('1 = 0');
        }

        return $context->campusIds === []
            ? $query->whereRaw('1 = 0')
            : $query->whereIn('campus_id', $context->campusIds);
    }
}
