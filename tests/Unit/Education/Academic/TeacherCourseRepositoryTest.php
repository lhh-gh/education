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
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\TeacherCourseRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class TeacherCourseRepositoryTest extends AcademicTestCase
{
    public function testPlatformContextCampusFiltersListByCourseWithoutLocalFilters(): void
    {
        $tenant = $this->tenant('teacher_course_platform_scope');
        $campusA = $this->campus($tenant, 'scope_a');
        $campusB = $this->campus($tenant, 'scope_b');
        $course = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campusA->id,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'status' => 'enabled',
        ]);
        $teacherA = $this->teacher((int) $tenant->id, (int) $campusA->id, 'T001', 'Teacher A');
        $teacherB = $this->teacher((int) $tenant->id, (int) $campusB->id, 'T002', 'Teacher B');
        $visible = EducationTeacherCourse::query()->create($this->teacherCourseRow((int) $tenant->id, (int) $campusA->id, (int) $course->id, (int) $teacherA->id));
        EducationTeacherCourse::query()->create($this->teacherCourseRow((int) $tenant->id, (int) $campusB->id, (int) $course->id, (int) $teacherB->id));

        $rows = make(TeacherCourseRepository::class)->listByCourse((int) $course->id, new EducationUserContext(
            userId: 1,
            tenantId: (int) $tenant->id,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: (int) $campusA->id
        ));

        self::assertCount(1, $rows);
        self::assertSame((int) $visible->id, (int) $rows[0]['id']);
    }

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

    private function teacherCourseRow(int $tenantId, int $campusId, int $courseId, int $teacherId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'course_id' => $courseId,
            'teacher_id' => $teacherId,
            'status' => 'enabled',
            'authorized_at' => '2026-06-15 09:00:00',
        ];
    }
}
