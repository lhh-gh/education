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

final class TeacherMobileAttendanceSheetSchema
{
    public function sheet(EducationLesson $lesson, array $records): array
    {
        $submittedCount = \count(array_filter($records, static fn (array $row): bool => $row['existing_attendance_status'] !== null));
        $defaultLeaveCount = \count(array_filter($records, static fn (array $row): bool => $row['default_attendance_status'] === 'leave'));

        return [
            'lesson' => [
                'id' => (int) $lesson->id,
                'title' => (string) $lesson->title,
                'start_at' => $lesson->start_at?->toDateTimeString(),
                'end_at' => $lesson->end_at?->toDateTimeString(),
                'status' => (string) $lesson->status,
                'lesson_units' => (string) $lesson->lesson_units,
            ],
            'submitted' => $submittedCount > 0,
            'records' => $records,
            'summary' => [
                'total_students' => \count($records),
                'default_leave_count' => $defaultLeaveCount,
                'submitted_count' => $submittedCount,
                'total_consumed_units' => $this->sumConsumedUnits($records),
            ],
        ];
    }

    private function sumConsumedUnits(array $records): string
    {
        $total = 0.0;
        foreach ($records as $record) {
            $total += (float) ($record['existing_consumed_units'] ?? $record['default_consumed_units']);
        }

        return number_format($total, 2, '.', '');
    }
}
