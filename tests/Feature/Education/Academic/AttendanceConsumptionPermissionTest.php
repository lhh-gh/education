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
use App\Model\Education\Academic\EducationLessonConsumption;

/**
 * @internal
 * @coversNothing
 */
final class AttendanceConsumptionPermissionTest extends AttendanceConsumptionTestCase
{
    public function testMissingAttendanceSubmitPermissionReturns403(): void
    {
        $fixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant']);

        $result = $this->post('/admin/education/academic/attendance/lessons/' . $fixture['lesson']->id . '/submit', [
            'records' => [[
                'lesson_student_id' => $fixture['lessonStudent']->id,
                'attendance_status' => 'present',
                'consume_policy' => 'consume',
                'consumed_units' => '1.00',
            ]],
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }

    public function testFrontDeskCannotRollbackConsumption(): void
    {
        $this->grantPermissions('education:academic:attendance:submit');
        $fixture = $this->fixture();
        $profile = $this->createTenantProfile($fixture['tenant']);
        $this->submitAttendance($fixture);
        $profile->role_code = 'front_desk';
        $profile->save();
        $this->clearCurrentUserCache();
        $consumption = EducationLessonConsumption::query()->where('source_type', 'attendance')->first();

        $result = $this->post('/admin/education/academic/consumptions/' . $consumption->id . '/rollback', [
            'reason' => 'not allowed',
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }
}
