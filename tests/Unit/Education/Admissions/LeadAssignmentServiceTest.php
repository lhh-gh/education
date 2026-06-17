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

namespace HyperfTests\Unit\Education\Admissions;

use App\Model\Education\Admissions\EducationLead;
use App\Model\Education\Admissions\EducationLeadAssignment;
use App\Service\Education\Admissions\LeadAssignmentService;

/**
 * @internal
 * @coversNothing
 */
final class LeadAssignmentServiceTest extends AdmissionsTestCase
{
    public function testAssignmentReplacesPreviousActiveAssignment(): void
    {
        $tenant = $this->tenant('adm_assign');
        $campus = $this->campus($tenant);
        $lead = $this->leadFixture($tenant, $campus, ['owner_user_id' => 11]);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 9001);
        $service = make(LeadAssignmentService::class);

        $first = $service->assign((int) $lead->id, 22, 'initial assignment', $context);
        $second = $service->assign((int) $lead->id, 33, 'reassignment', $context);

        self::assertSame('replaced', EducationLeadAssignment::query()->find($first['id'])->status);
        self::assertSame('active', EducationLeadAssignment::query()->find($second['id'])->status);
        self::assertSame(33, (int) EducationLead::query()->find($lead->id)->owner_user_id);
        self::assertSame(1, EducationLeadAssignment::query()->where('tenant_id', $tenant->id)->where('lead_id', $lead->id)->where('status', 'active')->count());
    }
}
