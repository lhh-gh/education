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

use App\Model\Education\Foundation\EducationUserCampusScope;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Permission\User;
use App\Repository\Education\Foundation\UserCampusScopeRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class UserCampusScopeRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationUserCampusScope::query()->delete();
        EducationUserProfile::query()->forceDelete();
        User::query()->where('username', 'like', 'edu_%')->delete();
    }

    public function testCampusIdsForUserAreTenantScoped(): void
    {
        $user = User::query()->create(['username' => 'edu_scope_repo']);
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'tenant:1:' . $user->id,
            'tenant_id' => 1,
            'user_id' => $user->id,
            'role_code' => 'teacher',
            'display_name' => 'Teacher',
            'status' => 'enabled',
        ]);
        EducationUserCampusScope::query()->create([
            'tenant_id' => 1,
            'user_profile_id' => $profile->id,
            'user_id' => $user->id,
            'campus_id' => 11,
        ]);
        EducationUserCampusScope::query()->create([
            'tenant_id' => 2,
            'user_profile_id' => $profile->id,
            'user_id' => $user->id,
            'campus_id' => 22,
        ]);

        $repository = make(UserCampusScopeRepository::class);

        self::assertSame([11], $repository->campusIdsForUser(1, (int) $user->id));
        self::assertSame([22], $repository->campusIdsForUser(2, (int) $user->id));
    }
}
