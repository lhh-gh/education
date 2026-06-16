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

namespace App\Schema\Education\Academic;

use App\Model\Education\Academic\EducationLeaveRequest;

final class TeacherMobileLeaveSchema
{
    public function leave(EducationLeaveRequest $leave): array
    {
        $lesson = $leave->lesson;

        return [
            'id' => (int) $leave->id,
            'leave_no' => (string) $leave->leave_no,
            'source' => (string) $leave->source,
            'leave_type' => (string) $leave->leave_type,
            'lesson_id' => (int) $leave->lesson_id,
            'lesson_student_id' => (int) $leave->lesson_student_id,
            'class_id' => (int) $leave->class_id,
            'course_id' => (int) $leave->course_id,
            'student_id' => (int) $leave->student_id,
            'student_name_snapshot' => $leave->lessonStudent?->student_name_snapshot,
            'teacher_id' => $leave->teacher_id === null ? null : (int) $leave->teacher_id,
            'reason' => (string) $leave->reason,
            'status' => (string) $leave->status,
            'requested_at' => $leave->requested_at?->toDateTimeString(),
            'reviewed_at' => $leave->reviewed_at?->toDateTimeString(),
            'reviewed_by' => $leave->reviewed_by,
            'review_remark' => $leave->review_remark,
            'makeup_required' => (bool) $leave->makeup_required,
            'makeup_lesson_id' => $leave->makeup_lesson_id,
            'lesson_title' => $lesson?->title,
            'lesson_start_at' => $lesson?->start_at?->toDateTimeString(),
            'lesson_end_at' => $lesson?->end_at?->toDateTimeString(),
            'created_at' => $leave->created_at?->toDateTimeString(),
        ];
    }
}
