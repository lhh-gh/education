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

use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Academic\EducationTeacherCourse;
use App\Repository\Education\Academic\TeacherCourseRepository;

/**
 * @internal
 * @coversNothing
 */
final class TeacherCourseRepositoryTest extends AcademicTestCase
{
    public function testReplaceEnabledTeachersSoftDeletesRemovedAuthorizations(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $course = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'status' => 'enabled',
        ]);
        $teacherA = $this->teacher($tenant->id, $campus->id, 'T001', 'Teacher A');
        $teacherB = $this->teacher($tenant->id, $campus->id, 'T002', 'Teacher B');
        $teacherC = $this->teacher($tenant->id, $campus->id, 'T003', 'Teacher C');
        $repository = make(TeacherCourseRepository::class);

        $repository->replaceEnabledTeachers((int) $course->id, [(int) $teacherA->id, (int) $teacherB->id], (int) $tenant->id, (int) $campus->id, 901);
        $after = $repository->replaceEnabledTeachers((int) $course->id, [(int) $teacherB->id, (int) $teacherC->id], (int) $tenant->id, (int) $campus->id, 902);

        $removed = EducationTeacherCourse::withTrashed()
            ->where('course_id', $course->id)
            ->where('teacher_id', $teacherA->id)
            ->first();

        self::assertNotNull($removed?->deleted_at);
        self::assertSame([(int) $teacherB->id, (int) $teacherC->id], array_column($after, 'teacher_id'));
    }

    private function teacher(int $tenantId, int $campusId, string $teacherNo, string $name): EducationTeacher
    {
        return EducationTeacher::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'teacher_no' => $teacherNo,
            'name' => $name,
            'status' => 'enabled',
        ]);
    }
}
