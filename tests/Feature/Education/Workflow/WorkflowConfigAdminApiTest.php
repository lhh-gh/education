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
use App\Model\Education\Workflow\EducationWorkflowEscalationPolicy;
use App\Model\Education\Workflow\EducationWorkflowSlaPolicy;
use App\Model\Education\Workflow\EducationWorkflowTemplate;

/**
 * @internal
 * @coversNothing
 */
final class WorkflowConfigAdminApiTest extends WorkflowApiCase
{
    public function testConfigPagesAndDashboardAliasAreAvailable(): void
    {
        $fixture = $this->workflowFixture('workflow_config_api');
        $this->grantPermissions(
            'education:workflow:sla:page',
            'education:workflow:escalation:page',
            'education:workflow:template:page',
            'education:workflow:metric:page',
        );
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);

        EducationWorkflowSlaPolicy::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'policy_code' => 'SLA_RENEWAL',
            'policy_name' => 'Renewal SLA',
            'task_type' => 'renewal_follow',
            'due_minutes' => 60,
            'status' => 'enabled',
        ]);
        EducationWorkflowEscalationPolicy::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'policy_code' => 'ESC_RENEWAL',
            'task_type' => 'renewal_follow',
            'overdue_minutes' => 30,
            'escalate_to_user_ids_json' => [$this->user->id],
            'status' => 'enabled',
        ]);
        EducationWorkflowTemplate::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'template_code' => 'TPL_RENEWAL',
            'template_name' => 'Renewal template',
            'task_type' => 'renewal_follow',
            'template_json' => ['steps' => []],
            'status' => 'enabled',
        ]);

        $slaPage = $this->get('/admin/education/workflow/sla-policies/page', ['task_type' => 'renewal_follow'], $headers);
        $escalationPage = $this->get('/admin/education/workflow/escalation-policies/page', ['task_type' => 'renewal_follow'], $headers);
        $templatePage = $this->get('/admin/education/workflow/templates/page', ['task_type' => 'renewal_follow'], $headers);
        $dashboard = $this->get('/admin/education/workflow/dashboard', [], $headers);

        self::assertSame(ResultCode::SUCCESS->value, $slaPage['code']);
        self::assertSame(1, $slaPage['data']['total']);
        self::assertSame('SLA_RENEWAL', $slaPage['data']['list'][0]['policy_code']);
        self::assertSame(ResultCode::SUCCESS->value, $escalationPage['code']);
        self::assertSame(1, $escalationPage['data']['total']);
        self::assertSame('ESC_RENEWAL', $escalationPage['data']['list'][0]['policy_code']);
        self::assertSame(ResultCode::SUCCESS->value, $templatePage['code']);
        self::assertSame(1, $templatePage['data']['total']);
        self::assertSame('TPL_RENEWAL', $templatePage['data']['list'][0]['template_code']);
        self::assertSame(ResultCode::SUCCESS->value, $dashboard['code']);
        self::assertArrayHasKey('created_count', $dashboard['data']);
    }
}
