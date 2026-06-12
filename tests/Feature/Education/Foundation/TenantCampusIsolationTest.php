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
final class TenantCampusIsolationTest extends EducationAdminControllerCase
{
    public function testTenantAdminCannotPageOtherTenantCampus(): void
    {
        $this->forAddPermission('education:foundation:campus:page');
        [$tenantA, $tenantB] = $this->createTenantPair();
        $this->createEducationProfile((int) $tenantA->id, 'tenant_admin');

        EducationCampus::query()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Campus A',
            'code' => 'campus_a',
            'status' => 'enabled',
        ]);
        EducationCampus::query()->create([
            'tenant_id' => $tenantB->id,
            'name' => 'Campus B',
            'code' => 'campus_b',
            'status' => 'enabled',
        ]);

        $result = $this->get('/admin/education/foundation/campuses/page', [
            'token' => $this->token,
        ], ['X-Tenant-Id' => (string) $tenantA->id]);

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame(1, $result['data']['total']);
        self::assertSame('campus_a', $result['data']['list'][0]['code']);
    }

    public function testTenantAdminCannotUpdateOtherTenantCampus(): void
    {
        $this->forAddPermission('education:foundation:campus:update');
        [$tenantA, $tenantB] = $this->createTenantPair();
        $this->createEducationProfile((int) $tenantA->id, 'tenant_admin');

        $campus = EducationCampus::query()->create([
            'tenant_id' => $tenantB->id,
            'name' => 'Campus B',
            'code' => 'campus_b',
            'address' => 'Original',
            'status' => 'enabled',
        ]);

        $result = $this->put('/admin/education/foundation/campuses/' . $campus->id, [
            'name' => 'Campus B Updated',
            'code' => 'campus_b',
            'address' => 'Changed',
            'status' => 'enabled',
        ], $this->authHeaders(['X-Tenant-Id' => (string) $tenantA->id]));

        self::assertSame(ResultCode::NOT_FOUND->value, $result['code']);
        $campus->refresh();
        self::assertSame('Original', $campus->address);
    }

    public function testCampusCreateIgnoresRequestBodyTenantId(): void
    {
        $this->forAddPermission('education:foundation:campus:create');
        [$tenantA] = $this->createTenantPair();
        $this->createEducationProfile((int) $tenantA->id, 'tenant_admin');

        $result = $this->post('/admin/education/foundation/campuses', [
            'tenant_id' => 999,
            'name' => 'Main Campus',
            'code' => 'main',
            'status' => 'enabled',
        ], $this->authHeaders(['X-Tenant-Id' => (string) $tenantA->id]));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame($tenantA->id, $result['data']['tenant_id']);
        self::assertSame($tenantA->id, EducationCampus::query()->where('code', 'main')->first()?->tenant_id);
    }

    private function createTenantPair(): array
    {
        return [
            EducationTenant::query()->create(['name' => 'Tenant A', 'code' => 'tenant_a', 'status' => 'enabled']),
            EducationTenant::query()->create(['name' => 'Tenant B', 'code' => 'tenant_b', 'status' => 'enabled']),
        ];
    }
}
