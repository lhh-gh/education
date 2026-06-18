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

use App\Model\Education\Group\EducationRiskAuditEvent;
use App\Service\Education\Foundation\EducationUserContext;

final class RiskAuditRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationRiskAuditEvent
    {
        return EducationRiskAuditEvent::query()->create($data);
    }

    /**
     * @param array<string, mixed> $filters
     * @param int[] $campusIds
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context, array $campusIds): array
    {
        $query = EducationRiskAuditEvent::query()->where('tenant_id', $context->tenantId);
        if ($campusIds !== []) {
            $query->whereIn('campus_id', $campusIds);
        }
        if (($filters['risk_level'] ?? '') !== '') {
            $query->where('risk_level', $filters['risk_level']);
        }
        if (isset($filters['handled']) && $filters['handled'] !== '') {
            $query->where('handled', (bool) $filters['handled']);
        }
        $total = (clone $query)->count();
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->map(static fn (EducationRiskAuditEvent $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }

    public function markHandled(int $id, EducationUserContext $context): ?EducationRiskAuditEvent
    {
        $row = EducationRiskAuditEvent::query()->where('tenant_id', $context->tenantId)->whereKey($id)->first();
        if (! $row instanceof EducationRiskAuditEvent) {
            return null;
        }
        $row->fill(['handled' => true, 'handled_by' => $context->userId, 'handled_at' => date('Y-m-d H:i:s')]);
        $row->save();

        return $row;
    }
}
