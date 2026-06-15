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
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationUserContext;

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

        if (! $context->platformAccess) {
            $context->tenantId === null
                ? $query->whereRaw('1 = 0')
                : $query->where('tenant_id', $context->tenantId);
        }

        return $query->get()->toArray();
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
}
