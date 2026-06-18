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

use App\Model\Education\Growth\EducationGrowthAiTalkScript;

final class AiTalkScriptRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationGrowthAiTalkScript
    {
        return EducationGrowthAiTalkScript::query()->create($data);
    }

    public function script(int $id): EducationGrowthAiTalkScript
    {
        return EducationGrowthAiTalkScript::query()->findOrFail($id);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(int $tenantId, array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        $query = EducationGrowthAiTalkScript::query()->where('tenant_id', $tenantId);
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', $filters['status']);
        }
        $total = (int) $query->count();

        return ['list' => $query->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray(), 'total' => $total];
    }
}
