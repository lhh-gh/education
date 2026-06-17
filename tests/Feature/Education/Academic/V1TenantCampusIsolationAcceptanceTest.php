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
final class V1TenantCampusIsolationAcceptanceTest extends ProfileRecordAdminCase
{
    use AcademicReportApiFixture;

    public function testReportsExcludeOtherTenantData(): void
    {
        $this->grantPermissions('education:academic:report:dashboard');
        $fixture = $this->reportFixture('tenant_isolation');
        $other = $this->reportFixture('other_tenant_isolation');
        $this->createTenantProfile($fixture['tenant']);

        $response = $this->get('/admin/education/academic/reports/dashboard', [
            'start_at' => '2026-06-01 00:00:00',
            'end_at' => '2026-06-30 23:59:59',
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $response['code']);
        self::assertSame(1, $response['data']['metrics']['active_student_count']);
        self::assertNotSame((int) $other['tenant']->id, (int) $fixture['tenant']->id);
    }

    public function testReportsRejectOutOfScopeCampus(): void
    {
        $this->grantPermissions('education:academic:report:attendance', 'education:academic:report:acceptance');
        $fixture = $this->reportFixture('campus_isolation');
        $outOfScopeCampus = $this->scopedCampus($fixture['tenant'], 'west');
        $this->createTenantProfile($fixture['tenant'], 'academic_staff', $fixture['campus']);

        $attendance = $this->get('/admin/education/academic/reports/attendance', $this->reportRangeParams([
            'campus_id' => $outOfScopeCampus->id,
        ]), $this->tenantHeaders($fixture['tenant']));
        $acceptance = $this->get('/admin/education/academic/reports/v1-acceptance-summary', [
            'campus_id' => $outOfScopeCampus->id,
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::FORBIDDEN->value, $attendance['code']);
        self::assertSame(ResultCode::FORBIDDEN->value, $acceptance['code']);
    }
}
