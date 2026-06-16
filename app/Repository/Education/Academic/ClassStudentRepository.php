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

use App\Model\Education\Academic\EducationClassStudent;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Db;

/**
 * @extends IRepository<EducationClassStudent>
 */
final class ClassStudentRepository extends IRepository
{
    public function __construct(
        protected readonly EducationClassStudent $model
    ) {}

    public function listByClass(int $classId, EducationUserContext $context): array
    {
        $query = $this->applyContext($this->getQuery(), $context)
            ->where('class_id', $classId)
            ->orderByDesc('status')
            ->orderBy('id');

        return $query->get()->toArray();
    }

    public function activeStudentsByClass(int $classId, int $tenantId, int $campusId): array
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->orderBy('id')
            ->get()
            ->toArray();
    }

    public function replaceStudents(int $classId, array $studentRows, int $tenantId, int $campusId, ?int $operatorId): array
    {
        return Db::transaction(function () use ($classId, $studentRows, $tenantId, $campusId, $operatorId): array {
            $existing = $this->getQuery()
                ->where('tenant_id', $tenantId)
                ->where('campus_id', $campusId)
                ->where('class_id', $classId)
                ->get()
                ->keyBy('student_id');
            $incomingIds = [];
            $saved = [];

            foreach ($studentRows as $row) {
                $studentId = (int) $row['student_id'];
                $incomingIds[] = $studentId;
                $row['tenant_id'] = $tenantId;
                $row['campus_id'] = $campusId;
                $row['class_id'] = $classId;
                $row['status'] = 'active';
                $row['left_at'] = null;
                $row['updated_by'] = $operatorId;
                $row['joined_at'] ??= Carbon::now()->toDateTimeString();

                $current = $existing->get($studentId);
                if ($current instanceof EducationClassStudent) {
                    $current->fill($row);
                    $current->save();
                    $saved[] = $current->refresh()->toArray();
                    continue;
                }

                $row['created_by'] = $operatorId;
                $saved[] = $this->create($row)->refresh()->toArray();
            }

            $this->getQuery()
                ->where('tenant_id', $tenantId)
                ->where('campus_id', $campusId)
                ->where('class_id', $classId)
                ->whereIn('status', ['active', 'paused'])
                ->when($incomingIds !== [], static fn ($query) => $query->whereNotIn('student_id', $incomingIds))
                ->update([
                    'status' => 'left',
                    'left_at' => Carbon::now()->toDateTimeString(),
                    'updated_by' => $operatorId,
                ]);

            return $saved;
        });
    }

    public function studentIdsByClass(int $classId, int $tenantId): array
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->orderBy('id')
            ->pluck('student_id')
            ->map(static fn ($id): int => (int) $id)
            ->values()
            ->toArray();
    }

    public function studentIsActiveInClass(int $classId, int $studentId, int $tenantId): bool
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('class_id', $classId)
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->exists();
    }

    private function applyContext(Builder $query, EducationUserContext $context): Builder
    {
        if ($context->platformAccess) {
            return $query;
        }
        if ($context->tenantId === null) {
            $query->whereRaw('1 = 0');

            return $query;
        }
        $query->where('tenant_id', $context->tenantId);
        if ($context->roleCode === EducationRoleCode::TenantAdmin) {
            return $query;
        }

        $context->campusIds === [] ? $query->whereRaw('1 = 0') : $query->whereIn('campus_id', $context->campusIds);

        return $query;
    }
}
