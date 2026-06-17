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
final class AttendanceReportApiTest extends ProfileRecordAdminCase
{
    use AcademicReportApiFixture;

    public function testAttendanceReportContract(): void
    {
        $this->grantPermissions('education:academic:report:attendance');
        $fixture = $this->reportFixture('attendance_api');
        $this->createTenantProfile($fixture['tenant']);

        $response = $this->get('/admin/education/academic/reports/attendance', $this->reportRangeParams([
            'campus_id' => $fixture['campus']->id,
            'attendance_status' => 'present',
            'group_by' => 'teacher',
        ]), $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $response['code']);
        self::assertSame(1, $response['data']['summary']['total_records']);
        self::assertSame(1, $response['data']['summary']['present_count']);
        self::assertSame('100.00', $response['data']['summary']['attendance_rate']);
        self::assertSame(1, $response['data']['total']);
        self::assertSame('present', $response['data']['list'][0]['attendance_status']);
        self::assertSame('1.00', $response['data']['list'][0]['consumed_units']);
    }

    public function testAttendanceReportPermissionRequired(): void
    {
        $fixture = $this->reportFixture('attendance_permission');
        $this->createTenantProfile($fixture['tenant']);

        $response = $this->get('/admin/education/academic/reports/attendance', $this->reportRangeParams(), $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::FORBIDDEN->value, $response['code']);
    }
}
