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

namespace App\Model\Education\Family;

use App\Model\Enums\Education\Family\HomeworkStatus;
use Hyperf\DbConnection\Model\Model;

class EducationHomeworkTarget extends Model
{
    protected ?string $table = 'edu_homework_targets';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'homework_assignment_id', 'student_id', 'guardian_id', 'status',
        'submitted_at', 'reviewed_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'homework_assignment_id' => 'integer',
        'student_id' => 'integer',
        'guardian_id' => 'integer',
        'status' => HomeworkStatus::class,
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
