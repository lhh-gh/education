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

class EducationHomeworkReview extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_homework_reviews';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'homework_submission_id', 'teacher_id', 'score', 'content',
        'reviewed_at', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'homework_submission_id' => 'integer',
        'teacher_id' => 'integer',
        'score' => 'integer',
        'reviewed_at' => 'datetime',
        'status' => HomeworkStatus::class,
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
