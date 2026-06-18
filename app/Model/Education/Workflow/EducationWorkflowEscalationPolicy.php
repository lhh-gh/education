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

use App\Model\Enums\Education\Workflow\WorkflowRuleStatus;
use Hyperf\DbConnection\Model\Model;

class EducationWorkflowEscalationPolicy extends Model
{
    protected ?string $table = 'edu_workflow_escalation_policies';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'policy_code', 'task_type', 'overdue_minutes',
        'escalate_to_user_ids_json', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'overdue_minutes' => 'integer', 'escalate_to_user_ids_json' => 'array',
        'status' => WorkflowRuleStatus::class, 'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}
