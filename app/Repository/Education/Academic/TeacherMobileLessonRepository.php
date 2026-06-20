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

use App\Model\Education\Academic\EducationLeaveRequest;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonAttendance;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationLesson>
 */
final class TeacherMobileLessonRepository extends IRepository
{
    public function __construct(
        protected readonly EducationLesson $model
    ) {}

    public function listToday(int $tenantId, array $campusIds, int $teacherId, string $date): array
    {
        $query = $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('teacher_id', $teacherId)
            ->where('start_at', '>=', $date . ' 00:00:00')
            ->where('start_at', '<=', $date . ' 23:59:59');

        $this->applyCampusIds($query, $campusIds);

        return $query->orderBy('start_at')->orderBy('id')->get()->toArray();
    }

    public function pageAssigned(array $params, int $page, int $pageSize, EducationUserContext $context, int $teacherId): array
    {
        $query = $this->assignedLessonQuery($context, $teacherId, $params);

        foreach (['status'] as $column) {
            if (isset($params[$column]) && $params[$column] !== '') {
                $query->where($column, (string) $params[$column]);
            }
        }

        if (isset($params['start_at']) && $params['start_at'] !== '') {
            $query->where('end_at', '>', (string) $params['start_at']);
        }
        if (isset($params['end_at']) && $params['end_at'] !== '') {
            $query->where('start_at', '<', (string) $params['end_at']);
        }
        if (isset($params['keyword']) && $params['keyword'] !== '') {
            $keyword = '%' . $params['keyword'] . '%';
            $query->where(static function (Builder $query) use ($keyword): void {
                $query->where('lesson_no', 'like', $keyword)
                    ->orWhere('title', 'like', $keyword)
                    ->orWhere('class_name_snapshot', 'like', $keyword)
                    ->orWhere('course_name_snapshot', 'like', $keyword);
            });
        }

        $query->orderByDesc('start_at')->orderByDesc('id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function findAssignedLesson(int $lessonId, EducationUserContext $context, int $teacherId, ?int $campusId): ?EducationLesson
    {
        $query = $this->assignedLessonQuery($context, $teacherId, ['campus_id' => $campusId])
            ->whereKey($lessonId);

        $lesson = $query->first();

        return $lesson instanceof EducationLesson ? $lesson : null;
    }

    public function listLessonStudents(int $lessonId, int $tenantId): array
    {
        return EducationLessonStudent::query()
            ->where('tenant_id', $tenantId)
            ->where('lesson_id', $lessonId)
            ->where('status', '<>', 'cancelled')
            ->orderBy('id')
            ->get()
            ->toArray();
    }

    public function listAttendanceRows(int $lessonId, int $tenantId): array
    {
        return EducationLessonAttendance::query()
            ->where('tenant_id', $tenantId)
            ->where('lesson_id', $lessonId)
            ->get()
            ->keyBy('lesson_student_id')
            ->toArray();
    }

    public function listApprovedLeaveRows(int $lessonId, int $tenantId): array
    {
        return EducationLeaveRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('lesson_id', $lessonId)
            ->whereIn('status', ['approved', 'makeup_scheduled'])
            ->get()
            ->keyBy('lesson_student_id')
            ->toArray();
    }

    private function assignedLessonQuery(EducationUserContext $context, int $teacherId, array $params): Builder
    {
        $query = $this->getQuery()
            ->where('teacher_id', $teacherId);

        return (new EducationScopeQuery())->applyTenantCampus($query, $params, $context);
    }

    private function applyCampusIds(Builder $query, array $campusIds): void
    {
        $campusIds === []
            ? $query->whereRaw('1 = 0')
            : $query->whereIn('campus_id', array_map('intval', $campusIds));
    }
}
