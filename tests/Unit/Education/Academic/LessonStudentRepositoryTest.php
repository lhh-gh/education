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
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\LessonStudentRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class LessonStudentRepositoryTest extends AcademicTestCase
{
    public function testPlatformContextCampusFiltersListByLessonWithoutLocalFilters(): void
    {
        [$tenantId, $campusId, $lesson] = $this->fixture();
        $tenant = EducationTenant::query()->findOrFail($tenantId);
        $campusB = $this->campus($tenant, 'scope_b');
        $visible = EducationLessonStudent::query()->create($this->lessonStudentRow($tenantId, $campusId, (int) $lesson->id, (int) $lesson->class_id, (int) $lesson->course_id, 101));
        EducationLessonStudent::query()->create($this->lessonStudentRow($tenantId, (int) $campusB->id, (int) $lesson->id, (int) $lesson->class_id, (int) $lesson->course_id, 102));

        $rows = make(LessonStudentRepository::class)->listByLesson((int) $lesson->id, new EducationUserContext(
            userId: 1,
            tenantId: $tenantId,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: $campusId
        ));

        self::assertCount(1, $rows);
        self::assertSame((int) $visible->id, (int) $rows[0]['id']);
    }

    public function testOverlappingStudentLessonsReturnsConflictingStudentRows(): void
    {
        [$tenantId, $campusId, $lesson] = $this->fixture();
        EducationLessonStudent::query()->create($this->lessonStudentRow($tenantId, $campusId, (int) $lesson->id, (int) $lesson->class_id, (int) $lesson->course_id, 101));

        $rows = make(LessonStudentRepository::class)->overlappingStudentLessons(
            [101, 102],
            '2026-06-15 09:30:00',
            '2026-06-15 10:30:00',
            $tenantId,
            $campusId
        );

        self::assertCount(1, $rows);
        self::assertSame(101, (int) $rows[0]['student_id']);
        self::assertSame((int) $lesson->id, (int) $rows[0]['lesson_id']);
    }

    public function testCancelByLessonMarksSnapshotsCancelled(): void
    {
        [$tenantId, $campusId, $lesson] = $this->fixture();
        EducationLessonStudent::query()->create($this->lessonStudentRow($tenantId, $campusId, (int) $lesson->id, (int) $lesson->class_id, (int) $lesson->course_id, 101));
        EducationLessonStudent::query()->create($this->lessonStudentRow($tenantId, $campusId, (int) $lesson->id, (int) $lesson->class_id, (int) $lesson->course_id, 102));

        $affected = make(LessonStudentRepository::class)->cancelByLesson((int) $lesson->id, 901);

        self::assertSame(2, $affected);
        self::assertSame(2, EducationLessonStudent::query()->where('lesson_id', $lesson->id)->where('status', 'cancelled')->count());
    }

    private function fixture(): array
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
        $class = EducationClass::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'code' => 'C-001',
            'name' => 'Sunday Art',
            'class_type' => 'group',
            'lesson_units' => '1.00',
            'status' => 'enabled',
        ]);
        $lesson = EducationLesson::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lesson_no' => 'L001',
            'class_id' => $class->id,
            'course_id' => $course->id,
            'teacher_id' => 11,
            'classroom_id' => 22,
            'title' => 'Lesson',
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 10:00:00',
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 1,
            'status' => 'scheduled',
            'source_type' => 'manual',
            'class_name_snapshot' => 'Sunday Art',
            'course_name_snapshot' => 'Art Basics',
            'teacher_name_snapshot' => 'Teacher',
        ]);

        return [(int) $tenant->id, (int) $campus->id, $lesson];
    }

    private function lessonStudentRow(int $tenantId, int $campusId, int $lessonId, int $classId, int $courseId, int $studentId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'lesson_id' => $lessonId,
            'class_id' => $classId,
            'course_id' => $courseId,
            'student_id' => $studentId,
            'account_id' => $studentId + 1000,
            'student_name_snapshot' => 'Student ' . $studentId,
            'student_no_snapshot' => 'S' . $studentId,
            'lesson_units' => '1.00',
            'status' => 'planned',
        ];
    }
}
