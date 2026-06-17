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

use App\Model\Education\Finance\EducationPaymentRecord;
use App\Model\Education\Finance\EducationRefundRecord;
use App\Service\Education\Finance\FinanceOrderService;
use App\Service\Education\Finance\OfflineCollectionService;
use App\Service\Education\Finance\RefundService;

/**
 * @internal
 * @coversNothing
 */
final class RefundServiceTest extends FinanceTestCase
{
    public function testApprovedRefundCreatesRefundRecordAndKeepsOriginalPayment(): void
    {
        $tenant = $this->tenant('fin_refund');
        $campus = $this->campus($tenant);
        $student = $this->studentFixture($tenant, $campus);
        $package = $this->packageFixture($tenant, $campus);
        $enrollment = $this->pendingEnrollmentFixture($tenant, $campus, $student, $package);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 9401);
        $order = make(FinanceOrderService::class)->createFromEnrollment((int) $enrollment->id, [
            'student_id' => (int) $student->id,
            'items' => [['item_type' => 'lesson_package', 'item_name' => '24 lessons', 'quantity' => '1.00', 'unit_amount_cents' => 240000]],
        ], $context);
        make(OfflineCollectionService::class)->confirm([
            'order_id' => $order['order_id'],
            'channel_code' => 'offline_cash',
            'payment_no' => 'OFF-REF-001',
            'amount_cents' => 240000,
        ], $context);

        $request = make(RefundService::class)->request([
            'order_id' => $order['order_id'],
            'refund_amount_cents' => 60000,
            'reason' => 'student withdrawal',
        ], $context);
        $approved = make(RefundService::class)->approve((int) $request['refund_request_id'], ['review_note' => 'approved'], $context);

        self::assertSame('approved', $approved['status']);
        self::assertTrue(EducationPaymentRecord::query()->where('payment_no', 'OFF-REF-001')->exists());
        self::assertTrue(EducationRefundRecord::query()->where('refund_request_id', $request['refund_request_id'])->exists());
    }
}
