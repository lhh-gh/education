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

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationRenewalTask extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_renewal_tasks';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'student_id',
        'course_id',
        'renewal_alert_id',
        'assignee_id',
        'status',
        'next_follow_at',
        'result',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'student_id' => 'integer',
        'course_id' => 'integer',
        'renewal_alert_id' => 'integer',
        'assignee_id' => 'integer',
        'next_follow_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
