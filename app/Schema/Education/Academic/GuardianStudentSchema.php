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

final class GuardianStudentSchema
{
    public function student(array $row): array
    {
        return [
            'id' => (int) $row['student_id'],
            'student_id' => (int) $row['student_id'],
            'student_no' => (string) $row['student_no'],
            'name' => (string) $row['student_name'],
            'relation' => (string) $row['relation'],
            'is_primary' => (bool) $row['is_primary'],
            'can_receive_notice' => (bool) $row['can_receive_notice'],
            'can_submit_leave' => (bool) $row['can_submit_leave'],
            'campus_id' => (int) $row['campus_id'],
        ];
    }
}
