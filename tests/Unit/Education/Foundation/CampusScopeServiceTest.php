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
use App\Service\Education\Foundation\CampusScopeService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class CampusScopeServiceTest extends TestCase
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

    public function testSaveScopesRejectsCampusOutsideTenant(): void
    {
        $user = User::query()->create(['username' => 'edu_scope_outside']);
        $tenantA = EducationTenant::query()->create(['name' => 'Tenant A', 'code' => 'tenant_a', 'status' => 'enabled']);
        $tenantB = EducationTenant::query()->create(['name' => 'Tenant B', 'code' => 'tenant_b', 'status' => 'enabled']);
        $campusB = EducationCampus::query()->create([
            'tenant_id' => $tenantB->id,
            'name' => 'Other Campus',
            'code' => 'other',
            'status' => 'enabled',
        ]);
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenantA->id . ':' . $user->id,
            'tenant_id' => $tenantA->id,
            'user_id' => $user->id,
            'role_code' => 'tenant_admin',
            'display_name' => 'Admin',
            'status' => 'enabled',
        ]);

        try {
            make(CampusScopeService::class)->saveScopes((int) $profile->id, (int) $tenantA->id, [(int) $campusB->id], null);
            self::fail('Expected campus outside tenant to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::FORBIDDEN, $exception->getResponse()->code);
            self::assertSame($campusB->id, $exception->getResponse()->data['campus_id']);
        }
    }

    public function testTeacherEnabledProfileRequiresScope(): void
    {
        $user = User::query()->create(['username' => 'edu_t_scope']);
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenant->id . ':' . $user->id,
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role_code' => 'teacher',
            'display_name' => 'Teacher',
            'status' => 'enabled',
        ]);

        try {
            make(CampusScopeService::class)->saveScopes((int) $profile->id, (int) $tenant->id, [], null);
            self::fail('Expected enabled teacher without scope to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
        }
    }
}
