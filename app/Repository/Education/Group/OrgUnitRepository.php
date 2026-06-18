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

use App\Model\Education\Group\EducationCampusOrgRelation;
use App\Model\Education\Group\EducationOrgUnit;
use App\Service\Education\Foundation\EducationUserContext;

final class OrgUnitRepository
{
    public function find(int $id, EducationUserContext $context): ?EducationOrgUnit
    {
        $row = EducationOrgUnit::query()->where('tenant_id', $context->tenantId)->whereKey($id)->first();

        return $row instanceof EducationOrgUnit ? $row : null;
    }

    public function findByCode(int $tenantId, string $code): ?EducationOrgUnit
    {
        $row = EducationOrgUnit::query()->where('tenant_id', $tenantId)->where('code', $code)->first();

        return $row instanceof EducationOrgUnit ? $row : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationOrgUnit
    {
        return EducationOrgUnit::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(EducationOrgUnit $row, array $data): EducationOrgUnit
    {
        $row->fill($data);
        $row->save();

        return $row;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function tree(EducationUserContext $context): array
    {
        return EducationOrgUnit::query()
            ->where('tenant_id', $context->tenantId)
            ->orderBy('level')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(static fn (EducationOrgUnit $row): array => $row->toArray())
            ->all();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function bindCampus(array $data): EducationCampusOrgRelation
    {
        return EducationCampusOrgRelation::query()->updateOrCreate(
            [
                'tenant_id' => $data['tenant_id'],
                'org_unit_id' => $data['org_unit_id'],
                'campus_id' => $data['campus_id'],
            ],
            $data
        );
    }
}
