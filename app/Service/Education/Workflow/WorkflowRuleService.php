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

use App\Model\Education\Workflow\EducationWorkflowRule;
use App\Model\Education\Workflow\EducationWorkflowRuleAction;
use App\Model\Education\Workflow\EducationWorkflowRuleCondition;
use App\Repository\Education\Workflow\WorkflowRuleRepository;

final class WorkflowRuleService
{
    public function __construct(
        private readonly WorkflowRuleRepository $ruleRepository
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{rule_id: int, status: string}
     */
    public function save(array $data): array
    {
        $rule = $this->ruleRepository->create([
            'tenant_id' => $data['tenant_id'],
            'campus_id' => $data['campus_id'] ?? null,
            'rule_code' => $data['rule_code'],
            'rule_name' => $data['rule_name'],
            'event_type' => $data['event_type'],
            'status' => $data['status'] ?? 'disabled',
            'priority' => $data['priority'] ?? 0,
            'dedupe_window_minutes' => $data['dedupe_window_minutes'] ?? 1440,
            'description' => $data['description'] ?? null,
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ]);

        foreach (($data['conditions'] ?? []) as $index => $condition) {
            EducationWorkflowRuleCondition::query()->create([
                'tenant_id' => $data['tenant_id'],
                'campus_id' => $data['campus_id'] ?? null,
                'rule_id' => $rule->id,
                'condition_field' => $condition['condition_field'],
                'operator' => $condition['operator'],
                'condition_value_json' => $condition['condition_value_json'],
                'sort_order' => $condition['sort_order'] ?? $index,
                'created_by' => $data['created_by'] ?? null,
                'updated_by' => $data['updated_by'] ?? null,
            ]);
        }

        foreach (($data['actions'] ?? []) as $index => $action) {
            EducationWorkflowRuleAction::query()->create([
                'tenant_id' => $data['tenant_id'],
                'campus_id' => $data['campus_id'] ?? null,
                'rule_id' => $rule->id,
                'action_type' => $action['action_type'],
                'action_config_json' => $action['action_config_json'],
                'sort_order' => $action['sort_order'] ?? $index,
                'created_by' => $data['created_by'] ?? null,
                'updated_by' => $data['updated_by'] ?? null,
            ]);
        }

        return ['rule_id' => (int) $rule->id, 'status' => (string) $rule->status->value];
    }

    /**
     * @return array{rule_id: int, status: string}
     */
    public function setEnabled(int $id, bool $enabled): array
    {
        $rule = EducationWorkflowRule::query()->findOrFail($id);
        $rule->status = $enabled ? 'enabled' : 'disabled';
        $rule->save();

        return ['rule_id' => $id, 'status' => $enabled ? 'enabled' : 'disabled'];
    }
}
