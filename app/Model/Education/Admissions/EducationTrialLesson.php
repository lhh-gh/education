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

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationTrialLesson extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_trial_lessons';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'lead_id',
        'lead_student_id',
        'course_id',
        'teacher_id',
        'classroom_id',
        'start_time',
        'end_time',
        'status',
        'consultant_user_id',
        'remark',
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
        'lead_id' => 'integer',
        'lead_student_id' => 'integer',
        'course_id' => 'integer',
        'teacher_id' => 'integer',
        'classroom_id' => 'integer',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'consultant_user_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
