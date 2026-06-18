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
use App\Model\Education\Foundation\EducationAuditLog;

/**
 * @internal
 * @coversNothing
 */
final class WorkflowPermissionIsolationAuditTest extends WorkflowApiCase
{
    public function testWorkflowMutationsRequirePermissionAndWriteAudit(): void
    {
        $fixture = $this->workflowFixture('workflow_audit_api');
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);
        $payload = [
            'rule_code' => 'AUDIT_RULE',
            'rule_name' => 'Audit rule',
            'event_type' => 'renewal_alert.opened',
            'actions' => [['action_type' => 'create_task', 'action_config_json' => ['task_type' => 'renewal_follow']]],
        ];

        $denied = $this->post('/admin/education/workflow/rules', $payload, $headers);
        self::assertSame(ResultCode::FORBIDDEN->value, $denied['code']);

        $this->grantPermissions('education:workflow:rule:save');
        $allowed = $this->post('/admin/education/workflow/rules', $payload, $headers);

        self::assertSame(ResultCode::SUCCESS->value, $allowed['code']);
        self::assertSame(1, EducationAuditLog::query()->where('module', 'workflow')->where('action', 'education.workflow.rule.saved')->count());
    }
}
