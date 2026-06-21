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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Workflow\EducationOperationAlert;
use App\Model\Education\Workflow\EducationWorkflowRule;
use App\Model\Education\Workflow\EducationWorkflowTask;
use App\Model\Education\Workflow\EducationWorkflowTaskAssignee;
use App\Model\Education\Workflow\EducationWorkflowTaskComment;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Workflow\AlertService;
use App\Service\Education\Workflow\WorkflowRuleService;
use App\Service\Education\Workflow\WorkflowTaskService;

/**
 * @internal
 * @coversNothing
 */
final class WorkflowRuleServiceTest extends WorkflowTestCase
{
    public function testTaskPageOnlyReturnsCurrentCampusTasks(): void
    {
        $fixture = $this->workflowFixture('workflow_task_page_scope');
        $tenant = EducationTenant::query()->findOrFail($fixture['tenant_id']);
        $hiddenCampus = $this->campus($tenant, 'hidden-workflow-task-page');
        $visible = $this->workflowTask($fixture['tenant_id'], $fixture['campus_id'], 'Visible task');
        $this->workflowTask($fixture['tenant_id'], (int) $hiddenCampus->id, 'Hidden task');
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);

        $page = make(WorkflowTaskService::class)->pageTasks($context);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }

    public function testCompleteRejectsTaskOutsideCurrentCampus(): void
    {
        $fixture = $this->workflowFixture('workflow_task_complete_scope');
        $tenant = EducationTenant::query()->findOrFail($fixture['tenant_id']);
        $hiddenCampus = $this->campus($tenant, 'hidden-workflow-task-complete');
        $task = $this->workflowTask($fixture['tenant_id'], (int) $hiddenCampus->id, 'Hidden completion task');
        EducationWorkflowTaskAssignee::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $hiddenCampus->id,
            'workflow_task_id' => $task->id,
            'user_id' => $fixture['teacher_user_id'],
            'assignee_type' => 'owner',
            'status' => 'pending',
        ]);
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);

        try {
            make(WorkflowTaskService::class)->completeTask((int) $task->id, $context, 'done', 'hidden');
            self::fail('Hidden workflow task should not be completed.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::NOT_FOUND->value, $exception->getCode());
        }

        self::assertSame('pending', EducationWorkflowTask::query()->find($task->id)->status->value);
    }

    public function testCommentRejectsTaskOutsideCurrentCampus(): void
    {
        $fixture = $this->workflowFixture('workflow_task_comment_scope');
        $tenant = EducationTenant::query()->findOrFail($fixture['tenant_id']);
        $hiddenCampus = $this->campus($tenant, 'hidden-workflow-task-comment');
        $task = $this->workflowTask($fixture['tenant_id'], (int) $hiddenCampus->id, 'Hidden comment task');
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);

        try {
            make(WorkflowTaskService::class)->addComment((int) $task->id, $context, 'hidden');
            self::fail('Hidden workflow task should not be commented.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::NOT_FOUND->value, $exception->getCode());
        }

        self::assertSame(0, EducationWorkflowTaskComment::query()->where('workflow_task_id', $task->id)->count());
    }

    public function testEnableRejectsRuleOutsideCurrentCampus(): void
    {
        $fixture = $this->workflowFixture('workflow_rule_scope');
        $tenant = EducationTenant::query()->findOrFail($fixture['tenant_id']);
        $hiddenCampus = $this->campus($tenant, 'hidden-workflow-rule');
        $rule = EducationWorkflowRule::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $hiddenCampus->id,
            'rule_code' => uniqid('hidden_rule_', false),
            'rule_name' => 'Hidden Rule',
            'event_type' => 'renewal_alert.opened',
            'status' => 'disabled',
            'priority' => 10,
            'dedupe_window_minutes' => 1440,
        ]);
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);

        try {
            make(WorkflowRuleService::class)->setEnabled((int) $rule->id, true, $context);
            self::fail('Hidden workflow rule should not be enabled.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::NOT_FOUND->value, $exception->getCode());
        }

        self::assertSame('disabled', EducationWorkflowRule::query()->find($rule->id)->status->value);
    }

    public function testAlertPageOnlyReturnsCurrentCampusAlerts(): void
    {
        $fixture = $this->workflowFixture('workflow_alert_page_scope');
        $tenant = EducationTenant::query()->findOrFail($fixture['tenant_id']);
        $hiddenCampus = $this->campus($tenant, 'hidden-workflow-alert-page');
        $visible = $this->operationAlert($fixture['tenant_id'], $fixture['campus_id'], 'Visible alert');
        $this->operationAlert($fixture['tenant_id'], (int) $hiddenCampus->id, 'Hidden alert');
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);

        $page = make(AlertService::class)->page($context);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }

    public function testAlertConvertRejectsAlertOutsideCurrentCampus(): void
    {
        $fixture = $this->workflowFixture('workflow_alert_convert_scope');
        $tenant = EducationTenant::query()->findOrFail($fixture['tenant_id']);
        $hiddenCampus = $this->campus($tenant, 'hidden-workflow-alert-convert');
        $alert = $this->operationAlert($fixture['tenant_id'], (int) $hiddenCampus->id, 'Hidden convert alert');
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);

        try {
            make(AlertService::class)->convertToTask((int) $alert->id, $fixture['teacher_user_id'], null, $context);
            self::fail('Hidden workflow alert should not be converted.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::NOT_FOUND->value, $exception->getCode());
        }

        $alert->refresh();
        self::assertSame('open', $alert->status->value);
        self::assertNull($alert->converted_task_id);
    }

    public function testAlertStatusRejectsAlertOutsideCurrentCampus(): void
    {
        $fixture = $this->workflowFixture('workflow_alert_status_scope');
        $tenant = EducationTenant::query()->findOrFail($fixture['tenant_id']);
        $hiddenCampus = $this->campus($tenant, 'hidden-workflow-alert-status');
        $alert = $this->operationAlert($fixture['tenant_id'], (int) $hiddenCampus->id, 'Hidden status alert');
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);

        try {
            make(AlertService::class)->setStatus((int) $alert->id, 'closed', $context);
            self::fail('Hidden workflow alert should not be closed.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::NOT_FOUND->value, $exception->getCode());
        }

        self::assertSame('open', $alert->refresh()->status->value);
    }

    private function workflowTask(int $tenantId, int $campusId, string $title): EducationWorkflowTask
    {
        return EducationWorkflowTask::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'task_no' => uniqid('scope_task_', false),
            'task_type' => 'renewal_follow',
            'title' => $title,
            'priority' => 'normal',
            'status' => 'pending',
        ]);
    }

    private function operationAlert(int $tenantId, int $campusId, string $title): EducationOperationAlert
    {
        return EducationOperationAlert::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'alert_no' => uniqid('scope_alert_', false),
            'alert_type' => 'renewal',
            'level' => 'warning',
            'status' => 'open',
            'title' => $title,
            'content' => $title,
        ]);
    }
}
