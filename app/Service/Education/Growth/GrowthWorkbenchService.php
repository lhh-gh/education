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

use App\Model\Education\Admissions\EducationLead;
use App\Model\Education\Growth\EducationGrowthLeadScore;
use App\Repository\Education\Growth\FollowupStrategyRepository;

final class GrowthWorkbenchService
{
    public function __construct(private readonly FollowupStrategyRepository $suggestions) {}

    /**
     * @return array{hot_leads: array<int, array<string, mixed>>, suggestions: array<int, array<string, mixed>>}
     */
    public function workbench(int $tenantId, int $ownerUserId, ?int $campusId = null): array
    {
        $leadQuery = EducationLead::query()->where('tenant_id', $tenantId)->where('owner_user_id', $ownerUserId);
        if ($campusId !== null) {
            $leadQuery->where('campus_id', $campusId);
        }
        $leadIds = $leadQuery->pluck('id')->all();
        $hotScores = EducationGrowthLeadScore::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('lead_id', $leadIds)
            ->whereIn('score_level', ['hot', 'high'])
            ->orderByDesc('score')
            ->limit(20)
            ->get()
            ->toArray();

        return [
            'hot_leads' => $hotScores,
            'suggestions' => $this->suggestions->ownerSuggestions($tenantId, $ownerUserId)->toArray(),
        ];
    }
}
