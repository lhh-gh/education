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
use App\Model\Education\Operations\EducationDailyOperationMetric;
use App\Model\Education\Operations\EducationRenewalAlert;

/**
 * @internal
 * @coversNothing
 */
final class OperationDashboardAdminApiTest extends OperationApiCase
{
    public function testPlatformUserCanOpenDashboardWithoutTenantHeader(): void
    {
        $this->grantPermissions('education:operations:dashboard:overview');
        $fixture = $this->fixture('ops_dashboard_page_platform');
        $this->createEducationProfile();
        EducationDailyOperationMetric::query()->create([
            'tenant_id' => $fixture['tenant']->id,
            'campus_id' => $fixture['campus']->id,
            'metric_date' => '2026-06-18',
            'lessons_count' => 3,
            'pending_attendance_count' => 1,
            'consumed_credits' => '6.00',
            'present_count' => 8,
            'leave_count' => 1,
            'absent_count' => 0,
            'renewal_alert_count' => 2,
            'pending_review_count' => 1,
        ]);

        $overview = $this->get('/admin/education/operations/dashboard/overview', [], $this->authHeaders());

        self::assertSame(ResultCode::SUCCESS->value, $overview['code']);
        self::assertSame(3, $overview['data']['summary']['lessons_count']);
        self::assertSame('6.00', $overview['data']['summary']['consumed_credits']);
        self::assertCount(1, $overview['data']['trend']);
    }

    public function testPlatformUserCanOpenDashboardWidgetsWithoutTenantHeader(): void
    {
        $this->grantPermissions('education:operations:dashboard:overview');
        $fixture = $this->fixture('ops_dashboard_widgets_platform');
        $this->createEducationProfile();
        EducationDailyOperationMetric::query()->create([
            'tenant_id' => $fixture['tenant']->id,
            'campus_id' => $fixture['campus']->id,
            'metric_date' => '2026-06-18',
            'lessons_count' => 4,
            'pending_attendance_count' => 1,
            'consumed_credits' => '7.50',
            'present_count' => 8,
            'leave_count' => 1,
            'absent_count' => 0,
            'renewal_alert_count' => 3,
            'pending_review_count' => 2,
        ]);
        EducationRenewalAlert::query()->create(['tenant_id' => $fixture['tenant']->id, 'campus_id' => $fixture['campus']->id, 'student_id' => 1, 'course_id' => $fixture['course']->id, 'student_course_account_id' => 1, 'alert_type' => 'low_balance', 'alert_level' => 'urgent', 'status' => 'open', 'trigger_value' => '1.00', 'threshold_value' => '2.00']);

        $trend = $this->get('/admin/education/operations/dashboard/consumption-trend', [], $this->authHeaders());
        $renewal = $this->get('/admin/education/operations/dashboard/renewal-alert-summary', [], $this->authHeaders());
        $daily = $this->get('/admin/education/operations/dashboard/daily-metrics', [], $this->authHeaders());

        self::assertSame(ResultCode::SUCCESS->value, $trend['code']);
        self::assertSame('2026-06-18', $trend['data']['list'][0]['date']);
        self::assertSame('7.50', $trend['data']['list'][0]['consumed_units']);
        self::assertSame(2, $trend['data']['list'][0]['review_count']);
        self::assertSame(ResultCode::SUCCESS->value, $renewal['code']);
        self::assertSame(1, $renewal['data']['urgent_count']);
        self::assertSame(0, $renewal['data']['warning_count']);
        self::assertSame(ResultCode::SUCCESS->value, $daily['code']);
        self::assertSame('2026-06-18', $daily['data']['list'][0]['date']);
        self::assertSame(2, $daily['data']['list'][0]['consumption_review_count']);
        self::assertSame(3, $daily['data']['list'][0]['renewal_alert_count']);
    }
}
