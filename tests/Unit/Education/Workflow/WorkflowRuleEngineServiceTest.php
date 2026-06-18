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
use App\Model\Education\Workflow\EducationWorkflowExecutionLog;
use App\Model\Education\Workflow\EducationWorkflowRule;
use App\Model\Education\Workflow\EducationWorkflowRuleAction;
use App\Model\Education\Workflow\EducationWorkflowRuleCondition;
use App\Model\Education\Workflow\EducationWorkflowTask;
use App\Service\Education\Workflow\WorkflowRuleEngineService;

/**
 * @internal
 * @coversNothing
 */
final class WorkflowRuleEngineServiceTest extends WorkflowTestCase
{
    public function testActiveRuleGeneratesOneTaskByDedupeKey(): void
    {
        $fixture = $this->workflowFixture('workflow_rule_engine');
        $rule = $this->enabledRule($fixture, 'renewal_alert.opened');
        EducationWorkflowRuleCondition::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'rule_id' => $rule->id,
            'condition_field' => 'alert_level',
            'operator' => 'eq',
            'condition_value_json' => 'urgent',
        ]);
        EducationWorkflowRuleAction::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'rule_id' => $rule->id,
            'action_type' => 'create_task',
            'action_config_json' => [
                'task_type' => 'renewal_follow',
                'title' => 'Renewal follow',
                'assignee_user_id' => $fixture['teacher_user_id'],
            ],
        ]);
        $service = make(WorkflowRuleEngineService::class);

        $first = $service->handleEvent($fixture['tenant_id'], $fixture['campus_id'], 'renewal_alert.opened', [
            'alert_level' => 'urgent',
            'dedupe_key' => 'renewal:' . $fixture['student_id'],
            'source_type' => 'renewal',
            'source_id' => $fixture['student_id'],
        ], $fixture['teacher_user_id']);
        $second = $service->handleEvent($fixture['tenant_id'], $fixture['campus_id'], 'renewal_alert.opened', [
            'alert_level' => 'urgent',
            'dedupe_key' => 'renewal:' . $fixture['student_id'],
            'source_type' => 'renewal',
            'source_id' => $fixture['student_id'],
        ], $fixture['teacher_user_id']);

        self::assertSame($first['task_ids'], $second['task_ids']);
        self::assertCount(1, $first['task_ids']);
        self::assertSame(1, EducationWorkflowTask::query()->where('dedupe_key', 'renewal:' . $fixture['student_id'])->count());
        self::assertTrue(EducationWorkflowExecutionLog::query()->where('rule_id', $rule->id)->where('status', 'succeeded')->exists());
    }

    public function testForbiddenBusinessMutationActionIsRejected(): void
    {
        $fixture = $this->workflowFixture('workflow_forbidden_action');
        $rule = $this->enabledRule($fixture, 'finance.order.created');
        EducationWorkflowRuleAction::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'rule_id' => $rule->id,
            'action_type' => 'modify_finance',
            'action_config_json' => ['table' => 'orders'],
        ]);
        $service = make(WorkflowRuleEngineService::class);

        try {
            $service->handleEvent($fixture['tenant_id'], $fixture['campus_id'], 'finance.order.created', [
                'dedupe_key' => 'finance:' . $fixture['student_id'],
            ], $fixture['teacher_user_id']);
            self::fail('Forbidden workflow action should throw.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $exception->getCode());
            self::assertSame('workflow action cannot mutate protected business records', $exception->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $fixture
     */
    private function enabledRule(array $fixture, string $eventType): EducationWorkflowRule
    {
        return EducationWorkflowRule::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'rule_code' => uniqid('rule_', false),
            'rule_name' => 'Rule',
            'event_type' => $eventType,
            'status' => 'enabled',
            'priority' => 10,
            'dedupe_window_minutes' => 1440,
        ]);
    }
}
