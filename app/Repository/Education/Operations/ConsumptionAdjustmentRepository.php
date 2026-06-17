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

namespace App\Repository\Education\Operations;

use App\Model\Education\Operations\EducationLessonConsumptionAdjustment;

final class ConsumptionAdjustmentRepository
{
    public function pageByConsumption(int $tenantId, int $consumptionId): array
    {
        $rows = EducationLessonConsumptionAdjustment::query()
            ->where('tenant_id', $tenantId)
            ->where('original_consumption_id', $consumptionId)
            ->orderByDesc('id')
            ->get()
            ->map(static fn ($row): array => $row->toArray())
            ->all();

        return ['list' => $rows, 'total' => \count($rows)];
    }

    public function createReverseRecord(array $data): EducationLessonConsumptionAdjustment
    {
        return EducationLessonConsumptionAdjustment::query()->create($data);
    }

    public function sumAdjustedCredits(int $tenantId, int $consumptionId): string
    {
        $sum = EducationLessonConsumptionAdjustment::query()
            ->where('tenant_id', $tenantId)
            ->where('original_consumption_id', $consumptionId)
            ->sum('credits');

        return number_format((float) $sum, 2, '.', '');
    }
}
