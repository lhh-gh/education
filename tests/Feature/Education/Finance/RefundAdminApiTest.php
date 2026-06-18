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
final class RefundAdminApiTest extends FinanceApiCase
{
    public function testRefundRequestAndApproveApi(): void
    {
        $this->grantPermissions('education:finance:order:create', 'education:finance:payment:offline', 'education:finance:refund:create', 'education:finance:refund:approve');
        $fixture = $this->financeFixture('finance_refund_api');
        $this->createTenantProfile($fixture['tenant']);
        $order = $this->post('/admin/education/finance/orders/from-enrollment', [
            'enrollment_id' => $fixture['enrollment']->id,
            'student_id' => $fixture['student']->id,
            'items' => [['item_type' => 'lesson_package', 'item_name' => '24 lessons', 'quantity' => '1.00', 'unit_amount_cents' => 240000]],
        ], $this->tenantHeaders($fixture['tenant']));
        $this->post('/admin/education/finance/offline-payments', [
            'order_id' => $order['data']['order_id'],
            'channel_code' => 'offline_cash',
            'payment_no' => 'OFF-REF-API-001',
            'amount_cents' => 240000,
        ], $this->tenantHeaders($fixture['tenant']));

        $request = $this->post('/admin/education/finance/refund-requests', [
            'order_id' => $order['data']['order_id'],
            'refund_amount_cents' => 60000,
            'reason' => 'student withdrawal',
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::SUCCESS->value, $request['code']);

        $approved = $this->post('/admin/education/finance/refund-requests/' . $request['data']['refund_request_id'] . '/approve', [
            'review_note' => 'approved',
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::SUCCESS->value, $approved['code']);
        self::assertSame('approved', $approved['data']['status']);
    }
}
