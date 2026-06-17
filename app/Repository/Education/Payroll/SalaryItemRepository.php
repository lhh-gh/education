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

final class SalaryItemRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationTeacherSalaryItem
    {
        return EducationTeacherSalaryItem::query()->create($data);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listBySlip(int $tenantId, int $salarySlipId): array
    {
        return EducationTeacherSalaryItem::query()
            ->where('tenant_id', $tenantId)
            ->where('salary_slip_id', $salarySlipId)
            ->orderBy('id')
            ->get()
            ->map(static fn (EducationTeacherSalaryItem $row): array => $row->toArray())
            ->all();
    }
}
