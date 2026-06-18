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

namespace App\Repository\Education\Standards;

use App\Model\Education\Standards\EducationCourseStageGoal;
use App\Model\Education\Standards\EducationCourseStageGoalAbilityRelation;

final class StageGoalRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationCourseStageGoal
    {
        if (isset($data['id'])) {
            $goal = EducationCourseStageGoal::query()->where('tenant_id', $data['tenant_id'])->findOrFail($data['id']);
            $goal->fill($data);
            $goal->save();

            return $goal;
        }

        return EducationCourseStageGoal::query()->create($data);
    }

    /**
     * @param list<int> $abilityPointIds
     */
    public function syncAbilityPoints(int $tenantId, int $campusId, int $stageGoalId, array $abilityPointIds): void
    {
        EducationCourseStageGoalAbilityRelation::query()
            ->where('tenant_id', $tenantId)
            ->where('stage_goal_id', $stageGoalId)
            ->delete();

        foreach (array_values(array_unique($abilityPointIds)) as $abilityPointId) {
            EducationCourseStageGoalAbilityRelation::query()->create([
                'tenant_id' => $tenantId,
                'campus_id' => $campusId,
                'stage_goal_id' => $stageGoalId,
                'ability_point_id' => $abilityPointId,
                'weight' => '1.0000',
            ]);
        }
    }
}
