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

use App\Model\Education\Academic\EducationStudent;
use App\Repository\Education\Academic\StudentRepository;

/**
 * @internal
 * @coversNothing
 */
final class StudentRepositoryTest extends AcademicTestCase
{
    public function testStudentNoUniquenessIsTenantScoped(): void
    {
        $tenantA = $this->tenant('tenant_a');
        $tenantB = $this->tenant('tenant_b');
        $campusA = $this->campus($tenantA, 'main_a');
        $campusB = $this->campus($tenantB, 'main_b');

        EducationStudent::query()->create([
            'tenant_id' => $tenantA->id,
            'campus_id' => $campusA->id,
            'student_no' => 'S001',
            'name' => 'Student A',
        ]);
        EducationStudent::query()->create([
            'tenant_id' => $tenantB->id,
            'campus_id' => $campusB->id,
            'student_no' => 'S001',
            'name' => 'Student B',
        ]);

        $repository = make(StudentRepository::class);

        self::assertTrue($repository->existsStudentNo((int) $tenantA->id, 'S001'));
        self::assertFalse($repository->existsStudentNo((int) $tenantA->id, 'S002'));
        self::assertTrue($repository->existsStudentNo((int) $tenantB->id, 'S001'));
    }
}
