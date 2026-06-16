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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationLeaveRequest;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Academic\TeacherMobileAttendanceService;

/**
 * @internal
 * @coversNothing
 */
final class TeacherMobileAttendanceServiceTest extends AcademicTestCase
{
    public function testSheetDefaultsApprovedLeaveToNoConsume(): void
    {
        $fixture = $this->fixture();
        $this->leave($fixture, 'approved');

        $sheet = make(TeacherMobileAttendanceService::class)->sheet((int) $fixture['lesson']->id, [
            'campus_id' => $fixture['campus_id'],
        ], $fixture['context']);

        self::assertFalse($sheet['submitted']);
        self::assertSame('leave', $sheet['records'][0]['default_attendance_status']);
        self::assertSame('no_consume', $sheet['records'][0]['default_consume_policy']);
        self::assertSame('0.00', $sheet['records'][0]['default_consumed_units']);
    }

    public function testSubmitDelegatesToV104AttendanceService(): void
    {
        $fixture = $this->fixture();

        $result = make(TeacherMobileAttendanceService::class)->submit((int) $fixture['lesson']->id, [
            'submitted_at' => '2026-06-12 10:05:00',
            'records' => [[
                'lesson_student_id' => $fixture['lessonStudent']->id,
                'attendance_status' => 'present',
                'consume_policy' => 'consume',
                'consumed_units' => '1.00',
            ]],
        ], $fixture['context']);

        self::assertSame((int) $fixture['lesson']->id, $result['lesson_id']);
        self::assertSame(1, $result['attendance_count']);
        self::assertSame('completed', $fixture['lesson']->refresh()->status);
        self::assertSame('9.00', $fixture['account']->refresh()->available_units);
    }

    public function testSubmitRejectsMissingStudentRow(): void
    {
        $fixture = $this->fixture();

        try {
            make(TeacherMobileAttendanceService::class)->submit((int) $fixture['lesson']->id, [
                'records' => [],
            ], $fixture['context']);
            self::fail('Expected missing attendance row to be rejected.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    private function fixture(): array
    {
        $tenant = $this->tenant('teacher_mobile_attendance');
        $campus = $this->campus($tenant, 'main');
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'teacher-mobile-attendance',
            'tenant_id' => $tenant->id,
            'user_id' => 9201,
            'role_code' => EducationRoleCode::Teacher->value,
            'display_name' => 'Teacher Mobile',
            'status' => 'enabled',
            'current_campus_id' => $campus->id,
        ]);
        $teacher = EducationTeacher::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'user_profile_id' => $profile->id,
            'teacher_no' => 'T-ATT',
            'name' => 'Teacher Mobile',
            'status' => 'enabled',
        ]);
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'ART-001', 'name' => 'Art Basics', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'C-001', 'name' => 'Sunday Art', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S001', 'name' => 'Student Zhang', 'status' => 'enabled']);
        $account = EducationStudentCourseAccount::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_id' => $student->id, 'course_id' => $course->id, 'purchased_units' => '10.00', 'bonus_units' => '0.00', 'consumed_units' => '0.00', 'adjusted_units' => '0.00', 'refunded_units' => '0.00', 'frozen_units' => '0.00', 'available_units' => '10.00', 'status' => 'active']);
        $lesson = EducationLesson::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_no' => 'L-ATT', 'class_id' => $class->id, 'course_id' => $course->id, 'teacher_id' => $teacher->id, 'classroom_id' => 201, 'title' => 'Drawing', 'start_at' => '2026-06-12 09:00:00', 'end_at' => '2026-06-12 10:00:00', 'duration_minutes' => 60, 'lesson_units' => '1.00', 'student_count' => 1, 'status' => 'scheduled', 'source_type' => 'manual', 'class_name_snapshot' => 'Sunday Art', 'course_name_snapshot' => 'Art Basics', 'teacher_name_snapshot' => 'Teacher Mobile']);
        $lessonStudent = EducationLessonStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_id' => $lesson->id, 'class_id' => $class->id, 'course_id' => $course->id, 'student_id' => $student->id, 'account_id' => $account->id, 'student_name_snapshot' => $student->name, 'student_no_snapshot' => $student->student_no, 'lesson_units' => '1.00', 'status' => 'planned']);

        return [
            'tenant_id' => (int) $tenant->id,
            'campus_id' => (int) $campus->id,
            'class_id' => (int) $class->id,
            'course_id' => (int) $course->id,
            'teacher_id' => (int) $teacher->id,
            'lesson' => $lesson,
            'lessonStudent' => $lessonStudent,
            'account' => $account,
            'context' => $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9201),
        ];
    }

    private function leave(array $fixture, string $status): EducationLeaveRequest
    {
        return EducationLeaveRequest::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'leave_no' => 'LEA-ATT',
            'source' => 'staff',
            'leave_type' => 'sick',
            'lesson_id' => $fixture['lesson']->id,
            'lesson_student_id' => $fixture['lessonStudent']->id,
            'class_id' => $fixture['class_id'],
            'course_id' => $fixture['course_id'],
            'student_id' => 1001,
            'account_id' => $fixture['account']->id,
            'teacher_id' => $fixture['teacher_id'],
            'reason' => 'Sick leave',
            'status' => $status,
            'makeup_required' => true,
        ]);
    }
}
