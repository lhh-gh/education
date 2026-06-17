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

use App\Repository\Education\Group\GroupMetricRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class GroupMetricService
{
    public function __construct(
        private readonly GroupMetricRepository $repository,
        private readonly DataPermissionService $dataPermissionService
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{id: int}
     */
    public function save(array $data, EducationUserContext $context): array
    {
        $row = $this->repository->create([
            'tenant_id' => (int) $context->tenantId,
            'campus_id' => isset($data['campus_id']) ? (int) $data['campus_id'] : $context->currentCampusId,
            'metric_date' => (string) $data['metric_date'],
            'org_unit_id' => $data['org_unit_id'] ?? null,
            'campus_count' => (int) ($data['campus_count'] ?? 0),
            'student_count' => (int) ($data['student_count'] ?? 0),
            'revenue_cents' => (int) ($data['revenue_cents'] ?? 0),
            'consumed_credits' => (string) ($data['consumed_credits'] ?? '0.00'),
            'renewal_alert_count' => (int) ($data['renewal_alert_count'] ?? 0),
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return ['id' => (int) $row->id];
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
     * @return array<string, int>
     */
    public function dashboard(array $filters, EducationUserContext $context): array
    {
        $page = $this->page($filters, $context);

        return [
            'row_count' => $page['total'],
            'student_count' => array_sum(array_map(static fn (array $row): int => (int) ($row['student_count'] ?? 0), $page['list'])),
            'revenue_cents' => array_sum(array_map(static fn (array $row): int => (int) ($row['revenue_cents'] ?? 0), $page['list'])),
        ];
    }
}
