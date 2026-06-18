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

namespace HyperfTests\Feature\Education\Group;

use App\Http\Common\ResultCode;

/**
 * @internal
 * @coversNothing
 */
final class ApprovalAdminApiTest extends GroupApiCase
{
    public function testApprovalApisCreateDuplicateAndCompleteTask(): void
    {
        $fixture = $this->groupFixture('group_approval_api');
        $this->createTenantProfile($fixture['tenant'], 'tenant_admin', $fixture['campus']);
        $this->grantPermissions(
            'education:group:approval-template:save',
            'education:group:approval:create',
            'education:group:approval:complete',
            'education:group:approval-task:page'
        );

        $template = $this->post('/admin/education/group/approval-templates', [
            'template_code' => 'CONTRACT',
            'template_name' => 'Contract Approval',
            'business_type' => 'contract',
            'nodes' => [[
                'node_code' => 'manager',
                'node_name' => 'Manager',
                'sort_order' => 1,
                'assignee_user_id' => $this->user->id,
            ]],
        ], $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus']->id]));
        self::assertSame(ResultCode::SUCCESS->value, $template['code']);

        $instance = $this->post('/admin/education/group/approval-instances', [
            'template_id' => $template['data']['template_id'],
            'business_type' => 'contract',
            'business_id' => 1001,
            'payload_json' => ['amount_cents' => 500000],
        ], $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus']->id]));
        self::assertSame(ResultCode::SUCCESS->value, $instance['code']);
        self::assertSame('pending', $instance['data']['status']);

        $duplicate = $this->post('/admin/education/group/approval-instances', [
            'template_id' => $template['data']['template_id'],
            'business_type' => 'contract',
            'business_id' => 1001,
            'payload_json' => ['amount_cents' => 500000],
        ], $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus']->id]));
        self::assertSame(ResultCode::CONFLICT->value, $duplicate['code']);

        $completed = $this->post('/admin/education/group/approval-tasks/' . $instance['data']['task_id'] . '/complete', [
            'result' => 'approved',
            'comment' => 'ok',
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::SUCCESS->value, $completed['code']);
        self::assertSame('approved', $completed['data']['instance_status']);
    }
}
