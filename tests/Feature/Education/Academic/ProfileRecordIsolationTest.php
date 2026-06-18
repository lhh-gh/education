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
use App\Model\Education\Academic\EducationClassroom;
use App\Model\Education\Academic\EducationStudent;

/**
 * @internal
 * @coversNothing
 */
final class ProfileRecordIsolationTest extends ProfileRecordAdminCase
{
    public function testTenantUserCannotReadOtherTenantStudent(): void
    {
        $this->grantPermissions('education:academic:student:page', 'education:academic:student:update');
        $tenantA = $this->tenant('tenant_a');
        $tenantB = $this->tenant('tenant_b');
        $campusA = $this->campus($tenantA, 'main_a');
        $campusB = $this->campus($tenantB, 'main_b');
        $this->createTenantProfile($tenantA);
        EducationStudent::query()->create(['tenant_id' => $tenantA->id, 'campus_id' => $campusA->id, 'student_no' => 'A001', 'name' => 'Student A']);
        $studentB = EducationStudent::query()->create(['tenant_id' => $tenantB->id, 'campus_id' => $campusB->id, 'student_no' => 'B001', 'name' => 'Student B']);

        $page = $this->get('/admin/education/academic/students/page', ['token' => $this->token, 'page' => 1, 'pageSize' => 20], ['X-Tenant-Id' => (string) $tenantA->id]);
        self::assertSame(1, $page['data']['total']);
        self::assertSame('A001', $page['data']['list'][0]['student_no']);

        $update = $this->put('/admin/education/academic/students/' . $studentB->id, [
            'campus_id' => $campusA->id,
            'student_no' => 'B001',
            'name' => 'Student B',
            'gender' => 'unknown',
            'status' => 'enabled',
        ], $this->tenantHeaders($tenantA));
        self::assertSame(ResultCode::FORBIDDEN->value, $update['code']);
    }

    public function testCampusScopedUserCannotUpdateOtherCampusClassroom(): void
    {
        $this->grantPermissions('education:academic:classroom:update');
        $tenant = $this->tenant();
        $allowedCampus = $this->campus($tenant, 'allowed');
        $blockedCampus = $this->campus($tenant, 'blocked');
        $this->createTenantProfile($tenant, 'academic_staff', $allowedCampus);
        $classroom = EducationClassroom::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $blockedCampus->id,
            'code' => 'B101',
            'name' => 'Blocked Room',
        ]);

        $result = $this->put('/admin/education/academic/classrooms/' . $classroom->id, [
            'campus_id' => $blockedCampus->id,
            'code' => 'B101',
            'name' => 'Blocked Room',
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }
}
