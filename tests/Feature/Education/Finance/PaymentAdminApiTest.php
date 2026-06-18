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

namespace HyperfTests\Feature\Education\Finance;

use App\Http\Common\ResultCode;

/**
 * @internal
 * @coversNothing
 */
final class PaymentAdminApiTest extends FinanceApiCase
{
    public function testOfflinePaymentApiConfirmsOrder(): void
    {
        $this->grantPermissions('education:finance:order:create', 'education:finance:payment:offline');
        $fixture = $this->financeFixture('finance_payment_api');
        $this->createTenantProfile($fixture['tenant']);
        $order = $this->post('/admin/education/finance/orders/from-enrollment', [
            'enrollment_id' => $fixture['enrollment']->id,
            'student_id' => $fixture['student']->id,
            'items' => [['item_type' => 'lesson_package', 'item_name' => '24 lessons', 'quantity' => '1.00', 'unit_amount_cents' => 240000]],
        ], $this->tenantHeaders($fixture['tenant']));

        $paid = $this->post('/admin/education/finance/offline-payments', [
            'order_id' => $order['data']['order_id'],
            'channel_code' => 'offline_cash',
            'payment_no' => 'OFF-API-001',
            'amount_cents' => 240000,
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $paid['code']);
        self::assertSame('paid', $paid['data']['order_status']);
    }
}
