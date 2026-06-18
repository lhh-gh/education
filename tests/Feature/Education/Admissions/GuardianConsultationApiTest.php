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
use App\Model\Education\Admissions\EducationLeadStudent;

/**
 * @internal
 * @coversNothing
 */
final class GuardianConsultationApiTest extends AdmissionsApiCase
{
    public function testGuardianConsultationCreatesLead(): void
    {
        $tenant = $this->tenant('adm_mobile_guardian');
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant, 'guardian', $campus);

        $result = $this->post('/mobile/education/admissions/guardian/consultations', [
            'contact_name' => 'Ms Li',
            'contact_mobile' => '13900000000',
            'student_name' => 'Kid Li',
            'student_age' => 8,
            'interested_course' => 'Art',
        ], $this->mobileHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertTrue(EducationLeadStudent::query()->where('tenant_id', $tenant->id)->where('lead_id', $result['data']['id'])->exists());
    }
}
