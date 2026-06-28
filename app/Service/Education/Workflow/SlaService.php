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

use App\Model\Education\Workflow\EducationWorkflowSlaPolicy;
use App\Repository\Education\Workflow\WorkflowTaskRepository;
use Carbon\Carbon;

final class SlaService
{
    public function __construct(
        private readonly WorkflowTaskRepository $taskRepository,
        private readonly WorkflowTaskService $taskService
    ) {}

    /**
     * @return array{overdue_count: int}
     */
    public function markOverdueTasks(int $tenantId, ?string $now = null): array
    {
        $nowString = $now ?? Carbon::now()->toDateTimeString();
        $count = 0;
        foreach ($this->taskRepository->overdueTasks($tenantId, $nowString) as $task) {
            $before = (string) $task->status->value;
            $task->status = 'overdue';
            $task->save();
            $this->taskService->writeLog($task, null, 'sla_overdue', $before, 'overdue', 'task exceeded SLA due time');
            ++$count;
        }

        return ['overdue_count' => $count];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{policy_id: int}
     */
    public function savePolicy(array $data): array
    {
        $policy = EducationWorkflowSlaPolicy::query()->create($data);

        return ['policy_id' => (int) $policy->id];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pagePolicies(int $tenantId, array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        $query = EducationWorkflowSlaPolicy::query()->where('tenant_id', $tenantId);
        if (($filters['task_type'] ?? '') !== '') {
            $query->where('task_type', $filters['task_type']);
        }
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', $filters['status']);
        }
        $total = (int) $query->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray();

        return ['list' => $list, 'total' => $total];
    }
}
