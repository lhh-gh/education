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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Workflow\EducationWorkflowTask;
use App\Repository\Education\Workflow\WorkflowTaskLogRepository;
use App\Repository\Education\Workflow\WorkflowTaskRepository;
use Carbon\Carbon;

final class WorkflowTaskService
{
    public function __construct(
        private readonly WorkflowTaskRepository $taskRepository,
        private readonly WorkflowTaskLogRepository $logRepository,
        private readonly TaskAssignmentService $assignmentService
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{task_id: int, status: string}
     */
    public function createTask(array $data): array
    {
        $tenantId = (int) $data['tenant_id'];
        $dedupeKey = (string) ($data['dedupe_key'] ?? '');
        if ($dedupeKey !== '') {
            $existing = $this->taskRepository->activeByDedupeKey($tenantId, $dedupeKey);
            if ($existing instanceof EducationWorkflowTask) {
                return ['task_id' => (int) $existing->id, 'status' => $this->statusValue($existing->status)];
            }
        }

        $task = $this->taskRepository->create([
            'tenant_id' => $tenantId,
            'campus_id' => $data['campus_id'] ?? null,
            'task_no' => $data['task_no'] ?? uniqid('WFT', true),
            'task_type' => (string) $data['task_type'],
            'title' => (string) $data['title'],
            'priority' => $data['priority'] ?? 'normal',
            'status' => $data['status'] ?? 'pending',
            'source_type' => $data['source_type'] ?? null,
            'source_id' => $data['source_id'] ?? null,
            'dedupe_key' => $data['dedupe_key'] ?? null,
            'due_at' => $data['due_at'] ?? null,
            'completed_at' => $data['completed_at'] ?? null,
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? ($data['created_by'] ?? null),
        ]);

        if (isset($data['assignee_user_id'])) {
            $this->assignmentService->assign($tenantId, $data['campus_id'] ?? null, (int) $task->id, (int) $data['assignee_user_id']);
        }

        $this->writeLog($task, null, 'created', null, $this->statusValue($task->status), 'workflow task created');

        return ['task_id' => (int) $task->id, 'status' => $this->statusValue($task->status)];
    }

    /**
     * @return array{task_id: int, status: string}
     */
    public function completeTask(int $taskId, int $userId, string $result, string $content): array
    {
        $task = $this->taskRepository->task($taskId);
        $assignee = $this->taskRepository->assignee($taskId, $userId);
        if ($assignee === null) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'workflow task is assigned to another user', ['task_id' => $taskId]);
        }

        if ($this->statusValue($task->status) === 'completed') {
            return ['task_id' => $taskId, 'status' => 'completed'];
        }

        $before = $this->statusValue($task->status);
        $task->status = 'completed';
        $task->completed_at = Carbon::now();
        $task->updated_by = $userId;
        $task->save();

        $assignee->status = 'completed';
        $assignee->save();

        $this->writeLog($task, $userId, 'completed', $before, 'completed', $result . ': ' . $content);

        return ['task_id' => $taskId, 'status' => 'completed'];
    }

    public function addComment(int $taskId, int $userId, string $content): void
    {
        $task = $this->taskRepository->task($taskId);
        $this->taskRepository->addComment([
            'tenant_id' => (int) $task->tenant_id,
            'campus_id' => $task->campus_id,
            'workflow_task_id' => $taskId,
            'commenter_user_id' => $userId,
            'content' => $content,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
        $this->writeLog($task, $userId, 'commented', $this->statusValue($task->status), $this->statusValue($task->status), $content);
    }

    /**
     * @param array{file_name: string, file_url: string, file_size?: int} $file
     */
    public function attachFile(int $taskId, int $userId, array $file): void
    {
        $task = $this->taskRepository->task($taskId);
        $this->taskRepository->addAttachment([
            'tenant_id' => (int) $task->tenant_id,
            'campus_id' => $task->campus_id,
            'workflow_task_id' => $taskId,
            'file_name' => $file['file_name'],
            'file_url' => $file['file_url'],
            'file_size' => $file['file_size'] ?? 0,
            'uploaded_by' => $userId,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function writeLog(EducationWorkflowTask $task, ?int $operatorId, string $action, ?string $beforeStatus, string $afterStatus, ?string $content = null): void
    {
        $this->logRepository->create([
            'tenant_id' => (int) $task->tenant_id,
            'campus_id' => $task->campus_id,
            'workflow_task_id' => (int) $task->id,
            'operator_id' => $operatorId,
            'action' => $action,
            'before_status' => $beforeStatus,
            'after_status' => $afterStatus,
            'content' => $content,
            'created_by' => $operatorId,
            'updated_by' => $operatorId,
        ]);
    }

    private function statusValue(mixed $status): string
    {
        return \is_object($status) && property_exists($status, 'value') ? (string) $status->value : (string) $status;
    }
}
