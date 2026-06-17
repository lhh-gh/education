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

class EducationRenewalAlert extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_renewal_alerts';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'student_id',
        'course_id',
        'student_course_account_id',
        'alert_type',
        'alert_level',
        'status',
        'trigger_value',
        'threshold_value',
        'due_date',
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
        'student_course_account_id' => 'integer',
        'due_date' => 'date',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
