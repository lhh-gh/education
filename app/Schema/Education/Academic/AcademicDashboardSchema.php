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

#[Schema(title: 'EducationAcademicDashboardSchema')]
final class AcademicDashboardSchema implements \JsonSerializable
{
    public function __construct(private readonly array $payload) {}

    public function jsonSerialize(): mixed
    {
        return [
            'range' => [
                'start_at' => $this->nested('range', 'start_at'),
                'end_at' => $this->nested('range', 'end_at'),
            ],
            'campus_id' => $this->payload['campus_id'] ?? null,
            'metrics' => [
                'student_count' => $this->metric('student_count'),
                'active_student_count' => $this->metric('active_student_count'),
                'guardian_count' => $this->metric('guardian_count'),
                'teacher_count' => $this->metric('teacher_count'),
                'active_class_count' => $this->metric('active_class_count'),
                'scheduled_lesson_count' => $this->metric('scheduled_lesson_count'),
                'completed_lesson_count' => $this->metric('completed_lesson_count'),
                'cancelled_lesson_count' => $this->metric('cancelled_lesson_count'),
                'attendance_count' => $this->metric('attendance_count'),
                'present_count' => $this->metric('present_count'),
                'late_count' => $this->metric('late_count'),
                'absent_count' => $this->metric('absent_count'),
                'leave_count' => $this->metric('leave_count'),
                'consumed_units' => $this->metric('consumed_units', '0.00'),
                'rollback_units' => $this->metric('rollback_units', '0.00'),
                'net_consumed_units' => $this->metric('net_consumed_units', '0.00'),
                'total_available_units' => $this->metric('total_available_units', '0.00'),
                'frozen_units' => $this->metric('frozen_units', '0.00'),
                'low_balance_account_count' => $this->metric('low_balance_account_count'),
                'expiring_account_count' => $this->metric('expiring_account_count'),
                'pending_leave_count' => $this->metric('pending_leave_count'),
                'approved_leave_count' => $this->metric('approved_leave_count'),
                'makeup_scheduled_count' => $this->metric('makeup_scheduled_count'),
                'published_notice_count' => $this->metric('published_notice_count'),
                'unread_notice_receipt_count' => $this->metric('unread_notice_receipt_count'),
            ],
            'trends' => array_map(static fn (array $row): array => [
                'date' => $row['date'] ?? null,
                'scheduled_lesson_count' => $row['scheduled_lesson_count'] ?? 0,
                'completed_lesson_count' => $row['completed_lesson_count'] ?? 0,
                'consumed_units' => $row['consumed_units'] ?? '0.00',
            ], $this->payload['trends'] ?? []),
            'alerts' => array_map(static fn (array $row): array => [
                'type' => $row['type'] ?? null,
                'level' => $row['level'] ?? null,
                'title' => $row['title'] ?? null,
                'count' => $row['count'] ?? 0,
            ], $this->payload['alerts'] ?? []),
        ];
    }

    private function nested(string $group, string $key): mixed
    {
        return $this->payload[$group][$key] ?? null;
    }

    private function metric(string $key, mixed $default = 0): mixed
    {
        return $this->payload['metrics'][$key] ?? $default;
    }
}
