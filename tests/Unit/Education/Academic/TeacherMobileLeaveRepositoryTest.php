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
use App\Model\Education\Academic\EducationLeaveRequest;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\TeacherMobileLeaveRepository;

/**
 * @internal
 * @coversNothing
 */
final class TeacherMobileLeaveRepositoryTest extends AcademicTestCase
{
    public function testLeavePageFiltersByAssignedLessonTeacher(): void
    {
        [$tenantId, $campusId, $classId, $courseId] = $this->fixture();
        $assignedLesson = $this->lesson($tenantId, $campusId, $classId, $courseId, 101, '2026-06-12 09:00:00');
        $otherLesson = $this->lesson($tenantId, $campusId, $classId, $courseId, 102, '2026-06-12 10:00:00');
        $visible = $this->leave($tenantId, $campusId, (int) $assignedLesson->id, $classId, $courseId, 10001, 'LEA001');
        $this->leave($tenantId, $campusId, (int) $otherLesson->id, $classId, $courseId, 10002, 'LEA002');

        $result = make(TeacherMobileLeaveRepository::class)->pageAssignedLeave([
            'status' => 'pending',
        ], 1, 20, $this->context($tenantId, EducationRoleCode::Teacher, [$campusId]), 101);

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
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

        return [(int) $tenant->id, (int) $campus->id, (int) $class->id, (int) $course->id];
    }

    private function lesson(int $tenantId, int $campusId, int $classId, int $courseId, int $teacherId, string $startAt): EducationLesson
    {
        return EducationLesson::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'lesson_no' => uniqid('L', false),
            'class_id' => $classId,
            'course_id' => $courseId,
            'teacher_id' => $teacherId,
            'classroom_id' => 201,
            'title' => 'Lesson',
            'start_at' => $startAt,
            'end_at' => date('Y-m-d H:i:s', strtotime($startAt . ' +1 hour')),
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 0,
            'status' => 'scheduled',
            'source_type' => 'manual',
            'class_name_snapshot' => 'Sunday Art',
            'course_name_snapshot' => 'Art Basics',
            'teacher_name_snapshot' => 'Teacher',
        ]);
    }

    private function leave(int $tenantId, int $campusId, int $lessonId, int $classId, int $courseId, int $lessonStudentId, string $leaveNo): EducationLeaveRequest
    {
        return EducationLeaveRequest::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'leave_no' => $leaveNo,
            'source' => 'staff',
            'leave_type' => 'sick',
            'lesson_id' => $lessonId,
            'lesson_student_id' => $lessonStudentId,
            'class_id' => $classId,
            'course_id' => $courseId,
            'student_id' => $lessonStudentId + 100,
            'account_id' => $lessonStudentId + 200,
            'teacher_id' => 101,
            'reason' => 'Sick leave',
            'status' => 'pending',
            'makeup_required' => true,
        ]);
    }
}
