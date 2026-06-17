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

use App\Model\Education\Finance\EducationReceipt;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class ReceiptRepository
{
    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        $query = EducationReceipt::query();
        if (! $context->platformAccess) {
            $context->tenantId === null ? $query->whereRaw('1 = 0') : $query->where('tenant_id', $context->tenantId);
        } elseif (isset($filters['tenant_id']) && $filters['tenant_id'] !== '') {
            $query->where('tenant_id', (int) $filters['tenant_id']);
        }
        foreach (['campus_id', 'order_id', 'student_id', 'status'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $total = (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->map(static fn (EducationReceipt $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationReceipt
    {
        return EducationReceipt::query()->create($data);
    }

    public function lock(int $id, EducationUserContext $context): ?EducationReceipt
    {
        $query = EducationReceipt::query()->whereKey($id)->lockForUpdate();
        if (! $context->platformAccess) {
            $context->tenantId === null ? $query->whereRaw('1 = 0') : $query->where('tenant_id', $context->tenantId);
        }
        $receipt = $query->first();

        return $receipt instanceof EducationReceipt ? $receipt : null;
    }

    public function nextReceiptNo(int $tenantId, ?int $campusId): string
    {
        $tenantPart = str_pad((string) ($tenantId % 10000), 4, '0', \STR_PAD_LEFT);
        $campusPart = str_pad((string) (($campusId ?? 0) % 1000), 3, '0', \STR_PAD_LEFT);

        return 'RC' . Carbon::now()->format('YmdHis') . $tenantPart . $campusPart . random_int(1000, 9999);
    }
}
