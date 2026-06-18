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

#[Schema(title: 'EducationAttendanceSubmitResultSchema')]
final class AttendanceSubmitResultSchema implements \JsonSerializable
{
    public function __construct(private readonly array $data) {}

    public function jsonSerialize(): mixed
    {
        return [
            'lesson_id' => $this->data['lesson_id'] ?? null,
            'attendance_batch_no' => $this->data['attendance_batch_no'] ?? null,
            'attendance_count' => $this->data['attendance_count'] ?? 0,
            'consumed_count' => $this->data['consumed_count'] ?? 0,
            'no_consume_count' => $this->data['no_consume_count'] ?? 0,
            'total_consumed_units' => $this->data['total_consumed_units'] ?? '0.00',
            'account_changes' => $this->data['account_changes'] ?? [],
        ];
    }
}
