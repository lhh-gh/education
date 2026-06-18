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

namespace App\Repository\Education\Operations;

use App\Model\Education\Operations\EducationMakeupEntitlement;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\EducationUserContext;

final class MakeupEntitlementRepository
{
    public function pageAvailable(array $params, EducationUserContext $context): array
    {
        $query = $this->scopedQuery($params, $context);
        foreach (['campus_id', 'student_id', 'course_id', 'status'] as $field) {
            if (isset($params[$field]) && $params[$field] !== '') {
                $query->where($field, $params[$field]);
            }
        }

        return $this->paginate($query->orderByDesc('id'), (int) ($params['page'] ?? 1), (int) ($params['pageSize'] ?? 20));
    }

    public function lockEntitlement(int $tenantId, int $id): ?EducationMakeupEntitlement
    {
        return EducationMakeupEntitlement::query()->where('tenant_id', $tenantId)->whereKey($id)->lockForUpdate()->first();
    }

    public function createFromLeave(array $data): EducationMakeupEntitlement
    {
        return EducationMakeupEntitlement::query()->firstOrCreate([
            'tenant_id' => $data['tenant_id'],
            'source_leave_request_id' => $data['source_leave_request_id'],
            'student_id' => $data['student_id'],
        ], $data);
    }

    public function markUsed(EducationMakeupEntitlement $entitlement, int $lessonId, string $usedAt): EducationMakeupEntitlement
    {
        $entitlement->update(['status' => 'used', 'used_lesson_id' => $lessonId, 'used_at' => $usedAt]);

        return $entitlement->refresh();
    }

    public function restoreAvailable(EducationMakeupEntitlement $entitlement): EducationMakeupEntitlement
    {
        $entitlement->update(['status' => 'available', 'used_lesson_id' => null, 'used_at' => null]);

        return $entitlement->refresh();
    }

    public function markExpired(EducationMakeupEntitlement $entitlement): EducationMakeupEntitlement
    {
        $entitlement->update(['status' => 'expired']);

        return $entitlement->refresh();
    }

    private function paginate(mixed $query, int $page, int $pageSize): array
    {
        $total = (clone $query)->count();
        $list = $query->forPage(max(1, $page), max(1, min(100, $pageSize)))->get()->map(static fn ($row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }

    private function scopedQuery(array $params, EducationUserContext $context): mixed
    {
        $query = EducationMakeupEntitlement::query();
        if ($context->platformAccess) {
            if (isset($params['tenant_id']) && $params['tenant_id'] !== '') {
                $query->where('tenant_id', (int) $params['tenant_id']);
            }

            return $query;
        }

        if ($context->tenantId === null) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('tenant_id', $context->tenantId);
        if ($context->roleCode !== EducationRoleCode::TenantAdmin) {
            $query->whereIn('campus_id', $context->campusIds ?: [0]);
        }

        return $query;
    }
}
