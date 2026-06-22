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

use App\Model\Education\Content\EducationLearningMaterial;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class LearningMaterialRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data, ?EducationUserContext $context = null): EducationLearningMaterial
    {
        if (isset($data['id'])) {
            $material = $context === null
                ? EducationLearningMaterial::query()->where('tenant_id', $data['tenant_id'])->findOrFail($data['id'])
                : $this->findInContext($context, (int) $data['id']);
            if ($context !== null) {
                $data = array_merge($data, [
                    'tenant_id' => (int) $material->tenant_id,
                    'campus_id' => $material->campus_id === null ? null : (int) $material->campus_id,
                ]);
            }
            $material->fill($data);
            $material->save();

            return $material;
        }

        if ($context !== null) {
            $data = array_merge($data, [
                'tenant_id' => $this->tenantId($context, $data),
                'campus_id' => $context->currentCampusId,
            ]);
        }

        return EducationLearningMaterial::query()->create($data);
    }

    public function findInTenant(int $tenantId, int $id): EducationLearningMaterial
    {
        return EducationLearningMaterial::query()->where('tenant_id', $tenantId)->findOrFail($id);
    }

    public function findInContext(EducationUserContext $context, int $id): EducationLearningMaterial
    {
        return (new EducationScopeQuery())
            ->applyTenantCampus(EducationLearningMaterial::query(), [], $context)
            ->findOrFail($id);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampus(EducationLearningMaterial::query(), $filters, $context);
        foreach (['course_id', 'status', 'material_type'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }
        if (! empty($filters['keyword'])) {
            $query->where('material_name', 'like', '%' . $filters['keyword'] . '%');
        }

        $total = (int) (clone $query)->count();
        $list = $query->orderByDesc('id')
            ->forPage($page, $pageSize)
            ->get()
            ->map(static fn (EducationLearningMaterial $row): array => $row->toArray())
            ->all();

        return ['list' => $list, 'total' => $total];
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
