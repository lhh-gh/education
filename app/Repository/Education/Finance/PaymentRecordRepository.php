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

namespace App\Repository\Education\Finance;

use App\Model\Education\Finance\EducationPaymentRecord;
use App\Service\Education\Foundation\EducationUserContext;

final class PaymentRecordRepository
{
    public function __construct(
        private readonly FinanceOrderRepository $orderRepository
    ) {}

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        $orderIds = $this->orderRepository->scopedQuery($filters, $context)->pluck('id')->all();
        $query = EducationPaymentRecord::query()->whereIn('order_id', $orderIds);
        foreach (['order_id', 'channel_code', 'status'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }

        return $this->paginate($query->orderByDesc('id'), $filters);
    }

    public function findByPaymentNo(int $tenantId, string $paymentNo): ?EducationPaymentRecord
    {
        $record = EducationPaymentRecord::query()->where('tenant_id', $tenantId)->where('payment_no', $paymentNo)->first();

        return $record instanceof EducationPaymentRecord ? $record : null;
    }

    public function findByTradeNo(string $channelCode, string $channelTradeNo): ?EducationPaymentRecord
    {
        $record = EducationPaymentRecord::query()
            ->where('channel_code', $channelCode)
            ->where('channel_trade_no', $channelTradeNo)
            ->first();

        return $record instanceof EducationPaymentRecord ? $record : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationPaymentRecord
    {
        return EducationPaymentRecord::query()->create($data);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    private function paginate(mixed $query, array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $total = (clone $query)->count();
        $list = $query->forPage($page, $pageSize)->get()->map(static fn (EducationPaymentRecord $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}
