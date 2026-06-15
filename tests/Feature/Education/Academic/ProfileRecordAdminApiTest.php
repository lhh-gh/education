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
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Permission\User;

/**
 * @internal
 * @coversNothing
 */
final class ProfileRecordAdminApiTest extends ProfileRecordAdminCase
{
    public function testClassroomCrudReturnsMineadminResultShape(): void
    {
        $this->grantPermissions(
            'education:academic:classroom:page',
            'education:academic:classroom:create',
            'education:academic:classroom:update',
            'education:academic:classroom:status',
            'education:academic:classroom:delete'
        );
        $tenant = $this->tenant();
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant);

        $create = $this->post('/admin/education/academic/classrooms', [
            'campus_id' => $campus->id,
            'code' => 'A101',
            'name' => 'A101',
            'capacity' => 20,
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $create['code']);
        self::assertSame('A101', $create['data']['code']);

        $id = (int) $create['data']['id'];
        $update = $this->put('/admin/education/academic/classrooms/' . $id, [
            'campus_id' => $campus->id,
            'code' => 'A101',
            'name' => 'A101 Plus',
            'capacity' => 22,
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $update['code']);
        self::assertSame('A101 Plus', $update['data']['name']);

        $status = $this->put('/admin/education/academic/classrooms/' . $id . '/status', [
            'status' => 'disabled',
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $status['code']);
        self::assertSame('disabled', $status['data']['status']);

        $page = $this->get('/admin/education/academic/classrooms/page', [
            'token' => $this->token,
            'page' => 1,
            'pageSize' => 20,
            'campus_id' => $campus->id,
        ], ['X-Tenant-Id' => (string) $tenant->id]);
        self::assertSame(ResultCode::SUCCESS->value, $page['code']);
        self::assertSame(1, $page['data']['total']);

        $delete = $this->delete('/admin/education/academic/classrooms/' . $id, [], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $delete['code']);
    }

    public function testStudentCrudAndGuardianSaveWorkflow(): void
    {
        $this->grantPermissions(
            'education:academic:student:create',
            'education:academic:student-guardian:save',
            'education:academic:student-guardian:page',
            'education:academic:guardian:create'
        );
        $tenant = $this->tenant();
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant);

        $student = $this->post('/admin/education/academic/students', [
            'campus_id' => $campus->id,
            'student_no' => 'S001',
            'name' => 'Student Zhang',
            'gender' => 'female',
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $student['code']);

        $guardian = $this->post('/admin/education/academic/guardians', [
            'name' => 'Guardian Li',
            'mobile' => '13900000000',
            'gender' => 'female',
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $guardian['code']);

        $save = $this->put('/admin/education/academic/students/' . $student['data']['id'] . '/guardians', [
            'relations' => [[
                'guardian_id' => $guardian['data']['id'],
                'relation' => 'mother',
                'is_primary' => true,
            ]],
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $save['code']);
        self::assertSame('Guardian Li', $save['data']['list'][0]['guardian_name']);

        $list = $this->get('/admin/education/academic/students/' . $student['data']['id'] . '/guardians', [
            'token' => $this->token,
        ], ['X-Tenant-Id' => (string) $tenant->id]);
        self::assertSame(ResultCode::SUCCESS->value, $list['code']);
        self::assertTrue($list['data']['list'][0]['is_primary']);
    }

    public function testGuardianCrudReturnsExpectedFields(): void
    {
        $this->grantPermissions('education:academic:guardian:create', 'education:academic:guardian:update', 'education:academic:guardian:page');
        $tenant = $this->tenant();
        $this->createTenantProfile($tenant);

        $create = $this->post('/admin/education/academic/guardians', [
            'name' => 'Guardian Li',
            'mobile' => '13900000000',
            'gender' => 'female',
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $create['code']);

        $update = $this->put('/admin/education/academic/guardians/' . $create['data']['id'], [
            'name' => 'Guardian Li Plus',
            'mobile' => '13900000000',
            'gender' => 'female',
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $update['code']);

        $page = $this->get('/admin/education/academic/guardians/page', ['token' => $this->token, 'page' => 1, 'pageSize' => 20], ['X-Tenant-Id' => (string) $tenant->id]);
        self::assertSame('13900000000', $page['data']['list'][0]['mobile']);
        self::assertSame(0, $page['data']['list'][0]['student_count']);
    }

    public function testTeacherCrudValidatesUserProfile(): void
    {
        $this->grantPermissions('education:academic:teacher:create', 'education:academic:teacher:page');
        $tenant = $this->tenant();
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant);
        $teacherUser = User::query()->create(['username' => 'edu_ac_tuser']);
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenant->id . ':' . $teacherUser->id,
            'tenant_id' => $tenant->id,
            'user_id' => $teacherUser->id,
            'role_code' => 'teacher',
            'display_name' => 'Teacher Wang',
            'status' => 'enabled',
            'current_campus_id' => $campus->id,
        ]);

        $create = $this->post('/admin/education/academic/teachers', [
            'campus_id' => $campus->id,
            'user_profile_id' => $profile->id,
            'teacher_no' => 'T001',
            'name' => 'Teacher Wang',
            'gender' => 'male',
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $create['code']);
        self::assertSame((int) $profile->id, (int) $create['data']['user_profile_id']);
    }
}
