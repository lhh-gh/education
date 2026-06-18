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

enum FinanceOrderStatus: string
{
    case Pending = 'pending';
    case Paying = 'paying';
    case Paid = 'paid';
    case PartialRefunded = 'partial_refunded';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';
    case Closed = 'closed';
}
