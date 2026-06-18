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
use App\Model\Education\Finance\EducationPaymentCallback;
use App\Model\Education\Finance\EducationPaymentRecord;
use App\Model\Enums\Education\Finance\FinanceOrderStatus;
use App\Model\Enums\Education\Finance\PaymentStatus;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Finance\FinanceOrderRepository;
use App\Repository\Education\Finance\PaymentCallbackRepository;
use App\Repository\Education\Finance\PaymentRecordRepository;
use App\Service\Education\Academic\EnrollmentService;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class PaymentCallbackService
{
    private const CHANNEL_CODE = 'wechat';

    public function __construct(
        private readonly FinanceOrderRepository $orderRepository,
        private readonly PaymentRecordRepository $paymentRepository,
        private readonly PaymentCallbackRepository $callbackRepository,
        private readonly EnrollmentService $enrollmentService
    ) {}

    /**
     * @param array<string, mixed> $payload
     * @return array{payment_record_id: int, order_status: string, message: string}
     */
    public function handleWechatCallback(array $payload): array
    {
        $channelTradeNo = (string) ($payload['channel_trade_no'] ?? '');
        if (($payload['signature'] ?? null) !== 'valid') {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'payment callback signature is invalid');
        }

        return Db::transaction(function () use ($payload, $channelTradeNo): array {
            $existingCallback = $this->callbackRepository->lockByTradeNo(self::CHANNEL_CODE, $channelTradeNo);
            if ($existingCallback instanceof EducationPaymentCallback && $existingCallback->processed && $existingCallback->payment_record_id !== null) {
                $payment = EducationPaymentRecord::query()->find((int) $existingCallback->payment_record_id);
                $order = $payment instanceof EducationPaymentRecord ? EducationFinanceOrder::query()->find((int) $payment->order_id) : null;

                return [
                    'payment_record_id' => (int) $existingCallback->payment_record_id,
                    'order_status' => $order instanceof EducationFinanceOrder ? (string) $order->status : FinanceOrderStatus::Paid->value,
                    'message' => 'payment already processed',
                ];
            }

            $order = $this->orderRepository->lockById((int) ($payload['order_id'] ?? 0));
            if (! $order instanceof EducationFinanceOrder) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'finance order not found', ['order_id' => (int) ($payload['order_id'] ?? 0)]);
            }
            $amount = (int) ($payload['amount_cents'] ?? 0);
            if ($amount !== (int) $order->total_amount_cents) {
                $this->callbackRepository->create([
                    'tenant_id' => (int) $order->tenant_id,
                    'campus_id' => (int) $order->campus_id,
                    'channel_code' => self::CHANNEL_CODE,
                    'channel_trade_no' => $channelTradeNo,
                    'raw_payload_json' => $payload,
                    'signature_valid' => true,
                    'processed' => false,
                    'error_message' => 'callback amount does not match order amount',
                ]);
                throw new BusinessException(ResultCode::CONFLICT, 'callback amount does not match order amount');
            }

            $now = Carbon::now()->toDateTimeString();
            $callback = $existingCallback instanceof EducationPaymentCallback ? $existingCallback : $this->callbackRepository->create([
                'tenant_id' => (int) $order->tenant_id,
                'campus_id' => (int) $order->campus_id,
                'channel_code' => self::CHANNEL_CODE,
                'channel_trade_no' => $channelTradeNo,
                'raw_payload_json' => $payload,
                'signature_valid' => true,
                'processed' => false,
            ]);
            $payment = $this->paymentRepository->findByTradeNo(self::CHANNEL_CODE, $channelTradeNo);
            if (! $payment instanceof EducationPaymentRecord) {
                $payment = $this->paymentRepository->create([
                    'tenant_id' => (int) $order->tenant_id,
                    'campus_id' => (int) $order->campus_id,
                    'order_id' => (int) $order->id,
                    'payment_no' => (string) ($payload['payment_no'] ?? $this->nextPaymentNo((int) $order->tenant_id, (int) $order->campus_id)),
                    'channel_code' => self::CHANNEL_CODE,
                    'channel_trade_no' => $channelTradeNo,
                    'amount_cents' => $amount,
                    'channel_fee_cents' => (int) ($payload['channel_fee_cents'] ?? 0),
                    'status' => PaymentStatus::Paid->value,
                    'paid_at' => $now,
                    'operator_id' => 0,
                    'created_by' => 0,
                    'updated_by' => 0,
                ]);
            }

            if ((int) $order->paid_amount_cents < (int) $order->total_amount_cents) {
                $order->fill([
                    'paid_amount_cents' => (int) $order->total_amount_cents,
                    'status' => FinanceOrderStatus::Paid->value,
                    'paid_at' => $order->paid_at ?? $now,
                    'updated_by' => 0,
                ]);
                $order->save();
            }

            $callback->fill([
                'payment_record_id' => (int) $payment->id,
                'processed' => true,
                'processed_at' => $now,
                'error_message' => null,
                'updated_by' => 0,
            ]);
            $callback->save();

            if ($order->enrollment_id !== null) {
                $this->enrollmentService->confirm((int) $order->enrollment_id, $this->systemContext($order), 0);
            }

            return [
                'payment_record_id' => (int) $payment->id,
                'order_status' => FinanceOrderStatus::Paid->value,
                'message' => 'payment processed',
            ];
        });
    }

    private function systemContext(EducationFinanceOrder $order): EducationUserContext
    {
        return new EducationUserContext(
            userId: 0,
            tenantId: (int) $order->tenant_id,
            roleCode: EducationRoleCode::TenantAdmin,
            platformAccess: false,
            campusIds: [(int) $order->campus_id],
            currentCampusId: (int) $order->campus_id
        );
    }

    private function nextPaymentNo(int $tenantId, ?int $campusId): string
    {
        $tenantPart = str_pad((string) ($tenantId % 10000), 4, '0', \STR_PAD_LEFT);
        $campusPart = str_pad((string) (($campusId ?? 0) % 1000), 3, '0', \STR_PAD_LEFT);

        return 'PM' . Carbon::now()->format('YmdHis') . $tenantPart . $campusPart . random_int(1000, 9999);
    }
}
