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

use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationFeatureFlag;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserCampusScope;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Permission\User;
use App\Repository\Education\Foundation\MobileContextRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class MobileContextRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationFeatureFlag::query()->whereRaw('1=1')->forceDelete();
        EducationUserCampusScope::query()->delete();
        EducationUserProfile::query()->forceDelete();
        EducationCampus::query()->forceDelete();
        EducationTenant::query()->forceDelete();
        User::query()->where('username', 'like', 'edum_%')->delete();
    }

    public function testFindProfileForUserIsTenantScoped(): void
    {
        $user = User::query()->create(['username' => 'edum_repo_prof']);
        $tenantA = EducationTenant::query()->create(['name' => 'Tenant A', 'code' => 'mobile_repo_a', 'status' => 'enabled']);
        $tenantB = EducationTenant::query()->create(['name' => 'Tenant B', 'code' => 'mobile_repo_b', 'status' => 'enabled']);
        $profileA = EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenantA->id . ':' . $user->id,
            'tenant_id' => $tenantA->id,
            'user_id' => $user->id,
            'role_code' => 'teacher',
            'display_name' => 'Teacher A',
            'status' => 'enabled',
        ]);
        EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenantB->id . ':' . $user->id,
            'tenant_id' => $tenantB->id,
            'user_id' => $user->id,
            'role_code' => 'teacher',
            'display_name' => 'Teacher B',
            'status' => 'enabled',
        ]);

        $repository = make(MobileContextRepository::class);

        $profile = $repository->findProfileForUser((int) $user->id, (int) $tenantA->id);

        self::assertNotNull($profile);
        self::assertSame((int) $profileA->id, (int) $profile->id);
        self::assertNull($repository->findProfileForUser((int) $user->id, 999999));
    }

    public function testListCampusScopesReturnsEnabledCampusesOnly(): void
    {
        $user = User::query()->create(['username' => 'edum_repo_scope']);
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'mobile_repo_scope', 'status' => 'enabled']);
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenant->id . ':' . $user->id,
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role_code' => 'teacher',
            'display_name' => 'Teacher',
            'status' => 'enabled',
        ]);
        $enabledCampus = EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Enabled Campus',
            'code' => 'enabled',
            'status' => 'enabled',
        ]);
        $disabledCampus = EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Disabled Campus',
            'code' => 'disabled',
            'status' => 'disabled',
        ]);
        foreach ([$enabledCampus, $disabledCampus] as $campus) {
            EducationUserCampusScope::query()->create([
                'tenant_id' => $tenant->id,
                'user_profile_id' => $profile->id,
                'user_id' => $user->id,
                'campus_id' => $campus->id,
            ]);
        }

        $scopes = make(MobileContextRepository::class)->listCampusScopes((int) $tenant->id, (int) $user->id);

        self::assertSame([
            ['campus_id' => (int) $enabledCampus->id, 'campus_name' => 'Enabled Campus'],
        ], $scopes);
    }

    public function testListEnabledFeatureFlagsUsesTenantOverride(): void
    {
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'mobile_repo_flag', 'status' => 'enabled']);
        EducationFeatureFlag::query()->create([
            'owner_type' => 'system',
            'owner_key' => 'system',
            'feature_code' => 'education.mobile.context',
            'feature_name' => 'Mobile Context',
            'enabled' => false,
            'status' => 'enabled',
        ]);
        EducationFeatureFlag::query()->create([
            'owner_type' => 'tenant',
            'tenant_id' => $tenant->id,
            'owner_key' => 'tenant:' . $tenant->id,
            'feature_code' => 'education.mobile.context',
            'feature_name' => 'Mobile Context',
            'enabled' => true,
            'status' => 'enabled',
        ]);
        EducationFeatureFlag::query()->create([
            'owner_type' => 'system',
            'owner_key' => 'system',
            'feature_code' => 'education.mobile.disabled',
            'feature_name' => 'Disabled Mobile',
            'enabled' => false,
            'status' => 'enabled',
        ]);

        $flags = make(MobileContextRepository::class)->listEnabledFeatureFlags((int) $tenant->id);

        self::assertTrue($flags['education.mobile.context']);
        self::assertFalse($flags['education.mobile.disabled']);
    }
}
