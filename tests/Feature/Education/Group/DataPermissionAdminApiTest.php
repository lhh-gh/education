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

namespace HyperfTests\Feature\Education\Group;

use App\Http\Common\ResultCode;

/**
 * @internal
 * @coversNothing
 */
final class DataPermissionAdminApiTest extends GroupApiCase
{
    public function testPermissionApiValidationAndPreviewMatchCatalog(): void
    {
        $fixture = $this->groupFixture('group_perm_api');
        $this->createTenantProfile($fixture['tenant'], 'tenant_admin', $fixture['campus']);
        $this->grantPermissions(
            'education:group:data-permission:save',
            'education:group:data-permission:page',
            'education:group:data-permission:preview'
        );

        $invalid = $this->post('/admin/education/group/data-permissions', [
            'user_id' => $this->user->id,
            'scope_type' => 'invalid',
            'campus_ids' => [$fixture['campus']->id],
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $invalid['code']);

        $saved = $this->post('/admin/education/group/data-permissions', [
            'user_id' => $this->user->id,
            'scope_type' => 'campus_set',
            'campus_ids' => [$fixture['campus']->id],
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::SUCCESS->value, $saved['code']);
        self::assertSame([$fixture['campus']->id], $saved['data']['allowed_campus_ids']);

        $preview = $this->get('/admin/education/group/data-permissions/preview', [], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::SUCCESS->value, $preview['code']);
        self::assertSame([$fixture['campus']->id], $preview['data']['allowed_campus_ids']);
    }
}
