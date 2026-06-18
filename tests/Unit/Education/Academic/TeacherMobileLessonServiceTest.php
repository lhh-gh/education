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
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Academic\TeacherMobileLessonService;

/**
 * @internal
 * @coversNothing
 */
final class TeacherMobileLessonServiceTest extends AcademicTestCase
{
    public function testTodayReturnsAssignedLessons(): void
    {
        $fixture = $this->fixture();
        $visible = $this->lesson($fixture, '2026-06-12 09:00:00');
        $this->lesson($fixture, '2026-06-13 09:00:00');

        $result = make(TeacherMobileLessonService::class)->today([
            'campus_id' => $fixture['campus_id'],
            'date' => '2026-06-12',
        ], $fixture['context']);

        self::assertSame('2026-06-12', $result['date']);
        self::assertCount(1, $result['list']);
        self::assertSame((int) $visible->id, $result['list'][0]['id']);
    }

    public function testDetailReturnsStudentsAttendanceAndLeaveDefaults(): void
    {
        $fixture = $this->fixture();
        $lesson = $this->lesson($fixture, '2026-06-12 09:00:00');
        $student = $this->lessonStudent($fixture, (int) $lesson->id);
        $this->leave($fixture, (int) $lesson->id, (int) $student->id);

        $detail = make(TeacherMobileLessonService::class)->detail((int) $lesson->id, [
            'campus_id' => $fixture['campus_id'],
        ], $fixture['context']);

        self::assertSame((int) $lesson->id, $detail['id']);
        self::assertFalse($detail['attendance_submitted']);
        self::assertTrue($detail['can_submit_attendance']);
        self::assertSame(1, $detail['pending_leave_count']);
        self::assertSame((int) $student->id, $detail['lesson_students'][0]['lesson_student_id']);
    }

    private function fixture(): array
    {
        $tenant = $this->tenant('teacher_mobile_lesson');
        $campus = $this->campus($tenant, 'main');
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'teacher-mobile-lesson',
            'tenant_id' => $tenant->id,
            'user_id' => 9101,
            'role_code' => EducationRoleCode::Teacher->value,
            'display_name' => 'Teacher Mobile',
            'status' => 'enabled',
            'current_campus_id' => $campus->id,
        ]);
        $teacher = EducationTeacher::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'user_profile_id' => $profile->id,
            'teacher_no' => 'T-MOBILE',
            'name' => 'Teacher Mobile',
            'status' => 'enabled',
        ]);
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

        return [
            'tenant_id' => (int) $tenant->id,
            'campus_id' => (int) $campus->id,
            'teacher_id' => (int) $teacher->id,
            'class_id' => (int) $class->id,
            'course_id' => (int) $course->id,
            'context' => $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9101),
        ];
    }

    private function lesson(array $fixture, string $startAt): EducationLesson
    {
        return EducationLesson::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'lesson_no' => uniqid('L', false),
            'class_id' => $fixture['class_id'],
            'course_id' => $fixture['course_id'],
            'teacher_id' => $fixture['teacher_id'],
            'classroom_id' => 201,
            'title' => 'Art Basics Lesson',
            'start_at' => $startAt,
            'end_at' => date('Y-m-d H:i:s', strtotime($startAt . ' +1 hour')),
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 1,
            'status' => 'scheduled',
            'source_type' => 'manual',
            'class_name_snapshot' => 'Sunday Art',
            'course_name_snapshot' => 'Art Basics',
            'teacher_name_snapshot' => 'Teacher Mobile',
            'classroom_name_snapshot' => 'Room 1',
        ]);
    }

    private function lessonStudent(array $fixture, int $lessonId): EducationLessonStudent
    {
        return EducationLessonStudent::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'lesson_id' => $lessonId,
            'class_id' => $fixture['class_id'],
            'course_id' => $fixture['course_id'],
            'student_id' => 1001,
            'account_id' => 2001,
            'student_name_snapshot' => 'Student Zhang',
            'student_no_snapshot' => 'S001',
            'lesson_units' => '1.00',
            'status' => 'planned',
        ]);
    }

    private function leave(array $fixture, int $lessonId, int $lessonStudentId): EducationLeaveRequest
    {
        return EducationLeaveRequest::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'leave_no' => 'LEA-MOBILE-LESSON',
            'source' => 'staff',
            'leave_type' => 'sick',
            'lesson_id' => $lessonId,
            'lesson_student_id' => $lessonStudentId,
            'class_id' => $fixture['class_id'],
            'course_id' => $fixture['course_id'],
            'student_id' => 1001,
            'account_id' => 2001,
            'teacher_id' => $fixture['teacher_id'],
            'reason' => 'Sick leave',
            'status' => 'pending',
            'makeup_required' => true,
        ]);
    }
}
