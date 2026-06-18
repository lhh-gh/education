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
final class CampusAdminApiTest extends EducationAdminControllerCase
{
    public function testTenantAdminCanCreateUpdateStatusAndPageCampus(): void
    {
        $this->grantPermissions(
            'education:foundation:campus:create',
            'education:foundation:campus:update',
            'education:foundation:campus:status',
            'education:foundation:campus:page'
        );

        $tenant = EducationTenant::query()->create([
            'name' => 'Tenant',
            'code' => 'tenant',
            'status' => 'enabled',
        ]);
        $this->createEducationProfile((int) $tenant->id, 'tenant_admin');

        $create = $this->post('/admin/education/foundation/campuses', [
            'name' => 'Main Campus',
            'code' => 'main',
            'contact_name' => 'Carol',
            'contact_phone' => '021-00000000',
            'address' => 'Road 1',
            'status' => 'enabled',
        ], $this->authHeaders(['X-Tenant-Id' => (string) $tenant->id]));

        self::assertSame(ResultCode::SUCCESS->value, $create['code']);
        self::assertSame($tenant->id, $create['data']['tenant_id']);

        $campus = EducationCampus::query()->where('code', 'main')->first();
        self::assertNotNull($campus);
        self::assertSame($tenant->id, $campus->tenant_id);

        $update = $this->put('/admin/education/foundation/campuses/' . $campus->id, [
            'name' => 'Main Campus',
            'code' => 'main',
            'address' => 'Road 2',
            'status' => 'enabled',
        ], $this->authHeaders(['X-Tenant-Id' => (string) $tenant->id]));

        self::assertSame(ResultCode::SUCCESS->value, $update['code']);

        $status = $this->put('/admin/education/foundation/campuses/' . $campus->id . '/status', [
            'status' => 'disabled',
        ], $this->authHeaders(['X-Tenant-Id' => (string) $tenant->id]));

        self::assertSame(ResultCode::SUCCESS->value, $status['code']);
        self::assertSame('disabled', $status['data']['status']);

        $page = $this->get('/admin/education/foundation/campuses/page', [
            'token' => $this->token,
        ], ['X-Tenant-Id' => (string) $tenant->id]);

        self::assertSame(ResultCode::SUCCESS->value, $page['code']);
        self::assertSame(1, $page['data']['total']);
        self::assertSame('main', $page['data']['list'][0]['code']);

        $campus->refresh();
        self::assertSame('Road 2', $campus->address);
        self::assertSame('disabled', $campus->status);
    }

    public function testMissingTenantHeaderReturnsValidationFailure(): void
    {
        $this->createEducationProfile();
        $this->forAddPermission('education:foundation:campus:page');

        $result = $this->get('/admin/education/foundation/campuses/page', ['token' => $this->token]);

        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $result['code']);
        self::assertSame('X-Tenant-Id header is required', $result['message']);
        self::assertSame('X-Tenant-Id', $result['data']['header']);
    }
}
