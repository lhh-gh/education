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

use App\Model\Education\Finance\EducationPaymentChannel;
use App\Service\Education\Foundation\EducationUserContext;

final class PaymentChannelRepository
{
    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function list(array $filters, EducationUserContext $context): array
    {
        $query = EducationPaymentChannel::query();
        if (! $context->platformAccess) {
            if ($context->tenantId === null) {
                return [];
            }
            $query->where('tenant_id', $context->tenantId);
        } elseif (isset($filters['tenant_id']) && $filters['tenant_id'] !== '') {
            $query->where('tenant_id', (int) $filters['tenant_id']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('sort_order')->orderByDesc('id')->get()->map(static fn (EducationPaymentChannel $row): array => $row->toArray())->all();
    }

    public function findByCode(int $tenantId, string $code): ?EducationPaymentChannel
    {
        $channel = EducationPaymentChannel::query()->where('tenant_id', $tenantId)->where('channel_code', $code)->first();

        return $channel instanceof EducationPaymentChannel ? $channel : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationPaymentChannel
    {
        return EducationPaymentChannel::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'channel_code' => $data['channel_code'],
        ], $data);
    }
}
