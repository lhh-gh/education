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

/**
 * @internal
 * @coversNothing
 */
final class TenantContextUpgradeTest extends EducationAdminControllerCase
{
    public function testCampusApiUsesProfileTenantWhenHeaderMissing(): void
    {
        $this->grantPermissions('education:foundation:campus:page');
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenant->id, 'tenant_admin');
        EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Campus',
            'code' => 'main',
            'status' => 'enabled',
        ]);

        $result = $this->get('/admin/education/foundation/campuses/page', ['token' => $this->token]);

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame(1, $result['data']['total']);
        self::assertSame('main', $result['data']['list'][0]['code']);
    }

    public function testCampusApiRejectsMismatchedHeader(): void
    {
        $this->grantPermissions('education:foundation:campus:page');
        $tenantA = EducationTenant::query()->create(['name' => 'Tenant A', 'code' => 'tenant_a', 'status' => 'enabled']);
        $tenantB = EducationTenant::query()->create(['name' => 'Tenant B', 'code' => 'tenant_b', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenantA->id, 'tenant_admin');

        $result = $this->get('/admin/education/foundation/campuses/page', [
            'token' => $this->token,
        ], ['X-Tenant-Id' => (string) $tenantB->id]);

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }

    public function testPlatformProfileCanRequestTenantHeader(): void
    {
        $this->grantPermissions('education:foundation:campus:page');
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $this->createEducationProfile(null, 'platform_operator');
        EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Campus',
            'code' => 'main',
            'status' => 'enabled',
        ]);

        $result = $this->get('/admin/education/foundation/campuses/page', [
            'token' => $this->token,
        ], ['X-Tenant-Id' => (string) $tenant->id]);

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame(1, $result['data']['total']);
    }
}
