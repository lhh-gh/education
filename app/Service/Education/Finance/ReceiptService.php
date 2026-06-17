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
use App\Model\Education\Finance\EducationReceipt;
use App\Model\Enums\Education\Finance\ReceiptStatus;
use App\Repository\Education\Finance\FinanceOrderRepository;
use App\Repository\Education\Finance\ReceiptRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class ReceiptService
{
    public function __construct(
        private readonly ReceiptRepository $repository,
        private readonly FinanceOrderRepository $orderRepository
    ) {}

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->repository->page($filters, $context);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function issue(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $order = $this->orderRepository->lockScoped((int) ($data['order_id'] ?? 0), $context);
            if (! $order instanceof EducationFinanceOrder) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'finance order not found in current context', ['order_id' => (int) ($data['order_id'] ?? 0)]);
            }
            if ((int) $order->paid_amount_cents <= 0) {
                throw new BusinessException(ResultCode::CONFLICT, 'unpaid order cannot issue receipt');
            }
            $receipt = $this->repository->create([
                'tenant_id' => (int) $order->tenant_id,
                'campus_id' => (int) $order->campus_id,
                'receipt_no' => $this->repository->nextReceiptNo((int) $order->tenant_id, (int) $order->campus_id),
                'order_id' => (int) $order->id,
                'student_id' => (int) $order->student_id,
                'amount_cents' => (int) ($data['amount_cents'] ?? $order->paid_amount_cents),
                'status' => ReceiptStatus::Issued->value,
                'issued_by' => $context->userId,
                'issued_at' => Carbon::now()->toDateTimeString(),
                'pdf_url' => $data['pdf_url'] ?? null,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            return $receipt->toArray();
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function void(int $id, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($id, $context): array {
            $receipt = $this->repository->lock($id, $context);
            if (! $receipt instanceof EducationReceipt) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'receipt not found in current context', ['receipt_id' => $id]);
            }
            if ($receipt->status === ReceiptStatus::Voided->value) {
                throw new BusinessException(ResultCode::CONFLICT, 'receipt already voided');
            }
            $receipt->fill([
                'status' => ReceiptStatus::Voided->value,
                'voided_by' => $context->userId,
                'voided_at' => Carbon::now()->toDateTimeString(),
                'updated_by' => $context->userId,
            ]);
            $receipt->save();

            return $receipt->refresh()->toArray();
        });
    }
}
