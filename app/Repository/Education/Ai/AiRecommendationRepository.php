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

namespace App\Repository\Education\Ai;

use App\Model\Education\Ai\EducationAiRecommendationTask;

final class AiRecommendationRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationAiRecommendationTask
    {
        return EducationAiRecommendationTask::query()->create($data);
    }

    public function markHandled(int $id): void
    {
        EducationAiRecommendationTask::query()->where('id', $id)->update(['status' => 'handled', 'handled_at' => date('Y-m-d H:i:s')]);
    }
}
