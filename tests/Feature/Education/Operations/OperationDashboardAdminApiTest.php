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
}
