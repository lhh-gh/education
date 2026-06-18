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

use App\Model\Education\Growth\EducationGrowthConversionFunnel;

final class ConversionFunnelRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function saveDaily(array $data): EducationGrowthConversionFunnel
    {
        return EducationGrowthConversionFunnel::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'campus_id' => $data['campus_id'],
            'metric_date' => $data['metric_date'],
            'source_id' => $data['source_id'],
            'stage' => $data['stage'],
        ], $data);
    }
}
