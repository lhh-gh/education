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

#[Schema(title: 'EducationAccountBalanceReportSchema')]
final class AccountBalanceReportSchema implements \JsonSerializable
{
    public function __construct(private readonly array $payload) {}

    public function jsonSerialize(): mixed
    {
        return [
            'summary' => [
                'account_count' => $this->summary('account_count'),
                'active_count' => $this->summary('active_count'),
                'frozen_count' => $this->summary('frozen_count'),
                'closed_count' => $this->summary('closed_count'),
                'total_purchased_units' => $this->summary('total_purchased_units', '0.00'),
                'total_bonus_units' => $this->summary('total_bonus_units', '0.00'),
                'total_consumed_units' => $this->summary('total_consumed_units', '0.00'),
                'total_adjusted_units' => $this->summary('total_adjusted_units', '0.00'),
                'total_refunded_units' => $this->summary('total_refunded_units', '0.00'),
                'total_frozen_units' => $this->summary('total_frozen_units', '0.00'),
                'total_available_units' => $this->summary('total_available_units', '0.00'),
                'low_balance_count' => $this->summary('low_balance_count'),
                'expiring_count' => $this->summary('expiring_count'),
            ],
            'list' => array_map(static fn (array $row): array => [
                'account_id' => $row['account_id'] ?? null,
                'campus_id' => $row['campus_id'] ?? null,
                'campus_name' => $row['campus_name'] ?? null,
                'student_id' => $row['student_id'] ?? null,
                'student_name' => $row['student_name'] ?? null,
                'student_no' => $row['student_no'] ?? null,
                'course_id' => $row['course_id'] ?? null,
                'course_name' => $row['course_name'] ?? null,
                'purchased_units' => $row['purchased_units'] ?? '0.00',
                'bonus_units' => $row['bonus_units'] ?? '0.00',
                'consumed_units' => $row['consumed_units'] ?? '0.00',
                'adjusted_units' => $row['adjusted_units'] ?? '0.00',
                'refunded_units' => $row['refunded_units'] ?? '0.00',
                'frozen_units' => $row['frozen_units'] ?? '0.00',
                'available_units' => $row['available_units'] ?? '0.00',
                'status' => $row['status'] ?? null,
                'balance_level' => $row['balance_level'] ?? null,
                'opened_at' => $row['opened_at'] ?? null,
                'expires_at' => $row['expires_at'] ?? null,
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
