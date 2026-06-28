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
use App\Model\Education\Ai\EducationAiSafetyEvent;
use App\Repository\Education\Ai\AiGenerationRepository;
use App\Repository\Education\Ai\AiSafetyRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class AiSafetyService
{
    public function __construct(
        private readonly AiGenerationRepository $generationRepository,
        private readonly AiSafetyRepository $safetyRepository
    ) {}

    /**
     * @param array<string, mixed> $payload
     * @return array{safety_event_id: int}
     */
    public function blockTask(int $taskId, string $eventType, string $summary, array $payload = []): array
    {
        $task = $this->generationRepository->task($taskId);
        $task->status = 'blocked';
        $task->finished_at = Carbon::now();
        $task->error_message = $summary;
        $task->save();
        $event = $this->safetyRepository->create([
            'tenant_id' => (int) $task->tenant_id,
            'campus_id' => $task->campus_id,
            'generation_task_id' => $taskId,
            'risk_level' => 'blocked',
            'event_type' => $eventType,
            'summary' => $summary,
            'payload_json' => $payload,
            'handled' => false,
            'created_by' => $task->requester_user_id,
            'updated_by' => $task->requester_user_id,
        ]);

        return ['safety_event_id' => (int) $event->id];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(EducationUserContext $context, array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampus(EducationAiSafetyEvent::query(), $filters, $context);
        if (($filters['risk_level'] ?? '') !== '') {
            $query->where('risk_level', $filters['risk_level']);
        }
        $total = (int) (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * @return array{safety_event_id: int, handled: bool}
     */
    public function markHandled(int $id, EducationUserContext $context): array
    {
        $event = (new EducationScopeQuery())->applyTenantCampus(
            EducationAiSafetyEvent::query()->whereKey($id),
            [],
            $context
        )->first();
        if (! $event instanceof EducationAiSafetyEvent) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'ai safety event not found in current context', ['safety_event_id' => $id]);
        }

        $event->fill(['handled' => true, 'updated_by' => $context->userId]);
        $event->save();

        return ['safety_event_id' => $id, 'handled' => true];
    }
}
