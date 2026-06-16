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
use App\Service\Education\Academic\TeacherMobileLeaveService;

/**
 * @internal
 * @coversNothing
 */
final class TeacherMobileLeaveServiceTest extends AcademicTestCase
{
    public function testPageReturnsOnlyAssignedTeacherLeaves(): void
    {
        $fixture = $this->fixture();
        $visible = $this->leave($fixture, $fixture['lesson'], 'LEA-SVC-1', 'pending');
        $otherLesson = $this->lesson($fixture, $fixture['teacher_id'] + 99, '2026-06-12 11:00:00');
        $this->leave($fixture, $otherLesson, 'LEA-SVC-2', 'pending');

        $result = make(TeacherMobileLeaveService::class)->page([
            'campus_id' => $fixture['campus_id'],
            'status' => 'pending',
        ], $fixture['context']);

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, $result['list'][0]['id']);
        self::assertSame('LEA-SVC-1', $result['list'][0]['leave_no']);
    }

    public function testDetailWrongTeacherReturns404(): void
    {
        $fixture = $this->fixture();
        $otherLesson = $this->lesson($fixture, $fixture['teacher_id'] + 99, '2026-06-12 11:00:00');
        $leave = $this->leave($fixture, $otherLesson, 'LEA-SVC-3', 'pending');

        try {
            make(TeacherMobileLeaveService::class)->detail((int) $leave->id, [
                'campus_id' => $fixture['campus_id'],
            ], $fixture['context']);
            self::fail('Expected wrong teacher leave to be rejected.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::NOT_FOUND, $exception->getResponse()->code);
        }
    }

    public function testApprovePendingLeave(): void
    {
        $fixture = $this->fixture();
        $leave = $this->leave($fixture, $fixture['lesson'], 'LEA-SVC-4', 'pending');

        $result = make(TeacherMobileLeaveService::class)->approve((int) $leave->id, [
            'campus_id' => $fixture['campus_id'],
            'review_remark' => 'Approved by teacher',
        ], $fixture['context']);

        self::assertSame('approved', $result['status']);
        self::assertSame('Approved by teacher', $leave->refresh()->review_remark);
        self::assertSame(9301, $leave->reviewed_by);
    }

    public function testRejectNonPendingLeaveReturns409(): void
    {
        $fixture = $this->fixture();
        $leave = $this->leave($fixture, $fixture['lesson'], 'LEA-SVC-5', 'approved');

        try {
            make(TeacherMobileLeaveService::class)->reject((int) $leave->id, [
                'campus_id' => $fixture['campus_id'],
                'review_remark' => 'Reject later',
            ], $fixture['context']);
            self::fail('Expected non-pending leave to conflict.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    private function fixture(): array
    {
        $tenant = $this->tenant('teacher_mobile_leave_service');
        $campus = $this->campus($tenant, 'main');
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'teacher-mobile-leave-service',
            'tenant_id' => $tenant->id,
            'user_id' => 9301,
            'role_code' => EducationRoleCode::Teacher->value,
            'display_name' => 'Teacher Mobile',
            'status' => 'enabled',
            'current_campus_id' => $campus->id,
        ]);
        $teacher = EducationTeacher::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'user_profile_id' => $profile->id,
            'teacher_no' => 'T-LEAVE-SVC',
            'name' => 'Teacher Mobile',
            'status' => 'enabled',
        ]);
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'ART-001', 'name' => 'Art Basics', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'C-001', 'name' => 'Sunday Art', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S001', 'name' => 'Student Zhang', 'status' => 'enabled']);
        $account = EducationStudentCourseAccount::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_id' => $student->id, 'course_id' => $course->id, 'purchased_units' => '10.00', 'bonus_units' => '0.00', 'consumed_units' => '0.00', 'adjusted_units' => '0.00', 'refunded_units' => '0.00', 'frozen_units' => '0.00', 'available_units' => '10.00', 'status' => 'active']);
        $fixture = [
            'tenant_id' => (int) $tenant->id,
            'campus_id' => (int) $campus->id,
            'class_id' => (int) $class->id,
            'course_id' => (int) $course->id,
            'teacher_id' => (int) $teacher->id,
            'student_id' => (int) $student->id,
            'account_id' => (int) $account->id,
            'context' => $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9301),
        ];
        $lesson = $this->lesson($fixture, (int) $teacher->id, '2026-06-12 09:00:00');
        $lessonStudent = $this->lessonStudent($fixture, (int) $lesson->id);
        $fixture['lesson'] = $lesson;
        $fixture['lessonStudent'] = $lessonStudent;

        return $fixture;
    }

    private function lesson(array $fixture, int $teacherId, string $startAt): EducationLesson
    {
        return EducationLesson::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'lesson_no' => uniqid('L', false),
            'class_id' => $fixture['class_id'],
            'course_id' => $fixture['course_id'],
            'teacher_id' => $teacherId,
            'classroom_id' => 201,
            'title' => 'Drawing',
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
            'student_id' => $fixture['student_id'],
            'account_id' => $fixture['account_id'],
            'student_name_snapshot' => 'Student Zhang',
            'student_no_snapshot' => 'S001',
            'lesson_units' => '1.00',
            'status' => 'planned',
        ]);
    }

    private function leave(array $fixture, EducationLesson $lesson, string $leaveNo, string $status): EducationLeaveRequest
    {
        $lessonStudent = $lesson->id === $fixture['lesson']->id ? $fixture['lessonStudent'] : $this->lessonStudent($fixture, (int) $lesson->id);

        return EducationLeaveRequest::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'leave_no' => $leaveNo,
            'source' => 'staff',
            'leave_type' => 'sick',
            'lesson_id' => $lesson->id,
            'lesson_student_id' => $lessonStudent->id,
            'class_id' => $fixture['class_id'],
            'course_id' => $fixture['course_id'],
            'student_id' => $fixture['student_id'],
            'account_id' => $fixture['account_id'],
            'teacher_id' => $fixture['teacher_id'],
            'reason' => 'Sick leave',
            'status' => $status,
            'makeup_required' => true,
        ]);
    }
}
