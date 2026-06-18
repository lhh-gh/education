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

namespace HyperfTests\Unit\Education\Group;

use App\Service\Education\Group\ApprovalInstanceService;
use App\Service\Education\Group\ApprovalTemplateService;

/**
 * @internal
 * @coversNothing
 */
final class ApprovalInstanceServiceTest extends GroupTestCase
{
    public function testAssignedUserCompletesTaskAndAdvancesState(): void
    {
        $tenant = $this->tenant('group_approval');
        $context = $this->context((int) $tenant->id, userId: 7301);
        $templateService = make(ApprovalTemplateService::class);
        $instanceService = make(ApprovalInstanceService::class);

        $template = $templateService->save([
            'template_code' => 'CONTRACT_REVIEW',
            'template_name' => 'Contract Review',
            'business_type' => 'contract',
            'nodes' => [[
                'node_code' => 'review',
                'node_name' => 'Review',
                'sort_order' => 1,
                'assignee_type' => 'user',
                'assignee_user_id' => 7302,
            ]],
        ], $context);
        $instance = $instanceService->create([
            'template_id' => $template['template_id'],
            'business_type' => 'contract',
            'business_id' => 1001,
            'payload_json' => ['amount_cents' => 500000],
        ], $context);
        $result = $instanceService->completeTask($instance['task_id'], ['result' => 'approved', 'comment' => 'ok'], $this->context((int) $tenant->id, userId: 7302));

        self::assertSame('completed', $result['task_status']);
        self::assertSame('approved', $result['instance_status']);
    }
}
