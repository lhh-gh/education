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

namespace HyperfTests\Feature\Education\Workflow;

use App\Http\Common\ResultCode;
use App\Model\Education\Workflow\EducationWorkflowTaskComment;
use App\Service\Education\Workflow\WorkflowTaskService;

/**
 * @internal
 * @coversNothing
 */
final class WorkflowTaskAdminApiTest extends WorkflowApiCase
{
    public function testAssignedAdminCanCompleteAndCommentTask(): void
    {
        $fixture = $this->workflowFixture('workflow_task_api');
        $this->grantPermissions('education:workflow:task:complete', 'education:workflow:task:comment');
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);
        $task = make(WorkflowTaskService::class)->createTask([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'task_type' => 'renewal_follow',
            'title' => 'Renewal follow',
            'assignee_user_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);

        $comment = $this->post('/admin/education/workflow/tasks/' . $task['task_id'] . '/comments', [
            'content' => 'called parent',
        ], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $comment['code']);
        self::assertTrue(EducationWorkflowTaskComment::query()->where('workflow_task_id', $task['task_id'])->where('content', 'called parent')->exists());

        $completed = $this->post('/admin/education/workflow/tasks/' . $task['task_id'] . '/complete', [
            'result' => 'done',
            'content' => 'completed',
        ], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $completed['code']);
        self::assertSame('completed', $completed['data']['status']);
    }
}
