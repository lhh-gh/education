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

namespace App\Repository\Education\Workflow;

use App\Model\Education\Workflow\EducationWorkflowTask;
use App\Model\Education\Workflow\EducationWorkflowTaskAssignee;
use App\Model\Education\Workflow\EducationWorkflowTaskAttachment;
use App\Model\Education\Workflow\EducationWorkflowTaskComment;
use Hyperf\Database\Model\Collection;

final class WorkflowTaskRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationWorkflowTask
    {
        return EducationWorkflowTask::query()->create($data);
    }

    public function task(int $id): EducationWorkflowTask
    {
        return EducationWorkflowTask::query()->findOrFail($id);
    }

    public function activeByDedupeKey(int $tenantId, string $dedupeKey): ?EducationWorkflowTask
    {
        return EducationWorkflowTask::query()
            ->where('tenant_id', $tenantId)
            ->where('dedupe_key', $dedupeKey)
            ->whereIn('status', ['pending', 'processing', 'overdue'])
            ->first();
    }

    /**
     * @return Collection<int, EducationWorkflowTask>
     */
    public function overdueTasks(int $tenantId, string $now): Collection
    {
        return EducationWorkflowTask::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'processing'])
            ->whereNotNull('due_at')
            ->where('due_at', '<', $now)
            ->get();
    }

    /**
     * @return Collection<int, EducationWorkflowTask>
     */
    public function escalatableOverdueTasks(int $tenantId, string $now): Collection
    {
        return EducationWorkflowTask::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'overdue')
            ->whereNotNull('due_at')
            ->where('due_at', '<', $now)
            ->get();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function assign(array $data): EducationWorkflowTaskAssignee
    {
        return EducationWorkflowTaskAssignee::query()->firstOrCreate([
            'tenant_id' => $data['tenant_id'],
            'workflow_task_id' => $data['workflow_task_id'],
            'user_id' => $data['user_id'],
        ], $data);
    }

    public function assignee(int $taskId, int $userId): ?EducationWorkflowTaskAssignee
    {
        return EducationWorkflowTaskAssignee::query()
            ->where('workflow_task_id', $taskId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function addComment(array $data): EducationWorkflowTaskComment
    {
        return EducationWorkflowTaskComment::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function addAttachment(array $data): EducationWorkflowTaskAttachment
    {
        return EducationWorkflowTaskAttachment::query()->create($data);
    }
}
