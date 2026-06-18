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

class EducationTeacherWorkloadRecord extends Model
{
    protected ?string $table = 'edu_teacher_workload_records';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'teacher_id',
        'lesson_id',
        'workload_type',
        'lesson_type',
        'credits',
        'student_count',
        'present_count',
        'leave_count',
        'absent_count',
        'recorded_at',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'teacher_id' => 'integer',
        'lesson_id' => 'integer',
        'credits' => 'decimal:2',
        'student_count' => 'integer',
        'present_count' => 'integer',
        'leave_count' => 'integer',
        'absent_count' => 'integer',
        'recorded_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
