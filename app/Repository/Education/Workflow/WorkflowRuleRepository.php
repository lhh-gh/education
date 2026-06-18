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

namespace App\Repository\Education\Workflow;

use App\Model\Education\Workflow\EducationWorkflowRule;
use App\Model\Education\Workflow\EducationWorkflowRuleAction;
use App\Model\Education\Workflow\EducationWorkflowRuleCondition;
use Hyperf\Database\Model\Collection;

final class WorkflowRuleRepository
{
    /**
     * @return Collection<int, EducationWorkflowRule>
     */
    public function activeByEvent(int $tenantId, string $eventType): Collection
    {
        return EducationWorkflowRule::query()
            ->where('tenant_id', $tenantId)
            ->where('event_type', $eventType)
            ->where('status', 'enabled')
            ->orderByDesc('priority')
            ->get();
    }

    /**
     * @return Collection<int, EducationWorkflowRuleCondition>
     */
    public function conditions(int $ruleId): Collection
    {
        return EducationWorkflowRuleCondition::query()
            ->where('rule_id', $ruleId)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return Collection<int, EducationWorkflowRuleAction>
     */
    public function actions(int $ruleId): Collection
    {
        return EducationWorkflowRuleAction::query()
            ->where('rule_id', $ruleId)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationWorkflowRule
    {
        return EducationWorkflowRule::query()->create($data);
    }
}
