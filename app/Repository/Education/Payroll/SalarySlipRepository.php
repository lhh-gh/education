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

use App\Model\Education\Payroll\EducationTeacherSalaryItem;
use App\Model\Education\Payroll\EducationTeacherSalarySlip;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class SalarySlipRepository
{
    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        $query = $this->scopedQuery($filters, $context);
        foreach (['teacher_id', 'salary_month', 'status', 'batch_id'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $total = (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->map(static fn (EducationTeacherSalarySlip $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }

    public function lockScoped(int $id, EducationUserContext $context): ?EducationTeacherSalarySlip
    {
        $slip = $this->scopedQuery([], $context)->whereKey($id)->lockForUpdate()->first();

        return $slip instanceof EducationTeacherSalarySlip ? $slip : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationTeacherSalarySlip
    {
        return EducationTeacherSalarySlip::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createItem(array $data): EducationTeacherSalaryItem
    {
        return EducationTeacherSalaryItem::query()->create($data);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function scopedQuery(array $filters, EducationUserContext $context): mixed
    {
        return (new EducationScopeQuery())->applyTenantCampus(EducationTeacherSalarySlip::query(), $filters, $context);
    }
}
