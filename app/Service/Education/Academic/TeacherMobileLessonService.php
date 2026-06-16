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

namespace App\Service\Education\Academic;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationLeaveRequest;
use App\Repository\Education\Academic\TeacherMobileLessonRepository;
use App\Schema\Education\Academic\TeacherMobileLessonSchema;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class TeacherMobileLessonService
{
    public function __construct(
        private readonly TeacherMobileContextResolver $contextResolver,
        private readonly TeacherMobileLessonRepository $repository,
        private readonly TeacherMobileLessonSchema $schema
    ) {}

    public function today(array $params, EducationUserContext $context): array
    {
        $teacher = $this->contextResolver->resolveTeacher($context);
        $campusId = $this->contextResolver->assertCampusAllowed($context, isset($params['campus_id']) ? (int) $params['campus_id'] : null);
        $campusIds = $this->campusIds($context, $campusId);
        $date = (string) ($params['date'] ?? Carbon::now()->toDateString());

        $list = array_map(
            fn (array $row): array => $this->schema->lesson($this->repository->getModel()->newFromBuilder($row), false, false, $this->pendingLeaveCount((int) $row['id'], (int) $context->tenantId)),
            $this->repository->listToday((int) $context->tenantId, $campusIds, (int) $teacher->id, $date)
        );

        return [
            'date' => $date,
            'list' => $list,
        ];
    }

    public function page(array $params, EducationUserContext $context): array
    {
        $teacher = $this->contextResolver->resolveTeacher($context);
        $campusId = $this->contextResolver->assertCampusAllowed($context, isset($params['campus_id']) ? (int) $params['campus_id'] : null);
        if ($campusId !== null) {
            $params['campus_id'] = $campusId;
        }

        $result = $this->repository->pageAssigned(
            $params,
            max(1, (int) ($params['page'] ?? 1)),
            max(1, min(100, (int) ($params['pageSize'] ?? 20))),
            $context,
            (int) $teacher->id
        );

        $result['list'] = array_map(
            fn (array $row): array => $this->schema->lesson($this->repository->getModel()->newFromBuilder($row), false, false, $this->pendingLeaveCount((int) $row['id'], (int) $context->tenantId)),
            $result['list']
        );

        return $result;
    }

    public function detail(int $lessonId, array $params, EducationUserContext $context): array
    {
        $teacher = $this->contextResolver->resolveTeacher($context);
        $campusId = $this->contextResolver->assertCampusAllowed($context, isset($params['campus_id']) ? (int) $params['campus_id'] : null);
        $lesson = $this->repository->findAssignedLesson($lessonId, $context, (int) $teacher->id, $campusId);
        if ($lesson === null) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson not found in current teacher context', ['lesson_id' => $lessonId]);
        }

        $attendanceRows = $this->repository->listAttendanceRows($lessonId, (int) $context->tenantId);
        $students = $this->repository->listLessonStudents($lessonId, (int) $context->tenantId);
        $attendanceSubmitted = $attendanceRows !== [];

        return $this->schema->lesson(
            lesson: $lesson,
            attendanceSubmitted: $attendanceSubmitted,
            canSubmitAttendance: (string) $lesson->status === 'scheduled' && ! $attendanceSubmitted,
            pendingLeaveCount: $this->pendingLeaveCount($lessonId, (int) $context->tenantId)
        ) + [
            'lesson_students' => array_map(fn (array $row): array => $this->schema->student($row), $students),
        ];
    }

    /**
     * @return int[]
     */
    private function campusIds(EducationUserContext $context, ?int $campusId): array
    {
        if ($campusId !== null) {
            return [$campusId];
        }

        return $context->campusIds;
    }

    private function pendingLeaveCount(int $lessonId, int $tenantId): int
    {
        return EducationLeaveRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('lesson_id', $lessonId)
            ->where('status', 'pending')
            ->count();
    }
}
