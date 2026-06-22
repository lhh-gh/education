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

use App\Model\Education\Content\EducationLearningMaterialVersion;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class MaterialVersionRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationLearningMaterialVersion
    {
        if (isset($data['id'])) {
            $version = EducationLearningMaterialVersion::query()->where('tenant_id', $data['tenant_id'])->findOrFail($data['id']);
            $version->fill($data);
            $version->save();

            return $version;
        }

        return EducationLearningMaterialVersion::query()->create($data);
    }

    public function findInTenant(int $tenantId, int $id): EducationLearningMaterialVersion
    {
        return EducationLearningMaterialVersion::query()->where('tenant_id', $tenantId)->findOrFail($id);
    }

    public function findInContext(EducationUserContext $context, int $id): EducationLearningMaterialVersion
    {
        return (new EducationScopeQuery())
            ->applyTenantCampus(EducationLearningMaterialVersion::query(), [], $context)
            ->findOrFail($id);
    }

    public function nextVersionNo(int $tenantId, int $materialId): int
    {
        return ((int) EducationLearningMaterialVersion::query()
            ->where('tenant_id', $tenantId)
            ->where('material_id', $materialId)
            ->max('version_no')) + 1;
    }

    /**
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(int $materialId, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampus(EducationLearningMaterialVersion::query(), [], $context)
            ->where('material_id', $materialId);

        $total = (int) (clone $query)->count();
        $list = $query->orderByDesc('version_no')
            ->forPage($page, $pageSize)
            ->get()
            ->toArray();

        return [
            'list' => $list,
            'total' => $total,
        ];
    }
}
