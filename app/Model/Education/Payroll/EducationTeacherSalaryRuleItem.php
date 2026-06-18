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

class EducationTeacherSalaryRuleItem extends Model
{
    protected ?string $table = 'edu_teacher_salary_rule_items';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'rule_id', 'item_type', 'workload_type', 'course_id', 'class_type',
        'calculation_method', 'unit_amount_cents', 'rate', 'condition_json', 'sort_order',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'rule_id' => 'integer',
        'course_id' => 'integer',
        'unit_amount_cents' => 'integer',
        'condition_json' => 'array',
        'sort_order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
