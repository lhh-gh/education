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

final class GuardianConsumptionSchema
{
    public function consumption(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'consumption_no' => (string) $row['consumption_no'],
            'account_id' => (int) $row['account_id'],
            'student_id' => (int) $row['student_id'],
            'course_id' => (int) $row['course_id'],
            'lesson_id' => (int) $row['lesson_id'],
            'lesson_student_id' => (int) $row['lesson_student_id'],
            'source_type' => (string) $row['source_type'],
            'direction' => (string) $row['direction'],
            'units' => (string) $row['units'],
            'before_available_units' => (string) $row['before_available_units'],
            'after_available_units' => (string) $row['after_available_units'],
            'status' => (string) $row['status'],
            'created_at' => isset($row['created_at']) ? (string) $row['created_at'] : null,
        ];
    }
}
