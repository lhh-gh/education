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

namespace App\Model\Education\Operations;

use Hyperf\DbConnection\Model\Model;

class EducationStudentFollowRecord extends Model
{
    protected ?string $table = 'edu_student_follow_records';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'student_id',
        'renewal_task_id',
        'follow_type',
        'content',
        'next_follow_at',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'student_id' => 'integer',
        'renewal_task_id' => 'integer',
        'next_follow_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
