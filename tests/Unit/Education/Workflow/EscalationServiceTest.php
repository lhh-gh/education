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

namespace HyperfTests\Unit\Education\Workflow;

use App\Model\Education\Workflow\EducationWorkflowEscalationPolicy;
use App\Model\Education\Workflow\EducationWorkflowTaskAssignee;
use App\Model\Education\Workflow\EducationWorkflowTaskLog;
use App\Service\Education\Workflow\EscalationService;
use App\Service\Education\Workflow\WorkflowTaskService;

/**
 * @internal
 * @coversNothing
 */
final class EscalationServiceTest extends WorkflowTestCase
{
    public function testEscalationCreatesAssigneeOrAlert(): void
    {
        $fixture = $this->workflowFixture('workflow_escalation');
        $managerUserId = $fixture['teacher_user_id'] + 900000;
        EducationWorkflowEscalationPolicy::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'policy_code' => 'renewal-escalation',
            'task_type' => 'renewal_follow',
            'overdue_minutes' => 30,
            'escalate_to_user_ids_json' => [$managerUserId],
            'status' => 'enabled',
        ]);
        $task = make(WorkflowTaskService::class)->createTask([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'task_type' => 'renewal_follow',
            'title' => 'Renewal follow',
            'status' => 'overdue',
            'due_at' => '2026-06-10 10:00:00',
            'assignee_user_id' => $fixture['teacher_user_id'],
            'created_by' => $fixture['teacher_user_id'],
        ]);

        $result = make(EscalationService::class)->escalateOverdueTasks($fixture['tenant_id'], '2026-06-10 11:00:00');

        self::assertSame(1, $result['escalated_count']);
        self::assertTrue(EducationWorkflowTaskAssignee::query()->where('workflow_task_id', $task['task_id'])->where('user_id', $managerUserId)->exists());
        self::assertTrue(EducationWorkflowTaskLog::query()->where('workflow_task_id', $task['task_id'])->where('action', 'sla_escalated')->exists());
    }
}
