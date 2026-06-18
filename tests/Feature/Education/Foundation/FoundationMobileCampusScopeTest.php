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
use App\Model\Education\Foundation\EducationUserCampusScope;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\Context\Context;

/**
 * @internal
 * @coversNothing
 */
final class FoundationMobileCampusScopeTest extends EducationAdminControllerCase
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

    public function testTeacherCannotRequestOutOfScopeCampus(): void
    {
        $tenant = $this->createTenant('mobile_scope_teacher');
        $allowedCampus = $this->createCampus($tenant, 'Allowed Campus', 'allowed');
        $otherCampus = $this->createCampus($tenant, 'Other Campus', 'other');
        $profile = $this->createProfile($tenant, 'teacher', $allowedCampus);
        $this->assignCampus($tenant, $profile, $allowedCampus);

        $result = $this->get('/mobile/education/foundation/teacher/context', [
            'campus_id' => $otherCampus->id,
            'client_type' => 'h5',
        ], $this->mobileHeaders($tenant));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
        self::assertSame('campus is outside current user scope', $result['message']);
        self::assertSame((int) $otherCampus->id, $result['data']['campus_id']);
    }

    public function testFrontDeskSeesOnlyAllowedCampusScope(): void
    {
        $tenant = $this->createTenant('mobile_scope_front');
        $allowedCampus = $this->createCampus($tenant, 'Allowed Campus', 'allowed');
        $this->createCampus($tenant, 'Unscoped Campus', 'unscoped');
        $profile = $this->createProfile($tenant, 'front_desk', $allowedCampus);
        $this->assignCampus($tenant, $profile, $allowedCampus);

        $otherTenant = $this->createTenant('mobile_scope_other');
        $otherCampus = $this->createCampus($otherTenant, 'Other Tenant Campus', 'other');
        $otherProfile = $this->createProfile($otherTenant, 'front_desk', $otherCampus);
        $this->assignCampus($otherTenant, $otherProfile, $otherCampus);

        $result = $this->get('/mobile/education/foundation/operator/context', [], $this->mobileHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame([[
            'campus_id' => (int) $allowedCampus->id,
            'campus_name' => 'Allowed Campus',
            'is_current' => true,
        ]], $result['data']['campus_scopes']);
    }

    private function createTenant(string $code): EducationTenant
    {
        return EducationTenant::query()->create([
            'name' => 'Mobile Scope Tenant',
            'code' => $code,
            'status' => 'enabled',
        ]);
    }

    private function createCampus(EducationTenant $tenant, string $name, string $code): EducationCampus
    {
        return EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'code' => $code,
            'status' => 'enabled',
        ]);
    }

    private function createProfile(EducationTenant $tenant, string $roleCode, EducationCampus $campus): EducationUserProfile
    {
        return EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenant->id . ':' . $this->user->id,
            'tenant_id' => $tenant->id,
            'user_id' => $this->user->id,
            'role_code' => $roleCode,
            'display_name' => 'Mobile Scoped User',
            'status' => 'enabled',
            'current_campus_id' => $campus->id,
        ]);
    }

    private function assignCampus(EducationTenant $tenant, EducationUserProfile $profile, EducationCampus $campus): void
    {
        EducationUserCampusScope::query()->create([
            'tenant_id' => $tenant->id,
            'user_profile_id' => $profile->id,
            'user_id' => $this->user->id,
            'campus_id' => $campus->id,
        ]);
    }

    private function mobileHeaders(EducationTenant $tenant): array
    {
        return $this->authHeaders(['X-Tenant-Id' => (string) $tenant->id]);
    }
}
