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
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonAttendance;
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Service\Education\Academic\AttendanceService;

/**
 * @internal
 * @coversNothing
 */
final class AttendanceServiceTest extends AcademicTestCase
{
    public function testSubmitAttendanceCreatesAttendanceAndConsumptionTransactionally(): void
    {
        $fixture = $this->fixture();

        $result = make(AttendanceService::class)->submit((int) $fixture['lesson']->id, [[
            'lesson_student_id' => $fixture['lessonStudent']->id,
            'attendance_status' => 'present',
            'consume_policy' => 'consume',
            'consumed_units' => '1.50',
        ]], '2026-06-16 10:00:00', $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        self::assertSame(1, $result['attendance_count']);
        self::assertSame(1, $result['consumed_count']);
        self::assertSame('1.50', $result['total_consumed_units']);
        self::assertSame('completed', $fixture['lesson']->refresh()->status);
        self::assertSame('8.50', $fixture['account']->refresh()->available_units);
        self::assertSame('1.50', $fixture['account']->refresh()->consumed_units);
    }

    public function testSubmitAttendanceIsIdempotentForSamePayload(): void
    {
        $fixture = $this->fixture();
        $payload = [[
            'lesson_student_id' => $fixture['lessonStudent']->id,
            'attendance_status' => 'present',
            'consume_policy' => 'consume',
            'consumed_units' => '1.00',
        ]];

        make(AttendanceService::class)->submit((int) $fixture['lesson']->id, $payload, null, $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);
        $result = make(AttendanceService::class)->submit((int) $fixture['lesson']->id, $payload, null, $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        self::assertSame(1, $result['attendance_count']);
        self::assertSame(1, EducationLessonConsumption::query()->count());
    }

    public function testSubmitAttendanceRejectsChangedPayloadAfterSubmit(): void
    {
        $fixture = $this->fixture();
        make(AttendanceService::class)->submit((int) $fixture['lesson']->id, [[
            'lesson_student_id' => $fixture['lessonStudent']->id,
            'attendance_status' => 'present',
            'consume_policy' => 'consume',
            'consumed_units' => '1.00',
        ]], null, $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        try {
            make(AttendanceService::class)->submit((int) $fixture['lesson']->id, [[
                'lesson_student_id' => $fixture['lessonStudent']->id,
                'attendance_status' => 'late',
                'consume_policy' => 'consume',
                'consumed_units' => '1.00',
            ]], null, $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);
            self::fail('Expected changed attendance payload to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    public function testSubmitAttendanceRequiresAllLessonStudents(): void
    {
        $fixture = $this->fixture();

        try {
            make(AttendanceService::class)->submit((int) $fixture['lesson']->id, [], null, $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);
            self::fail('Expected missing attendance records to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
        }
    }

    public function testLeaveStatusCannotConsume(): void
    {
        $fixture = $this->fixture();

        try {
            make(AttendanceService::class)->submit((int) $fixture['lesson']->id, [[
                'lesson_student_id' => $fixture['lessonStudent']->id,
                'attendance_status' => 'leave',
                'consume_policy' => 'consume',
                'consumed_units' => '1.00',
            ]], null, $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);
            self::fail('Expected leave consumption to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
        }
    }

    public function testSubmitAttendanceRejectsInsufficientBalance(): void
    {
        $fixture = $this->fixture(availableUnits: '0.50');

        try {
            make(AttendanceService::class)->submit((int) $fixture['lesson']->id, [[
                'lesson_student_id' => $fixture['lessonStudent']->id,
                'attendance_status' => 'present',
                'consume_policy' => 'consume',
                'consumed_units' => '1.00',
            ]], null, $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);
            self::fail('Expected insufficient balance to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
            self::assertSame(0, EducationLessonAttendance::query()->count());
        }
    }

    private function fixture(string $availableUnits = '10.00'): array
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $course = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'status' => 'enabled',
        ]);
        $class = EducationClass::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'code' => 'C-001',
            'name' => 'Sunday Art',
            'class_type' => 'group',
            'lesson_units' => '1.00',
            'status' => 'enabled',
        ]);
        $student = EducationStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_no' => 'S001',
            'name' => 'Student',
            'status' => 'enabled',
        ]);
        $account = EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'purchased_units' => '10.00',
            'bonus_units' => '0.00',
            'consumed_units' => '0.00',
            'adjusted_units' => '0.00',
            'refunded_units' => '0.00',
            'frozen_units' => '0.00',
            'available_units' => $availableUnits,
            'status' => 'active',
        ]);
        $lesson = EducationLesson::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lesson_no' => 'L001',
            'class_id' => $class->id,
            'course_id' => $course->id,
            'teacher_id' => 501,
            'title' => 'Drawing',
            'start_at' => '2026-06-16 09:00:00',
            'end_at' => '2026-06-16 10:00:00',
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 1,
            'status' => 'scheduled',
            'source_type' => 'manual',
            'class_name_snapshot' => 'Sunday Art',
            'course_name_snapshot' => 'Art Basics',
            'teacher_name_snapshot' => 'Teacher',
        ]);
        $lessonStudent = EducationLessonStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lesson_id' => $lesson->id,
            'class_id' => $class->id,
            'course_id' => $course->id,
            'student_id' => $student->id,
            'account_id' => $account->id,
            'student_name_snapshot' => $student->name,
            'student_no_snapshot' => $student->student_no,
            'lesson_units' => '1.50',
            'status' => 'planned',
        ]);

        return [
            'tenant_id' => (int) $tenant->id,
            'campus_id' => (int) $campus->id,
            'lesson' => $lesson,
            'lessonStudent' => $lessonStudent,
            'account' => $account,
        ];
    }
}
