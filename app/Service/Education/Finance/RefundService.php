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
use App\Model\Education\Finance\EducationRefundRequest;
use App\Model\Enums\Education\Finance\FinanceOrderStatus;
use App\Model\Enums\Education\Finance\RefundStatus;
use App\Repository\Education\Finance\FinanceAdjustmentRepository;
use App\Repository\Education\Finance\FinanceOrderRepository;
use App\Repository\Education\Finance\RefundRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class RefundService
{
    public function __construct(
        private readonly FinanceOrderRepository $orderRepository,
        private readonly RefundRepository $refundRepository,
        private readonly FinanceAdjustmentRepository $adjustmentRepository
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{refund_request_id: int, status: string}
     */
    public function request(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $order = $this->orderRepository->lockScoped((int) ($data['order_id'] ?? 0), $context);
            if (! $order instanceof EducationFinanceOrder) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'finance order not found in current context', ['order_id' => (int) ($data['order_id'] ?? 0)]);
            }
            if ((int) $order->paid_amount_cents <= 0) {
                throw new BusinessException(ResultCode::CONFLICT, 'unpaid order cannot be refunded');
            }
            $amount = (int) ($data['refund_amount_cents'] ?? 0);
            if ($amount <= 0 || $amount > (int) $order->paid_amount_cents - (int) $order->refund_amount_cents) {
                throw new BusinessException(ResultCode::CONFLICT, 'refund amount exceeds refundable amount');
            }
            $paymentId = $data['payment_record_id'] ?? null;
            if ($paymentId === null || $paymentId === '') {
                $payment = EducationPaymentRecord::query()->where('tenant_id', $order->tenant_id)->where('order_id', $order->id)->orderByDesc('id')->first();
                $paymentId = $payment instanceof EducationPaymentRecord ? (int) $payment->id : null;
            }
            $request = $this->refundRepository->createRequest([
                'tenant_id' => (int) $order->tenant_id,
                'campus_id' => (int) $order->campus_id,
                'order_id' => (int) $order->id,
                'payment_record_id' => $paymentId,
                'refund_no' => $this->refundRepository->nextRefundNo((int) $order->tenant_id, (int) $order->campus_id),
                'refund_amount_cents' => $amount,
                'reason' => (string) ($data['reason'] ?? ''),
                'status' => RefundStatus::Pending->value,
                'requested_by' => $context->userId,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            return ['refund_request_id' => (int) $request->id, 'status' => (string) $request->status];
        });
    }

    /**
     * @param array<string, mixed> $data
     * @return array{refund_request_id: int, status: string}
     */
    public function approve(int $refundRequestId, array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($refundRequestId, $data, $context): array {
            $request = $this->refundRepository->lockRequest($refundRequestId, $context);
            if (! $request instanceof EducationRefundRequest) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'refund request not found in current context', ['refund_request_id' => $refundRequestId]);
            }
            if ($request->status !== RefundStatus::Pending->value) {
                throw new BusinessException(ResultCode::CONFLICT, 'refund request is not pending');
            }
            $order = $this->orderRepository->lockScoped((int) $request->order_id, $context);
            if (! $order instanceof EducationFinanceOrder) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'finance order not found in current context', ['order_id' => (int) $request->order_id]);
            }
            $newRefunded = (int) $order->refund_amount_cents + (int) $request->refund_amount_cents;
            if ($newRefunded > (int) $order->paid_amount_cents) {
                throw new BusinessException(ResultCode::CONFLICT, 'refund amount exceeds refundable amount');
            }
            $now = Carbon::now()->toDateTimeString();
            $request->fill([
                'status' => RefundStatus::Approved->value,
                'reviewed_by' => $context->userId,
                'reviewed_at' => $now,
                'review_note' => $data['review_note'] ?? null,
                'updated_by' => $context->userId,
            ]);
            $request->save();
            $this->refundRepository->createRecord([
                'tenant_id' => (int) $request->tenant_id,
                'campus_id' => (int) $request->campus_id,
                'refund_request_id' => (int) $request->id,
                'payment_record_id' => $request->payment_record_id === null ? null : (int) $request->payment_record_id,
                'refund_trade_no' => $data['refund_trade_no'] ?? null,
                'refund_amount_cents' => (int) $request->refund_amount_cents,
                'status' => RefundStatus::Refunded->value,
                'refunded_at' => $now,
                'raw_payload_json' => ['review_note' => $data['review_note'] ?? null],
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            $order->fill([
                'refund_amount_cents' => $newRefunded,
                'status' => $newRefunded >= (int) $order->paid_amount_cents ? FinanceOrderStatus::Refunded->value : FinanceOrderStatus::PartialRefunded->value,
                'updated_by' => $context->userId,
            ]);
            $order->save();
            $this->adjustmentRepository->create([
                'tenant_id' => (int) $order->tenant_id,
                'campus_id' => (int) $order->campus_id,
                'order_id' => (int) $order->id,
                'adjustment_type' => 'refund',
                'amount_cents' => -1 * (int) $request->refund_amount_cents,
                'reason' => (string) $request->reason,
                'operator_id' => $context->userId,
                'source_type' => 'refund_request',
                'source_id' => (int) $request->id,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            return ['refund_request_id' => (int) $request->id, 'status' => (string) $request->status];
        });
    }
}
