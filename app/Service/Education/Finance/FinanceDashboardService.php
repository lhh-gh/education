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

namespace App\Service\Education\Finance;

use App\Model\Education\Finance\EducationFinanceOrder;
use App\Model\Education\Finance\EducationPaymentRecord;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class FinanceDashboardService
{
    /**
     * @param array<string, mixed> $filters
     * @return array{order_count: int, paid_amount_cents: int, refund_amount_cents: int, payment_count: int}
     */
    public function summary(array $filters, EducationUserContext $context): array
    {
        $orderQuery = (new EducationScopeQuery())->applyTenantCampus(EducationFinanceOrder::query(), $filters, $context);

        $orderIds = (clone $orderQuery)->pluck('id')->all();

        return [
            'order_count' => (clone $orderQuery)->count(),
            'paid_amount_cents' => (int) (clone $orderQuery)->sum('paid_amount_cents'),
            'refund_amount_cents' => (int) (clone $orderQuery)->sum('refund_amount_cents'),
            'payment_count' => EducationPaymentRecord::query()->whereIn('order_id', $orderIds)->count(),
        ];
    }
}
