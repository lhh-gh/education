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

namespace App\Model\Enums\Education\Foundation;

enum AuditActorType: string
{
    case Admin = 'admin';
    case Teacher = 'teacher';
    case Guardian = 'guardian';
    case System = 'system';
}
