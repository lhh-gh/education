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

use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Foundation\EducationAuditLog;

/**
 * @internal
 * @coversNothing
 */
final class AttendanceConsumptionAuditTest extends AttendanceConsumptionTestCase
{
    public function testAttendanceSubmitCreatesAuditLog(): void
    {
        $this->grantPermissions('education:academic:attendance:submit');
        $fixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant']);
        $this->submitAttendance($fixture);

        self::assertTrue(EducationAuditLog::query()->where('action', 'education.academic.attendance.submitted')->exists());
    }

    public function testConsumptionRollbackCreatesAuditLog(): void
    {
        $this->grantPermissions('education:academic:attendance:submit', 'education:academic:consumption:rollback');
        $fixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant']);
        $this->submitAttendance($fixture);
        $consumption = EducationLessonConsumption::query()->where('source_type', 'attendance')->first();
        $this->post('/admin/education/academic/consumptions/' . $consumption->id . '/rollback', ['reason' => 'audit'], $this->tenantHeaders($fixture['tenant']));

        self::assertTrue(EducationAuditLog::query()->where('action', 'education.academic.consumption.rollback')->exists());
    }

    public function testAdjustmentCreateAndRollbackCreateAuditLogs(): void
    {
        $this->grantPermissions('education:academic:account-adjustment:create', 'education:academic:account-adjustment:rollback');
        $fixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant']);
        $created = $this->post('/admin/education/academic/account-adjustments', [
            'account_id' => $fixture['account']->id,
            'units' => '1.00',
            'reason' => 'material fee',
        ], $this->tenantHeaders($fixture['tenant']));
        $this->post('/admin/education/academic/account-adjustments/' . $created['data']['adjustment']['id'] . '/rollback', ['reason' => 'audit'], $this->tenantHeaders($fixture['tenant']));

        self::assertTrue(EducationAuditLog::query()->where('action', 'education.academic.account_adjustment.created')->exists());
        self::assertTrue(EducationAuditLog::query()->where('action', 'education.academic.account_adjustment.rollback')->exists());
    }
}
