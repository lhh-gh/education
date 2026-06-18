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

namespace App\Repository\Education\Payroll;

use App\Model\Education\Payroll\EducationTeacherWorkloadDispute;
use App\Service\Education\Foundation\EducationUserContext;

final class WorkloadDisputeRepository
{
    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        $query = EducationTeacherWorkloadDispute::query();
        if (! $context->platformAccess) {
            $context->tenantId === null ? $query->whereRaw('1 = 0') : $query->where('tenant_id', $context->tenantId);
        }
        foreach (['teacher_id', 'status', 'campus_id', 'source_workload_id'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $total = (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->map(static fn (EducationTeacherWorkloadDispute $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }

    public function lockScoped(int $id, EducationUserContext $context): ?EducationTeacherWorkloadDispute
    {
        $query = EducationTeacherWorkloadDispute::query()->whereKey($id)->lockForUpdate();
        if (! $context->platformAccess) {
            $context->tenantId === null ? $query->whereRaw('1 = 0') : $query->where('tenant_id', $context->tenantId);
        }
        $dispute = $query->first();

        return $dispute instanceof EducationTeacherWorkloadDispute ? $dispute : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationTeacherWorkloadDispute
    {
        return EducationTeacherWorkloadDispute::query()->create($data);
    }
}
