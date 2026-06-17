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

class EducationLessonConsumptionAdjustment extends Model
{
    protected ?string $table = 'edu_lesson_consumption_adjustments';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'original_consumption_id',
        'adjustment_consumption_id',
        'student_id',
        'student_course_account_id',
        'credits',
        'reason',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'original_consumption_id' => 'integer',
        'adjustment_consumption_id' => 'integer',
        'student_id' => 'integer',
        'student_course_account_id' => 'integer',
        'credits' => 'decimal:2',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
