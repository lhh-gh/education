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

use App\Model\Education\Ai\EducationAiRecommendationTask;
use App\Repository\Education\Ai\AiRecommendationRepository;

final class AiRecommendationService
{
    public function __construct(private readonly AiRecommendationRepository $repository) {}

    /**
     * @param array<string, mixed> $data
     * @return array{recommendation_task_id: int, status: string}
     */
    public function create(array $data): array
    {
        $task = $this->repository->create($data + ['status' => $data['status'] ?? 'pending']);

        return ['recommendation_task_id' => (int) $task->id, 'status' => (string) $task->status];
    }

    public function markHandled(int $id): void
    {
        $this->repository->markHandled($id);
    }

    /**
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(int $tenantId, int $page = 1, int $pageSize = 20): array
    {
        $query = EducationAiRecommendationTask::query()->where('tenant_id', $tenantId);
        $total = (int) $query->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray();

        return ['list' => $list, 'total' => $total];
    }
}
