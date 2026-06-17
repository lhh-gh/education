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

namespace App\Repository\Education\Admissions;

use App\Model\Education\Admissions\EducationLeadFollowRecord;

final class FollowRecordRepository
{
    public function create(array $data): EducationLeadFollowRecord
    {
        return EducationLeadFollowRecord::query()->create($data);
    }

    public function listByLead(int $tenantId, int $leadId): array
    {
        return EducationLeadFollowRecord::query()
            ->where('tenant_id', $tenantId)
            ->where('lead_id', $leadId)
            ->orderByDesc('id')
            ->get()
            ->map(static fn ($row): array => $row->toArray())
            ->all();
    }
}
