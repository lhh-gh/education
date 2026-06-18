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

namespace App\Model\Enums\Education\Growth;

enum AiTalkScriptStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';
    case Used = 'used';
}
