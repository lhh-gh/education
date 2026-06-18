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
use App\Model\Education\Workflow\EducationWorkflowExecutionLog;
use App\Model\Education\Workflow\EducationWorkflowRuleAction;
use App\Model\Education\Workflow\EducationWorkflowRuleCondition;
use App\Repository\Education\Workflow\WorkflowRuleRepository;

final class WorkflowRuleEngineService
{
    private const FORBIDDEN_ACTIONS = [
        'modify_finance', 'modify_payroll', 'modify_enrollment', 'modify_consumption',
        'modify_course_account', 'modify_contract', 'modify_attendance',
    ];

    public function __construct(
        private readonly WorkflowRuleRepository $ruleRepository,
        private readonly WorkflowTaskService $taskService,
        private readonly AlertService $alertService
    ) {}

    /**
     * @param array<string, mixed> $payload
     * @return array{task_ids: list<int>, alert_ids: list<int>}
     */
    public function handleEvent(int $tenantId, ?int $campusId, string $eventType, array $payload, ?int $operatorId = null): array
    {
        $taskIds = [];
        $alertIds = [];
        foreach ($this->ruleRepository->activeByEvent($tenantId, $eventType) as $rule) {
            if (! $this->matchesConditions((int) $rule->id, $payload)) {
                continue;
            }
            foreach ($this->ruleRepository->actions((int) $rule->id) as $action) {
                if (\in_array((string) $action->action_type, self::FORBIDDEN_ACTIONS, true)) {
                    throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'workflow action cannot mutate protected business records', [
                        'action_type' => (string) $action->action_type,
                    ]);
                }
                if ($action->action_type === 'create_task') {
                    $taskIds[] = $this->createTaskFromAction($tenantId, $campusId, $action, $payload, $operatorId);
                }
                if ($action->action_type === 'create_alert') {
                    $alertIds[] = $this->createAlertFromAction($tenantId, $campusId, $action, $payload, $operatorId);
                }
            }
            EducationWorkflowExecutionLog::query()->create([
                'tenant_id' => $tenantId,
                'campus_id' => $campusId,
                'rule_id' => (int) $rule->id,
                'event_type' => $eventType,
                'dedupe_key' => $payload['dedupe_key'] ?? null,
                'status' => 'succeeded',
                'result_json' => ['task_ids' => $taskIds, 'alert_ids' => $alertIds],
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ]);
        }

        return ['task_ids' => array_values(array_unique($taskIds)), 'alert_ids' => array_values(array_unique($alertIds))];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function matchesConditions(int $ruleId, array $payload): bool
    {
        foreach ($this->ruleRepository->conditions($ruleId) as $condition) {
            if (! $this->matchesCondition($condition, $payload)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function matchesCondition(EducationWorkflowRuleCondition $condition, array $payload): bool
    {
        $actual = $payload[(string) $condition->condition_field] ?? null;
        $expected = $condition->condition_value_json;
        if (\is_array($expected) && \array_key_exists('value', $expected)) {
            $expected = $expected['value'];
        }

        return match ((string) $condition->operator) {
            'eq' => $actual === $expected,
            'neq' => $actual !== $expected,
            'in' => \is_array($expected) && \in_array($actual, $expected, true),
            default => false,
        };
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function createTaskFromAction(int $tenantId, ?int $campusId, EducationWorkflowRuleAction $action, array $payload, ?int $operatorId): int
    {
        $config = $action->action_config_json;
        $task = $this->taskService->createTask([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'task_type' => $config['task_type'] ?? 'workflow_task',
            'title' => $config['title'] ?? 'Workflow task',
            'priority' => $config['priority'] ?? 'normal',
            'source_type' => $payload['source_type'] ?? null,
            'source_id' => $payload['source_id'] ?? null,
            'dedupe_key' => $payload['dedupe_key'] ?? null,
            'due_at' => $config['due_at'] ?? null,
            'assignee_user_id' => $config['assignee_user_id'] ?? null,
            'created_by' => $operatorId,
            'updated_by' => $operatorId,
        ]);

        return $task['task_id'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function createAlertFromAction(int $tenantId, ?int $campusId, EducationWorkflowRuleAction $action, array $payload, ?int $operatorId): int
    {
        $config = $action->action_config_json;
        $alert = $this->alertService->createAlert([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'alert_type' => $config['alert_type'] ?? 'workflow_alert',
            'level' => $config['level'] ?? 'warning',
            'title' => $config['title'] ?? 'Workflow alert',
            'content' => $config['content'] ?? 'Workflow alert',
            'source_type' => $payload['source_type'] ?? null,
            'source_id' => $payload['source_id'] ?? null,
            'dedupe_key' => $payload['dedupe_key'] ?? null,
            'created_by' => $operatorId,
            'updated_by' => $operatorId,
        ]);

        return $alert['alert_id'];
    }
}
