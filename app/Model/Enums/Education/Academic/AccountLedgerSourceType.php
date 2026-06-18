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

namespace App\Model\Enums\Education\Academic;

enum AccountLedgerSourceType: string
{
    case Enrollment = 'enrollment';
    case EnrollmentCancel = 'enrollment_cancel';
    case Consumption = 'consumption';
    case Adjustment = 'adjustment';
}
