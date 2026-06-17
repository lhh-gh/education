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

#[Schema(title: 'EducationLeaveReportSchema')]
final class LeaveReportSchema implements \JsonSerializable
{
    public function __construct(private readonly array $payload) {}

    public function jsonSerialize(): mixed
    {
        return [
            'summary' => [
                'total_count' => $this->summary('total_count'),
                'pending_count' => $this->summary('pending_count'),
                'approved_count' => $this->summary('approved_count'),
                'rejected_count' => $this->summary('rejected_count'),
                'cancelled_count' => $this->summary('cancelled_count'),
                'makeup_scheduled_count' => $this->summary('makeup_scheduled_count'),
                'guardian_source_count' => $this->summary('guardian_source_count'),
                'teacher_source_count' => $this->summary('teacher_source_count'),
                'staff_source_count' => $this->summary('staff_source_count'),
            ],
            'list' => array_map(static fn (array $row): array => [
                'leave_id' => $row['leave_id'] ?? null,
                'leave_no' => $row['leave_no'] ?? null,
                'source' => $row['source'] ?? null,
                'leave_type' => $row['leave_type'] ?? null,
                'status' => $row['status'] ?? null,
                'campus_id' => $row['campus_id'] ?? null,
                'campus_name' => $row['campus_name'] ?? null,
                'class_id' => $row['class_id'] ?? null,
                'class_name' => $row['class_name'] ?? null,
                'teacher_id' => $row['teacher_id'] ?? null,
                'teacher_name' => $row['teacher_name'] ?? null,
                'course_id' => $row['course_id'] ?? null,
                'course_name' => $row['course_name'] ?? null,
                'student_id' => $row['student_id'] ?? null,
                'student_name' => $row['student_name'] ?? null,
                'lesson_id' => $row['lesson_id'] ?? null,
                'lesson_title' => $row['lesson_title'] ?? null,
                'requested_at' => $row['requested_at'] ?? null,
                'reviewed_at' => $row['reviewed_at'] ?? null,
                'makeup_required' => $row['makeup_required'] ?? false,
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
