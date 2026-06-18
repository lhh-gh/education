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
final class FoundationMobileRoleIsolationTest extends EducationAdminControllerCase
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

    public function testGuardianCannotAccessTeacherContext(): void
    {
        $tenant = $this->createTenant();
        $this->createProfile($tenant, 'guardian');

        $result = $this->get('/mobile/education/foundation/teacher/context', [], $this->mobileHeaders($tenant));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
        self::assertSame('teacher profile is required', $result['message']);
        self::assertSame('guardian', $result['data']['role_code']);
    }

    public function testTeacherCannotAccessGuardianContext(): void
    {
        $tenant = $this->createTeacherProfile();

        $result = $this->get('/mobile/education/foundation/guardian/context', [], $this->mobileHeaders($tenant));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
        self::assertSame('guardian profile is not bound', $result['message']);
        self::assertSame('teacher', $result['data']['role_code']);
    }

    public function testTeacherCannotAccessOperatorContext(): void
    {
        $tenant = $this->createTeacherProfile();

        $result = $this->get('/mobile/education/foundation/operator/context', [], $this->mobileHeaders($tenant));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
        self::assertSame('operator role is required', $result['message']);
        self::assertSame('teacher', $result['data']['role_code']);
    }

    private function createTeacherProfile(): EducationTenant
    {
        $tenant = $this->createTenant();
        $campus = EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Teacher Campus',
            'code' => 'teacher',
            'status' => 'enabled',
        ]);
        $profile = $this->createProfile($tenant, 'teacher', $campus);
        EducationUserCampusScope::query()->create([
            'tenant_id' => $tenant->id,
            'user_profile_id' => $profile->id,
            'user_id' => $this->user->id,
            'campus_id' => $campus->id,
        ]);

        return $tenant;
    }

    private function createTenant(): EducationTenant
    {
        return EducationTenant::query()->create([
            'name' => 'Mobile Role Tenant',
            'code' => 'mobile_role',
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
            'status' => 'enabled',
            'current_campus_id' => $campus?->id,
        ]);
    }

    private function mobileHeaders(EducationTenant $tenant): array
    {
        return $this->authHeaders(['X-Tenant-Id' => (string) $tenant->id]);
    }
}
