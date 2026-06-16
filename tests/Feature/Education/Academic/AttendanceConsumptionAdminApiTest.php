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
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;

/**
 * @internal
 * @coversNothing
 */
final class AttendanceConsumptionAdminApiTest extends ProfileRecordAdminCase
{
    public function testAttendanceLessonPageAndDetailReturnMineadminShape(): void
    {
        $this->grantPermissions('education:academic:attendance:lesson-page', 'education:academic:attendance:detail');
        $fixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant']);

        $page = $this->get('/admin/education/academic/attendance/lessons/page', [
            'token' => $this->token,
            'page' => 1,
            'pageSize' => 20,
            'campus_id' => $fixture['campus']->id,
        ], ['X-Tenant-Id' => (string) $fixture['tenant']->id]);

        self::assertSame(ResultCode::SUCCESS->value, $page['code']);
        self::assertSame(1, $page['data']['total']);

        $detail = $this->get('/admin/education/academic/attendance/lessons/' . $fixture['lesson']->id, ['token' => $this->token], ['X-Tenant-Id' => (string) $fixture['tenant']->id]);
        self::assertSame(ResultCode::SUCCESS->value, $detail['code']);
        self::assertSame((int) $fixture['lesson']->id, (int) $detail['data']['lesson']['id']);
    }

    public function testAttendanceSubmitReturnsSummaryAndDuplicatePayloadIsIdempotent(): void
    {
        $this->grantPermissions('education:academic:attendance:submit');
        $fixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant']);
        $payload = [
            'submitted_at' => '2026-06-16 10:00:00',
            'records' => [[
                'lesson_student_id' => $fixture['lessonStudent']->id,
                'attendance_status' => 'present',
                'consume_policy' => 'consume',
                'consumed_units' => '1.00',
            ]],
        ];

        $first = $this->post('/admin/education/academic/attendance/lessons/' . $fixture['lesson']->id . '/submit', $payload, $this->tenantHeaders($fixture['tenant']));
        $second = $this->post('/admin/education/academic/attendance/lessons/' . $fixture['lesson']->id . '/submit', $payload, $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $first['code']);
        self::assertSame(1, $first['data']['attendance_count']);
        self::assertSame('1.00', $first['data']['total_consumed_units']);
        self::assertSame(ResultCode::SUCCESS->value, $second['code']);
        self::assertSame(1, EducationLessonConsumption::query()->count());
    }

    public function testConsumptionPageDetailAndRollbackReturnExpectedShape(): void
    {
        $this->grantPermissions('education:academic:attendance:submit', 'education:academic:consumption:page', 'education:academic:consumption:detail', 'education:academic:consumption:rollback');
        $fixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant']);
        $this->submitAttendance($fixture);
        $consumption = EducationLessonConsumption::query()->where('source_type', 'attendance')->first();

        $page = $this->get('/admin/education/academic/consumptions/page', [
            'token' => $this->token,
            'page' => 1,
            'pageSize' => 20,
            'account_id' => $fixture['account']->id,
        ], ['X-Tenant-Id' => (string) $fixture['tenant']->id]);
        $detail = $this->get('/admin/education/academic/consumptions/' . $consumption->id, ['token' => $this->token], ['X-Tenant-Id' => (string) $fixture['tenant']->id]);
        $rollback = $this->post('/admin/education/academic/consumptions/' . $consumption->id . '/rollback', ['reason' => 'wrong attendance'], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(1, $page['data']['total']);
        self::assertSame((int) $consumption->id, (int) $detail['data']['id']);
        self::assertSame('reversed', $rollback['data']['original']['status']);
        self::assertSame('rollback', $rollback['data']['rollback']['source_type']);
    }

    public function testAccountAdjustmentCreateAndRollbackReturnExpectedShape(): void
    {
        $this->grantPermissions('education:academic:account-adjustment:create', 'education:academic:account-adjustment:page', 'education:academic:account-adjustment:detail', 'education:academic:account-adjustment:rollback');
        $fixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant']);

        $created = $this->post('/admin/education/academic/account-adjustments', [
            'account_id' => $fixture['account']->id,
            'units' => '1.00',
            'reason' => 'material fee',
        ], $this->tenantHeaders($fixture['tenant']));
        $page = $this->get('/admin/education/academic/account-adjustments/page', [
            'token' => $this->token,
            'page' => 1,
            'pageSize' => 20,
            'account_id' => $fixture['account']->id,
        ], ['X-Tenant-Id' => (string) $fixture['tenant']->id]);
        $detail = $this->get('/admin/education/academic/account-adjustments/' . $created['data']['adjustment']['id'], ['token' => $this->token], ['X-Tenant-Id' => (string) $fixture['tenant']->id]);
        $rollback = $this->post('/admin/education/academic/account-adjustments/' . $created['data']['adjustment']['id'] . '/rollback', ['reason' => 'wrong account'], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $created['code']);
        self::assertSame(1, $page['data']['total']);
        self::assertSame((int) $created['data']['adjustment']['id'], (int) $detail['data']['id']);
        self::assertSame('rolled_back', $rollback['data']['original']['status']);
    }

    public function testValidationAndBusinessFailuresMatchCatalog(): void
    {
        $this->grantPermissions('education:academic:attendance:submit', 'education:academic:account-adjustment:create');
        $fixture = $this->fixture('0.50');
        $this->createTenantProfile($fixture['tenant']);

        $validation = $this->post('/admin/education/academic/attendance/lessons/' . $fixture['lesson']->id . '/submit', [], $this->tenantHeaders($fixture['tenant']));
        $business = $this->post('/admin/education/academic/attendance/lessons/' . $fixture['lesson']->id . '/submit', [
            'records' => [[
                'lesson_student_id' => $fixture['lessonStudent']->id,
                'attendance_status' => 'present',
                'consume_policy' => 'consume',
                'consumed_units' => '1.00',
            ]],
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $validation['code']);
        self::assertSame(ResultCode::CONFLICT->value, $business['code']);
    }

    private function submitAttendance(array $fixture): void
    {
        $this->post('/admin/education/academic/attendance/lessons/' . $fixture['lesson']->id . '/submit', [
            'records' => [[
                'lesson_student_id' => $fixture['lessonStudent']->id,
                'attendance_status' => 'present',
                'consume_policy' => 'consume',
                'consumed_units' => '1.00',
            ]],
        ], $this->tenantHeaders($fixture['tenant']));
    }

    private function fixture(string $availableUnits = '10.00'): array
    {
        $tenant = $this->tenant();
        $campus = $this->campus($tenant);
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'ART-001', 'name' => 'Art Basics', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'C-001', 'name' => 'Sunday Art', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S001', 'name' => 'Student', 'status' => 'enabled']);
        $account = EducationStudentCourseAccount::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_id' => $student->id, 'course_id' => $course->id, 'purchased_units' => '10.00', 'bonus_units' => '0.00', 'consumed_units' => '0.00', 'adjusted_units' => '0.00', 'refunded_units' => '0.00', 'frozen_units' => '0.00', 'available_units' => $availableUnits, 'status' => 'active']);
        $lesson = EducationLesson::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_no' => uniqid('L'), 'class_id' => $class->id, 'course_id' => $course->id, 'teacher_id' => 501, 'title' => 'Drawing', 'start_at' => '2026-06-16 09:00:00', 'end_at' => '2026-06-16 10:00:00', 'duration_minutes' => 60, 'lesson_units' => '1.00', 'student_count' => 1, 'status' => 'scheduled', 'source_type' => 'manual', 'class_name_snapshot' => 'Sunday Art', 'course_name_snapshot' => 'Art Basics', 'teacher_name_snapshot' => 'Teacher']);
        $lessonStudent = EducationLessonStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_id' => $lesson->id, 'class_id' => $class->id, 'course_id' => $course->id, 'student_id' => $student->id, 'account_id' => $account->id, 'student_name_snapshot' => $student->name, 'student_no_snapshot' => $student->student_no, 'lesson_units' => '1.00', 'status' => 'planned']);

        return compact('tenant', 'campus', 'course', 'class', 'student', 'account', 'lesson', 'lessonStudent');
    }
}
