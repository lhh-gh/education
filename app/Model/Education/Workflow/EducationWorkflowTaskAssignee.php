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

use App\Model\Enums\Education\Workflow\WorkflowTaskStatus;
use Hyperf\DbConnection\Model\Model;

class EducationWorkflowTaskAssignee extends Model
{
    protected ?string $table = 'edu_workflow_task_assignees';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'workflow_task_id', 'user_id', 'assignee_type',
        'status', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'workflow_task_id' => 'integer', 'user_id' => 'integer', 'status' => WorkflowTaskStatus::class,
        'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}
