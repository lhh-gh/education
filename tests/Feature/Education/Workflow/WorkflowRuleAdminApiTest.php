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
use App\Model\Education\Workflow\EducationWorkflowRuleAction;

/**
 * @internal
 * @coversNothing
 */
final class WorkflowRuleAdminApiTest extends WorkflowApiCase
{
    public function testWorkflowRuleSaveValidatesAndStoresRows(): void
    {
        $fixture = $this->workflowFixture('workflow_rule_api');
        $this->grantPermissions('education:workflow:rule:save');
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);

        $invalid = $this->post('/admin/education/workflow/rules', ['rule_code' => 'LOW_BALANCE'], $headers);
        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $invalid['code']);
        self::assertSame('rule_name is required', $invalid['message']);

        $saved = $this->post('/admin/education/workflow/rules', [
            'rule_code' => 'LOW_BALANCE',
            'rule_name' => 'Low balance',
            'event_type' => 'renewal_alert.opened',
            'conditions' => [
                ['condition_field' => 'alert_level', 'operator' => 'eq', 'condition_value_json' => 'urgent'],
            ],
            'actions' => [
                ['action_type' => 'create_task', 'action_config_json' => ['task_type' => 'renewal_follow']],
            ],
        ], $headers);

        self::assertSame(ResultCode::SUCCESS->value, $saved['code']);
        self::assertSame('disabled', $saved['data']['status']);
        self::assertTrue(EducationWorkflowRuleAction::query()->where('rule_id', $saved['data']['rule_id'])->where('action_type', 'create_task')->exists());
    }
}
