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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Service\Education\Admissions\LeadService;

/**
 * @internal
 * @coversNothing
 */
final class LeadServiceTest extends AdmissionsTestCase
{
    public function testDuplicateMobileIsTenantScoped(): void
    {
        $tenantA = $this->tenant('adm_tenant_a');
        $campusA = $this->campus($tenantA);
        $tenantB = $this->tenant('adm_tenant_b');
        $campusB = $this->campus($tenantB);
        $service = make(LeadService::class);
        $mobile = '13800000000';

        $lead = $service->create([
            'campus_id' => (int) $campusA->id,
            'contact_name' => 'Ms Wang',
            'contact_mobile' => $mobile,
            'lead_students' => [['name' => 'Kid A']],
        ], $this->context((int) $tenantA->id, campusIds: [(int) $campusA->id]));

        try {
            $service->create([
                'campus_id' => (int) $campusA->id,
                'contact_name' => 'Duplicate',
                'contact_mobile' => $mobile,
                'lead_students' => [['name' => 'Kid Duplicate']],
            ], $this->context((int) $tenantA->id, campusIds: [(int) $campusA->id]));
            self::fail('Expected duplicate mobile conflict.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
            self::assertSame((int) $lead['id'], $exception->getResponse()->data['lead_id']);
        }

        $otherTenantLead = $service->create([
            'campus_id' => (int) $campusB->id,
            'contact_name' => 'Ms Wang',
            'contact_mobile' => $mobile,
            'lead_students' => [['name' => 'Kid B']],
        ], $this->context((int) $tenantB->id, campusIds: [(int) $campusB->id]));

        self::assertNotSame((int) $lead['id'], (int) $otherTenantLead['id']);
    }
}
