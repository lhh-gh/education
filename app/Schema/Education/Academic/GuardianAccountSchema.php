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

final class GuardianAccountSchema
{
    public function account(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'student_id' => (int) $row['student_id'],
            'course_id' => (int) $row['course_id'],
            'purchased_units' => (string) $row['purchased_units'],
            'bonus_units' => (string) $row['bonus_units'],
            'consumed_units' => (string) $row['consumed_units'],
            'adjusted_units' => (string) $row['adjusted_units'],
            'refunded_units' => (string) $row['refunded_units'],
            'frozen_units' => (string) $row['frozen_units'],
            'available_units' => (string) $row['available_units'],
            'status' => (string) $row['status'],
            'expires_at' => isset($row['expires_at']) ? (string) $row['expires_at'] : null,
        ];
    }
}
