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

use App\Model\Education\Finance\EducationReconciliationBatch;
use App\Model\Education\Finance\EducationReconciliationItem;
use Carbon\Carbon;

final class ReconciliationRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function createBatch(array $data): EducationReconciliationBatch
    {
        return EducationReconciliationBatch::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createItem(array $data): EducationReconciliationItem
    {
        return EducationReconciliationItem::query()->create($data);
    }

    public function nextBatchNo(int $tenantId, ?int $campusId): string
    {
        $tenantPart = str_pad((string) ($tenantId % 10000), 4, '0', \STR_PAD_LEFT);
        $campusPart = str_pad((string) (($campusId ?? 0) % 1000), 3, '0', \STR_PAD_LEFT);

        return 'RB' . Carbon::now()->format('YmdHis') . $tenantPart . $campusPart . random_int(1000, 9999);
    }
}
