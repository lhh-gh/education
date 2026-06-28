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
use App\Repository\Education\Academic\StudentGuardianRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class StudentGuardianRepositoryTest extends AcademicTestCase
{
    public function testPlatformContextCampusFiltersListByStudentThroughStudentRecord(): void
    {
        $tenant = $this->tenant('student_guardian_platform_scope');
        $campusA = $this->campus($tenant, 'main_a');
        $campusB = $this->campus($tenant, 'main_b');
        $student = EducationStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campusB->id,
            'student_no' => 'S-SG-B',
            'name' => 'Student B',
        ]);
        $guardian = EducationGuardian::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Guardian B',
            'mobile' => '13800002001',
        ]);
        EducationStudentGuardian::query()->create([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'relation' => 'father',
        ]);

        $relations = make(StudentGuardianRepository::class)->listByStudent((int) $student->id, new EducationUserContext(
            userId: 1,
            tenantId: (int) $tenant->id,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: (int) $campusA->id
        ));

        self::assertSame([], $relations);
    }

    public function testListByStudentReturnsOnlySameTenantRelations(): void
    {
        $tenantA = $this->tenant('tenant_a');
        $tenantB = $this->tenant('tenant_b');
        $campusA = $this->campus($tenantA, 'main_a');
        $student = EducationStudent::query()->create([
            'tenant_id' => $tenantA->id,
            'campus_id' => $campusA->id,
            'student_no' => 'S001',
            'name' => 'Student A',
        ]);
        $guardianA = EducationGuardian::query()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Guardian A',
            'mobile' => '13800000001',
        ]);
        $guardianB = EducationGuardian::query()->create([
            'tenant_id' => $tenantB->id,
            'name' => 'Guardian B',
            'mobile' => '13800000002',
        ]);

        EducationStudentGuardian::query()->create([
            'tenant_id' => $tenantA->id,
            'student_id' => $student->id,
            'guardian_id' => $guardianA->id,
            'relation' => 'mother',
        ]);
        EducationStudentGuardian::query()->create([
            'tenant_id' => $tenantB->id,
            'student_id' => $student->id,
            'guardian_id' => $guardianB->id,
            'relation' => 'guardian',
        ]);

        $relations = make(StudentGuardianRepository::class)->listByStudent(
            (int) $student->id,
            $this->context((int) $tenantA->id)
        );

        self::assertCount(1, $relations);
        self::assertSame((int) $guardianA->id, (int) $relations[0]['guardian_id']);
    }
}
