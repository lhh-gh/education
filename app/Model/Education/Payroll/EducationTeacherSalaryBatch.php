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

class EducationTeacherSalaryBatch extends Model
{
    protected ?string $table = 'edu_teacher_salary_batches';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'batch_no', 'salary_month', 'status', 'source_start', 'source_end',
        'teacher_count', 'total_amount_cents', 'calculated_by', 'calculated_at', 'submitted_at', 'approved_at',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'source_start' => 'date',
        'source_end' => 'date',
        'teacher_count' => 'integer',
        'total_amount_cents' => 'integer',
        'calculated_by' => 'integer',
        'calculated_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
