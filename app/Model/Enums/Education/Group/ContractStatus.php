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

enum ContractStatus: string
{
    case Draft = 'draft';
    case Reviewing = 'reviewing';
    case Active = 'active';
    case Expired = 'expired';
    case Terminated = 'terminated';
    case Archived = 'archived';
}
