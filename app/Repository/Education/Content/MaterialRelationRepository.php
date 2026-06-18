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

namespace App\Repository\Education\Content;

use App\Model\Education\Content\EducationLearningMaterialRelation;

final class MaterialRelationRepository
{
    /**
     * @param list<array<string, mixed>> $relations
     */
    public function replaceForMaterial(int $tenantId, ?int $campusId, int $materialId, array $relations): void
    {
        EducationLearningMaterialRelation::query()
            ->where('tenant_id', $tenantId)
            ->where('material_id', $materialId)
            ->delete();

        foreach ($relations as $relation) {
            EducationLearningMaterialRelation::query()->create($relation + [
                'tenant_id' => $tenantId,
                'campus_id' => $campusId,
                'material_id' => $materialId,
            ]);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function pageByTarget(int $tenantId, string $targetType, int $targetId): array
    {
        return EducationLearningMaterialRelation::query()
            ->where('tenant_id', $tenantId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->get()
            ->toArray();
    }
}
