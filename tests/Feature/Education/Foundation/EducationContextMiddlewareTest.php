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
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Model\Permission\Role;
use App\Service\Education\Foundation\UserProfileService;
use Hyperf\Context\ApplicationContext;

/**
 * @internal
 * @coversNothing
 */
final class EducationContextMiddlewareTest extends EducationAdminControllerCase
{
    public function testSuperAdminWithoutEducationProfileResolvesPlatformContext(): void
    {
        $superAdminRole = Role::query()->create([
            'name' => 'Super Admin',
            'code' => 'SuperAdmin',
            'sort' => 1,
            'status' => 1,
            'remark' => '',
        ]);
        $this->user->roles()->syncWithoutDetaching($superAdminRole);
        $this->clearCurrentUserCache();

        $result = $this->get('/admin/education/foundation/tenants/page', ['token' => $this->token]);

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        $context = ApplicationContext::getContainer()
            ->get(UserProfileService::class)
            ->resolveForUser((int) $this->user->id, null);
        self::assertSame(EducationRoleCode::PlatformSuperAdmin, $context->roleCode);
        self::assertTrue($context->platformAccess);
        self::assertNull($context->tenantId);
        self::assertSame([], $context->campusIds);
        self::assertNull($context->currentCampusId);
    }

    public function testTenantProfileResolvesContextWithoutHeader(): void
    {
        $this->grantPermissions('education:foundation:campus:page');
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenant->id, 'teacher');

        $result = $this->get('/admin/education/foundation/campuses/page', ['token' => $this->token]);

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        $context = ApplicationContext::getContainer()
            ->get(UserProfileService::class)
            ->resolveForUser((int) $this->user->id, null);
        self::assertSame($tenant->id, $context->tenantId);
        self::assertSame('teacher', $context->roleCode->value);
    }

    public function testCampusHeaderOverridesCurrentCampusInsideUserScope(): void
    {
        $this->grantPermissions('education:foundation:campus:page');
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $defaultCampus = EducationCampus::query()->create(['tenant_id' => $tenant->id, 'name' => 'Default', 'code' => 'default', 'status' => 'enabled']);
        $requestedCampus = EducationCampus::query()->create(['tenant_id' => $tenant->id, 'name' => 'Requested', 'code' => 'requested', 'status' => 'enabled']);
        $profile = $this->createEducationProfile((int) $tenant->id, 'teacher');
        $profile->update(['current_campus_id' => $defaultCampus->id]);
        foreach ([$defaultCampus, $requestedCampus] as $campus) {
            EducationUserCampusScope::query()->create([
                'tenant_id' => $tenant->id,
                'user_profile_id' => $profile->id,
                'user_id' => $this->user->id,
                'campus_id' => $campus->id,
            ]);
        }

        $result = $this->get('/admin/education/foundation/campuses/page', [
            'token' => $this->token,
        ], [
            'X-Tenant-Id' => (string) $tenant->id,
            'X-Campus-Id' => (string) $requestedCampus->id,
        ]);

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        $context = ApplicationContext::getContainer()
            ->get(UserProfileService::class)
            ->resolveForUser((int) $this->user->id, (int) $tenant->id, (int) $requestedCampus->id);
        self::assertSame((int) $requestedCampus->id, $context->currentCampusId);
    }

    public function testCampusHeaderOutsideUserScopeIsRejected(): void
    {
        $this->grantPermissions('education:foundation:campus:page');
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $allowedCampus = EducationCampus::query()->create(['tenant_id' => $tenant->id, 'name' => 'Allowed', 'code' => 'allowed', 'status' => 'enabled']);
        $outsideCampus = EducationCampus::query()->create(['tenant_id' => $tenant->id, 'name' => 'Outside', 'code' => 'outside', 'status' => 'enabled']);
        $profile = $this->createEducationProfile((int) $tenant->id, 'teacher');
        EducationUserCampusScope::query()->create([
            'tenant_id' => $tenant->id,
            'user_profile_id' => $profile->id,
            'user_id' => $this->user->id,
            'campus_id' => $allowedCampus->id,
        ]);

        $result = $this->get('/admin/education/foundation/campuses/page', [
            'token' => $this->token,
        ], [
            'X-Tenant-Id' => (string) $tenant->id,
            'X-Campus-Id' => (string) $outsideCampus->id,
        ]);

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }

    public function testTenantProfileCannotRequestOtherTenant(): void
    {
        $this->grantPermissions('education:foundation:campus:page');
        $tenantA = EducationTenant::query()->create(['name' => 'Tenant A', 'code' => 'tenant_a', 'status' => 'enabled']);
        $tenantB = EducationTenant::query()->create(['name' => 'Tenant B', 'code' => 'tenant_b', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenantA->id, 'teacher');

        $result = $this->get('/admin/education/foundation/campuses/page', [
            'token' => $this->token,
        ], ['X-Tenant-Id' => (string) $tenantB->id]);

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }

    public function testDisabledProfileIsRejected(): void
    {
        $this->grantPermissions('education:foundation:campus:page');
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenant->id, 'teacher', 'disabled');

        $result = $this->get('/admin/education/foundation/campuses/page', ['token' => $this->token]);

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }
}
