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
final class TenantAdminApiTest extends EducationAdminControllerCase
{
    public function testPlatformAdminCanCreateUpdateStatusAndPageTenant(): void
    {
        $this->createEducationProfile();
        $this->grantPermissions(
            'education:foundation:tenant:create',
            'education:foundation:tenant:update',
            'education:foundation:tenant:status',
            'education:foundation:tenant:page'
        );

        $create = $this->post('/admin/education/foundation/tenants', [
            'name' => 'Demo Tenant',
            'code' => 'demo',
            'short_name' => 'Demo',
            'contact_name' => 'Alice',
            'contact_phone' => '13800000000',
            'status' => 'enabled',
            'settings' => ['timezone' => 'Asia/Shanghai'],
        ], $this->authHeaders());

        self::assertSame(ResultCode::SUCCESS->value, $create['code']);
        self::assertIsInt($create['data']['id']);

        $tenant = EducationTenant::query()->where('code', 'demo')->first();
        self::assertNotNull($tenant);

        $update = $this->put('/admin/education/foundation/tenants/' . $tenant->id, [
            'name' => 'Demo Tenant Updated',
            'code' => 'demo',
            'contact_name' => 'Bob',
            'status' => 'enabled',
        ], $this->authHeaders());

        self::assertSame(ResultCode::SUCCESS->value, $update['code']);
        self::assertSame($tenant->id, $update['data']['id']);

        $status = $this->put('/admin/education/foundation/tenants/' . $tenant->id . '/status', [
            'status' => 'disabled',
        ], $this->authHeaders());

        self::assertSame(ResultCode::SUCCESS->value, $status['code']);
        self::assertSame('disabled', $status['data']['status']);

        $page = $this->get('/admin/education/foundation/tenants/page', ['token' => $this->token]);

        self::assertSame(ResultCode::SUCCESS->value, $page['code']);
        self::assertSame(1, $page['data']['total']);
        self::assertSame('demo', $page['data']['list'][0]['code']);

        $tenant->refresh();
        self::assertSame('Bob', $tenant->contact_name);
        self::assertSame('disabled', $tenant->status);
    }

    public function testDuplicateTenantCodeReturnsConflict(): void
    {
        $this->createEducationProfile();
        $this->forAddPermission('education:foundation:tenant:create');

        EducationTenant::query()->create([
            'name' => 'Existing Tenant',
            'code' => 'demo',
            'status' => 'enabled',
        ]);

        $result = $this->post('/admin/education/foundation/tenants', [
            'name' => 'Duplicate Tenant',
            'code' => 'demo',
            'status' => 'enabled',
        ], $this->authHeaders());

        self::assertSame(ResultCode::CONFLICT->value, $result['code']);
        self::assertSame('tenant code already exists', $result['message']);
        self::assertSame('demo', $result['data']['code']);
    }
}
