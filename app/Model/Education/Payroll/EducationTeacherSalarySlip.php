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

class EducationTeacherSalarySlip extends Model
{
    protected ?string $table = 'edu_teacher_salary_slips';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'batch_id', 'teacher_id', 'salary_month', 'status',
        'workload_snapshot_json', 'gross_amount_cents', 'adjustment_amount_cents', 'payable_amount_cents',
        'paid_amount_cents', 'approved_at', 'paid_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'batch_id' => 'integer',
        'teacher_id' => 'integer',
        'workload_snapshot_json' => 'array',
        'gross_amount_cents' => 'integer',
        'adjustment_amount_cents' => 'integer',
        'payable_amount_cents' => 'integer',
        'paid_amount_cents' => 'integer',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
