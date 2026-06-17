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

use App\Repository\Education\Group\ContractRenewalRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class ContractRenewalService
{
    public function __construct(private readonly ContractRenewalRepository $repository) {}

    /**
     * @param array<string, mixed> $data
     * @return array{renewal_id: int, status: string}
     */
    public function save(array $data, EducationUserContext $context): array
    {
        $row = $this->repository->create([
            'tenant_id' => (int) $context->tenantId,
            'campus_id' => isset($data['campus_id']) ? (int) $data['campus_id'] : $context->currentCampusId,
            'contract_id' => (int) $data['contract_id'],
            'renewal_type' => (string) ($data['renewal_type'] ?? 'renewal'),
            'status' => (string) ($data['status'] ?? 'pending'),
            'due_date' => (string) $data['due_date'],
            'handled_by' => $data['handled_by'] ?? null,
            'handled_at' => $data['handled_at'] ?? null,
            'result' => $data['result'] ?? null,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return ['renewal_id' => (int) $row->id, 'status' => (string) $row->status];
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
