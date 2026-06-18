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

use App\Model\Education\Growth\EducationGrowthFollowupStrategy;
use App\Model\Education\Growth\EducationGrowthFollowupSuggestion;
use Hyperf\Database\Model\Collection;

final class FollowupStrategyRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationGrowthFollowupStrategy
    {
        return EducationGrowthFollowupStrategy::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'strategy_code' => $data['strategy_code'],
        ], $data);
    }

    public function match(int $tenantId, string $leadStage, string $scoreLevel): ?EducationGrowthFollowupStrategy
    {
        return EducationGrowthFollowupStrategy::query()
            ->where('tenant_id', $tenantId)
            ->where('lead_stage', $leadStage)
            ->where('status', 'enabled')
            ->where(static function ($query) use ($scoreLevel): void {
                $query->whereNull('score_level')->orWhere('score_level', $scoreLevel);
            })
            ->orderByRaw('score_level is null asc')
            ->first();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createSuggestion(array $data): EducationGrowthFollowupSuggestion
    {
        return EducationGrowthFollowupSuggestion::query()->create($data);
    }

    /**
     * @return Collection<int, EducationGrowthFollowupSuggestion>
     */
    public function ownerSuggestions(int $tenantId, int $ownerUserId, string $status = 'pending'): Collection
    {
        return EducationGrowthFollowupSuggestion::query()
            ->where('tenant_id', $tenantId)
            ->where('owner_user_id', $ownerUserId)
            ->where('status', $status)
            ->orderBy('due_at')
            ->get();
    }
}
