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

namespace App\Repository\Education\Growth;

use App\Model\Education\Growth\EducationGrowthLeadLossRecord;
use App\Model\Education\Growth\EducationGrowthLossReason;

final class LossReasonRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function saveReason(array $data): EducationGrowthLossReason
    {
        return EducationGrowthLossReason::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'reason_code' => $data['reason_code'],
        ], $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createLossRecord(array $data): EducationGrowthLeadLossRecord
    {
        return EducationGrowthLeadLossRecord::query()->create($data);
    }
}
