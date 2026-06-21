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

use App\Model\Education\Group\EducationContract;
use App\Model\Education\Group\EducationContractAttachment;
use App\Model\Education\Group\EducationContractParty;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class ContractRepository
{
    public function find(int $id, EducationUserContext $context): ?EducationContract
    {
        $row = (new EducationScopeQuery())->applyTenantCampus(EducationContract::query()->whereKey($id), [], $context)->first();

        return $row instanceof EducationContract ? $row : null;
    }

    public function findByNo(int $tenantId, string $contractNo): ?EducationContract
    {
        $row = EducationContract::query()->where('tenant_id', $tenantId)->where('contract_no', $contractNo)->first();

        return $row instanceof EducationContract ? $row : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationContract
    {
        return EducationContract::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(EducationContract $contract, array $data): EducationContract
    {
        $contract->fill($data);
        $contract->save();

        return $contract;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createParty(array $data): EducationContractParty
    {
        return EducationContractParty::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createAttachment(array $data): EducationContractAttachment
    {
        return EducationContractAttachment::query()->create($data);
    }

    /**
     * @param array<string, mixed> $filters
     * @param int[] $campusIds
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context, array $campusIds): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampus(EducationContract::query(), $filters, $context);
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', $filters['status']);
        }
        $total = (clone $query)->count();
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->map(static fn (EducationContract $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}
