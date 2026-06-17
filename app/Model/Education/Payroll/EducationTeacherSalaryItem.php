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

class EducationTeacherSalaryItem extends Model
{
    protected ?string $table = 'edu_teacher_salary_items';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'salary_slip_id', 'teacher_id', 'source_workload_id', 'item_type',
        'item_name', 'quantity', 'unit_amount_cents', 'amount_cents', 'rule_item_id', 'snapshot_json',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'salary_slip_id' => 'integer',
        'teacher_id' => 'integer',
        'source_workload_id' => 'integer',
        'unit_amount_cents' => 'integer',
        'amount_cents' => 'integer',
        'rule_item_id' => 'integer',
        'snapshot_json' => 'array',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
