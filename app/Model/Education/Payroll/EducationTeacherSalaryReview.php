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

class EducationTeacherSalaryReview extends Model
{
    protected ?string $table = 'edu_teacher_salary_reviews';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'batch_id', 'salary_slip_id', 'review_level', 'reviewer_id',
        'status', 'review_note', 'reviewed_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'batch_id' => 'integer',
        'salary_slip_id' => 'integer',
        'review_level' => 'integer',
        'reviewer_id' => 'integer',
        'reviewed_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
