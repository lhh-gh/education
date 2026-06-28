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

use App\Model\Education\Group\EducationFranchiseRecord;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class FranchiseRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationFranchiseRecord
    {
        return EducationFranchiseRecord::query()->updateOrCreate(
            ['tenant_id' => $data['tenant_id'], 'franchise_code' => $data['franchise_code']],
            $data
        );
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampus(EducationFranchiseRecord::query(), $filters, $context);
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', $filters['status']);
        }
        $total = (clone $query)->count();
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->map(static fn (EducationFranchiseRecord $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}
