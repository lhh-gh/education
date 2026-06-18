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
use App\Repository\Education\Foundation\UserProfileRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class UserProfileRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationUserCampusScope::query()->delete();
        EducationUserProfile::query()->forceDelete();
        User::query()->where('username', 'like', 'edu_%')->delete();
    }

    public function testFindByUserTenantReturnsCorrectProfile(): void
    {
        $user = User::query()->create(['username' => 'edu_profile_repo']);
        $tenantOneProfile = EducationUserProfile::query()->create([
            'profile_key' => 'tenant:1:' . $user->id,
            'tenant_id' => 1,
            'user_id' => $user->id,
            'role_code' => 'teacher',
            'display_name' => 'Teacher One',
            'status' => 'enabled',
        ]);
        EducationUserProfile::query()->create([
            'profile_key' => 'tenant:2:' . $user->id,
            'tenant_id' => 2,
            'user_id' => $user->id,
            'role_code' => 'teacher',
            'display_name' => 'Teacher Two',
            'status' => 'enabled',
        ]);

        $repository = make(UserProfileRepository::class);

        $profile = $repository->findByUserTenant((int) $user->id, 1);

        self::assertNotNull($profile);
        self::assertSame($tenantOneProfile->id, $profile->id);
        self::assertNull($repository->findByUserTenant((int) $user->id, 3));
    }
}
