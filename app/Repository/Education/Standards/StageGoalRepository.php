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
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class StageGoalRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data, ?EducationUserContext $context = null): EducationCourseStageGoal
    {
        if (isset($data['id'])) {
            $goal = $context === null
                ? EducationCourseStageGoal::query()->where('tenant_id', $data['tenant_id'])->findOrFail($data['id'])
                : $this->findInContext($context, (int) $data['id']);
            if ($context !== null) {
                $data = array_merge($data, [
                    'tenant_id' => (int) $goal->tenant_id,
                    'campus_id' => $goal->campus_id === null ? null : (int) $goal->campus_id,
                ]);
            }
            $goal->fill($data);
            $goal->save();

            return $goal;
        }

        if ($context !== null) {
            $data = array_merge($data, [
                'tenant_id' => $this->tenantId($context, $data),
                'campus_id' => $context->currentCampusId,
            ]);
        }

        return EducationCourseStageGoal::query()->create($data);
    }

    public function findInContext(EducationUserContext $context, int $id): EducationCourseStageGoal
    {
        return (new EducationScopeQuery())
            ->applyTenantCampus(EducationCourseStageGoal::query(), [], $context)
            ->findOrFail($id);
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

    /**
     * @param array<string, mixed> $data
     */
    private function tenantId(EducationUserContext $context, array $data): int
    {
        if ($context->tenantId !== null) {
            return $context->tenantId;
        }
        if (isset($data['tenant_id']) && $data['tenant_id'] !== '') {
            return (int) $data['tenant_id'];
        }

        throw new \RuntimeException('education tenant context is missing', 403);
    }
}
