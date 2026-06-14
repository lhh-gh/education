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
final class FoundationMobileContextApiTest extends EducationAdminControllerCase
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

    public function testTeacherContextReturnsMineadminResultShape(): void
    {
        [$tenant, $profile, $campus] = $this->createProfileWithCampus('teacher');

        $result = $this->get('/mobile/education/foundation/teacher/context', [
            'campus_id' => $campus->id,
            'client_type' => 'wechat_miniprogram',
        ], $this->mobileHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertArrayHasKey('message', $result);
        self::assertSame((int) $tenant->id, $result['data']['tenant']['id']);
        self::assertSame((int) $profile->id, $result['data']['profile']['id']);
        self::assertSame('teacher', $result['data']['profile']['role_code']);
        self::assertSame([[
            'campus_id' => (int) $campus->id,
            'campus_name' => 'Mobile Campus',
            'is_current' => true,
        ]], $result['data']['campus_scopes']);
    }

    public function testGuardianContextReturnsEmptyStudents(): void
    {
        $tenant = $this->createTenant('mobile_ctx_guardian');
        $this->createProfile($tenant, 'guardian');

        $result = $this->get('/mobile/education/foundation/guardian/context', [
            'client_type' => 'wechat_service',
        ], $this->mobileHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame([], $result['data']['bound_students']);
        self::assertSame('guardian_students_pending_v1', $result['data']['empty_state']['code']);
    }

    public function testOperatorContextReturnsFeatureFlags(): void
    {
        [$tenant] = $this->createProfileWithCampus('front_desk');
        EducationFeatureFlag::query()->create([
            'owner_type' => 'tenant',
            'tenant_id' => $tenant->id,
            'owner_key' => 'tenant:' . $tenant->id,
            'feature_code' => 'education.v3.admissions_crm',
            'feature_name' => 'Admissions CRM',
            'enabled' => true,
            'status' => 'enabled',
        ]);

        $result = $this->get('/mobile/education/foundation/operator/context', [
            'client_type' => 'h5',
        ], $this->mobileHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertIsArray($result['data']['feature_flags']);
        self::assertTrue($result['data']['feature_flags']['education.v3.admissions_crm']);
    }

    private function createTenant(string $code): EducationTenant
    {
        return EducationTenant::query()->create([
            'name' => 'Mobile Tenant',
            'code' => $code,
            'short_name' => 'Mobile',
            'status' => 'enabled',
        ]);
    }

    private function createProfile(EducationTenant $tenant, string $roleCode, ?EducationCampus $campus = null): EducationUserProfile
    {
        return EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenant->id . ':' . $this->user->id,
            'tenant_id' => $tenant->id,
            'user_id' => $this->user->id,
            'role_code' => $roleCode,
            'display_name' => 'Mobile User',
            'mobile' => '13800000000',
            'status' => 'enabled',
            'current_campus_id' => $campus?->id,
        ]);
    }

    /**
     * @return array{0:EducationTenant, 1:EducationUserProfile, 2:EducationCampus}
     */
    private function createProfileWithCampus(string $roleCode): array
    {
        $tenant = $this->createTenant('mobile_ctx_' . $roleCode);
        $campus = EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Mobile Campus',
            'code' => 'mobile',
            'status' => 'enabled',
        ]);
        $profile = $this->createProfile($tenant, $roleCode, $campus);
        EducationUserCampusScope::query()->create([
            'tenant_id' => $tenant->id,
            'user_profile_id' => $profile->id,
            'user_id' => $this->user->id,
            'campus_id' => $campus->id,
        ]);

        return [$tenant, $profile, $campus];
    }

    private function mobileHeaders(EducationTenant $tenant): array
    {
        return $this->authHeaders([
            'X-Tenant-Id' => (string) $tenant->id,
            'X-Client-Type' => 'h5',
        ]);
    }
}
