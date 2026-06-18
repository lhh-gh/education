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

use App\Model\Education\Admissions\EducationLeadAssignment;

final class LeadAssignmentRepository
{
    public function activeForLead(int $tenantId, int $leadId): ?EducationLeadAssignment
    {
        $assignment = EducationLeadAssignment::query()
            ->where('tenant_id', $tenantId)
            ->where('lead_id', $leadId)
            ->where('status', 'active')
            ->lockForUpdate()
            ->first();

        return $assignment instanceof EducationLeadAssignment ? $assignment : null;
    }

    public function create(array $data): EducationLeadAssignment
    {
        return EducationLeadAssignment::query()->create($data);
    }
}
