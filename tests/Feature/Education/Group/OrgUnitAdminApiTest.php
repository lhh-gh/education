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
final class OrgUnitAdminApiTest extends GroupApiCase
{
    public function testOrgUnitApiValidationTreeAndCycleFailureMatchCatalog(): void
    {
        $fixture = $this->groupFixture('group_org_api');
        $this->createTenantProfile($fixture['tenant'], 'tenant_admin', $fixture['campus']);
        $this->grantPermissions('education:group:org:create', 'education:group:org:tree');

        $invalid = $this->post('/admin/education/group/org-units', [
            'name' => 'Missing code',
            'unit_type' => 'region',
        ], $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus']->id]));
        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $invalid['code']);

        $parent = $this->post('/admin/education/group/org-units', [
            'code' => 'EAST',
            'name' => 'East Region',
            'unit_type' => 'region',
        ], $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus']->id]));
        self::assertSame(ResultCode::SUCCESS->value, $parent['code']);
        self::assertSame('enabled', $parent['data']['status']);

        $child = $this->post('/admin/education/group/org-units', [
            'parent_id' => $parent['data']['id'],
            'code' => 'EAST-01',
            'name' => 'East Campus Cluster',
            'unit_type' => 'campus_cluster',
        ], $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus']->id]));
        self::assertSame(ResultCode::SUCCESS->value, $child['code']);

        $tree = $this->get('/admin/education/group/org-units/tree', [], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::SUCCESS->value, $tree['code']);
        self::assertNotEmpty($tree['data']);

        $cycle = $this->post('/admin/education/group/org-units', [
            'id' => $parent['data']['id'],
            'parent_id' => $child['data']['id'],
            'code' => 'EAST',
            'name' => 'East Region',
            'unit_type' => 'region',
        ], $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus']->id]));
        self::assertSame(ResultCode::CONFLICT->value, $cycle['code']);
    }
}
