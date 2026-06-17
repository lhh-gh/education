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

namespace App\Model\Enums\Education\Finance;

enum PaymentChannelType: string
{
    case OfflineCash = 'offline_cash';
    case OfflineBank = 'offline_bank';
    case OfflinePos = 'offline_pos';
    case Wechat = 'wechat';
}
