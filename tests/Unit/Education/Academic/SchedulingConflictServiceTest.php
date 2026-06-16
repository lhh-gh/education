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
use App\Service\Education\Academic\SchedulingConflictService;

/**
 * @internal
 * @coversNothing
 */
final class SchedulingConflictServiceTest extends AcademicTestCase
{
    public function testCheckSingleFindsTeacherClassroomClassAndStudentConflicts(): void
    {
        [$tenantId, $campusId, $class, $lesson] = $this->fixture('scheduled');
        EducationLessonStudent::query()->create($this->lessonStudentRow($tenantId, $campusId, (int) $lesson->id, (int) $class->id, (int) $class->course_id, 101));

        $result = make(SchedulingConflictService::class)->checkSingle([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'class_id' => (int) $class->id,
            'teacher_id' => 11,
            'classroom_id' => 22,
            'student_ids' => [101],
            'start_at' => '2026-06-15 09:30:00',
            'end_at' => '2026-06-15 10:30:00',
        ]);

        $types = array_column($result['conflicts'], 'conflict_type');
        sort($types);

        self::assertTrue($result['has_conflict']);
        self::assertSame(['class', 'classroom', 'student', 'teacher'], $types);
    }

    public function testCancelledLessonsDoNotConflict(): void
    {
        [$tenantId, $campusId, $class, $lesson] = $this->fixture('cancelled');
        EducationLessonStudent::query()->create($this->lessonStudentRow($tenantId, $campusId, (int) $lesson->id, (int) $class->id, (int) $class->course_id, 101));

        $result = make(SchedulingConflictService::class)->checkSingle([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'class_id' => (int) $class->id,
            'teacher_id' => 11,
            'classroom_id' => 22,
            'student_ids' => [101],
            'start_at' => '2026-06-15 09:30:00',
            'end_at' => '2026-06-15 10:30:00',
        ]);

        self::assertFalse($result['has_conflict']);
        self::assertSame([], $result['conflicts']);
    }

    private function fixture(string $status): array
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
            'status' => $status,
            'source_type' => 'manual',
            'class_name_snapshot' => 'Sunday Art',
            'course_name_snapshot' => 'Art Basics',
            'teacher_name_snapshot' => 'Teacher',
        ]);

        return [(int) $tenant->id, (int) $campus->id, $class, $lesson];
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
