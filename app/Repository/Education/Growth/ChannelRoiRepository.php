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

use App\Model\Education\Growth\EducationGrowthChannelCost;
use App\Model\Education\Growth\EducationGrowthChannelRoiDaily;

final class ChannelRoiRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function saveCost(array $data): EducationGrowthChannelCost
    {
        return EducationGrowthChannelCost::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function saveDaily(array $data): EducationGrowthChannelRoiDaily
    {
        return EducationGrowthChannelRoiDaily::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'campus_id' => $data['campus_id'],
            'source_id' => $data['source_id'],
            'metric_date' => $data['metric_date'],
        ], $data);
    }
}
