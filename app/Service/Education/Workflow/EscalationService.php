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

namespace App\Service\Education\Workflow;

use App\Repository\Education\Workflow\EscalationPolicyRepository;
use App\Repository\Education\Workflow\WorkflowTaskRepository;
use Carbon\Carbon;

final class EscalationService
{
    public function __construct(
        private readonly WorkflowTaskRepository $taskRepository,
        private readonly EscalationPolicyRepository $policyRepository,
        private readonly TaskAssignmentService $assignmentService,
        private readonly WorkflowTaskService $taskService
    ) {}

    /**
     * @return array{escalated_count: int}
     */
    public function escalateOverdueTasks(int $tenantId, ?string $now = null): array
    {
        $nowCarbon = Carbon::parse($now ?? Carbon::now()->toDateTimeString());
        $count = 0;
        foreach ($this->taskRepository->escalatableOverdueTasks($tenantId, $nowCarbon->toDateTimeString()) as $task) {
            foreach ($this->policyRepository->enabledForTaskType($tenantId, (string) $task->task_type) as $policy) {
                $dueAt = Carbon::parse((string) $task->due_at);
                if ($dueAt->copy()->addMinutes((int) $policy->overdue_minutes)->greaterThan($nowCarbon)) {
                    continue;
                }
                foreach ((array) $policy->escalate_to_user_ids_json as $userId) {
                    $this->assignmentService->assign((int) $task->tenant_id, $task->campus_id, (int) $task->id, (int) $userId, 'escalation');
                }
                $this->taskService->writeLog($task, null, 'sla_escalated', 'overdue', 'overdue', 'task escalated by policy ' . $policy->policy_code);
                ++$count;
            }
        }

        return ['escalated_count' => $count];
    }
}
