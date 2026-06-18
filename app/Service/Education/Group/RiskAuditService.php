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

use App\Repository\Education\Group\RiskAuditRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class RiskAuditService
{
    public function __construct(
        private readonly RiskAuditRepository $repository,
        private readonly DataPermissionService $dataPermissionService
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{id: int, risk_level: string}
     */
    public function record(array $data, EducationUserContext $context): array
    {
        $row = $this->repository->create([
            'tenant_id' => (int) $context->tenantId,
            'campus_id' => isset($data['campus_id']) ? (int) $data['campus_id'] : $context->currentCampusId,
            'event_type' => (string) ($data['event_type'] ?? ''),
            'risk_level' => (string) ($data['risk_level'] ?? 'normal'),
            'business_type' => (string) ($data['business_type'] ?? ''),
            'business_id' => isset($data['business_id']) ? (int) $data['business_id'] : null,
            'operator_id' => $data['operator_id'] ?? $context->userId,
            'summary' => (string) ($data['summary'] ?? ''),
            'payload_json' => \is_array($data['payload_json'] ?? null) ? $data['payload_json'] : [],
            'handled' => (bool) ($data['handled'] ?? false),
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return ['id' => (int) $row->id, 'risk_level' => (string) $row->risk_level];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->repository->page($filters, $context, $this->dataPermissionService->allowedCampusIds($context));
    }

    /**
     * @return null|array<string, mixed>
     */
    public function markHandled(int $id, EducationUserContext $context): ?array
    {
        return $this->repository->markHandled($id, $context)?->toArray();
    }
}
