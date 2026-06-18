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

namespace App\Repository\Education\Payroll;

use App\Model\Education\Payroll\EducationTeacherSalaryBatch;
use App\Model\Education\Payroll\EducationTeacherSalaryItem;
use App\Model\Education\Payroll\EducationTeacherSalarySlip;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class SalaryBatchRepository
{
    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        $query = $this->scopedQuery($filters, $context);
        foreach (['salary_month', 'status', 'campus_id'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $total = (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->map(static fn (EducationTeacherSalaryBatch $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }

    public function findByMonth(int $tenantId, ?int $campusId, string $salaryMonth): ?EducationTeacherSalaryBatch
    {
        $query = EducationTeacherSalaryBatch::query()->where('tenant_id', $tenantId)->where('salary_month', $salaryMonth);
        $campusId === null ? $query->whereNull('campus_id') : $query->where('campus_id', $campusId);
        $batch = $query->first();

        return $batch instanceof EducationTeacherSalaryBatch ? $batch : null;
    }

    public function lockScoped(int $id, EducationUserContext $context): ?EducationTeacherSalaryBatch
    {
        $batch = $this->scopedQuery([], $context)->whereKey($id)->lockForUpdate()->first();

        return $batch instanceof EducationTeacherSalaryBatch ? $batch : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationTeacherSalaryBatch
    {
        return EducationTeacherSalaryBatch::query()->create($data);
    }

    public function deleteDraftSlips(int $batchId, int $tenantId): void
    {
        $slipIds = EducationTeacherSalarySlip::query()->where('tenant_id', $tenantId)->where('batch_id', $batchId)->pluck('id')->all();
        if ($slipIds !== []) {
            EducationTeacherSalaryItem::query()->where('tenant_id', $tenantId)->whereIn('salary_slip_id', $slipIds)->delete();
            EducationTeacherSalarySlip::query()->where('tenant_id', $tenantId)->whereIn('id', $slipIds)->delete();
        }
    }

    public function nextBatchNo(int $tenantId, ?int $campusId): string
    {
        $tenantPart = str_pad((string) ($tenantId % 10000), 4, '0', \STR_PAD_LEFT);
        $campusPart = str_pad((string) (($campusId ?? 0) % 1000), 3, '0', \STR_PAD_LEFT);

        return 'SB' . Carbon::now()->format('YmdHis') . $tenantPart . $campusPart . random_int(1000, 9999);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function scopedQuery(array $filters, EducationUserContext $context): mixed
    {
        $query = EducationTeacherSalaryBatch::query();
        if ($context->platformAccess) {
            if (isset($filters['tenant_id']) && $filters['tenant_id'] !== '') {
                $query->where('tenant_id', (int) $filters['tenant_id']);
            }

            return $query;
        }
        if ($context->tenantId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('tenant_id', $context->tenantId);
    }
}
