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

namespace HyperfTests\Unit\Education\Group;

use App\Exception\BusinessException;
use App\Service\Education\Group\OrgUnitService;

/**
 * @internal
 * @coversNothing
 */
final class OrgUnitServiceTest extends GroupTestCase
{
    public function testOrgTreeRejectsCycle(): void
    {
        $tenant = $this->tenant('group_org');
        $context = $this->context((int) $tenant->id, userId: 7001);
        $service = make(OrgUnitService::class);

        $root = $service->save(['code' => 'ROOT', 'name' => 'Root', 'unit_type' => 'group'], $context);
        $child = $service->save(['parent_id' => $root['id'], 'code' => 'EAST', 'name' => 'East', 'unit_type' => 'region'], $context);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('org unit parent creates cycle');

        $service->save(['id' => $root['id'], 'parent_id' => $child['id'], 'code' => 'ROOT', 'name' => 'Root', 'unit_type' => 'group'], $context);
    }
}
