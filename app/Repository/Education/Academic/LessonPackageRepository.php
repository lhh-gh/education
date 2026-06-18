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

use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationLessonPackage;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationLessonPackage>
 */
final class LessonPackageRepository extends IRepository
{
    public function __construct(
        protected readonly EducationLessonPackage $model
    ) {}

    public function pageByContext(array $filters, int $page, int $pageSize, EducationUserContext $context): array
    {
        $query = $this->applyContext($this->getQuery(), $context, $filters);
        $this->applyFilters($query, $filters);
        $query->orderBy('sort_order')->orderByDesc('id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function findScoped(int $id, EducationUserContext $context): ?EducationLessonPackage
    {
        $package = $this->applyContext($this->getQuery(), $context, [])
            ->whereKey($id)
            ->first();

        return $package instanceof EducationLessonPackage ? $package : null;
    }

    public function findEnabledForEnrollment(int $id, int $tenantId, int $campusId, int $courseId): ?EducationLessonPackage
    {
        $package = $this->getQuery()
            ->whereKey($id)
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('course_id', $courseId)
            ->where('status', 'enabled')
            ->first();

        return $package instanceof EducationLessonPackage ? $package : null;
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

    public function hasEnrollmentReferences(int $id, int $tenantId): bool
    {
        return EducationEnrollment::query()
            ->where('tenant_id', $tenantId)
            ->where('lesson_package_id', $id)
            ->exists();
    }

    public function optionsByCourse(int $courseId, EducationUserContext $context): array
    {
        return $this->applyContext($this->getQuery(), $context, [])
            ->where('course_id', $courseId)
            ->where('status', 'enabled')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'tenant_id', 'campus_id', 'course_id', 'code', 'name', 'lesson_units', 'bonus_units', 'total_units', 'sale_price', 'validity_days'])
            ->toArray();
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
            $context->canAccessCampus($campusId)
                ? $query->where('campus_id', $campusId)
                : $query->whereRaw('1 = 0');

            return $query;
        }

        $context->campusIds === []
            ? $query->whereRaw('1 = 0')
            : $query->whereIn('campus_id', $context->campusIds);

        return $query;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (isset($filters['course_id']) && $filters['course_id'] !== '') {
            $query->where('course_id', (int) $filters['course_id']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
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
