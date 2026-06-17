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

namespace HyperfTests\Unit\Education\Finance;

use App\Service\Education\Finance\FinanceOrderService;
use App\Service\Education\Finance\PaymentCallbackService;
use App\Service\Education\Finance\ReconciliationService;

/**
 * @internal
 * @coversNothing
 */
final class ReconciliationServiceTest extends FinanceTestCase
{
    public function testImportMatchesPaymentRecordsByTradeNoAndAmount(): void
    {
        $tenant = $this->tenant('fin_reconcile');
        $campus = $this->campus($tenant);
        $student = $this->studentFixture($tenant, $campus);
        $package = $this->packageFixture($tenant, $campus);
        $enrollment = $this->pendingEnrollmentFixture($tenant, $campus, $student, $package);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 9501);
        $order = make(FinanceOrderService::class)->createFromEnrollment((int) $enrollment->id, [
            'student_id' => (int) $student->id,
            'items' => [['item_type' => 'lesson_package', 'item_name' => '24 lessons', 'quantity' => '1.00', 'unit_amount_cents' => 240000]],
        ], $context);
        make(PaymentCallbackService::class)->handleWechatCallback([
            'order_id' => $order['order_id'],
            'payment_no' => 'PAY-REC-001',
            'channel_trade_no' => 'WX-REC-001',
            'amount_cents' => 240000,
            'signature' => 'valid',
        ]);

        $batch = make(ReconciliationService::class)->import([
            'channel_code' => 'wechat',
            'business_date' => '2026-06-10',
            'rows' => [
                ['channel_trade_no' => 'WX-REC-001', 'amount_cents' => 240000, 'trade_time' => '2026-06-10 10:00:00'],
                ['channel_trade_no' => 'WX-REC-002', 'amount_cents' => 1000, 'trade_time' => '2026-06-10 11:00:00'],
            ],
        ], $context);

        self::assertSame(2, $batch['total_count']);
        self::assertSame(1, $batch['matched_count']);
        self::assertSame(1, $batch['unmatched_count']);
    }
}
