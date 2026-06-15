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

namespace HyperfTests\Unit\Education\Academic;

use App\Model\Education\Academic\EducationClassroom;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\ClassroomRepository;

/**
 * @internal
 * @coversNothing
 */
final class ClassroomRepositoryTest extends AcademicTestCase
{
    public function testPageFiltersByTenantAndCampusScope(): void
    {
        $tenantA = $this->tenant('tenant_a');
        $tenantB = $this->tenant('tenant_b');
        $allowedCampus = $this->campus($tenantA, 'allowed');
        $blockedCampus = $this->campus($tenantA, 'blocked');
        $otherTenantCampus = $this->campus($tenantB, 'other');

        $visible = EducationClassroom::query()->create([
            'tenant_id' => $tenantA->id,
            'campus_id' => $allowedCampus->id,
            'code' => 'A101',
            'name' => 'Visible Room',
        ]);
        EducationClassroom::query()->create([
            'tenant_id' => $tenantA->id,
            'campus_id' => $blockedCampus->id,
            'code' => 'A102',
            'name' => 'Blocked Room',
        ]);
        EducationClassroom::query()->create([
            'tenant_id' => $tenantB->id,
            'campus_id' => $otherTenantCampus->id,
            'code' => 'B101',
            'name' => 'Other Tenant Room',
        ]);

        $result = make(ClassroomRepository::class)->pageByContext(
            ['keyword' => 'Room', 'tenant_id' => $tenantB->id],
            1,
            10,
            $this->context((int) $tenantA->id, EducationRoleCode::AcademicStaff, [(int) $allowedCampus->id])
        );

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
    }
}
