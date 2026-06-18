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
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationNotice;
use App\Model\Education\Academic\EducationNoticeReceipt;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Academic\GuardianMobileService;

/**
 * @internal
 * @coversNothing
 */
final class GuardianMobileServiceTest extends AcademicTestCase
{
    public function testStudentsAndLessonsUseGuardianBinding(): void
    {
        $fixture = $this->fixture('guardian_mobile_service');
        $lessonStudent = $this->lessonStudent($fixture, (int) $fixture['student']->id);

        $service = make(GuardianMobileService::class);
        $students = $service->students([], $this->context($fixture['tenant_id'], EducationRoleCode::Guardian, [], $fixture['user_id']));
        $lessons = $service->lessons((int) $fixture['student']->id, ['start_at' => '2026-06-01 00:00:00', 'end_at' => '2026-06-30 23:59:59'], $this->context($fixture['tenant_id'], EducationRoleCode::Guardian, [], $fixture['user_id']));

        self::assertSame(1, $students['total']);
        self::assertSame((int) $fixture['student']->id, $students['list'][0]['id']);
        self::assertSame((int) $lessonStudent->id, $lessons['list'][0]['lesson_student_id']);
    }

    public function testNoticeReadIsIdempotent(): void
    {
        $fixture = $this->fixture('guardian_mobile_notice');
        $notice = EducationNotice::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'notice_no' => uniqid('NOT', false), 'notice_type' => 'academic', 'target_type' => 'student', 'target_id' => $fixture['student']->id, 'title' => 'Reminder', 'content' => 'Bring tools.', 'priority' => 'important', 'status' => 'published', 'receipt_count' => 1, 'read_count' => 0]);
        $receipt = EducationNoticeReceipt::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'notice_id' => $notice->id, 'guardian_id' => $fixture['guardian']->id, 'student_id' => $fixture['student']->id, 'relation' => 'mother', 'guardian_name_snapshot' => 'Guardian', 'student_name_snapshot' => 'Student', 'status' => 'unread']);

        $service = make(GuardianMobileService::class);
        $read = $service->readNotice((int) $receipt->id, $this->context($fixture['tenant_id'], EducationRoleCode::Guardian, [], $fixture['user_id']));
        $service->readNotice((int) $receipt->id, $this->context($fixture['tenant_id'], EducationRoleCode::Guardian, [], $fixture['user_id']));

        self::assertSame('read', $read['status']);
        self::assertSame(1, (int) $notice->refresh()->read_count);
    }

    private function fixture(string $code): array
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant, 'main');
        $userId = random_int(10000, 99999);
        EducationUserProfile::query()->create(['profile_key' => 'guardian-' . $userId, 'tenant_id' => $tenant->id, 'user_id' => $userId, 'role_code' => 'guardian', 'display_name' => 'Guardian', 'mobile' => '138' . random_int(10000000, 99999999), 'status' => 'enabled']);
        $profile = EducationUserProfile::query()->where('user_id', $userId)->first();
        $guardian = EducationGuardian::query()->create(['tenant_id' => $tenant->id, 'name' => 'Guardian', 'mobile' => $profile->mobile, 'status' => 'enabled']);
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => mb_strtoupper($code), 'name' => 'Art', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'CLS-' . mb_strtoupper($code), 'name' => 'Class', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S-' . mb_strtoupper($code), 'name' => 'Student', 'status' => 'enabled']);
        EducationStudentGuardian::query()->create(['tenant_id' => $tenant->id, 'student_id' => $student->id, 'guardian_id' => $guardian->id, 'relation' => 'mother', 'is_primary' => true, 'can_receive_notice' => true, 'can_submit_leave' => true]);

        return ['tenant_id' => (int) $tenant->id, 'campus_id' => (int) $campus->id, 'course_id' => (int) $course->id, 'class_id' => (int) $class->id, 'student' => $student, 'guardian' => $guardian, 'user_id' => $userId];
    }

    private function lessonStudent(array $fixture, int $studentId): EducationLessonStudent
    {
        $account = EducationStudentCourseAccount::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'student_id' => $studentId, 'course_id' => $fixture['course_id'], 'purchased_units' => '10.00', 'bonus_units' => '0.00', 'consumed_units' => '0.00', 'adjusted_units' => '0.00', 'refunded_units' => '0.00', 'frozen_units' => '0.00', 'available_units' => '10.00', 'status' => 'active']);
        $lesson = EducationLesson::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'lesson_no' => uniqid('L', false), 'class_id' => $fixture['class_id'], 'course_id' => $fixture['course_id'], 'teacher_id' => 1001, 'classroom_id' => 201, 'title' => 'Art Lesson', 'start_at' => '2026-06-12 09:00:00', 'end_at' => '2026-06-12 10:00:00', 'duration_minutes' => 60, 'lesson_units' => '1.00', 'student_count' => 1, 'status' => 'scheduled', 'source_type' => 'manual', 'class_name_snapshot' => 'Class', 'course_name_snapshot' => 'Art', 'teacher_name_snapshot' => 'Teacher']);

        return EducationLessonStudent::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'lesson_id' => $lesson->id, 'class_id' => $fixture['class_id'], 'course_id' => $fixture['course_id'], 'student_id' => $studentId, 'account_id' => $account->id, 'student_name_snapshot' => 'Student', 'student_no_snapshot' => 'S001', 'lesson_units' => '1.00', 'status' => 'planned']);
    }
}
