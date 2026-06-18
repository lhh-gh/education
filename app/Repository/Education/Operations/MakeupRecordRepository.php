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

    public function pageByStudent(array $params, int $tenantId): array
    {
        $query = EducationMakeupRecord::query()->where('tenant_id', $tenantId);
        foreach (['campus_id', 'student_id', 'status'] as $field) {
            if (isset($params[$field]) && $params[$field] !== '') {
                $query->where($field, $params[$field]);
            }
        }
        $total = (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage((int) ($params['page'] ?? 1), (int) ($params['pageSize'] ?? 20))->get()->map(static fn ($row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}
