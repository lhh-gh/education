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
use App\Model\Education\Foundation\EducationDictType;
use App\Model\Education\Foundation\EducationTenant;

/**
 * @internal
 * @coversNothing
 */
final class DictionaryFeatureIsolationTest extends EducationAdminControllerCase
{
    public function testTenantContextPagesOnlySystemAndCurrentTenantConfig(): void
    {
        $this->grantPermissions('education:foundation:dictionary:page');
        $tenantA = EducationTenant::query()->create(['name' => 'Tenant A', 'code' => 'tenant_a', 'status' => 'enabled']);
        $tenantB = EducationTenant::query()->create(['name' => 'Tenant B', 'code' => 'tenant_b', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenantA->id, 'tenant_admin');
        $this->createType('system', null, 'system', 'system_status');
        $this->createType('tenant', (int) $tenantA->id, 'tenant:' . $tenantA->id, 'tenant_a_status');
        $this->createType('tenant', (int) $tenantB->id, 'tenant:' . $tenantB->id, 'tenant_b_status');

        $result = $this->get('/admin/education/foundation/dict-types/page', [
            'token' => $this->token,
        ], ['X-Tenant-Id' => (string) $tenantA->id]);

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        $codes = array_column($result['data']['list'], 'code');
        sort($codes);

        self::assertSame(2, $result['data']['total']);
        self::assertSame(['system_status', 'tenant_a_status'], $codes);
    }

    private function createType(string $ownerType, ?int $tenantId, string $ownerKey, string $code): void
    {
        EducationDictType::query()->create([
            'owner_type' => $ownerType,
            'tenant_id' => $tenantId,
            'owner_key' => $ownerKey,
            'code' => $code,
            'name' => $code,
            'status' => 'enabled',
        ]);
    }
}
