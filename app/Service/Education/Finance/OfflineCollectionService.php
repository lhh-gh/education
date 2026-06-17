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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Finance\EducationFinanceOrder;
use App\Model\Education\Finance\EducationPaymentRecord;
use App\Model\Enums\Education\Finance\FinanceOrderStatus;
use App\Model\Enums\Education\Finance\PaymentStatus;
use App\Repository\Education\Finance\FinanceOrderRepository;
use App\Repository\Education\Finance\PaymentRecordRepository;
use App\Service\Education\Academic\EnrollmentService;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class OfflineCollectionService
{
    public function __construct(
        private readonly FinanceOrderRepository $orderRepository,
        private readonly PaymentRecordRepository $paymentRepository,
        private readonly EnrollmentService $enrollmentService
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{payment_record_id: int, order_status: string}
     */
    public function confirm(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $order = $this->orderRepository->lockScoped((int) ($data['order_id'] ?? 0), $context);
            if (! $order instanceof EducationFinanceOrder) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'finance order not found in current context', ['order_id' => (int) ($data['order_id'] ?? 0)]);
            }
            $paymentNo = (string) ($data['payment_no'] ?? '');
            $existing = $paymentNo === '' ? null : $this->paymentRepository->findByPaymentNo((int) $order->tenant_id, $paymentNo);
            if ($existing instanceof EducationPaymentRecord) {
                return ['payment_record_id' => (int) $existing->id, 'order_status' => (string) $order->status];
            }

            $amount = (int) ($data['amount_cents'] ?? 0);
            if ($amount <= 0) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'payment amount is invalid');
            }
            if ((int) $order->paid_amount_cents + $amount > (int) $order->total_amount_cents) {
                throw new BusinessException(ResultCode::CONFLICT, 'payment amount exceeds order unpaid amount');
            }

            $now = Carbon::now()->toDateTimeString();
            $payment = $this->paymentRepository->create([
                'tenant_id' => (int) $order->tenant_id,
                'campus_id' => (int) $order->campus_id,
                'order_id' => (int) $order->id,
                'payment_no' => $paymentNo !== '' ? $paymentNo : $this->nextPaymentNo((int) $order->tenant_id, (int) $order->campus_id),
                'channel_code' => (string) ($data['channel_code'] ?? 'offline_cash'),
                'channel_trade_no' => isset($data['channel_trade_no']) && $data['channel_trade_no'] !== '' ? (string) $data['channel_trade_no'] : null,
                'amount_cents' => $amount,
                'channel_fee_cents' => (int) ($data['channel_fee_cents'] ?? 0),
                'status' => PaymentStatus::Paid->value,
                'paid_at' => $now,
                'payer_name' => $data['payer_name'] ?? null,
                'operator_id' => $context->userId,
                'remark' => $data['remark'] ?? null,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            $paidAmount = (int) $order->paid_amount_cents + $amount;
            $order->fill([
                'paid_amount_cents' => $paidAmount,
                'status' => $paidAmount >= (int) $order->total_amount_cents ? FinanceOrderStatus::Paid->value : FinanceOrderStatus::Paying->value,
                'paid_at' => $paidAmount >= (int) $order->total_amount_cents ? $now : $order->paid_at,
                'updated_by' => $context->userId,
            ]);
            $order->save();
            $order = $order->refresh();

            if ($order->status === FinanceOrderStatus::Paid->value && $order->enrollment_id !== null) {
                $this->enrollmentService->confirm((int) $order->enrollment_id, $context, $context->userId);
            }

            return ['payment_record_id' => (int) $payment->id, 'order_status' => (string) $order->status];
        });
    }

    private function nextPaymentNo(int $tenantId, ?int $campusId): string
    {
        $tenantPart = str_pad((string) ($tenantId % 10000), 4, '0', \STR_PAD_LEFT);
        $campusPart = str_pad((string) (($campusId ?? 0) % 1000), 3, '0', \STR_PAD_LEFT);

        return 'PM' . Carbon::now()->format('YmdHis') . $tenantPart . $campusPart . random_int(1000, 9999);
    }
}
