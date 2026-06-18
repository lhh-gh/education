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
use App\Service\Education\Workflow\WorkflowTaskService;

/**
 * @internal
 * @coversNothing
 */
final class MobileWorkflowTaskApiTest extends WorkflowApiCase
{
    public function testMobileUserCanCompleteOnlyAssignedTask(): void
    {
        $fixture = $this->workflowFixture('workflow_mobile_api', 'teacher');
        $headers = $this->mobileHeaders($fixture['tenant']);
        $assigned = make(WorkflowTaskService::class)->createTask([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'task_type' => 'renewal_follow',
            'title' => 'Renewal follow',
            'assignee_user_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);
        $unassigned = make(WorkflowTaskService::class)->createTask([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'task_type' => 'renewal_follow',
            'title' => 'Other renewal follow',
            'assignee_user_id' => $this->user->id + 1000,
            'created_by' => $this->user->id,
        ]);

        $ok = $this->post('/mobile/education/workflow/tasks/' . $assigned['task_id'] . '/complete', [
            'result' => 'done',
            'content' => 'completed on mobile',
        ], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $ok['code']);

        $denied = $this->post('/mobile/education/workflow/tasks/' . $unassigned['task_id'] . '/complete', [
            'result' => 'done',
            'content' => 'completed on mobile',
        ], $headers);
        self::assertSame(ResultCode::FORBIDDEN->value, $denied['code']);
    }
}
