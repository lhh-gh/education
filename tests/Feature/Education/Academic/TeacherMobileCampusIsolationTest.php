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
final class TeacherMobileCampusIsolationTest extends ProfileRecordAdminCase
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

    public function testOutOfScopeCampusReturns403(): void
    {
        $fixture = $this->fixture();

        $page = $this->get('/mobile/education/academic/teacher/leave-requests/page', [
            'campus_id' => $fixture['other_campus_id'],
        ], $this->mobileHeaders($fixture['tenant']));
        $detail = $this->get('/mobile/education/academic/teacher/leave-requests/' . $fixture['leave']->id, [
            'campus_id' => $fixture['other_campus_id'],
        ], $this->mobileHeaders($fixture['tenant']));
        $approve = $this->put('/mobile/education/academic/teacher/leave-requests/' . $fixture['leave']->id . '/approve', [
            'campus_id' => $fixture['other_campus_id'],
            'review_remark' => 'Approved',
        ], $this->mobileHeaders($fixture['tenant']));

        foreach ([$page, $detail, $approve] as $result) {
            self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
            self::assertSame('campus is outside teacher scope', $result['message']);
        }
    }

    private function fixture(): array
    {
        $tenant = $this->tenant('teacher_mobile_campus_isolation');
        $campus = $this->campus($tenant, 'main');
        $otherCampus = $this->campus($tenant, 'branch');
        $profile = EducationUserProfile::query()->create(['profile_key' => 'tenant:' . $tenant->id . ':' . $this->user->id, 'tenant_id' => $tenant->id, 'user_id' => $this->user->id, 'role_code' => 'teacher', 'display_name' => 'Teacher Mobile', 'status' => 'enabled', 'current_campus_id' => $campus->id]);
        EducationUserCampusScope::query()->create(['tenant_id' => $tenant->id, 'user_profile_id' => $profile->id, 'user_id' => $this->user->id, 'campus_id' => $campus->id]);
        $teacher = EducationTeacher::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'user_profile_id' => $profile->id, 'teacher_no' => 'T-LEAVE-CAMPUS', 'name' => 'Teacher Mobile', 'status' => 'enabled']);
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'ART-001', 'name' => 'Art Basics', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'C-001', 'name' => 'Sunday Art', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S001', 'name' => 'Student Zhang', 'status' => 'enabled']);
        $account = EducationStudentCourseAccount::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_id' => $student->id, 'course_id' => $course->id, 'purchased_units' => '10.00', 'bonus_units' => '0.00', 'consumed_units' => '0.00', 'adjusted_units' => '0.00', 'refunded_units' => '0.00', 'frozen_units' => '0.00', 'available_units' => '10.00', 'status' => 'active']);
        $lesson = EducationLesson::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_no' => uniqid('L', false), 'class_id' => $class->id, 'course_id' => $course->id, 'teacher_id' => $teacher->id, 'classroom_id' => 201, 'title' => 'Drawing', 'start_at' => '2026-06-12 09:00:00', 'end_at' => '2026-06-12 10:00:00', 'duration_minutes' => 60, 'lesson_units' => '1.00', 'student_count' => 1, 'status' => 'scheduled', 'source_type' => 'manual', 'class_name_snapshot' => 'Sunday Art', 'course_name_snapshot' => 'Art Basics', 'teacher_name_snapshot' => 'Teacher Mobile']);
        $lessonStudent = EducationLessonStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_id' => $lesson->id, 'class_id' => $class->id, 'course_id' => $course->id, 'student_id' => $student->id, 'account_id' => $account->id, 'student_name_snapshot' => 'Student Zhang', 'student_no_snapshot' => 'S001', 'lesson_units' => '1.00', 'status' => 'planned']);
        $leave = EducationLeaveRequest::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'leave_no' => 'LEA-CAMPUS', 'source' => 'staff', 'leave_type' => 'sick', 'lesson_id' => $lesson->id, 'lesson_student_id' => $lessonStudent->id, 'class_id' => $class->id, 'course_id' => $course->id, 'student_id' => $student->id, 'account_id' => $account->id, 'teacher_id' => $teacher->id, 'reason' => 'Sick leave', 'status' => 'pending', 'makeup_required' => true]);

        return ['tenant' => $tenant, 'other_campus_id' => (int) $otherCampus->id, 'leave' => $leave];
    }

    private function mobileHeaders(EducationTenant $tenant): array
    {
        return $this->tenantHeaders($tenant, ['X-Client-Type' => 'wechat_miniprogram']);
    }
}
