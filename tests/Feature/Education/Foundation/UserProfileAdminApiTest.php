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

namespace HyperfTests\Feature\Education\Foundation;

use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Permission\User;

/**
 * @internal
 * @coversNothing
 */
final class UserProfileAdminApiTest extends EducationAdminControllerCase
{
    public function testTenantAdminCanCreateTeacherProfile(): void
    {
        $this->grantPermissions('education:foundation:user-profile:create');
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $campus = EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Campus',
            'code' => 'main',
            'status' => 'enabled',
        ]);
        $this->createEducationProfile((int) $tenant->id, 'tenant_admin');
        $teacher = User::query()->create(['username' => 'edu_teacher_api']);

        $result = $this->post('/admin/education/foundation/user-profiles', [
            'tenant_id' => $tenant->id,
            'user_id' => $teacher->id,
            'role_code' => 'teacher',
            'display_name' => 'Teacher',
            'mobile' => '13800000000',
            'status' => 'enabled',
            'current_campus_id' => $campus->id,
        ], $this->authHeaders(['X-Tenant-Id' => (string) $tenant->id]));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame('tenant:' . $tenant->id . ':' . $teacher->id, $result['data']['profile_key']);

        $profile = EducationUserProfile::query()->where('user_id', $teacher->id)->first();
        self::assertNotNull($profile);
        self::assertSame($tenant->id, $profile->tenant_id);
        self::assertSame('teacher', $profile->role_code->value);
    }

    public function testGuardianProfileCanStoreOpenid(): void
    {
        $this->grantPermissions('education:foundation:user-profile:create');
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenant->id, 'tenant_admin');
        $guardian = User::query()->create(['username' => 'edu_guardian_api']);

        $result = $this->post('/admin/education/foundation/user-profiles', [
            'tenant_id' => $tenant->id,
            'user_id' => $guardian->id,
            'role_code' => 'guardian',
            'display_name' => 'Guardian',
            'openid' => 'wx-openid-001',
            'status' => 'enabled',
        ], $this->authHeaders(['X-Tenant-Id' => (string) $tenant->id]));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);

        $profile = EducationUserProfile::query()->where('openid', 'wx-openid-001')->first();
        self::assertNotNull($profile);
        self::assertSame($guardian->id, $profile->user_id);
    }

    public function testProfileScopeReadAndSave(): void
    {
        $this->grantPermissions(
            'education:foundation:campus-scope:page',
            'education:foundation:campus-scope:save'
        );
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenant->id, 'tenant_admin');
        $teacher = User::query()->create(['username' => 'edu_scope_teacher']);
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenant->id . ':' . $teacher->id,
            'tenant_id' => $tenant->id,
            'user_id' => $teacher->id,
            'role_code' => 'teacher',
            'display_name' => 'Teacher',
            'status' => 'enabled',
        ]);
        $campusA = EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Campus A',
            'code' => 'campus_a',
            'status' => 'enabled',
        ]);
        $campusB = EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Campus B',
            'code' => 'campus_b',
            'status' => 'enabled',
        ]);

        $save = $this->put('/admin/education/foundation/user-profiles/' . $profile->id . '/campus-scopes', [
            'campus_ids' => [$campusB->id, $campusA->id],
        ], $this->authHeaders(['X-Tenant-Id' => (string) $tenant->id]));

        self::assertSame(ResultCode::SUCCESS->value, $save['code']);
        self::assertSame([$campusA->id, $campusB->id], $save['data']['campus_ids']);

        $read = $this->get('/admin/education/foundation/user-profiles/' . $profile->id . '/campus-scopes', [
            'token' => $this->token,
        ], ['X-Tenant-Id' => (string) $tenant->id]);

        self::assertSame(ResultCode::SUCCESS->value, $read['code']);
        self::assertSame($profile->id, $read['data']['user_profile_id']);
        self::assertSame([$campusA->id, $campusB->id], $read['data']['campus_ids']);
    }
}
