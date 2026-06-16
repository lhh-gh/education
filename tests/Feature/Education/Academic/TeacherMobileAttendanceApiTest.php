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
final class TeacherMobileAttendanceApiTest extends ProfileRecordAdminCase
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

    public function testAttendanceSubmitSuccessContract(): void
    {
        $fixture = $this->fixture();

        $result = $this->post('/mobile/education/academic/teacher/lessons/' . $fixture['lesson']->id . '/attendance', [
            'records' => [[
                'lesson_student_id' => $fixture['lessonStudent']->id,
                'attendance_status' => 'present',
                'consume_policy' => 'consume',
                'consumed_units' => '1.00',
            ]],
        ], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame(1, $result['data']['attendance_count']);
        self::assertSame('1.00', $result['data']['total_consumed_units']);
    }

    public function testAttendanceSubmitDifferentPayloadConflict(): void
    {
        $fixture = $this->fixture();
        $this->submit($fixture, 'present');

        $result = $this->post('/mobile/education/academic/teacher/lessons/' . $fixture['lesson']->id . '/attendance', [
            'records' => [[
                'lesson_student_id' => $fixture['lessonStudent']->id,
                'attendance_status' => 'late',
                'consume_policy' => 'consume',
                'consumed_units' => '1.00',
            ]],
        ], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::CONFLICT->value, $result['code']);
        self::assertSame('attendance already submitted with different payload', $result['message']);
    }

    public function testAttendanceResultNotFound(): void
    {
        $fixture = $this->fixture();

        $result = $this->get('/mobile/education/academic/teacher/lessons/' . $fixture['lesson']->id . '/attendance-result', [], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::NOT_FOUND->value, $result['code']);
        self::assertSame('attendance result not found', $result['message']);
    }

    private function submit(array $fixture, string $status): void
    {
        $this->post('/mobile/education/academic/teacher/lessons/' . $fixture['lesson']->id . '/attendance', [
            'records' => [[
                'lesson_student_id' => $fixture['lessonStudent']->id,
                'attendance_status' => $status,
                'consume_policy' => 'consume',
                'consumed_units' => '1.00',
            ]],
        ], $this->mobileHeaders($fixture['tenant']));
    }

    private function fixture(): array
    {
        $tenant = $this->tenant('teacher_mobile_attendance_api');
        $campus = $this->campus($tenant, 'main');
        $profile = EducationUserProfile::query()->create(['profile_key' => 'tenant:' . $tenant->id . ':' . $this->user->id, 'tenant_id' => $tenant->id, 'user_id' => $this->user->id, 'role_code' => 'teacher', 'display_name' => 'Teacher Mobile', 'status' => 'enabled', 'current_campus_id' => $campus->id]);
        EducationUserCampusScope::query()->create(['tenant_id' => $tenant->id, 'user_profile_id' => $profile->id, 'user_id' => $this->user->id, 'campus_id' => $campus->id]);
        $teacher = EducationTeacher::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'user_profile_id' => $profile->id, 'teacher_no' => 'T-ATT-API', 'name' => 'Teacher Mobile', 'status' => 'enabled']);
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'ART-001', 'name' => 'Art Basics', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'C-001', 'name' => 'Sunday Art', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S001', 'name' => 'Student Zhang', 'status' => 'enabled']);
        $account = EducationStudentCourseAccount::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_id' => $student->id, 'course_id' => $course->id, 'purchased_units' => '10.00', 'bonus_units' => '0.00', 'consumed_units' => '0.00', 'adjusted_units' => '0.00', 'refunded_units' => '0.00', 'frozen_units' => '0.00', 'available_units' => '10.00', 'status' => 'active']);
        $lesson = EducationLesson::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_no' => uniqid('L', false), 'class_id' => $class->id, 'course_id' => $course->id, 'teacher_id' => $teacher->id, 'classroom_id' => 201, 'title' => 'Drawing', 'start_at' => '2026-06-12 09:00:00', 'end_at' => '2026-06-12 10:00:00', 'duration_minutes' => 60, 'lesson_units' => '1.00', 'student_count' => 1, 'status' => 'scheduled', 'source_type' => 'manual', 'class_name_snapshot' => 'Sunday Art', 'course_name_snapshot' => 'Art Basics', 'teacher_name_snapshot' => 'Teacher Mobile']);
        $lessonStudent = EducationLessonStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_id' => $lesson->id, 'class_id' => $class->id, 'course_id' => $course->id, 'student_id' => $student->id, 'account_id' => $account->id, 'student_name_snapshot' => $student->name, 'student_no_snapshot' => $student->student_no, 'lesson_units' => '1.00', 'status' => 'planned']);

        return compact('tenant', 'lesson', 'lessonStudent');
    }

    private function mobileHeaders(EducationTenant $tenant): array
    {
        return $this->tenantHeaders($tenant, ['X-Client-Type' => 'wechat_miniprogram']);
    }
}
