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

use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\GuardianRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class GuardianRepositoryTest extends AcademicTestCase
{
    public function testPlatformContextCampusFiltersPageThroughStudentRelationsWithoutLocalFilters(): void
    {
        $tenant = $this->tenant('guardian_platform_scope');
        $campusA = $this->campus($tenant, 'main_a');
        $campusB = $this->campus($tenant, 'main_b');
        $studentA = EducationStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campusA->id,
            'student_no' => 'S-GR-A',
            'name' => 'Student A',
        ]);
        $studentB = EducationStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campusB->id,
            'student_no' => 'S-GR-B',
            'name' => 'Student B',
        ]);
        $visible = EducationGuardian::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Guardian A',
            'mobile' => '13800001001',
        ]);
        $hidden = EducationGuardian::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Guardian B',
            'mobile' => '13800001002',
        ]);
        EducationStudentGuardian::query()->create([
            'tenant_id' => $tenant->id,
            'student_id' => $studentA->id,
            'guardian_id' => $visible->id,
            'relation' => 'mother',
        ]);
        EducationStudentGuardian::query()->create([
            'tenant_id' => $tenant->id,
            'student_id' => $studentB->id,
            'guardian_id' => $hidden->id,
            'relation' => 'father',
        ]);

        $result = make(GuardianRepository::class)->pageByContext([], 1, 20, new EducationUserContext(
            userId: 1,
            tenantId: (int) $tenant->id,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: (int) $campusA->id
        ));

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
    }

    public function testMobileUniquenessIsTenantScoped(): void
    {
        $tenantA = $this->tenant('tenant_a');
        $tenantB = $this->tenant('tenant_b');

        EducationGuardian::query()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Guardian A',
            'mobile' => '13800000001',
        ]);
        EducationGuardian::query()->create([
            'tenant_id' => $tenantB->id,
            'name' => 'Guardian B',
            'mobile' => '13800000001',
        ]);

        $repository = make(GuardianRepository::class);

        self::assertTrue($repository->existsMobile((int) $tenantA->id, '13800000001'));
        self::assertFalse($repository->existsMobile((int) $tenantA->id, '13800000002'));
        self::assertTrue($repository->existsMobile((int) $tenantB->id, '13800000001'));
    }
}
