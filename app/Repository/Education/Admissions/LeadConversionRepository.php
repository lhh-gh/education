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

use App\Model\Education\Admissions\EducationLeadConversionRecord;

final class LeadConversionRepository
{
    public function findByLead(int $tenantId, int $leadId): ?EducationLeadConversionRecord
    {
        $record = EducationLeadConversionRecord::query()
            ->where('tenant_id', $tenantId)
            ->where('lead_id', $leadId)
            ->first();

        return $record instanceof EducationLeadConversionRecord ? $record : null;
    }

    public function create(array $data): EducationLeadConversionRecord
    {
        return EducationLeadConversionRecord::query()->create($data);
    }
}
