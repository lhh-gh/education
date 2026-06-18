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

use App\Model\Education\Admissions\EducationLeadFollowRecord;
use App\Model\Education\Admissions\EducationTrialLesson;
use App\Model\Education\Growth\EducationGrowthLeadScore;
use App\Model\Education\Growth\EducationGrowthLeadScoreFactor;
use App\Service\Education\Growth\LeadScoreService;

/**
 * @internal
 * @coversNothing
 */
final class LeadScoreServiceTest extends GrowthTestCase
{
    public function testScoreUpdatesAfterFollowAndTrialEvents(): void
    {
        [$tenant, $campus] = $this->tenantCampus('growth_score');
        $lead = $this->leadFixture($tenant, $campus, [
            'owner_user_id' => 201,
            'stage' => 'followed',
            'intention_level' => 'high',
        ]);
        EducationLeadFollowRecord::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lead_id' => $lead->id,
            'follow_type' => 'phone',
            'content' => 'Interested in trial',
            'result' => 'interested',
            'operator_user_id' => 201,
            'created_by' => 201,
            'updated_by' => 201,
        ]);
        EducationTrialLesson::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lead_id' => $lead->id,
            'lead_student_id' => 1,
            'course_id' => 1,
            'teacher_id' => 301,
            'start_time' => '2026-06-10 09:00:00',
            'end_time' => '2026-06-10 10:00:00',
            'status' => 'completed',
            'consultant_user_id' => 201,
            'created_by' => 201,
            'updated_by' => 201,
        ]);

        $result = make(LeadScoreService::class)->recalculate((int) $tenant->id, (int) $campus->id, (int) $lead->id, '2026-06-10');

        self::assertSame(['lead_id' => (int) $lead->id, 'score' => 75, 'score_level' => 'hot'], $result);
        self::assertTrue(EducationGrowthLeadScore::query()->where('lead_id', $lead->id)->where('score_level', 'hot')->exists());
        self::assertSame(3, EducationGrowthLeadScoreFactor::query()->where('tenant_id', $tenant->id)->count());
    }
}
