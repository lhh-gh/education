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
use App\Model\Education\Foundation\EducationAuditLog;

/**
 * @internal
 * @coversNothing
 */
final class ProfileRecordAuditTest extends ProfileRecordAdminCase
{
    public function testProfileWritesCreateAuditLogs(): void
    {
        $this->grantPermissions(
            'education:academic:classroom:create',
            'education:academic:classroom:update',
            'education:academic:classroom:status',
            'education:academic:classroom:delete',
            'education:academic:student:create',
            'education:academic:guardian:create',
            'education:academic:student-guardian:save'
        );
        $tenant = $this->tenant();
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant);

        $classroom = $this->post('/admin/education/academic/classrooms', [
            'campus_id' => $campus->id,
            'code' => 'A101',
            'name' => 'A101',
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant, ['X-Request-Id' => 'req-audit-classroom-create']));
        self::assertSame(ResultCode::SUCCESS->value, $classroom['code']);

        $this->put('/admin/education/academic/classrooms/' . $classroom['data']['id'], [
            'campus_id' => $campus->id,
            'code' => 'A101',
            'name' => 'A101 Plus',
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));
        $this->put('/admin/education/academic/classrooms/' . $classroom['data']['id'] . '/status', ['status' => 'disabled'], $this->tenantHeaders($tenant));
        $this->delete('/admin/education/academic/classrooms/' . $classroom['data']['id'], [], $this->tenantHeaders($tenant));

        $student = $this->post('/admin/education/academic/students', [
            'campus_id' => $campus->id,
            'student_no' => 'S001',
            'name' => 'Student Zhang',
            'gender' => 'female',
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));
        $guardian = $this->post('/admin/education/academic/guardians', [
            'name' => 'Guardian Li',
            'mobile' => '13900000000',
            'gender' => 'female',
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));
        $this->put('/admin/education/academic/students/' . $student['data']['id'] . '/guardians', [
            'relations' => [['guardian_id' => $guardian['data']['id'], 'relation' => 'mother']],
        ], $this->tenantHeaders($tenant));

        foreach ([
            'education.academic.classroom.created',
            'education.academic.classroom.updated',
            'education.academic.classroom.status_changed',
            'education.academic.classroom.deleted',
            'education.academic.student_guardian.saved',
        ] as $action) {
            self::assertTrue(EducationAuditLog::query()->where('action', $action)->exists(), "missing audit {$action}");
        }
    }
}
