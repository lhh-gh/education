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
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationHomeworkSubmission extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_homework_submissions';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'homework_target_id', 'student_id', 'guardian_id', 'content',
        'attachment_count', 'submitted_at', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'homework_target_id' => 'integer',
        'student_id' => 'integer',
        'guardian_id' => 'integer',
        'attachment_count' => 'integer',
        'submitted_at' => 'datetime',
        'status' => HomeworkStatus::class,
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
