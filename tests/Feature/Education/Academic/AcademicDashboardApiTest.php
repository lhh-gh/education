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

namespace HyperfTests\Feature\Education\Academic;

use App\Http\Common\ResultCode;

/**
 * @internal
 * @coversNothing
 */
final class AcademicDashboardApiTest extends ProfileRecordAdminCase
{
    use AcademicReportApiFixture;

    public function testDashboardContract(): void
    {
        $this->grantPermissions('education:academic:report:dashboard');
        $fixture = $this->reportFixture('dashboard_api');
        $this->createTenantProfile($fixture['tenant']);

        $response = $this->get('/admin/education/academic/reports/dashboard', [
            'campus_id' => $fixture['campus']->id,
            'start_at' => '2026-06-01 00:00:00',
            'end_at' => '2026-06-30 23:59:59',
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $response['code']);
        self::assertSame(1, $response['data']['metrics']['active_student_count']);
        self::assertSame(1, $response['data']['metrics']['completed_lesson_count']);
        self::assertSame('1.00', $response['data']['metrics']['net_consumed_units']);
        self::assertSame('19.00', $response['data']['metrics']['total_available_units']);
        self::assertSame(1, $response['data']['metrics']['pending_leave_count']);
        self::assertNotEmpty($response['data']['trends']);
    }
}
