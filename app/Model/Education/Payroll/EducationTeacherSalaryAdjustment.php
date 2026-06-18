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

namespace App\Model\Education\Payroll;

use Hyperf\DbConnection\Model\Model;

class EducationTeacherSalaryAdjustment extends Model
{
    protected ?string $table = 'edu_teacher_salary_adjustments';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'salary_slip_id', 'teacher_id', 'adjustment_type', 'amount_cents',
        'reason', 'operator_id', 'approved_by', 'approved_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'salary_slip_id' => 'integer',
        'teacher_id' => 'integer',
        'amount_cents' => 'integer',
        'operator_id' => 'integer',
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
