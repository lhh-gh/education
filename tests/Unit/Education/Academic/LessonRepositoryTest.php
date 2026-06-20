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
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\LessonRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class LessonRepositoryTest extends AcademicTestCase
{
    public function testPlatformContextCampusFiltersPageWithoutLocalFilters(): void
    {
        $tenant = $this->tenant('lesson_platform_scope');
        $campusA = $this->campus($tenant, 'scope_a');
        $campusB = $this->campus($tenant, 'scope_b');
        $courseA = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campusA->id,
            'code' => 'COURSE-A',
            'name' => 'Course A',
            'status' => 'enabled',
        ]);
        $courseB = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campusB->id,
            'code' => 'COURSE-B',
            'name' => 'Course B',
            'status' => 'enabled',
        ]);
        $classA = EducationClass::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campusA->id,
            'course_id' => $courseA->id,
            'code' => 'CLASS-A',
            'name' => 'Class A',
            'class_type' => 'group',
            'lesson_units' => '1.00',
            'status' => 'enabled',
        ]);
        $classB = EducationClass::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campusB->id,
            'course_id' => $courseB->id,
            'code' => 'CLASS-B',
            'name' => 'Class B',
            'class_type' => 'group',
            'lesson_units' => '1.00',
            'status' => 'enabled',
        ]);
        $visible = $this->lesson((int) $tenant->id, (int) $campusA->id, (int) $classA->id, (int) $courseA->id, 11, 22, '2026-06-15 09:00:00', '2026-06-15 10:00:00');
        $this->lesson((int) $tenant->id, (int) $campusB->id, (int) $classB->id, (int) $courseB->id, 12, 23, '2026-06-16 09:00:00', '2026-06-16 10:00:00');

        $result = make(LessonRepository::class)->pageByContext([], 1, 20, new EducationUserContext(
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

    public function testCalendarFiltersByRangeTeacherClassroomAndStatus(): void
    {
        [$tenantId, $campusId, $class, $courseId] = $this->fixture();
        $visible = $this->lesson($tenantId, $campusId, (int) $class->id, $courseId, 11, 22, '2026-06-15 09:00:00', '2026-06-15 10:00:00');
        $this->lesson($tenantId, $campusId, (int) $class->id, $courseId, 12, 22, '2026-06-15 09:00:00', '2026-06-15 10:00:00');
        $this->lesson($tenantId, $campusId, (int) $class->id, $courseId, 11, 22, '2026-06-16 09:00:00', '2026-06-16 10:00:00', 'cancelled');

        $rows = make(LessonRepository::class)->calendar([
            'start_at' => '2026-06-15 00:00:00',
            'end_at' => '2026-06-15 23:59:59',
            'teacher_id' => 11,
            'classroom_id' => 22,
            'status' => 'scheduled',
        ], $this->context($tenantId, campusIds: [$campusId]));

        self::assertCount(1, $rows);
        self::assertSame((int) $visible->id, (int) $rows[0]['id']);
    }

    public function testOverlappingLessonsExcludesCancelledAndCurrentLesson(): void
    {
        [$tenantId, $campusId, $class, $courseId] = $this->fixture();
        $conflict = $this->lesson($tenantId, $campusId, (int) $class->id, $courseId, 11, 22, '2026-06-15 09:00:00', '2026-06-15 10:00:00');
        $current = $this->lesson($tenantId, $campusId, (int) $class->id, $courseId, 11, 22, '2026-06-15 09:30:00', '2026-06-15 10:30:00');
        $this->lesson($tenantId, $campusId, (int) $class->id, $courseId, 11, 22, '2026-06-15 09:00:00', '2026-06-15 10:00:00', 'cancelled');

        $rows = make(LessonRepository::class)->overlappingLessons([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'teacher_id' => 11,
            'start_at' => '2026-06-15 09:15:00',
            'end_at' => '2026-06-15 09:45:00',
        ], (int) $current->id);

        self::assertCount(1, $rows);
        self::assertSame((int) $conflict->id, (int) $rows[0]['id']);
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

        return [(int) $tenant->id, (int) $campus->id, $class, (int) $course->id];
    }

    private function lesson(int $tenantId, int $campusId, int $classId, int $courseId, int $teacherId, int $classroomId, string $startAt, string $endAt, string $status = 'scheduled'): EducationLesson
    {
        return EducationLesson::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'lesson_no' => uniqid('L', false),
            'class_id' => $classId,
            'course_id' => $courseId,
            'teacher_id' => $teacherId,
            'classroom_id' => $classroomId,
            'title' => 'Lesson',
            'start_at' => $startAt,
            'end_at' => $endAt,
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 0,
            'status' => $status,
            'source_type' => 'manual',
            'class_name_snapshot' => 'Sunday Art',
            'course_name_snapshot' => 'Art Basics',
            'teacher_name_snapshot' => 'Teacher',
        ]);
    }
}
