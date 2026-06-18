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

class EducationTeacherWorkloadDispute extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_teacher_workload_disputes';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'teacher_id', 'source_workload_id', 'salary_slip_id', 'dispute_type',
        'content', 'status', 'reviewed_by', 'reviewed_at', 'review_note',
        'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'teacher_id' => 'integer',
        'source_workload_id' => 'integer',
        'salary_slip_id' => 'integer',
        'reviewed_by' => 'integer',
        'reviewed_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
