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

class EducationTeacherPerformanceMetric extends Model
{
    protected ?string $table = 'edu_teacher_performance_metrics';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'metric_month', 'teacher_id', 'lesson_count', 'workload_credits',
        'student_count', 'attendance_rate', 'family_service_count', 'dispute_count', 'salary_amount_cents',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'teacher_id' => 'integer',
        'lesson_count' => 'integer',
        'student_count' => 'integer',
        'family_service_count' => 'integer',
        'dispute_count' => 'integer',
        'salary_amount_cents' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
