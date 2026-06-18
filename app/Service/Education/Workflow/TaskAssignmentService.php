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

use App\Repository\Education\Workflow\WorkflowTaskRepository;

final class TaskAssignmentService
{
    public function __construct(
        private readonly WorkflowTaskRepository $taskRepository
    ) {}

    public function assign(int $tenantId, ?int $campusId, int $taskId, int $userId, string $type = 'owner'): void
    {
        $this->taskRepository->assign([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'workflow_task_id' => $taskId,
            'user_id' => $userId,
            'assignee_type' => $type,
            'status' => 'pending',
        ]);
    }
}
