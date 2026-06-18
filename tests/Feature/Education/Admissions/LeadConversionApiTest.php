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
use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Admissions\EducationLead;
use App\Model\Education\Admissions\EducationLeadConversionRecord;

/**
 * @internal
 * @coversNothing
 */
final class LeadConversionApiTest extends AdmissionsApiCase
{
    public function testLeadConversionApiCreatesV1Records(): void
    {
        $this->grantPermissions('education:admissions:lead:convert');
        $tenant = $this->tenant('adm_api_convert');
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant, campus: $campus);
        $lead = $this->admissionsLead($tenant, $campus);
        $package = $this->admissionsPackage($tenant, $campus);

        $result = $this->post('/admin/education/admissions/leads/' . $lead->id . '/convert', [
            'student_name' => 'Official Student',
            'guardian_name' => 'Official Guardian',
            'lesson_package_id' => $package->id,
            'paid_amount' => '3000.00',
            'enrolled_at' => '2026-06-13',
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertTrue(EducationEnrollment::query()->whereKey($result['data']['enrollment_id'])->exists());
        self::assertTrue(EducationLeadConversionRecord::query()->where('tenant_id', $tenant->id)->where('lead_id', $lead->id)->exists());
        self::assertSame('converted', EducationLead::query()->find($lead->id)->status);
    }
}
