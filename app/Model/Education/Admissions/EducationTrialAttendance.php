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

namespace App\Model\Education\Admissions;

use Hyperf\DbConnection\Model\Model;

class EducationTrialAttendance extends Model
{
    protected ?string $table = 'edu_trial_attendances';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'trial_lesson_id',
        'lead_student_id',
        'attendance_status',
        'checked_by',
        'checked_at',
        'remark',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'trial_lesson_id' => 'integer',
        'lead_student_id' => 'integer',
        'checked_by' => 'integer',
        'checked_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
