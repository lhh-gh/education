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
    public function save(array $data): EducationCourseMaterial
    {
        return EducationCourseMaterial::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'material_code' => $data['material_code'],
        ], $data + ['status' => 'draft']);
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
}
