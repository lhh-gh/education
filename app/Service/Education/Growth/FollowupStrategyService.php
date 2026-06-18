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

use App\Repository\Education\Growth\FollowupStrategyRepository;
use Carbon\Carbon;

final class FollowupStrategyService
{
    public function __construct(private readonly FollowupStrategyRepository $strategies) {}

    /**
     * @param array<string, mixed> $data
     * @return array{strategy_id: int, status: string}
     */
    public function save(array $data): array
    {
        $strategy = $this->strategies->save($data + ['status' => 'enabled']);

        return ['strategy_id' => (int) $strategy->id, 'status' => (string) $strategy->status];
    }

    /**
     * @return array{suggestion_id: int, status: string, due_at: string}
     */
    public function createSuggestionForLead(int $tenantId, int $campusId, int $leadId, int $ownerUserId, string $leadStage, string $scoreLevel, string $now): array
    {
        $strategy = $this->strategies->match($tenantId, $leadStage, $scoreLevel);
        $nextFollowHours = $strategy === null ? 24 : (int) $strategy->next_follow_hours;
        $suggestionText = $strategy === null ? 'Follow up this lead' : (string) $strategy->suggestion_template;
        $dueAt = Carbon::parse($now)->addHours($nextFollowHours)->format('Y-m-d H:i:s');
        $suggestion = $this->strategies->createSuggestion([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'lead_id' => $leadId,
            'strategy_id' => $strategy?->id,
            'owner_user_id' => $ownerUserId,
            'suggestion_text' => $suggestionText,
            'status' => 'pending',
            'due_at' => $dueAt,
        ]);

        return ['suggestion_id' => (int) $suggestion->id, 'status' => 'pending', 'due_at' => $dueAt];
    }
}
