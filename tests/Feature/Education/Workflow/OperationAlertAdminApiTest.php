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
use App\Model\Education\Workflow\EducationOperationAlert;

/**
 * @internal
 * @coversNothing
 */
final class OperationAlertAdminApiTest extends WorkflowApiCase
{
    public function testAlertCanBeConvertedToTask(): void
    {
        $fixture = $this->workflowFixture('workflow_alert_api');
        $this->grantPermissions('education:workflow:alert:convert', 'education:workflow:alert:page');
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);
        $alert = EducationOperationAlert::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'alert_no' => uniqid('AL', true),
            'alert_type' => 'renewal_follow',
            'level' => 'warning',
            'status' => 'open',
            'title' => 'Renewal warning',
            'content' => 'Low balance',
            'dedupe_key' => 'alert:' . $fixture['student_id'],
        ]);

        $converted = $this->post('/admin/education/workflow/alerts/' . $alert->id . '/convert-task', [
            'assignee_user_id' => $this->user->id,
            'due_at' => '2026-06-11 18:00:00',
        ], $headers);

        self::assertSame(ResultCode::SUCCESS->value, $converted['code']);
        self::assertSame('converted', $converted['data']['status']);
        self::assertGreaterThan(0, $converted['data']['converted_task_id']);
    }
}
