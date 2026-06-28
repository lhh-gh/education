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

use App\Model\Education\Standards\EducationCourseMaterial;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class CourseMaterialRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data, ?EducationUserContext $context = null): EducationCourseMaterial
    {
        $material = EducationCourseMaterial::query()
            ->where('tenant_id', $data['tenant_id'])
            ->where('material_code', $data['material_code'])
            ->first();

        if ($material !== null) {
            if ($context !== null) {
                $material = (new EducationScopeQuery())
                    ->applyTenantCampus(EducationCourseMaterial::query(), [], $context)
                    ->findOrFail((int) $material->id);
                $data = array_merge($data, [
                    'tenant_id' => (int) $material->tenant_id,
                    'campus_id' => $material->campus_id === null ? null : (int) $material->campus_id,
                ]);
            }
            $material->fill($data + ['status' => 'draft']);
            $material->save();

            return $material;
        }

        if ($context !== null) {
            $data = array_merge($data, [
                'tenant_id' => $this->tenantId($context, $data),
                'campus_id' => $context->currentCampusId,
            ]);
        }

        return EducationCourseMaterial::query()->create($data + ['status' => 'draft']);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampus(EducationCourseMaterial::query(), $filters, $context);
        if (isset($filters['course_id']) && $filters['course_id'] !== '') {
            $query->where('course_id', (int) $filters['course_id']);
        }
        if (($filters['material_type'] ?? '') !== '') {
            $query->where('material_type', (string) $filters['material_type']);
        }
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', (string) $filters['status']);
        }

        $total = (int) (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray();

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
