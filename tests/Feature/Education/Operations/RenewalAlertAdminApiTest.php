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

namespace HyperfTests\Feature\Education\Operations;

use App\Http\Common\ResultCode;
use App\Model\Education\Operations\EducationRenewalAlert;

/**
 * @internal
 * @coversNothing
 */
final class RenewalAlertAdminApiTest extends OperationApiCase
{
    public function testPlatformUserCanPageRenewalAlertsWithoutTenantHeader(): void
    {
        $this->grantPermissions('education:operations:renewal-alert:page');
        $fixture = $this->fixture('ops_renewal_page_platform');
        $this->createEducationProfile();
        $alert = EducationRenewalAlert::query()->create(['tenant_id' => $fixture['tenant']->id, 'campus_id' => $fixture['campus']->id, 'student_id' => 1, 'course_id' => $fixture['course']->id, 'student_course_account_id' => 1, 'alert_type' => 'low_balance', 'alert_level' => 'urgent', 'status' => 'open', 'trigger_value' => '1.00', 'threshold_value' => '2.00']);

        $page = $this->get('/admin/education/operations/renewal-alerts/page?page=1&pageSize=20&status=open', [], $this->authHeaders());

        self::assertSame(ResultCode::SUCCESS->value, $page['code']);
        self::assertSame(1, $page['data']['total']);
        self::assertSame($alert->id, $page['data']['list'][0]['id']);
    }

    public function testFollowRecordUpdatesTaskStatus(): void
    {
        $this->grantPermissions('education:operations:renewal-task:follow');
        $fixture = $this->fixture('ops_renewal_api');
        $this->createTenantProfile($fixture['tenant']);
        $alert = EducationRenewalAlert::query()->create(['tenant_id' => $fixture['tenant']->id, 'campus_id' => $fixture['campus']->id, 'student_id' => 1, 'course_id' => $fixture['course']->id, 'student_course_account_id' => 1, 'alert_type' => 'low_balance', 'alert_level' => 'urgent', 'status' => 'open', 'trigger_value' => '1.00', 'threshold_value' => '2.00']);
        $task = $this->post('/admin/education/operations/renewal-alerts/' . $alert->id . '/assign', ['assignee_id' => $this->user->id], $this->tenantHeaders($fixture['tenant']));
        $follow = $this->post('/admin/education/operations/renewal-tasks/' . $task['data']['id'] . '/follow', ['follow_type' => 'phone', 'content' => 'will renew'], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $follow['code']);
        self::assertSame('following', $follow['data']['task_status']);
    }
}
