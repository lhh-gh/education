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

namespace App\Service\Education\Growth;

use App\Repository\Education\Growth\GrowthCampaignRepository;

final class GrowthCampaignService
{
    public function __construct(private readonly GrowthCampaignRepository $campaigns) {}

    /**
     * @param array<string, mixed> $data
     * @return array{campaign_id: int, status: string}
     */
    public function save(array $data): array
    {
        $campaign = $this->campaigns->save($data + ['status' => 'draft']);

        return ['campaign_id' => (int) $campaign->id, 'status' => (string) $campaign->status->value];
    }
}
