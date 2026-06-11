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

namespace HyperfTests\Unit\Education\Foundation;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserCampusScope;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Permission\User;
use App\Service\Education\Foundation\UserProfileService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class UserProfileServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationUserCampusScope::query()->delete();
        EducationUserProfile::query()->forceDelete();
        EducationCampus::query()->forceDelete();
        EducationTenant::query()->forceDelete();
        User::query()->where('username', 'like', 'edu_%')->delete();
    }

    public function testPlatformRoleRequiresNullTenant(): void
    {
        $user = User::query()->create(['username' => 'edu_platform_role']);
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);

        try {
            make(UserProfileService::class)->createProfile([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'role_code' => 'platform_operator',
                'display_name' => 'Operator',
            ], null);
            self::fail('Expected platform profile with tenant_id to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
        }
    }

    public function testTenantRoleRequiresTenant(): void
    {
        $user = User::query()->create(['username' => 'edu_tenant_role']);

        try {
            make(UserProfileService::class)->createProfile([
                'user_id' => $user->id,
                'role_code' => 'teacher',
                'display_name' => 'Teacher',
            ], null);
            self::fail('Expected tenant role without tenant_id to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
        }
    }

    public function testDuplicateProfileKeyReturnsConflict(): void
    {
        $user = User::query()->create(['username' => 'edu_dup_prof']);
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $service = make(UserProfileService::class);

        $service->createProfile([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role_code' => 'teacher',
            'display_name' => 'Teacher',
        ], null);

        try {
            $service->createProfile([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'role_code' => 'teacher',
                'display_name' => 'Teacher',
            ], null);
            self::fail('Expected duplicate profile to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
            self::assertSame('tenant:' . $tenant->id . ':' . $user->id, $exception->getResponse()->data['profile_key']);
        }
    }
}
