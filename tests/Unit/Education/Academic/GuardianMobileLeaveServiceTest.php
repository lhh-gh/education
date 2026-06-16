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
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Academic\GuardianMobileLeaveService;

/**
 * @internal
 * @coversNothing
 */
final class GuardianMobileLeaveServiceTest extends AcademicTestCase
{
    public function testCreateGuardianLeaveAndRejectDuplicate(): void
    {
        $fixture = $this->fixture();
        $service = make(GuardianMobileLeaveService::class);
        $payload = ['lesson_student_id' => $fixture['lesson_student_id'], 'leave_type' => 'sick', 'reason' => 'Fever', 'makeup_required' => true];

        $created = $service->create($payload, $this->context($fixture['tenant_id'], EducationRoleCode::Guardian, [], $fixture['user_id']));

        self::assertSame('guardian', $created['source']);
        self::assertSame('pending', $created['status']);

        try {
            $service->create($payload, $this->context($fixture['tenant_id'], EducationRoleCode::Guardian, [], $fixture['user_id']));
            self::fail('Expected duplicate leave to be rejected.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    private function fixture(): array
    {
        $tenant = $this->tenant('guardian_mobile_leave_service');
        $campus = $this->campus($tenant, 'main');
        $userId = random_int(10000, 99999);
        $mobile = '139' . random_int(10000000, 99999999);
        EducationUserProfile::query()->create(['profile_key' => 'guardian-leave-' . $userId, 'tenant_id' => $tenant->id, 'user_id' => $userId, 'role_code' => 'guardian', 'display_name' => 'Guardian', 'mobile' => $mobile, 'status' => 'enabled']);
        $guardian = EducationGuardian::query()->create(['tenant_id' => $tenant->id, 'name' => 'Guardian', 'mobile' => $mobile, 'status' => 'enabled']);
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'ART-GL', 'name' => 'Art', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'CLS-GL', 'name' => 'Class', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S-GL', 'name' => 'Student', 'status' => 'enabled']);
        EducationStudentGuardian::query()->create(['tenant_id' => $tenant->id, 'student_id' => $student->id, 'guardian_id' => $guardian->id, 'relation' => 'mother', 'is_primary' => true, 'can_receive_notice' => true, 'can_submit_leave' => true]);
        $account = EducationStudentCourseAccount::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_id' => $student->id, 'course_id' => $course->id, 'purchased_units' => '10.00', 'bonus_units' => '0.00', 'consumed_units' => '0.00', 'adjusted_units' => '0.00', 'refunded_units' => '0.00', 'frozen_units' => '0.00', 'available_units' => '10.00', 'status' => 'active']);
        $lesson = EducationLesson::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_no' => uniqid('L', false), 'class_id' => $class->id, 'course_id' => $course->id, 'teacher_id' => 1001, 'classroom_id' => 201, 'title' => 'Art Lesson', 'start_at' => '2026-06-12 09:00:00', 'end_at' => '2026-06-12 10:00:00', 'duration_minutes' => 60, 'lesson_units' => '1.00', 'student_count' => 1, 'status' => 'scheduled', 'source_type' => 'manual', 'class_name_snapshot' => 'Class', 'course_name_snapshot' => 'Art', 'teacher_name_snapshot' => 'Teacher']);
        $lessonStudent = EducationLessonStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_id' => $lesson->id, 'class_id' => $class->id, 'course_id' => $course->id, 'student_id' => $student->id, 'account_id' => $account->id, 'student_name_snapshot' => 'Student', 'student_no_snapshot' => 'S001', 'lesson_units' => '1.00', 'status' => 'planned']);

        return ['tenant_id' => (int) $tenant->id, 'user_id' => $userId, 'lesson_student_id' => (int) $lessonStudent->id];
    }
}
