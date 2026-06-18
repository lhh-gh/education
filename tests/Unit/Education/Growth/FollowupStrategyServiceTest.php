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

namespace HyperfTests\Unit\Education\Growth;

use App\Model\Education\Growth\EducationGrowthFollowupSuggestion;
use App\Service\Education\Growth\FollowupStrategyService;

/**
 * @internal
 * @coversNothing
 */
final class FollowupStrategyServiceTest extends GrowthTestCase
{
    public function testStageStrategyCreatesDueSuggestion(): void
    {
        [$tenant, $campus] = $this->tenantCampus('growth_strategy');
        $lead = $this->leadFixture($tenant, $campus, ['owner_user_id' => 202, 'stage' => 'followed']);
        $service = make(FollowupStrategyService::class);
        $service->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'strategy_code' => 'HOT-TRIAL',
            'strategy_name' => 'Hot trial invite',
            'lead_stage' => 'followed',
            'score_level' => 'hot',
            'suggestion_template' => 'Invite trial within 4 hours',
            'next_follow_hours' => 4,
        ]);

        $result = $service->createSuggestionForLead((int) $tenant->id, (int) $campus->id, (int) $lead->id, 202, 'followed', 'hot', '2026-06-10 09:00:00');

        self::assertSame('pending', $result['status']);
        self::assertSame('2026-06-10 13:00:00', $result['due_at']);
        self::assertTrue(EducationGrowthFollowupSuggestion::query()->where('lead_id', $lead->id)->where('owner_user_id', 202)->exists());
    }
}
