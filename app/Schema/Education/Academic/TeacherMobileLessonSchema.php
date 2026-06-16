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

use App\Model\Education\Academic\EducationLesson;

final class TeacherMobileLessonSchema
{
    public function lesson(EducationLesson $lesson, bool $attendanceSubmitted = false, bool $canSubmitAttendance = false, int $pendingLeaveCount = 0): array
    {
        return [
            'id' => (int) $lesson->id,
            'tenant_id' => (int) $lesson->tenant_id,
            'campus_id' => (int) $lesson->campus_id,
            'lesson_no' => (string) $lesson->lesson_no,
            'class_id' => (int) $lesson->class_id,
            'class_name_snapshot' => (string) $lesson->class_name_snapshot,
            'course_id' => (int) $lesson->course_id,
            'course_name_snapshot' => (string) $lesson->course_name_snapshot,
            'teacher_id' => (int) $lesson->teacher_id,
            'teacher_name_snapshot' => (string) $lesson->teacher_name_snapshot,
            'classroom_id' => $lesson->classroom_id === null ? null : (int) $lesson->classroom_id,
            'classroom_name_snapshot' => $lesson->classroom_name_snapshot,
            'title' => (string) $lesson->title,
            'start_at' => $lesson->start_at?->toDateTimeString(),
            'end_at' => $lesson->end_at?->toDateTimeString(),
            'duration_minutes' => (int) $lesson->duration_minutes,
            'lesson_units' => (string) $lesson->lesson_units,
            'student_count' => (int) $lesson->student_count,
            'status' => (string) $lesson->status,
            'attendance_submitted' => $attendanceSubmitted,
            'can_submit_attendance' => $canSubmitAttendance,
            'pending_leave_count' => $pendingLeaveCount,
            'created_at' => $lesson->created_at?->toDateTimeString(),
        ];
    }

    public function student(array $row): array
    {
        return [
            'lesson_student_id' => (int) $row['id'],
            'student_id' => (int) $row['student_id'],
            'student_name_snapshot' => (string) $row['student_name_snapshot'],
            'student_no_snapshot' => (string) $row['student_no_snapshot'],
            'account_id' => (int) $row['account_id'],
            'lesson_units' => (string) $row['lesson_units'],
            'status' => (string) $row['status'],
        ];
    }
}
