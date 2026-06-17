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

use App\Model\Education\Finance\EducationFinanceOrder;
use App\Model\Education\Finance\EducationFinanceOrderItem;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class FinanceOrderRepository
{
    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        $query = $this->scopedQuery($filters, $context);
        foreach (['student_id', 'guardian_id', 'enrollment_id', 'status', 'order_type'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }
        if (isset($filters['keyword']) && $filters['keyword'] !== '') {
            $keyword = '%' . $filters['keyword'] . '%';
            $query->where(static fn ($query) => $query->where('order_no', 'like', $keyword));
        }

        return $this->paginate($query->orderByDesc('id'), $filters);
    }

    public function findScoped(int $id, EducationUserContext $context): ?EducationFinanceOrder
    {
        $order = $this->scopedQuery([], $context)->whereKey($id)->first();

        return $order instanceof EducationFinanceOrder ? $order : null;
    }

    public function lockScoped(int $id, EducationUserContext $context): ?EducationFinanceOrder
    {
        $order = $this->scopedQuery([], $context)->whereKey($id)->lockForUpdate()->first();

        return $order instanceof EducationFinanceOrder ? $order : null;
    }

    public function lockById(int $id): ?EducationFinanceOrder
    {
        $order = EducationFinanceOrder::query()->whereKey($id)->lockForUpdate()->first();

        return $order instanceof EducationFinanceOrder ? $order : null;
    }

    public function findByEnrollment(int $tenantId, int $enrollmentId): ?EducationFinanceOrder
    {
        $order = EducationFinanceOrder::query()
            ->where('tenant_id', $tenantId)
            ->where('enrollment_id', $enrollmentId)
            ->first();

        return $order instanceof EducationFinanceOrder ? $order : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationFinanceOrder
    {
        return EducationFinanceOrder::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createItem(array $data): EducationFinanceOrderItem
    {
        return EducationFinanceOrderItem::query()->create($data);
    }

    public function nextOrderNo(int $tenantId, ?int $campusId): string
    {
        $tenantPart = str_pad((string) ($tenantId % 10000), 4, '0', \STR_PAD_LEFT);
        $campusPart = str_pad((string) (($campusId ?? 0) % 1000), 3, '0', \STR_PAD_LEFT);

        return 'FO' . Carbon::now()->format('YmdHis') . $tenantPart . $campusPart . random_int(1000, 9999);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function scopedQuery(array $filters, EducationUserContext $context): mixed
    {
        $query = EducationFinanceOrder::query();
        if ($context->platformAccess) {
            if (isset($filters['tenant_id']) && $filters['tenant_id'] !== '') {
                $query->where('tenant_id', (int) $filters['tenant_id']);
            }
            if (isset($filters['campus_id']) && $filters['campus_id'] !== '') {
                $query->where('campus_id', (int) $filters['campus_id']);
            }

            return $query;
        }

        if ($context->tenantId === null) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('tenant_id', $context->tenantId);
        $campusId = isset($filters['campus_id']) && $filters['campus_id'] !== '' ? (int) $filters['campus_id'] : null;
        if ($context->roleCode === EducationRoleCode::TenantAdmin) {
            return $campusId === null ? $query : $query->where('campus_id', $campusId);
        }
        if ($campusId !== null) {
            return $context->canAccessCampus($campusId) ? $query->where('campus_id', $campusId) : $query->whereRaw('1 = 0');
        }

        return $context->campusIds === [] ? $query->whereRaw('1 = 0') : $query->whereIn('campus_id', $context->campusIds);
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
        $list = $query->forPage($page, $pageSize)->get()->map(static fn (EducationFinanceOrder $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}
