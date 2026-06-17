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

use App\Model\Education\Finance\EducationRefundRecord;
use App\Model\Education\Finance\EducationRefundRequest;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class RefundRepository
{
    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        $query = EducationRefundRequest::query();
        if (! $context->platformAccess) {
            if ($context->tenantId === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('tenant_id', $context->tenantId);
            }
        } elseif (isset($filters['tenant_id']) && $filters['tenant_id'] !== '') {
            $query->where('tenant_id', (int) $filters['tenant_id']);
        }
        foreach (['campus_id', 'order_id', 'status'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $total = (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->map(static fn (EducationRefundRequest $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }

    public function lockRequest(int $id, EducationUserContext $context): ?EducationRefundRequest
    {
        $query = EducationRefundRequest::query()->whereKey($id)->lockForUpdate();
        if (! $context->platformAccess) {
            $context->tenantId === null ? $query->whereRaw('1 = 0') : $query->where('tenant_id', $context->tenantId);
        }
        $request = $query->first();

        return $request instanceof EducationRefundRequest ? $request : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createRequest(array $data): EducationRefundRequest
    {
        return EducationRefundRequest::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createRecord(array $data): EducationRefundRecord
    {
        return EducationRefundRecord::query()->create($data);
    }

    public function nextRefundNo(int $tenantId, ?int $campusId): string
    {
        $tenantPart = str_pad((string) ($tenantId % 10000), 4, '0', \STR_PAD_LEFT);
        $campusPart = str_pad((string) (($campusId ?? 0) % 1000), 3, '0', \STR_PAD_LEFT);

        return 'FR' . Carbon::now()->format('YmdHis') . $tenantPart . $campusPart . random_int(1000, 9999);
    }
}
