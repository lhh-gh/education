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
use App\Model\Education\Foundation\EducationFeatureFlag;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserCampusScope;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\Context\Context;

/**
 * @internal
 * @coversNothing
 */
final class FoundationMobileFeatureFlagTest extends EducationAdminControllerCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Context::destroy(MobileContextService::CONTEXT_KEY);
    }

    protected function tearDown(): void
    {
        Context::destroy(MobileContextService::CONTEXT_KEY);
        parent::tearDown();
    }

    public function testContextIncludesEnabledTenantFeatureFlags(): void
    {
        [$tenant] = $this->createTeacherProfile();
        $this->createFeatureFlag($tenant, 'education.v1.core_academic', true);

        $result = $this->get('/mobile/education/foundation/teacher/context', [], $this->mobileHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertTrue($result['data']['feature_flags']['education.v1.core_academic']);
    }

    public function testDisabledFeatureFlagsAreReturnedFalse(): void
    {
        [$tenant] = $this->createTeacherProfile();
        $this->createFeatureFlag($tenant, 'education.v7.family_service', false);

        $result = $this->get('/mobile/education/foundation/teacher/context', [], $this->mobileHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertFalse($result['data']['feature_flags']['education.v7.family_service']);
    }

    /**
     * @return array{0:EducationTenant, 1:EducationUserProfile, 2:EducationCampus}
     */
    private function createTeacherProfile(): array
    {
        $tenant = EducationTenant::query()->create([
            'name' => 'Mobile Flag Tenant',
            'code' => 'mobile_flag',
            'status' => 'enabled',
        ]);
        $campus = EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Flag Campus',
            'code' => 'flag',
            'status' => 'enabled',
        ]);
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenant->id . ':' . $this->user->id,
            'tenant_id' => $tenant->id,
            'user_id' => $this->user->id,
            'role_code' => 'teacher',
            'display_name' => 'Flag Teacher',
            'status' => 'enabled',
            'current_campus_id' => $campus->id,
        ]);
        EducationUserCampusScope::query()->create([
            'tenant_id' => $tenant->id,
            'user_profile_id' => $profile->id,
            'user_id' => $this->user->id,
            'campus_id' => $campus->id,
        ]);

        return [$tenant, $profile, $campus];
    }

    private function createFeatureFlag(EducationTenant $tenant, string $featureCode, bool $enabled): void
    {
        EducationFeatureFlag::query()->create([
            'owner_type' => 'tenant',
            'tenant_id' => $tenant->id,
            'owner_key' => 'tenant:' . $tenant->id,
            'feature_code' => $featureCode,
            'feature_name' => $featureCode,
            'enabled' => $enabled,
            'status' => 'enabled',
        ]);
    }

    private function mobileHeaders(EducationTenant $tenant): array
    {
        return $this->authHeaders(['X-Tenant-Id' => (string) $tenant->id]);
    }
}
