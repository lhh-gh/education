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

namespace App\Model\Education\Workflow;

use App\Model\Enums\Education\Workflow\WorkflowPriority;
use App\Model\Enums\Education\Workflow\WorkflowTaskStatus;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationWorkflowTask extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_workflow_tasks';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'task_no', 'task_type', 'title', 'priority', 'status',
        'source_type', 'source_id', 'dedupe_key', 'due_at', 'completed_at', 'created_by',
        'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'priority' => WorkflowPriority::class, 'status' => WorkflowTaskStatus::class,
        'source_id' => 'integer', 'due_at' => 'datetime', 'completed_at' => 'datetime',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];
}
