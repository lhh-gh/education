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

use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationStudentGuardian>
 */
final class StudentGuardianRepository extends IRepository
{
    public function __construct(
        protected readonly EducationStudentGuardian $model
    ) {}

    public function listByStudent(int $studentId, EducationUserContext $context): array
    {
        $query = $this->getQuery()
            ->with('guardian')
            ->where('student_id', $studentId)
            ->orderByDesc('is_primary')
            ->orderBy('id');
        $this->applyContext($query, $context);

        return $query->get()
            ->map(static function (EducationStudentGuardian $relation): array {
                $row = $relation->toArray();
                $guardian = $relation->guardian;
                $row['guardian_name'] = $guardian?->name;
                $row['guardian_mobile'] = $guardian?->mobile;
                unset($row['guardian']);

                return $row;
            })
            ->all();
    }

    public function replaceForStudent(int $tenantId, int $studentId, array $relations, ?int $operatorId): void
    {
        $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $studentId)
            ->forceDelete();

        foreach ($relations as $relation) {
            $this->create([
                'tenant_id' => $tenantId,
                'student_id' => $studentId,
                'guardian_id' => (int) $relation['guardian_id'],
                'relation' => (string) $relation['relation'],
                'is_primary' => (bool) $relation['is_primary'],
                'can_receive_notice' => (bool) $relation['can_receive_notice'],
                'can_submit_leave' => (bool) $relation['can_submit_leave'],
                'remark' => $relation['remark'] ?? null,
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ]);
        }
    }

    /**
     * @return int[]
     */
    public function guardianIdsForStudent(int $tenantId, int $studentId): array
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $studentId)
            ->pluck('guardian_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    private function applyContext(mixed $query, EducationUserContext $context): void
    {
        $scope = new EducationScopeQuery();
        $tenantId = $scope->tenantId([], $context);
        if ($tenantId === null && ! $context->platformAccess) {
            $query->whereRaw('1 = 0');

            return;
        }
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $campusId = $scope->campusId([], $context);
        if ($campusId !== null) {
            if (! $context->platformAccess && $context->roleCode !== EducationRoleCode::TenantAdmin && ! $context->canAccessCampus($campusId)) {
                $query->whereRaw('1 = 0');

                return;
            }
            $this->whereStudentCampus($query, $tenantId, [$campusId]);

            return;
        }

        if ($context->platformAccess || $context->roleCode === EducationRoleCode::TenantAdmin) {
            return;
        }

        $context->campusIds === []
            ? $query->whereRaw('1 = 0')
            : $this->whereStudentCampus($query, $tenantId, $context->campusIds);
    }

    /**
     * @param int[] $campusIds
     */
    private function whereStudentCampus(mixed $query, ?int $tenantId, array $campusIds): void
    {
        $query->whereHas('student', static function (Builder $studentQuery) use ($tenantId, $campusIds): void {
            if ($tenantId !== null) {
                $studentQuery->where('tenant_id', $tenantId);
            }
            $studentQuery->whereIn('campus_id', $campusIds);
        });
    }
}
