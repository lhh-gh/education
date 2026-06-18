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

class EducationHomeworkAssignment extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_homework_assignments';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'title', 'content', 'course_id', 'class_id', 'lesson_id',
        'status', 'publish_at', 'due_at', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'course_id' => 'integer',
        'class_id' => 'integer',
        'lesson_id' => 'integer',
        'status' => HomeworkStatus::class,
        'publish_at' => 'datetime',
        'due_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
