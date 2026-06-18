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

namespace HyperfTests\Unit\Education\Standards;

use App\Model\Education\Standards\EducationCourseAbilityPoint;
use App\Model\Education\Standards\EducationCourseStageGoalAbilityRelation;
use App\Service\Education\Standards\ServicePackageService;
use App\Service\Education\Standards\StageGoalService;

/**
 * @internal
 * @coversNothing
 */
final class StageGoalServiceTest extends StandardsTestCase
{
    public function testAbilityPointsAttachToStageGoal(): void
    {
        [$tenant, $campus] = $this->tenantCampus('standards_goal');
        $package = make(ServicePackageService::class)->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'package_code' => 'ART-GOAL',
            'package_name' => 'Art Goal',
            'course_id' => 302,
        ]);
        $line = EducationCourseAbilityPoint::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'ability_code' => 'LINE',
            'ability_name' => 'Line Control',
            'ability_group' => 'drawing',
        ]);
        $color = EducationCourseAbilityPoint::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'ability_code' => 'COLOR',
            'ability_name' => 'Color Sense',
            'ability_group' => 'drawing',
        ]);

        $goal = make(StageGoalService::class)->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'service_package_id' => $package['service_package_id'],
            'goal_code' => 'S1',
            'goal_name' => 'Line basics',
            'goal_content' => 'control line',
            'ability_point_ids' => [$line->id, $color->id],
        ]);

        $attached = EducationCourseStageGoalAbilityRelation::query()
            ->where('stage_goal_id', $goal['stage_goal_id'])
            ->pluck('ability_point_id')
            ->sort()
            ->values()
            ->all();
        self::assertSame([(int) $line->id, (int) $color->id], $attached);
    }
}
