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

use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonAttendance;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationLessonAttendance>
 */
final class AttendanceRepository extends IRepository
{
    public function __construct(
        protected readonly EducationLessonAttendance $model
    ) {}

    public function lessonPage(array $filters, EducationUserContext $context): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? $filters['page_size'] ?? 15)));
        unset($filters['page'], $filters['pageSize'], $filters['page_size']);

        $query = $this->applyLessonContext(EducationLesson::query(), $context, $filters);
        $this->applyLessonFilters($query, $filters);
        $query->orderByDesc('start_at')->orderByDesc('id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function lessonDetail(int $lessonId, EducationUserContext $context): array
    {
        $lesson = $this->applyLessonContext(EducationLesson::query(), $context, [])
            ->whereKey($lessonId)
            ->first();

        if (! $lesson instanceof EducationLesson) {
            return ['lesson' => null, 'lesson_students' => [], 'attendances' => []];
        }

        return [
            'lesson' => $lesson->toArray(),
            'lesson_students' => EducationLessonStudent::query()
                ->where('tenant_id', $lesson->tenant_id)
                ->where('lesson_id', $lessonId)
                ->orderBy('id')
                ->get()
                ->toArray(),
            'attendances' => $this->getQuery()
                ->where('tenant_id', $lesson->tenant_id)
                ->where('lesson_id', $lessonId)
                ->orderBy('id')
                ->get()
                ->toArray(),
        ];
    }

    public function existingByLesson(int $lessonId, int $tenantId): array
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('lesson_id', $lessonId)
            ->orderBy('lesson_student_id')
            ->get()
            ->toArray();
    }

    public function existingByLessonStudentIds(array $lessonStudentIds, int $tenantId): array
    {
        if ($lessonStudentIds === []) {
            return [];
        }

        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->whereIn('lesson_student_id', array_map('intval', $lessonStudentIds))
            ->orderBy('lesson_student_id')
            ->get()
            ->toArray();
    }

    public function bulkCreate(array $rows): array
    {
        $created = [];
        foreach ($rows as $row) {
            $created[] = $this->create($row)->toArray();
        }

        return $created;
    }

    public function markConsumptionStatus(int $attendanceId, string $status, ?int $operatorId): EducationLessonAttendance
    {
        $attendance = $this->getQuery()->whereKey($attendanceId)->first();
        if (! $attendance instanceof EducationLessonAttendance) {
            throw new \RuntimeException('Attendance not found.');
        }
        $attendance->consumption_status = $status;
        $attendance->updated_by = $operatorId;
        $attendance->save();

        return $attendance->refresh();
    }

    private function applyLessonContext(Builder $query, EducationUserContext $context, array $filters): Builder
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
            $context->canAccessCampus($campusId) ? $query->where('campus_id', $campusId) : $query->whereRaw('1 = 0');

            return $query;
        }

        $context->campusIds === [] ? $query->whereRaw('1 = 0') : $query->whereIn('campus_id', $context->campusIds);

        return $query;
    }

    private function applyLessonFilters(Builder $query, array $filters): void
    {
        foreach (['class_id', 'teacher_id'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, (int) $filters[$column]);
            }
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['start_at']) && $filters['start_at'] !== '') {
            $query->where('end_at', '>', (string) $filters['start_at']);
        }
        if (isset($filters['end_at']) && $filters['end_at'] !== '') {
            $query->where('start_at', '<', (string) $filters['end_at']);
        }
        if (isset($filters['keyword']) && $filters['keyword'] !== '') {
            $keyword = '%' . $filters['keyword'] . '%';
            $query->where(static function (Builder $query) use ($keyword): void {
                $query->where('lesson_no', 'like', $keyword)
                    ->orWhere('title', 'like', $keyword);
            });
        }
    }
}
