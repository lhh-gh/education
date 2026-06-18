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
use App\Model\Education\Academic\EducationTeacherCourse;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationTeacherCourse>
 */
final class TeacherCourseRepository extends IRepository
{
    public function __construct(
        protected readonly EducationTeacherCourse $model
    ) {}

    public function listByCourse(int $courseId, EducationUserContext $context): array
    {
        $rows = $this->applyContext($this->getQuery(), $context, [])
            ->where('course_id', $courseId)
            ->where('status', 'enabled')
            ->orderBy('teacher_id')
            ->get();
        $teacherIds = [];
        foreach ($rows as $row) {
            if ($row instanceof EducationTeacherCourse) {
                $teacherIds[] = (int) $row->teacher_id;
            }
        }
        $teachers = $teacherIds === [] ? [] : EducationTeacher::query()
            ->whereIn('id', $teacherIds)
            ->get()
            ->keyBy('id')
            ->all();

        $result = [];
        foreach ($rows as $row) {
            if (! $row instanceof EducationTeacherCourse) {
                continue;
            }
            $teacher = $teachers[$row->teacher_id] ?? null;

            $result[] = [
                'id' => (int) $row->id,
                'tenant_id' => (int) $row->tenant_id,
                'campus_id' => (int) $row->campus_id,
                'course_id' => (int) $row->course_id,
                'teacher_id' => (int) $row->teacher_id,
                'teacher_name' => $teacher?->name,
                'teacher_no' => $teacher?->teacher_no,
                'teacher_mobile' => $teacher?->mobile,
                'status' => $row->status,
                'authorized_at' => $row->authorized_at?->toDateTimeString(),
                'remark' => $row->remark,
            ];
        }

        return $result;
    }

    /**
     * @return int[]
     */
    public function enabledTeacherIdsByCourse(int $courseId, int $tenantId, int $campusId): array
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('course_id', $courseId)
            ->where('status', 'enabled')
            ->pluck('teacher_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @param int[] $teacherIds
     */
    public function replaceEnabledTeachers(int $courseId, array $teacherIds, int $tenantId, int $campusId, ?int $operatorId): array
    {
        $teacherIds = array_values(array_unique(array_map('intval', $teacherIds)));
        $now = Carbon::now()->toDateTimeString();

        $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('course_id', $courseId)
            ->whereNotIn('teacher_id', $teacherIds === [] ? [0] : $teacherIds)
            ->get()
            ->each(static function (EducationTeacherCourse $row) use ($operatorId): void {
                $row->updated_by = $operatorId;
                $row->save();
                $row->delete();
            });

        foreach ($teacherIds as $teacherId) {
            $row = EducationTeacherCourse::withTrashed()
                ->where('tenant_id', $tenantId)
                ->where('course_id', $courseId)
                ->where('teacher_id', $teacherId)
                ->first();

            if (! $row instanceof EducationTeacherCourse) {
                $this->create([
                    'tenant_id' => $tenantId,
                    'campus_id' => $campusId,
                    'course_id' => $courseId,
                    'teacher_id' => $teacherId,
                    'status' => 'enabled',
                    'authorized_at' => $now,
                    'created_by' => $operatorId,
                    'updated_by' => $operatorId,
                ]);
                continue;
            }

            if ($row->trashed()) {
                $row->restore();
            }
            $row->fill([
                'campus_id' => $campusId,
                'status' => 'enabled',
                'authorized_at' => $row->authorized_at ?? $now,
                'updated_by' => $operatorId,
            ]);
            $row->save();
        }

        $rows = $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('course_id', $courseId)
            ->whereIn('teacher_id', $teacherIds === [] ? [0] : $teacherIds)
            ->get()
            ->keyBy('teacher_id');

        $result = [];
        foreach ($teacherIds as $teacherId) {
            $row = $rows[$teacherId] ?? null;
            if (! $row instanceof EducationTeacherCourse) {
                continue;
            }

            $result[] = [
                'id' => (int) $row->id,
                'course_id' => (int) $row->course_id,
                'teacher_id' => (int) $row->teacher_id,
                'status' => $row->status,
            ];
        }

        return $result;
    }

    public function teacherCanTeachCourse(int $teacherId, int $courseId, int $tenantId, int $campusId): bool
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('course_id', $courseId)
            ->where('teacher_id', $teacherId)
            ->where('status', 'enabled')
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
}
