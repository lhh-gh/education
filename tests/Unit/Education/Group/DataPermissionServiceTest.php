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

use App\Service\Education\Group\DataPermissionService;

/**
 * @internal
 * @coversNothing
 */
final class DataPermissionServiceTest extends GroupTestCase
{
    public function testGroupAdminGetsAuthorizedCampusSet(): void
    {
        $tenant = $this->tenant('group_scope');
        $campusA = $this->campus($tenant, 'east');
        $campusB = $this->campus($tenant, 'west');
        $context = $this->context((int) $tenant->id, userId: 7101);
        $service = make(DataPermissionService::class);

        $service->saveUserPermission([
            'user_id' => 7101,
            'scope_code' => 'SCOPE_EAST_WEST',
            'scope_name' => 'East West',
            'scope_type' => 'campus_set',
            'campus_ids' => [(int) $campusA->id, (int) $campusB->id],
        ], $context);

        self::assertSame([(int) $campusA->id, (int) $campusB->id], $service->allowedCampusIds($context));
    }

    public function testTeacherMobileScopeIsNotExpandedByGroupPermission(): void
    {
        $tenant = $this->tenant('group_mobile_scope');
        $campusA = $this->campus($tenant, 'only');
        $campusB = $this->campus($tenant, 'outside');
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campusA->id], userId: 7201);
        $service = make(DataPermissionService::class);

        $service->saveUserPermission([
            'user_id' => 7201,
            'scope_code' => 'GROUP_ALL',
            'scope_name' => 'Group all',
            'scope_type' => 'group_all',
            'campus_ids' => [(int) $campusA->id, (int) $campusB->id],
        ], $context);

        self::assertSame([(int) $campusA->id], $service->allowedCampusIds($context, mobile: true));
    }
}
