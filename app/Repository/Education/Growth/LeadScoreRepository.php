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

use App\Model\Education\Growth\EducationGrowthLeadScore;
use App\Model\Education\Growth\EducationGrowthLeadScoreFactor;

final class LeadScoreRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function saveScore(array $data): EducationGrowthLeadScore
    {
        return EducationGrowthLeadScore::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'lead_id' => $data['lead_id'],
            'score_date' => $data['score_date'],
        ], $data);
    }

    /**
     * @param list<array<string, mixed>> $factors
     */
    public function replaceFactors(int $tenantId, int $leadScoreId, array $factors): void
    {
        EducationGrowthLeadScoreFactor::query()
            ->where('tenant_id', $tenantId)
            ->where('lead_score_id', $leadScoreId)
            ->delete();

        foreach ($factors as $factor) {
            EducationGrowthLeadScoreFactor::query()->create($factor + [
                'tenant_id' => $tenantId,
                'lead_score_id' => $leadScoreId,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(int $tenantId, array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        $query = EducationGrowthLeadScore::query()->where('tenant_id', $tenantId);
        if (($filters['score_level'] ?? '') !== '') {
            $query->where('score_level', $filters['score_level']);
        }
        if (isset($filters['owner_user_id'])) {
            $query->where('owner_user_id', $filters['owner_user_id']);
        }
        $total = (int) $query->count();

        return [
            'list' => $query->orderByDesc('score')->forPage($page, $pageSize)->get()->toArray(),
            'total' => $total,
        ];
    }
}
