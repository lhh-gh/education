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

use App\Model\Education\Content\EducationStudentWork;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class StudentWorkRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationStudentWork
    {
        if (isset($data['id'])) {
            $work = EducationStudentWork::query()->where('tenant_id', $data['tenant_id'])->findOrFail($data['id']);
            $work->fill($data);
            $work->save();

            return $work;
        }

        return EducationStudentWork::query()->create($data);
    }

    public function findInTenant(int $tenantId, int $id): EducationStudentWork
    {
        return EducationStudentWork::query()->where('tenant_id', $tenantId)->findOrFail($id);
    }

    public function findInContext(EducationUserContext $context, int $id): EducationStudentWork
    {
        return (new EducationScopeQuery())
            ->applyTenantCampus(EducationStudentWork::query(), [], $context)
            ->findOrFail($id);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampus(EducationStudentWork::query(), $filters, $context);
        foreach (['student_id', 'teacher_id', 'status'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }

        $total = (int) (clone $query)->count();
        $list = $query->orderByDesc('id')
            ->forPage($page, $pageSize)
            ->get()
            ->toArray();

        return ['list' => $list, 'total' => $total];
    }
}
