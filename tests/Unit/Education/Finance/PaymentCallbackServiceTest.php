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

use App\Exception\BusinessException;
use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Finance\EducationFinanceOrder;
use App\Service\Education\Finance\FinanceOrderService;
use App\Service\Education\Finance\PaymentCallbackService;

/**
 * @internal
 * @coversNothing
 */
final class PaymentCallbackServiceTest extends FinanceTestCase
{
    public function testDuplicateCallbackIsIdempotent(): void
    {
        [$tenant, $campus, $student, $enrollment] = $this->orderFixture();
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 9202);
        $order = make(FinanceOrderService::class)->createFromEnrollment((int) $enrollment->id, [
            'student_id' => (int) $student->id,
            'items' => [['item_type' => 'lesson_package', 'item_name' => '24 lessons', 'quantity' => '1.00', 'unit_amount_cents' => 240000]],
        ], $context);
        $service = make(PaymentCallbackService::class);

        $first = $service->handleWechatCallback([
            'order_id' => $order['order_id'],
            'payment_no' => 'PAY-CB-001',
            'channel_trade_no' => 'WX-CB-001',
            'amount_cents' => 240000,
            'signature' => 'valid',
        ]);
        $second = $service->handleWechatCallback([
            'order_id' => $order['order_id'],
            'payment_no' => 'PAY-CB-001',
            'channel_trade_no' => 'WX-CB-001',
            'amount_cents' => 240000,
            'signature' => 'valid',
        ]);

        self::assertSame($first['payment_record_id'], $second['payment_record_id']);
        self::assertSame('payment already processed', $second['message']);
        self::assertSame(240000, (int) EducationFinanceOrder::query()->find($order['order_id'])->paid_amount_cents);
    }

    public function testSuccessfulCallbackConfirmsEnrollmentAndDoesNotRematerializeAccount(): void
    {
        [$tenant, $campus, $student, $enrollment] = $this->orderFixture();
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 9203);
        $order = make(FinanceOrderService::class)->createFromEnrollment((int) $enrollment->id, [
            'student_id' => (int) $student->id,
            'items' => [['item_type' => 'lesson_package', 'item_name' => '24 lessons', 'quantity' => '1.00', 'unit_amount_cents' => 240000]],
        ], $context);
        $service = make(PaymentCallbackService::class);

        $service->handleWechatCallback([
            'order_id' => $order['order_id'],
            'payment_no' => 'PAY-CB-002',
            'channel_trade_no' => 'WX-CB-002',
            'amount_cents' => 240000,
            'signature' => 'valid',
        ]);
        $account = EducationStudentCourseAccount::query()->where('student_id', $student->id)->first();
        self::assertSame('confirmed', EducationEnrollment::query()->find($enrollment->id)->status);
        self::assertSame('24.00', $account->available_units);

        $service->handleWechatCallback([
            'order_id' => $order['order_id'],
            'payment_no' => 'PAY-CB-002',
            'channel_trade_no' => 'WX-CB-002',
            'amount_cents' => 240000,
            'signature' => 'valid',
        ]);
        self::assertSame('24.00', $account->refresh()->available_units);
    }

    public function testCallbackAmountMismatchIsRejected(): void
    {
        [$tenant, $campus, $student, $enrollment] = $this->orderFixture();
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 9204);
        $order = make(FinanceOrderService::class)->createFromEnrollment((int) $enrollment->id, [
            'student_id' => (int) $student->id,
            'items' => [['item_type' => 'lesson_package', 'item_name' => '24 lessons', 'quantity' => '1.00', 'unit_amount_cents' => 240000]],
        ], $context);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('callback amount does not match order amount');
        make(PaymentCallbackService::class)->handleWechatCallback([
            'order_id' => $order['order_id'],
            'payment_no' => 'PAY-CB-003',
            'channel_trade_no' => 'WX-CB-003',
            'amount_cents' => 230000,
            'signature' => 'valid',
        ]);
    }

    private function orderFixture(): array
    {
        $tenant = $this->tenant('fin_callback');
        $campus = $this->campus($tenant);
        $student = $this->studentFixture($tenant, $campus);
        $package = $this->packageFixture($tenant, $campus);
        $enrollment = $this->pendingEnrollmentFixture($tenant, $campus, $student, $package);

        return [$tenant, $campus, $student, $enrollment];
    }
}
