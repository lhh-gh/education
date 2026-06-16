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

use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationClassStudent;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationNotice;
use App\Model\Education\Academic\EducationNoticeReceipt;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\Context\Context;

trait GuardianMobileApiFixture
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

    protected function guardianFixture(string $code, bool $canSubmitLeave = true): array
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant, 'main');
        $mobile = '137' . random_int(10000000, 99999999);
        EducationUserProfile::query()->create(['profile_key' => 'guardian:' . $code . ':' . $this->user->id, 'tenant_id' => $tenant->id, 'user_id' => $this->user->id, 'role_code' => 'guardian', 'display_name' => 'Guardian Mobile', 'mobile' => $mobile, 'status' => 'enabled', 'current_campus_id' => $campus->id]);
        $guardian = EducationGuardian::query()->create(['tenant_id' => $tenant->id, 'name' => 'Guardian Mobile', 'mobile' => $mobile, 'status' => 'enabled']);
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'ART-' . mb_strtoupper($code), 'name' => 'Art Basics', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'CLS-' . mb_strtoupper($code), 'name' => 'Sunday Art', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S-' . mb_strtoupper($code), 'name' => 'Student Zhang', 'status' => 'enabled']);
        EducationStudentGuardian::query()->create(['tenant_id' => $tenant->id, 'student_id' => $student->id, 'guardian_id' => $guardian->id, 'relation' => 'mother', 'is_primary' => true, 'can_receive_notice' => true, 'can_submit_leave' => $canSubmitLeave]);
        $account = EducationStudentCourseAccount::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_id' => $student->id, 'course_id' => $course->id, 'purchased_units' => '10.00', 'bonus_units' => '0.00', 'consumed_units' => '1.00', 'adjusted_units' => '0.00', 'refunded_units' => '0.00', 'frozen_units' => '0.00', 'available_units' => '9.00', 'status' => 'active']);
        EducationClassStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'class_id' => $class->id, 'course_id' => $course->id, 'student_id' => $student->id, 'account_id' => $account->id, 'student_name_snapshot' => 'Student Zhang', 'student_no_snapshot' => 'S001', 'status' => 'active']);
        $lesson = EducationLesson::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_no' => uniqid('L', false), 'class_id' => $class->id, 'course_id' => $course->id, 'teacher_id' => 1001, 'classroom_id' => 201, 'title' => 'Art Basics Lesson', 'start_at' => '2026-06-12 09:00:00', 'end_at' => '2026-06-12 10:00:00', 'duration_minutes' => 60, 'lesson_units' => '1.00', 'student_count' => 1, 'status' => 'scheduled', 'source_type' => 'manual', 'class_name_snapshot' => 'Sunday Art', 'course_name_snapshot' => 'Art Basics', 'teacher_name_snapshot' => 'Teacher Wang', 'classroom_name_snapshot' => 'Room 1']);
        $lessonStudent = EducationLessonStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_id' => $lesson->id, 'class_id' => $class->id, 'course_id' => $course->id, 'student_id' => $student->id, 'account_id' => $account->id, 'student_name_snapshot' => 'Student Zhang', 'student_no_snapshot' => 'S001', 'lesson_units' => '1.00', 'status' => 'planned']);
        $consumption = EducationLessonConsumption::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'consumption_no' => uniqid('CON', false), 'account_id' => $account->id, 'student_id' => $student->id, 'course_id' => $course->id, 'lesson_id' => $lesson->id, 'lesson_student_id' => $lessonStudent->id, 'attendance_id' => 5001, 'source_type' => 'attendance', 'direction' => 'decrease', 'units' => '1.00', 'before_available_units' => '10.00', 'after_available_units' => '9.00', 'before_consumed_units' => '0.00', 'after_consumed_units' => '1.00', 'status' => 'active']);

        return ['tenant' => $tenant, 'tenant_id' => (int) $tenant->id, 'campus_id' => (int) $campus->id, 'guardian' => $guardian, 'student' => $student, 'class' => $class, 'course' => $course, 'account' => $account, 'lesson' => $lesson, 'lesson_student' => $lessonStudent, 'consumption' => $consumption];
    }

    protected function teacherProfileFixture(string $code): EducationTenant
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant, 'main');
        EducationUserProfile::query()->create(['profile_key' => 'teacher:' . $code . ':' . $this->user->id, 'tenant_id' => $tenant->id, 'user_id' => $this->user->id, 'role_code' => 'teacher', 'display_name' => 'Teacher Mobile', 'status' => 'enabled', 'current_campus_id' => $campus->id]);

        return $tenant;
    }

    protected function noticeReceipt(array $fixture): EducationNoticeReceipt
    {
        $notice = EducationNotice::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'notice_no' => uniqid('NOT', false), 'notice_type' => 'academic', 'target_type' => 'student', 'target_id' => $fixture['student']->id, 'title' => 'Class reminder', 'content' => 'Bring tools tomorrow.', 'priority' => 'important', 'status' => 'published', 'published_at' => '2026-06-12 09:00:00', 'receipt_count' => 1, 'read_count' => 0]);

        return EducationNoticeReceipt::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'notice_id' => $notice->id, 'guardian_id' => $fixture['guardian']->id, 'student_id' => $fixture['student']->id, 'relation' => 'mother', 'guardian_name_snapshot' => 'Guardian Mobile', 'student_name_snapshot' => 'Student Zhang', 'status' => 'unread', 'delivered_at' => '2026-06-12 09:00:00']);
    }

    protected function mobileHeaders(EducationTenant $tenant): array
    {
        return $this->tenantHeaders($tenant, ['X-Client-Type' => 'wechat_miniprogram']);
    }
}
