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

#[Schema(title: 'EducationConsumptionReportSchema')]
final class ConsumptionReportSchema implements \JsonSerializable
{
    public function __construct(private readonly array $payload) {}

    public function jsonSerialize(): mixed
    {
        return [
            'summary' => [
                'decrease_units' => $this->summary('decrease_units', '0.00'),
                'rollback_units' => $this->summary('rollback_units', '0.00'),
                'net_units' => $this->summary('net_units', '0.00'),
                'active_row_count' => $this->summary('active_row_count'),
                'reversed_row_count' => $this->summary('reversed_row_count'),
            ],
            'list' => array_map(static fn (array $row): array => [
                'date' => $row['date'] ?? null,
                'campus_id' => $row['campus_id'] ?? null,
                'campus_name' => $row['campus_name'] ?? null,
                'consumption_no' => $row['consumption_no'] ?? null,
                'account_id' => $row['account_id'] ?? null,
                'student_id' => $row['student_id'] ?? null,
                'student_name' => $row['student_name'] ?? null,
                'course_id' => $row['course_id'] ?? null,
                'course_name' => $row['course_name'] ?? null,
                'class_id' => $row['class_id'] ?? null,
                'class_name' => $row['class_name'] ?? null,
                'teacher_id' => $row['teacher_id'] ?? null,
                'teacher_name' => $row['teacher_name'] ?? null,
                'lesson_id' => $row['lesson_id'] ?? null,
                'lesson_title' => $row['lesson_title'] ?? null,
                'source_type' => $row['source_type'] ?? null,
                'direction' => $row['direction'] ?? null,
                'units' => $row['units'] ?? '0.00',
                'before_available_units' => $row['before_available_units'] ?? '0.00',
                'after_available_units' => $row['after_available_units'] ?? '0.00',
                'status' => $row['status'] ?? null,
                'created_at' => $row['created_at'] ?? null,
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
