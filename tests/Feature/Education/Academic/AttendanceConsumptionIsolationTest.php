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
final class AttendanceConsumptionIsolationTest extends AttendanceConsumptionTestCase
{
    public function testTenantUserCannotReadOtherTenantConsumption(): void
    {
        $this->grantPermissions('education:academic:attendance:submit', 'education:academic:consumption:page');
        $tenantA = $this->fixture();
        $tenantB = $this->fixture();
        $this->createTenantProfile($tenantA['tenant']);
        $this->submitAttendance($tenantA);
        $this->createTenantProfile($tenantB['tenant']);
        $this->submitAttendance($tenantB);

        $page = $this->get('/admin/education/academic/consumptions/page', [
            'token' => $this->token,
            'page' => 1,
            'pageSize' => 20,
        ], ['X-Tenant-Id' => (string) $tenantA['tenant']->id]);

        self::assertSame(ResultCode::SUCCESS->value, $page['code']);
        self::assertSame(1, $page['data']['total']);
        self::assertSame((int) $tenantA['account']->id, (int) $page['data']['list'][0]['account_id']);
    }

    public function testCampusScopedUserCannotSubmitOtherCampusLessonAttendance(): void
    {
        $this->grantPermissions('education:academic:attendance:submit');
        $fixture = $this->fixture();
        $other = $this->fixture();
        $this->createTenantProfile($fixture['tenant'], 'academic_staff', $fixture['campus']);

        $result = $this->post('/admin/education/academic/attendance/lessons/' . $other['lesson']->id . '/submit', [
            'records' => [[
                'lesson_student_id' => $other['lessonStudent']->id,
                'attendance_status' => 'present',
                'consume_policy' => 'consume',
                'consumed_units' => '1.00',
            ]],
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }

    public function testAccountAdjustmentRespectsCampusScope(): void
    {
        $this->grantPermissions('education:academic:account-adjustment:create');
        $fixture = $this->fixture();
        $other = $this->fixture();
        $this->createTenantProfile($fixture['tenant'], 'academic_staff', $fixture['campus']);

        $result = $this->post('/admin/education/academic/account-adjustments', [
            'account_id' => $other['account']->id,
            'units' => '1.00',
            'reason' => 'material fee',
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }
}
