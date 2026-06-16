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

namespace HyperfTests\Unit\Education\Academic;

use App\Exception\BusinessException;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Academic\GuardianMobileContextResolver;

/**
 * @internal
 * @coversNothing
 */
final class GuardianMobileContextResolverTest extends AcademicTestCase
{
    public function testResolveByUnionid(): void
    {
        [$tenantId, $profile] = $this->profileFixture(['unionid' => 'union-1']);
        $guardian = $this->guardian($tenantId, ['unionid' => 'union-1']);

        $resolved = make(GuardianMobileContextResolver::class)->resolveGuardian(
            $this->context($tenantId, EducationRoleCode::Guardian, [], (int) $profile->user_id)
        );

        self::assertSame((int) $guardian->id, (int) $resolved->id);
    }

    public function testResolveByOpenid(): void
    {
        [$tenantId, $profile] = $this->profileFixture(['openid' => 'openid-1']);
        $guardian = $this->guardian($tenantId, ['openid' => 'openid-1']);

        $resolved = make(GuardianMobileContextResolver::class)->resolveGuardian(
            $this->context($tenantId, EducationRoleCode::Guardian, [], (int) $profile->user_id)
        );

        self::assertSame((int) $guardian->id, (int) $resolved->id);
    }

    public function testResolveByMobileFallback(): void
    {
        [$tenantId, $profile] = $this->profileFixture(['mobile' => '13800000001']);
        $guardian = $this->guardian($tenantId, ['mobile' => '13800000001']);

        $resolved = make(GuardianMobileContextResolver::class)->resolveGuardian(
            $this->context($tenantId, EducationRoleCode::Guardian, [], (int) $profile->user_id)
        );

        self::assertSame((int) $guardian->id, (int) $resolved->id);
    }

    public function testTeacherRoleRejected(): void
    {
        $tenant = $this->tenant('guardian_mobile_role');

        try {
            make(GuardianMobileContextResolver::class)->assertGuardianRole(
                $this->context((int) $tenant->id, EducationRoleCode::Teacher, [], 9001)
            );
            self::fail('Expected teacher role to be rejected.');
        } catch (BusinessException $exception) {
            self::assertSame(403, $exception->getResponse()->code->value);
            self::assertSame('guardian mobile role required', $exception->getResponse()->message);
        }
    }

    public function testUnboundGuardianProfileRejected(): void
    {
        [$tenantId, $profile] = $this->profileFixture(['mobile' => '13800000002']);

        try {
            make(GuardianMobileContextResolver::class)->resolveGuardian(
                $this->context($tenantId, EducationRoleCode::Guardian, [], (int) $profile->user_id)
            );
            self::fail('Expected unbound guardian to be rejected.');
        } catch (BusinessException $exception) {
            self::assertSame(403, $exception->getResponse()->code->value);
            self::assertSame('guardian record is not bound', $exception->getResponse()->message);
        }
    }

    private function profileFixture(array $overrides): array
    {
        $tenant = $this->tenant('guardian_mobile_context');
        $profile = EducationUserProfile::query()->create(array_merge([
            'profile_key' => 'guardian-9001',
            'tenant_id' => $tenant->id,
            'user_id' => 9001,
            'role_code' => EducationRoleCode::Guardian->value,
            'display_name' => 'Guardian Mobile',
            'status' => 'enabled',
        ], $overrides));

        return [(int) $tenant->id, $profile];
    }

    private function guardian(int $tenantId, array $overrides): EducationGuardian
    {
        return EducationGuardian::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'name' => 'Guardian Zhang',
            'mobile' => '13800000000',
            'status' => 'enabled',
        ], $overrides));
    }
}
