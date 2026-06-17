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

use App\Model\Education\Finance\EducationPaymentCallback;

final class PaymentCallbackRepository
{
    public function findByTradeNo(string $channelCode, string $channelTradeNo): ?EducationPaymentCallback
    {
        $callback = EducationPaymentCallback::query()
            ->where('channel_code', $channelCode)
            ->where('channel_trade_no', $channelTradeNo)
            ->first();

        return $callback instanceof EducationPaymentCallback ? $callback : null;
    }

    public function lockByTradeNo(string $channelCode, string $channelTradeNo): ?EducationPaymentCallback
    {
        $callback = EducationPaymentCallback::query()
            ->where('channel_code', $channelCode)
            ->where('channel_trade_no', $channelTradeNo)
            ->lockForUpdate()
            ->first();

        return $callback instanceof EducationPaymentCallback ? $callback : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationPaymentCallback
    {
        return EducationPaymentCallback::query()->create($data);
    }
}
