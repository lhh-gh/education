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

namespace App\Service\Education\Ai;

use App\Model\Education\Ai\EducationAiUsageLog;

final class AiUsageService
{
    /**
     * @param array<string, mixed> $filters
     * @return array{total_tokens: int, cost_cents: int}
     */
    public function summary(int $tenantId, array $filters = []): array
    {
        $query = EducationAiUsageLog::query()->where('tenant_id', $tenantId);
        if (isset($filters['start_date'])) {
            $query->where('usage_date', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->where('usage_date', '<=', $filters['end_date']);
        }

        return [
            'total_tokens' => (int) $query->sum('total_tokens'),
            'cost_cents' => (int) $query->sum('cost_cents'),
        ];
    }

    /**
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(int $tenantId, int $page = 1, int $pageSize = 20): array
    {
        $query = EducationAiUsageLog::query()->where('tenant_id', $tenantId);
        $total = (int) $query->count();
        $list = $query->orderByDesc('usage_date')->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray();

        return ['list' => $list, 'total' => $total];
    }
}
