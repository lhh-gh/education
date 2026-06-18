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

use App\Model\Education\Workflow\EducationWorkflowTask;
use App\Model\Education\Workflow\EducationWorkflowTaskLog;
use App\Service\Education\Workflow\SlaService;
use App\Service\Education\Workflow\WorkflowTaskService;

/**
 * @internal
 * @coversNothing
 */
final class SlaServiceTest extends WorkflowTestCase
{
    public function testOverdueTaskMarksStatusAndWritesLog(): void
    {
        $fixture = $this->workflowFixture('workflow_sla');
        $task = make(WorkflowTaskService::class)->createTask([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'task_type' => 'renewal_follow',
            'title' => 'Renewal follow',
            'due_at' => '2026-06-10 10:00:00',
            'assignee_user_id' => $fixture['teacher_user_id'],
            'created_by' => $fixture['teacher_user_id'],
        ]);

        $result = make(SlaService::class)->markOverdueTasks($fixture['tenant_id'], '2026-06-10 11:00:00');

        self::assertSame(1, $result['overdue_count']);
        self::assertSame('overdue', EducationWorkflowTask::query()->find($task['task_id'])->status->value);
        self::assertTrue(EducationWorkflowTaskLog::query()->where('workflow_task_id', $task['task_id'])->where('action', 'sla_overdue')->exists());
    }
}
