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

namespace App\Model\Enums\Education\Payroll;

enum SalaryItemType: string
{
    case Workload = 'workload';
    case Bonus = 'bonus';
    case Deduction = 'deduction';
    case Adjustment = 'adjustment';
    case Subsidy = 'subsidy';
}
