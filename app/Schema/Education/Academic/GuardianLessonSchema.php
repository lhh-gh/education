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

final class GuardianLessonSchema
{
    public function lesson(array $row): array
    {
        return [
            'lesson_id' => (int) $row['lesson_id'],
            'lesson_student_id' => (int) $row['id'],
            'class_id' => (int) $row['class_id'],
            'course_id' => (int) $row['course_id'],
            'student_id' => (int) $row['student_id'],
            'account_id' => (int) $row['account_id'],
            'title' => (string) $row['title'],
            'class_name_snapshot' => (string) ($row['class_name_snapshot'] ?? ''),
            'course_name_snapshot' => (string) ($row['course_name_snapshot'] ?? ''),
            'teacher_name_snapshot' => (string) ($row['teacher_name_snapshot'] ?? ''),
            'classroom_name_snapshot' => $row['classroom_name_snapshot'] ?? null,
            'student_name_snapshot' => (string) $row['student_name_snapshot'],
            'student_no_snapshot' => (string) $row['student_no_snapshot'],
            'start_at' => (string) $row['start_at'],
            'end_at' => (string) $row['end_at'],
            'lesson_units' => (string) $row['lesson_units'],
            'lesson_status' => (string) $row['lesson_status'],
            'lesson_student_status' => (string) $row['status'],
        ];
    }
}
