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

namespace App\Model\Enums\Education\Group;

enum DataScopeType: string
{
    case GroupAll = 'group_all';
    case OrgTree = 'org_tree';
    case CampusSet = 'campus_set';
    case Self = 'self';
}
