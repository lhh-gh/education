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

namespace HyperfTests\Feature\Education\Admissions;

use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationAuditLog;

/**
 * @internal
 * @coversNothing
 */
final class AdmissionsPermissionIsolationAuditTest extends AdmissionsApiCase
{
    public function testAssignmentRequiresPermissionAndWritesAudit(): void
    {
        $tenant = $this->tenant('adm_perm_audit');
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant, campus: $campus);
        $lead = $this->admissionsLead($tenant, $campus);

        $denied = $this->post('/admin/education/admissions/leads/' . $lead->id . '/assign', ['to_user_id' => 66, 'reason' => 'assign'], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::FORBIDDEN->value, $denied['code']);

        $this->grantPermissions('education:admissions:lead:assign');
        $allowed = $this->post('/admin/education/admissions/leads/' . $lead->id . '/assign', ['to_user_id' => 66, 'reason' => 'assign'], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $allowed['code']);
        self::assertTrue(EducationAuditLog::query()->where('tenant_id', $tenant->id)->where('action', 'education.admissions.lead.assigned')->exists());
    }
}
