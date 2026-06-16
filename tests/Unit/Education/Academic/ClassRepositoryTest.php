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

use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationCourse;
use App\Repository\Education\Academic\ClassRepository;

/**
 * @internal
 * @coversNothing
 */
final class ClassRepositoryTest extends AcademicTestCase
{
    public function testPageFiltersByTenantCampusCourseKeywordStatus(): void
    {
        $tenantA = $this->tenant('tenant_a');
        $tenantB = $this->tenant('tenant_b');
        $campusA = $this->campus($tenantA, 'main_a');
        $campusB = $this->campus($tenantB, 'main_b');
        $courseA = $this->course((int) $tenantA->id, (int) $campusA->id, 'ART-001');
        $courseB = $this->course((int) $tenantB->id, (int) $campusB->id, 'ART-001');

        $visible = $this->classRow((int) $tenantA->id, (int) $campusA->id, (int) $courseA->id, 'C-001', 'Sunday Art', 'enabled');
        $this->classRow((int) $tenantA->id, (int) $campusA->id, (int) $courseA->id, 'C-002', 'Sunday Disabled', 'disabled');
        $this->classRow((int) $tenantB->id, (int) $campusB->id, (int) $courseB->id, 'C-001', 'Sunday Other', 'enabled');

        $result = make(ClassRepository::class)->pageByContext([
            'campus_id' => $campusA->id,
            'course_id' => $courseA->id,
            'keyword' => 'Sunday',
            'status' => 'enabled',
        ], 1, 20, $this->context((int) $tenantA->id));

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
    }

    public function testExistsCodeIgnoresDeletedRowsAndCurrentId(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $course = $this->course((int) $tenant->id, (int) $campus->id);
        $deleted = $this->classRow((int) $tenant->id, (int) $campus->id, (int) $course->id, 'OLD', 'Deleted Class');
        $deleted->delete();
        $active = $this->classRow((int) $tenant->id, (int) $campus->id, (int) $course->id, 'C-001', 'Active Class');

        $repository = make(ClassRepository::class);

        self::assertFalse($repository->existsCode((int) $tenant->id, (int) $campus->id, 'OLD'));
        self::assertTrue($repository->existsCode((int) $tenant->id, (int) $campus->id, 'C-001'));
        self::assertFalse($repository->existsCode((int) $tenant->id, (int) $campus->id, 'C-001', (int) $active->id));
    }

    private function course(int $tenantId, int $campusId, string $code = 'ART-001'): EducationCourse
    {
        return EducationCourse::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'code' => $code,
            'name' => 'Art Basics',
            'status' => 'enabled',
        ]);
    }

    private function classRow(int $tenantId, int $campusId, int $courseId, string $code, string $name, string $status = 'enabled'): EducationClass
    {
        return EducationClass::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'course_id' => $courseId,
            'code' => $code,
            'name' => $name,
            'class_type' => 'group',
            'lesson_units' => '1.00',
            'status' => $status,
        ]);
    }
}
