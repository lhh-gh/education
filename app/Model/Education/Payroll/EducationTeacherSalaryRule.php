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

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationTeacherSalaryRule extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_teacher_salary_rules';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'rule_code', 'rule_name', 'campus_scope_json', 'teacher_grade',
        'status', 'effective_start', 'effective_end', 'priority', 'remark',
        'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'campus_scope_json' => 'array',
        'effective_start' => 'date',
        'effective_end' => 'date',
        'priority' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
