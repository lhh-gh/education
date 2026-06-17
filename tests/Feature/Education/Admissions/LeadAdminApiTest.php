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

/**
 * @internal
 * @coversNothing
 */
final class LeadAdminApiTest extends AdmissionsApiCase
{
    public function testLeadApiValidationAndBusinessFailuresMatchCatalog(): void
    {
        $this->grantPermissions('education:admissions:lead:create');
        $tenant = $this->tenant('adm_api_lead');
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant, campus: $campus);

        $validation = $this->post('/admin/education/admissions/leads', ['campus_id' => $campus->id, 'contact_name' => 'Missing Mobile'], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $validation['code']);

        $created = $this->post('/admin/education/admissions/leads', [
            'campus_id' => $campus->id,
            'contact_name' => 'Ms Wang',
            'contact_mobile' => '13800000000',
            'lead_students' => [['name' => 'Kid']],
        ], $this->tenantHeaders($tenant));
        $duplicate = $this->post('/admin/education/admissions/leads', [
            'campus_id' => $campus->id,
            'contact_name' => 'Ms Wang',
            'contact_mobile' => '13800000000',
            'lead_students' => [['name' => 'Kid']],
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $created['code']);
        self::assertSame(ResultCode::CONFLICT->value, $duplicate['code']);
        self::assertSame($created['data']['id'], $duplicate['data']['lead_id']);
    }
}
