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

use App\Repository\Education\Ai\AiGenerationRepository;
use App\Repository\Education\Ai\AiSafetyRepository;
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
}
