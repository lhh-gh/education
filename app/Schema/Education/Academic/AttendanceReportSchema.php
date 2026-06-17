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

use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationAttendanceReportSchema')]
final class AttendanceReportSchema implements \JsonSerializable
{
    public function __construct(private readonly array $payload) {}

    public function jsonSerialize(): mixed
    {
        return [
            'summary' => [
                'total_records' => $this->summary('total_records'),
                'present_count' => $this->summary('present_count'),
                'late_count' => $this->summary('late_count'),
                'absent_count' => $this->summary('absent_count'),
                'leave_count' => $this->summary('leave_count'),
                'attendance_rate' => $this->summary('attendance_rate', '0.00'),
                'leave_rate' => $this->summary('leave_rate', '0.00'),
            ],
            'list' => array_map(static fn (array $row): array => [
                'date' => $row['date'] ?? null,
                'campus_id' => $row['campus_id'] ?? null,
                'campus_name' => $row['campus_name'] ?? null,
                'class_id' => $row['class_id'] ?? null,
                'class_name' => $row['class_name'] ?? null,
                'teacher_id' => $row['teacher_id'] ?? null,
                'teacher_name' => $row['teacher_name'] ?? null,
                'course_id' => $row['course_id'] ?? null,
                'course_name' => $row['course_name'] ?? null,
                'lesson_id' => $row['lesson_id'] ?? null,
                'lesson_title' => $row['lesson_title'] ?? null,
                'student_id' => $row['student_id'] ?? null,
                'student_name' => $row['student_name'] ?? null,
                'attendance_status' => $row['attendance_status'] ?? null,
                'consume_policy' => $row['consume_policy'] ?? null,
                'consumed_units' => $row['consumed_units'] ?? '0.00',
                'submitted_at' => $row['submitted_at'] ?? null,
            ], $this->payload['list'] ?? $this->payload['rows'] ?? []),
            'total' => $this->payload['total'] ?? 0,
            'page' => $this->payload['page'] ?? 1,
            'pageSize' => $this->payload['pageSize'] ?? 20,
        ];
    }

    private function summary(string $key, mixed $default = 0): mixed
    {
        return $this->payload['summary'][$key] ?? $default;
    }
}
