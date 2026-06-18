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
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Repository\Education\Academic\GuardianMobileRepository;

/**
 * @internal
 * @coversNothing
 */
final class GuardianMobileRepositoryTest extends AcademicTestCase
{
    public function testBoundStudentListExcludesOtherGuardianStudents(): void
    {
        $fixture = $this->fixture();
        $visible = $this->student($fixture, 'S001');
        $hidden = $this->student($fixture, 'S002');
        $this->bind($fixture, (int) $visible->id, $fixture['guardian_id']);
        $this->bind($fixture, (int) $hidden->id, $fixture['other_guardian_id']);

        $rows = make(GuardianMobileRepository::class)->listBoundStudents($fixture['tenant_id'], $fixture['guardian_id']);

        self::assertCount(1, $rows);
        self::assertSame((int) $visible->id, (int) $rows[0]['student_id']);
    }

    public function testScheduleExcludesUnboundStudentLessons(): void
    {
        $fixture = $this->fixture();
        $visible = $this->student($fixture, 'S003');
        $hidden = $this->student($fixture, 'S004');
        $this->bind($fixture, (int) $visible->id, $fixture['guardian_id']);
        $visibleLessonStudent = $this->lessonStudent($fixture, (int) $visible->id, '2026-06-12 09:00:00');
        $this->lessonStudent($fixture, (int) $hidden->id, '2026-06-12 10:00:00');

        $result = make(GuardianMobileRepository::class)->pageStudentLessons(
            $fixture['tenant_id'],
            $fixture['guardian_id'],
            (int) $visible->id,
            ['start_at' => '2026-06-01 00:00:00', 'end_at' => '2026-06-30 23:59:59'],
            1,
            20
        );

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visibleLessonStudent->id, (int) $result['list'][0]['id']);
    }

    public function testAccountsAndConsumptionsAreBoundStudentOnly(): void
    {
        $fixture = $this->fixture();
        $student = $this->student($fixture, 'S005');
        $this->bind($fixture, (int) $student->id, $fixture['guardian_id']);
        $account = $this->account($fixture, (int) $student->id);
        $consumption = $this->consumption($fixture, (int) $student->id, (int) $account->id);

        $repository = make(GuardianMobileRepository::class);
        $accounts = $repository->pageStudentAccounts($fixture['tenant_id'], $fixture['guardian_id'], (int) $student->id, [], 1, 20);
        $consumptions = $repository->pageStudentConsumptions($fixture['tenant_id'], $fixture['guardian_id'], (int) $student->id, [], 1, 20);

        self::assertSame((int) $account->id, (int) $accounts['list'][0]['id']);
        self::assertSame((int) $consumption->id, (int) $consumptions['list'][0]['id']);
    }

    public function testCanSubmitLeaveFalseIsReturnedForLeaveChecks(): void
    {
        $fixture = $this->fixture();
        $student = $this->student($fixture, 'S006');
        $this->bind($fixture, (int) $student->id, $fixture['guardian_id'], false);

        self::assertFalse(make(GuardianMobileRepository::class)->canSubmitLeave($fixture['tenant_id'], $fixture['guardian_id'], (int) $student->id));
    }

    private function fixture(): array
    {
        $tenant = $this->tenant('guardian_mobile_repository');
        $campus = $this->campus($tenant, 'main');
        $guardian = EducationGuardian::query()->create(['tenant_id' => $tenant->id, 'name' => 'Guardian A', 'mobile' => '13800000000', 'status' => 'enabled']);
        $otherGuardian = EducationGuardian::query()->create(['tenant_id' => $tenant->id, 'name' => 'Guardian B', 'mobile' => '13800000001', 'status' => 'enabled']);
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'ART-001', 'name' => 'Art Basics', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'C-001', 'name' => 'Sunday Art', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);

        return [
            'tenant_id' => (int) $tenant->id,
            'campus_id' => (int) $campus->id,
            'guardian_id' => (int) $guardian->id,
            'other_guardian_id' => (int) $otherGuardian->id,
            'course_id' => (int) $course->id,
            'class_id' => (int) $class->id,
        ];
    }

    private function student(array $fixture, string $studentNo): EducationStudent
    {
        return EducationStudent::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'student_no' => $studentNo, 'name' => 'Student ' . $studentNo, 'status' => 'enabled']);
    }

    private function bind(array $fixture, int $studentId, int $guardianId, bool $canSubmitLeave = true): EducationStudentGuardian
    {
        return EducationStudentGuardian::query()->create(['tenant_id' => $fixture['tenant_id'], 'student_id' => $studentId, 'guardian_id' => $guardianId, 'relation' => 'mother', 'is_primary' => true, 'can_receive_notice' => true, 'can_submit_leave' => $canSubmitLeave]);
    }

    private function lessonStudent(array $fixture, int $studentId, string $startAt): EducationLessonStudent
    {
        $lesson = EducationLesson::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'lesson_no' => uniqid('L', false), 'class_id' => $fixture['class_id'], 'course_id' => $fixture['course_id'], 'teacher_id' => 1001, 'classroom_id' => 201, 'title' => 'Drawing', 'start_at' => $startAt, 'end_at' => date('Y-m-d H:i:s', strtotime($startAt . ' +1 hour')), 'duration_minutes' => 60, 'lesson_units' => '1.00', 'student_count' => 1, 'status' => 'scheduled', 'source_type' => 'manual', 'class_name_snapshot' => 'Sunday Art', 'course_name_snapshot' => 'Art Basics', 'teacher_name_snapshot' => 'Teacher']);

        return EducationLessonStudent::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'lesson_id' => $lesson->id, 'class_id' => $fixture['class_id'], 'course_id' => $fixture['course_id'], 'student_id' => $studentId, 'account_id' => 2001, 'student_name_snapshot' => 'Student', 'student_no_snapshot' => 'S', 'lesson_units' => '1.00', 'status' => 'planned']);
    }

    private function account(array $fixture, int $studentId): EducationStudentCourseAccount
    {
        return EducationStudentCourseAccount::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'student_id' => $studentId, 'course_id' => $fixture['course_id'], 'purchased_units' => '10.00', 'bonus_units' => '0.00', 'consumed_units' => '1.00', 'adjusted_units' => '0.00', 'refunded_units' => '0.00', 'frozen_units' => '0.00', 'available_units' => '9.00', 'status' => 'active']);
    }

    private function consumption(array $fixture, int $studentId, int $accountId): EducationLessonConsumption
    {
        return EducationLessonConsumption::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'consumption_no' => uniqid('CON', false), 'account_id' => $accountId, 'student_id' => $studentId, 'course_id' => $fixture['course_id'], 'lesson_id' => 3001, 'lesson_student_id' => 4001, 'attendance_id' => 5001, 'source_type' => 'attendance', 'direction' => 'decrease', 'units' => '1.00', 'before_available_units' => '10.00', 'after_available_units' => '9.00', 'before_consumed_units' => '0.00', 'after_consumed_units' => '1.00', 'status' => 'active']);
    }
}
