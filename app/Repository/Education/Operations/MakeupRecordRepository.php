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

use App\Model\Education\Operations\EducationMakeupRecord;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\EducationUserContext;

final class MakeupRecordRepository
{
    public function create(array $data): EducationMakeupRecord
    {
        return EducationMakeupRecord::query()->create($data);
    }

    public function lockRecord(int $tenantId, int $id): ?EducationMakeupRecord
    {
        return EducationMakeupRecord::query()->where('tenant_id', $tenantId)->whereKey($id)->lockForUpdate()->first();
    }

    public function pageByStudent(array $params, EducationUserContext $context): array
    {
        $query = $this->scopedQuery($params, $context);
        foreach (['campus_id', 'student_id', 'status'] as $field) {
            if (isset($params[$field]) && $params[$field] !== '') {
                $query->where($field, $params[$field]);
            }
        }
        $total = (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage((int) ($params['page'] ?? 1), (int) ($params['pageSize'] ?? 20))->get()->map(static fn ($row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }

    private function scopedQuery(array $params, EducationUserContext $context): mixed
    {
        $query = EducationMakeupRecord::query();
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
