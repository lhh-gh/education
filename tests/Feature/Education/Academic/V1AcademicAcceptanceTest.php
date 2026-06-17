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
final class V1AcademicAcceptanceTest extends ProfileRecordAdminCase
{
    use AcademicReportApiFixture;

    public function testV1EnrollmentToConsumptionHappyPath(): void
    {
        $this->grantPermissions(
            'education:academic:report:dashboard',
            'education:academic:report:attendance',
            'education:academic:report:consumption',
            'education:academic:report:account-balance',
            'education:academic:report:acceptance'
        );
        $fixture = $this->reportFixture('v1_happy_path');
        $this->createTenantProfile($fixture['tenant']);
        $headers = $this->tenantHeaders($fixture['tenant']);

        $dashboard = $this->get('/admin/education/academic/reports/dashboard', [
            'campus_id' => $fixture['campus']->id,
            'start_at' => '2026-06-01 00:00:00',
            'end_at' => '2026-06-30 23:59:59',
        ], $headers);
        $attendance = $this->get('/admin/education/academic/reports/attendance', $this->reportRangeParams([
            'campus_id' => $fixture['campus']->id,
        ]), $headers);
        $consumption = $this->get('/admin/education/academic/reports/consumption', $this->reportRangeParams([
            'campus_id' => $fixture['campus']->id,
        ]), $headers);
        $balances = $this->get('/admin/education/academic/reports/account-balances', [
            'page' => 1,
            'pageSize' => 20,
            'campus_id' => $fixture['campus']->id,
        ], $headers);
        $acceptance = $this->get('/admin/education/academic/reports/v1-acceptance-summary', [
            'campus_id' => $fixture['campus']->id,
            'include_detail' => true,
        ], $headers);

        self::assertSame(ResultCode::SUCCESS->value, $dashboard['code']);
        self::assertSame(1, $dashboard['data']['metrics']['active_student_count']);
        self::assertSame('1.00', $dashboard['data']['metrics']['net_consumed_units']);
        self::assertSame(ResultCode::SUCCESS->value, $attendance['code']);
        self::assertSame(1, $attendance['data']['summary']['present_count']);
        self::assertSame(ResultCode::SUCCESS->value, $consumption['code']);
        self::assertSame('1.00', $consumption['data']['summary']['net_units']);
        self::assertSame(ResultCode::SUCCESS->value, $balances['code']);
        self::assertSame('19.00', $balances['data']['summary']['total_available_units']);
        self::assertSame(ResultCode::SUCCESS->value, $acceptance['code']);
        self::assertSame('pass', $acceptance['data']['overall_status']);
        self::assertSame(0, $acceptance['data']['ledger']['mismatch_count']);
    }

    public function testV1LeaveAndMakeupFlowIsVisibleInReports(): void
    {
        $this->grantPermissions('education:academic:report:dashboard', 'education:academic:report:leave');
        $fixture = $this->reportFixture('v1_leave_flow');
        $this->createTenantProfile($fixture['tenant']);
        $headers = $this->tenantHeaders($fixture['tenant']);

        $pending = $this->get('/admin/education/academic/reports/leaves', $this->reportRangeParams([
            'campus_id' => $fixture['campus']->id,
            'status' => 'pending',
        ]), $headers);
        $fixture['leave']->update([
            'status' => 'approved',
            'reviewed_at' => '2026-06-12 09:00:00',
            'reviewed_by' => $this->user->id,
            'review_remark' => 'Approved for makeup scheduling',
        ]);
        $approved = $this->get('/admin/education/academic/reports/leaves', $this->reportRangeParams([
            'campus_id' => $fixture['campus']->id,
            'status' => 'approved',
        ]), $headers);
        $dashboard = $this->get('/admin/education/academic/reports/dashboard', [
            'campus_id' => $fixture['campus']->id,
            'start_at' => '2026-06-01 00:00:00',
            'end_at' => '2026-06-30 23:59:59',
        ], $headers);

        self::assertSame(ResultCode::SUCCESS->value, $pending['code']);
        self::assertSame(1, $pending['data']['summary']['pending_count']);
        self::assertSame(ResultCode::SUCCESS->value, $approved['code']);
        self::assertSame(1, $approved['data']['summary']['approved_count']);
        self::assertSame('Approved for makeup scheduling', $fixture['leave']->refresh()->review_remark);
        self::assertSame(ResultCode::SUCCESS->value, $dashboard['code']);
        self::assertSame(1, $dashboard['data']['metrics']['approved_leave_count']);
    }
}
