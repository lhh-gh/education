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

namespace HyperfTests\Feature\Education\Academic;

use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationLeaveRequest;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserCampusScope;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\Context\Context;

/**
 * @internal
 * @coversNothing
 */
final class TeacherMobileLeaveApiTest extends ProfileRecordAdminCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Context::destroy(MobileContextService::CONTEXT_KEY);
    }

    protected function tearDown(): void
    {
        Context::destroy(MobileContextService::CONTEXT_KEY);
        parent::tearDown();
    }

    public function testLeavePageAndDetailContract(): void
    {
        $fixture = $this->fixture('teacher_mobile_leave_api_page');
        $leave = $this->leave($fixture, $fixture['lesson'], 'LEA-API-1', 'pending');

        $page = $this->get('/mobile/education/academic/teacher/leave-requests/page', [
            'campus_id' => $fixture['campus_id'],
            'status' => 'pending',
        ], $this->mobileHeaders($fixture['tenant']));
        $detail = $this->get('/mobile/education/academic/teacher/leave-requests/' . $leave->id, [
            'campus_id' => $fixture['campus_id'],
        ], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $page['code']);
        self::assertSame((int) $leave->id, $page['data']['list'][0]['id']);
        self::assertSame(ResultCode::SUCCESS->value, $detail['code']);
        self::assertSame('LEA-API-1', $detail['data']['leave_no']);
    }

    public function testApproveAndRejectContracts(): void
    {
        $fixture = $this->fixture('teacher_mobile_leave_api_review');
        $approved = $this->leave($fixture, $fixture['lesson'], 'LEA-API-2', 'pending');
        $rejectLesson = $this->lesson($fixture, $fixture['teacher_id'], '2026-06-12 11:00:00');
        $rejected = $this->leave($fixture, $rejectLesson, 'LEA-API-3', 'pending');

        $approve = $this->put('/mobile/education/academic/teacher/leave-requests/' . $approved->id . '/approve', [
            'review_remark' => 'Approved',
        ], $this->mobileHeaders($fixture['tenant']));
        $reject = $this->put('/mobile/education/academic/teacher/leave-requests/' . $rejected->id . '/reject', [
            'review_remark' => 'Rejected',
        ], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $approve['code']);
        self::assertSame('approved', $approve['data']['status']);
        self::assertSame(ResultCode::SUCCESS->value, $reject['code']);
        self::assertSame('rejected', $reject['data']['status']);
    }

    public function testNonPendingReviewReturns409(): void
    {
        $fixture = $this->fixture('teacher_mobile_leave_api_conflict');
        $leave = $this->leave($fixture, $fixture['lesson'], 'LEA-API-4', 'approved');

        $result = $this->put('/mobile/education/academic/teacher/leave-requests/' . $leave->id . '/reject', [
            'review_remark' => 'Rejected',
        ], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::CONFLICT->value, $result['code']);
        self::assertSame('only pending leave can be rejected', $result['message']);
    }

    public function testMissingReviewRemarkReturns422(): void
    {
        $fixture = $this->fixture('teacher_mobile_leave_api_validation');
        $leave = $this->leave($fixture, $fixture['lesson'], 'LEA-API-5', 'pending');

        $result = $this->put('/mobile/education/academic/teacher/leave-requests/' . $leave->id . '/approve', [], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $result['code']);
        self::assertSame('review_remark is required', $result['message']);
    }

    public function testWrongTeacherDetailReturns404(): void
    {
        $fixture = $this->fixture('teacher_mobile_leave_api_wrong_teacher');
        $otherLesson = $this->lesson($fixture, $fixture['teacher_id'] + 99, '2026-06-12 11:00:00');
        $leave = $this->leave($fixture, $otherLesson, 'LEA-API-6', 'pending');

        $result = $this->get('/mobile/education/academic/teacher/leave-requests/' . $leave->id, [
            'campus_id' => $fixture['campus_id'],
        ], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::NOT_FOUND->value, $result['code']);
        self::assertSame('leave request not found in current teacher context', $result['message']);
    }

    private function fixture(string $code): array
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant, 'main');
        $profile = EducationUserProfile::query()->create(['profile_key' => 'tenant:' . $tenant->id . ':' . $this->user->id, 'tenant_id' => $tenant->id, 'user_id' => $this->user->id, 'role_code' => 'teacher', 'display_name' => 'Teacher Mobile', 'status' => 'enabled', 'current_campus_id' => $campus->id]);
        EducationUserCampusScope::query()->create(['tenant_id' => $tenant->id, 'user_profile_id' => $profile->id, 'user_id' => $this->user->id, 'campus_id' => $campus->id]);
        $teacher = EducationTeacher::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'user_profile_id' => $profile->id, 'teacher_no' => 'T-LEAVE-API', 'name' => 'Teacher Mobile', 'status' => 'enabled']);
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'ART-001', 'name' => 'Art Basics', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'C-001', 'name' => 'Sunday Art', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S001', 'name' => 'Student Zhang', 'status' => 'enabled']);
        $account = EducationStudentCourseAccount::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_id' => $student->id, 'course_id' => $course->id, 'purchased_units' => '10.00', 'bonus_units' => '0.00', 'consumed_units' => '0.00', 'adjusted_units' => '0.00', 'refunded_units' => '0.00', 'frozen_units' => '0.00', 'available_units' => '10.00', 'status' => 'active']);
        $fixture = ['tenant' => $tenant, 'tenant_id' => (int) $tenant->id, 'campus_id' => (int) $campus->id, 'class_id' => (int) $class->id, 'course_id' => (int) $course->id, 'teacher_id' => (int) $teacher->id, 'student_id' => (int) $student->id, 'account_id' => (int) $account->id];
        $lesson = $this->lesson($fixture, (int) $teacher->id, '2026-06-12 09:00:00');
        $fixture['lesson'] = $lesson;
        $fixture['lessonStudent'] = $this->lessonStudent($fixture, (int) $lesson->id);

        return $fixture;
    }

    private function lesson(array $fixture, int $teacherId, string $startAt): EducationLesson
    {
        return EducationLesson::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'lesson_no' => uniqid('L', false), 'class_id' => $fixture['class_id'], 'course_id' => $fixture['course_id'], 'teacher_id' => $teacherId, 'classroom_id' => 201, 'title' => 'Drawing', 'start_at' => $startAt, 'end_at' => date('Y-m-d H:i:s', strtotime($startAt . ' +1 hour')), 'duration_minutes' => 60, 'lesson_units' => '1.00', 'student_count' => 1, 'status' => 'scheduled', 'source_type' => 'manual', 'class_name_snapshot' => 'Sunday Art', 'course_name_snapshot' => 'Art Basics', 'teacher_name_snapshot' => 'Teacher Mobile']);
    }

    private function lessonStudent(array $fixture, int $lessonId): EducationLessonStudent
    {
        return EducationLessonStudent::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'lesson_id' => $lessonId, 'class_id' => $fixture['class_id'], 'course_id' => $fixture['course_id'], 'student_id' => $fixture['student_id'], 'account_id' => $fixture['account_id'], 'student_name_snapshot' => 'Student Zhang', 'student_no_snapshot' => 'S001', 'lesson_units' => '1.00', 'status' => 'planned']);
    }

    private function leave(array $fixture, EducationLesson $lesson, string $leaveNo, string $status): EducationLeaveRequest
    {
        $lessonStudent = $lesson->id === $fixture['lesson']->id ? $fixture['lessonStudent'] : $this->lessonStudent($fixture, (int) $lesson->id);

        return EducationLeaveRequest::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'leave_no' => $leaveNo, 'source' => 'staff', 'leave_type' => 'sick', 'lesson_id' => $lesson->id, 'lesson_student_id' => $lessonStudent->id, 'class_id' => $fixture['class_id'], 'course_id' => $fixture['course_id'], 'student_id' => $fixture['student_id'], 'account_id' => $fixture['account_id'], 'teacher_id' => $fixture['teacher_id'], 'reason' => 'Sick leave', 'status' => $status, 'makeup_required' => true]);
    }

    private function mobileHeaders(EducationTenant $tenant): array
    {
        return $this->tenantHeaders($tenant, ['X-Client-Type' => 'wechat_miniprogram']);
    }
}
