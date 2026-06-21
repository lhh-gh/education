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
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

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
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampus(EducationLearningMaterialRelation::query(), $filters, $context);
        foreach (['material_id', 'target_id'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, (int) $filters[$field]);
            }
        }
        if (isset($filters['target_type']) && $filters['target_type'] !== '') {
            $query->where('target_type', (string) $filters['target_type']);
        }

        $total = (int) (clone $query)->count();
        $list = $query->orderByDesc('id')
            ->forPage($page, $pageSize)
            ->get()
            ->toArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function pageByTarget(EducationUserContext $context, string $targetType, int $targetId): array
    {
        return (new EducationScopeQuery())->applyTenantCampus(EducationLearningMaterialRelation::query(), [], $context)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->get()
            ->toArray();
    }
}
