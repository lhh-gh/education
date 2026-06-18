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

use App\Model\Education\Growth\EducationGrowthCampaign;

final class GrowthCampaignRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationGrowthCampaign
    {
        return EducationGrowthCampaign::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'campaign_code' => $data['campaign_code'],
        ], $data);
    }
}
