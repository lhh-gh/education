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

use App\Model\Enums\Education\Family\PublishStatus;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationLearningReport extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_learning_reports';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'student_id', 'report_title', 'report_period', 'status',
        'published_at', 'withdrawn_at', 'summary', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'student_id' => 'integer',
        'status' => PublishStatus::class,
        'published_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
