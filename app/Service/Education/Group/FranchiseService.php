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

namespace App\Service\Education\Group;

use App\Repository\Education\Group\FranchiseRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class FranchiseService
{
    public function __construct(private readonly FranchiseRepository $repository) {}

    /**
     * @param array<string, mixed> $data
     * @return array{id: int, status: string}
     */
    public function save(array $data, EducationUserContext $context): array
    {
        $row = $this->repository->save([
            'tenant_id' => (int) $context->tenantId,
            'campus_id' => isset($data['campus_id']) ? (int) $data['campus_id'] : $context->currentCampusId,
            'franchise_code' => (string) $data['franchise_code'],
            'franchise_name' => (string) $data['franchise_name'],
            'contact_name' => $data['contact_name'] ?? null,
            'contact_mobile' => $data['contact_mobile'] ?? null,
            'region' => $data['region'] ?? null,
            'status' => (string) ($data['status'] ?? 'potential'),
            'signed_contract_id' => $data['signed_contract_id'] ?? null,
            'remark' => $data['remark'] ?? null,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return ['id' => (int) $row->id, 'status' => (string) $row->status];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->repository->page($filters, $context);
    }
}
