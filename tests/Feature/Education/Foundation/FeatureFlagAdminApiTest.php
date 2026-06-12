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
use App\Model\Education\Foundation\EducationTenant;

/**
 * @internal
 * @coversNothing
 */
final class FeatureFlagAdminApiTest extends EducationAdminControllerCase
{
    public function testPlatformAdminCanCreateUpdateAndResolveFlag(): void
    {
        $this->createEducationProfile();
        $this->grantPermissions(
            'education:foundation:feature-flag:create',
            'education:foundation:feature-flag:update',
            'education:foundation:feature-flag:lookup'
        );

        $create = $this->post('/admin/education/foundation/feature-flags', [
            'owner_type' => 'system',
            'feature_code' => 'education.test.feature',
            'feature_name' => 'Test feature',
            'enabled' => false,
            'status' => 'enabled',
        ], $this->authHeaders());

        self::assertSame(ResultCode::SUCCESS->value, $create['code']);

        $update = $this->put('/admin/education/foundation/feature-flags/' . $create['data']['id'], [
            'owner_type' => 'system',
            'feature_code' => 'education.test.feature',
            'feature_name' => 'Test feature',
            'enabled' => true,
            'status' => 'enabled',
        ], $this->authHeaders());

        self::assertSame(ResultCode::SUCCESS->value, $update['code']);

        $resolved = $this->get('/admin/education/foundation/feature-flags/education.test.feature/resolved', ['token' => $this->token]);

        self::assertSame(ResultCode::SUCCESS->value, $resolved['code']);
        self::assertTrue($resolved['data']['enabled']);
    }

    public function testTenantAdminCannotWriteOtherTenantFlag(): void
    {
        $tenantA = EducationTenant::query()->create(['name' => 'Tenant A', 'code' => 'tenant_a', 'status' => 'enabled']);
        $tenantB = EducationTenant::query()->create(['name' => 'Tenant B', 'code' => 'tenant_b', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenantA->id, 'tenant_admin');
        $this->grantPermissions('education:foundation:feature-flag:create');

        $result = $this->post('/admin/education/foundation/feature-flags', [
            'owner_type' => 'tenant',
            'tenant_id' => $tenantB->id,
            'feature_code' => 'education.test.feature',
            'feature_name' => 'Test feature',
            'enabled' => true,
            'status' => 'enabled',
        ], $this->authHeaders(['X-Tenant-Id' => (string) $tenantA->id]));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }
}
