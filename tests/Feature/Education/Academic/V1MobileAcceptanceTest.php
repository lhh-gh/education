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
final class V1MobileAcceptanceTest extends ProfileRecordAdminCase
{
    use GuardianMobileApiFixture;

    public function testTeacherAndGuardianMobileRegressionContracts(): void
    {
        $teacher = $this->teacherFixture();
        $teacherHeaders = $this->mobileHeaders($teacher['tenant']);
        $today = $this->get('/mobile/education/academic/teacher/lessons/today', [
            'campus_id' => $teacher['campus_id'],
            'date' => '2026-06-12',
        ], $teacherHeaders);
        $submitted = $this->post('/mobile/education/academic/teacher/lessons/' . $teacher['lesson']->id . '/attendance', [
            'records' => [[
                'lesson_student_id' => $teacher['lessonStudent']->id,
                'attendance_status' => 'present',
                'consume_policy' => 'consume',
                'consumed_units' => '1.00',
            ]],
        ], $teacherHeaders);
        $attendanceResult = $this->get('/mobile/education/academic/teacher/lessons/' . $teacher['lesson']->id . '/attendance-result', [], $teacherHeaders);
        $teacherDeniedGuardian = $this->get('/mobile/education/academic/guardian/students', [], $teacherHeaders);

        self::assertSame(ResultCode::SUCCESS->value, $today['code']);
        self::assertSame((int) $teacher['lesson']->id, $today['data']['list'][0]['id']);
        self::assertSame(ResultCode::SUCCESS->value, $submitted['code']);
        self::assertSame('1.00', $submitted['data']['total_consumed_units']);
        self::assertSame(ResultCode::SUCCESS->value, $attendanceResult['code']);
        self::assertSame(1, $attendanceResult['data']['attendance_count']);
        self::assertSame(ResultCode::FORBIDDEN->value, $teacherDeniedGuardian['code']);

        Context::destroy(MobileContextService::CONTEXT_KEY);
        $this->clearCurrentUserCache();

        $guardian = $this->guardianFixture('v1_mobile_guardian');
        $receipt = $this->noticeReceipt($guardian);
        $guardianHeaders = $this->mobileHeaders($guardian['tenant']);
        $students = $this->get('/mobile/education/academic/guardian/students', [], $guardianHeaders);
        $accounts = $this->get('/mobile/education/academic/guardian/students/' . $guardian['student']->id . '/accounts', ['status' => 'active'], $guardianHeaders);
        $consumptions = $this->get('/mobile/education/academic/guardian/students/' . $guardian['student']->id . '/consumptions', ['account_id' => $guardian['account']->id], $guardianHeaders);
        $notices = $this->get('/mobile/education/academic/guardian/notices/page', ['status' => 'unread'], $guardianHeaders);
        $leave = $this->post('/mobile/education/academic/guardian/leave-requests', [
            'lesson_student_id' => $guardian['lesson_student']->id,
            'leave_type' => 'sick',
            'reason' => 'Fever',
            'makeup_required' => true,
        ], $guardianHeaders);
        $guardianDeniedTeacher = $this->get('/mobile/education/academic/teacher/lessons/today', [
            'campus_id' => $guardian['campus_id'],
            'date' => '2026-06-12',
        ], $guardianHeaders);

        self::assertSame(ResultCode::SUCCESS->value, $students['code']);
        self::assertSame((int) $guardian['student']->id, $students['data']['list'][0]['id']);
        self::assertSame(ResultCode::SUCCESS->value, $accounts['code']);
        self::assertSame((int) $guardian['account']->id, $accounts['data']['list'][0]['id']);
        self::assertSame(ResultCode::SUCCESS->value, $consumptions['code']);
        self::assertSame((int) $guardian['consumption']->id, $consumptions['data']['list'][0]['id']);
        self::assertSame(ResultCode::SUCCESS->value, $notices['code']);
        self::assertSame((int) $receipt->id, $notices['data']['list'][0]['receipt_id']);
        self::assertSame(ResultCode::SUCCESS->value, $leave['code']);
        self::assertSame('pending', $leave['data']['status']);
        self::assertSame(ResultCode::FORBIDDEN->value, $guardianDeniedTeacher['code']);
    }

    private function teacherFixture(): array
    {
        $tenant = $this->tenant('v1_mobile_teacher');
        $campus = $this->campus($tenant, 'main');
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenant->id . ':' . $this->user->id,
            'tenant_id' => $tenant->id,
            'user_id' => $this->user->id,
            'role_code' => 'teacher',
            'display_name' => 'Teacher Mobile',
            'status' => 'enabled',
            'current_campus_id' => $campus->id,
        ]);
        EducationUserCampusScope::query()->create([
            'tenant_id' => $tenant->id,
            'user_profile_id' => $profile->id,
            'user_id' => $this->user->id,
            'campus_id' => $campus->id,
        ]);
        $teacher = EducationTeacher::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'user_profile_id' => $profile->id,
            'teacher_no' => 'T-V1-MOBILE',
            'name' => 'Teacher Mobile',
            'status' => 'enabled',
        ]);
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'ART-V1-MOBILE', 'name' => 'Art Basics', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'CLS-V1-MOBILE', 'name' => 'Sunday Art', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S-V1-MOBILE', 'name' => 'Student Zhang', 'status' => 'enabled']);
        $account = EducationStudentCourseAccount::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_id' => $student->id, 'course_id' => $course->id, 'purchased_units' => '10.00', 'bonus_units' => '0.00', 'consumed_units' => '0.00', 'adjusted_units' => '0.00', 'refunded_units' => '0.00', 'frozen_units' => '0.00', 'available_units' => '10.00', 'status' => 'active']);
        $lesson = EducationLesson::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_no' => uniqid('L', false), 'class_id' => $class->id, 'course_id' => $course->id, 'teacher_id' => $teacher->id, 'classroom_id' => 201, 'title' => 'Drawing', 'start_at' => '2026-06-12 09:00:00', 'end_at' => '2026-06-12 10:00:00', 'duration_minutes' => 60, 'lesson_units' => '1.00', 'student_count' => 1, 'status' => 'scheduled', 'source_type' => 'manual', 'class_name_snapshot' => 'Sunday Art', 'course_name_snapshot' => 'Art Basics', 'teacher_name_snapshot' => 'Teacher Mobile']);
        $lessonStudent = EducationLessonStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_id' => $lesson->id, 'class_id' => $class->id, 'course_id' => $course->id, 'student_id' => $student->id, 'account_id' => $account->id, 'student_name_snapshot' => $student->name, 'student_no_snapshot' => $student->student_no, 'lesson_units' => '1.00', 'status' => 'planned']);

        return [
            'tenant' => $tenant,
            'campus_id' => (int) $campus->id,
            'lesson' => $lesson,
            'lessonStudent' => $lessonStudent,
        ];
    }

    protected function mobileHeaders(EducationTenant $tenant): array
    {
        return $this->tenantHeaders($tenant, ['X-Client-Type' => 'wechat_miniprogram']);
    }
}
