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

namespace App\Repository\Education\Group;

use App\Model\Education\Group\EducationGroupOperationMetric;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class GroupMetricRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationGroupOperationMetric
    {
        return EducationGroupOperationMetric::query()->create($data);
    }

    /**
     * @param array<string, mixed> $filters
     * @param int[] $campusIds
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context, array $campusIds): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampus(EducationGroupOperationMetric::query(), $filters, $context);
        if (($filters['start_date'] ?? '') !== '') {
            $query->where('metric_date', '>=', $filters['start_date']);
        }
        if (($filters['end_date'] ?? '') !== '') {
            $query->where('metric_date', '<=', $filters['end_date']);
        }
        $total = (clone $query)->count();
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $list = $query->orderByDesc('metric_date')->forPage($page, $pageSize)->get()->map(static fn (EducationGroupOperationMetric $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}
