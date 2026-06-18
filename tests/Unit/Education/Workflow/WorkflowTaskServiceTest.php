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

use App\Model\Education\Workflow\EducationWorkflowTaskLog;
use App\Service\Education\Workflow\WorkflowTaskService;

/**
 * @internal
 * @coversNothing
 */
final class WorkflowTaskServiceTest extends WorkflowTestCase
{
    public function testAssignedUserCanCompleteTaskOnlyOnce(): void
    {
        $fixture = $this->workflowFixture('workflow_task_complete');
        $service = make(WorkflowTaskService::class);
        $task = $service->createTask([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'task_type' => 'renewal_follow',
            'title' => 'Renewal follow',
            'source_type' => 'renewal',
            'source_id' => $fixture['student_id'],
            'dedupe_key' => 'renewal:' . $fixture['student_id'],
            'assignee_user_id' => $fixture['teacher_user_id'],
            'created_by' => $fixture['teacher_user_id'],
        ]);

        $first = $service->completeTask($task['task_id'], $fixture['teacher_user_id'], 'done', 'followed up');
        $second = $service->completeTask($task['task_id'], $fixture['teacher_user_id'], 'done', 'followed up again');

        self::assertSame('completed', $first['status']);
        self::assertSame('completed', $second['status']);
        self::assertSame(1, EducationWorkflowTaskLog::query()->where('workflow_task_id', $task['task_id'])->where('action', 'completed')->count());
    }
}
