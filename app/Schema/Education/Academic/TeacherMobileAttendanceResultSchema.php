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

final class TeacherMobileAttendanceResultSchema
{
    public function result(EducationLesson $lesson, array $summary, array $attendanceRows): array
    {
        return [
            'lesson_id' => (int) $lesson->id,
            'lesson_status' => (string) $lesson->status,
            'attendance_batch_no' => $summary['attendance_batch_no'] ?? ($attendanceRows[0]['attendance_batch_no'] ?? null),
            'attendance_count' => (int) ($summary['attendance_count'] ?? \count($attendanceRows)),
            'consumed_count' => (int) ($summary['consumed_count'] ?? $this->countPolicy($attendanceRows, 'consume')),
            'no_consume_count' => (int) ($summary['no_consume_count'] ?? $this->countPolicy($attendanceRows, 'no_consume')),
            'total_consumed_units' => (string) ($summary['total_consumed_units'] ?? $this->sumConsumedUnits($attendanceRows)),
            'records' => array_map(static fn (array $row): array => [
                'lesson_student_id' => (int) $row['lesson_student_id'],
                'student_id' => (int) $row['student_id'],
                'student_name_snapshot' => $row['student_name_snapshot'] ?? null,
                'attendance_status' => (string) $row['attendance_status'],
                'consume_policy' => (string) $row['consume_policy'],
                'consumed_units' => (string) $row['consumed_units'],
                'consumption_status' => (string) $row['consumption_status'],
            ], $attendanceRows),
            'account_changes' => $summary['account_changes'] ?? [],
        ];
    }

    private function countPolicy(array $rows, string $policy): int
    {
        return \count(array_filter($rows, static fn (array $row): bool => $row['consume_policy'] === $policy));
    }

    private function sumConsumedUnits(array $rows): string
    {
        $total = 0.0;
        foreach ($rows as $row) {
            $total += (float) $row['consumed_units'];
        }

        return number_format($total, 2, '.', '');
    }
}
