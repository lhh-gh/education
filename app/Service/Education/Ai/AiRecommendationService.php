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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Ai\EducationAiRecommendationTask;
use App\Repository\Education\Ai\AiRecommendationRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

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

    /**
     * @return array{recommendation_task_id: int, status: string}
     */
    public function markHandled(int $id, EducationUserContext $context): array
    {
        $task = $this->repository->markHandled($id, $context);
        if ($task === null) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'ai recommendation task not found in current context', ['recommendation_task_id' => $id]);
        }

        return ['recommendation_task_id' => $id, 'status' => 'handled'];
    }

    /**
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampus(EducationAiRecommendationTask::query(), [], $context);
        $total = (int) (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray();

        return ['list' => $list, 'total' => $total];
    }
}
