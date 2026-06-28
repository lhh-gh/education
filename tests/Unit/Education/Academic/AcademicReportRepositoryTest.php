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
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\AcademicReportRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

/**
 * @internal
 * @coversNothing
 */
final class AcademicReportRepositoryTest extends AcademicTestCase
{
    public function testPlatformContextCampusFiltersDashboardWithoutLocalFilters(): void
    {
        $fixture = $this->fixture('report_platform_scope');
        $otherCampus = $this->campus($fixture['tenant'], 'west');
        $this->student($fixture, 'S001');
        EducationStudent::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $otherCampus->id,
            'student_no' => 'S002',
            'name' => 'Other Campus',
            'status' => 'enabled',
        ]);
        $this->lesson($fixture, 'scheduled');
        EducationLesson::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $otherCampus->id,
            'lesson_no' => 'LES-OTHER',
            'class_id' => $fixture['class_id'],
            'course_id' => $fixture['course_id'],
            'teacher_id' => 1,
            'title' => 'Other campus lesson',
            'start_at' => '2026-06-12 10:00:00',
            'end_at' => '2026-06-12 11:00:00',
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 1,
            'status' => 'scheduled',
            'source_type' => 'manual',
            'class_name_snapshot' => 'Class',
            'course_name_snapshot' => 'Art',
            'teacher_name_snapshot' => 'Teacher',
        ]);

        $dashboard = make(AcademicReportRepository::class)->dashboard([
            'start_at' => '2026-06-01 00:00:00',
            'end_at' => '2026-06-30 23:59:59',
        ], new EducationUserContext(
            userId: 1,
            tenantId: $fixture['tenant_id'],
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: $fixture['campus_id']
        ));

        self::assertSame(1, $dashboard['metrics']['active_student_count']);
        self::assertSame(1, $dashboard['metrics']['scheduled_lesson_count']);
    }

    public function testDashboardCountsUseTenantAndCampusScope(): void
    {
        $fixture = $this->fixture('report_dashboard');
        $otherCampus = $this->campus($fixture['tenant'], 'west');
        $this->student($fixture, 'S001');
        EducationStudent::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $otherCampus->id, 'student_no' => 'S002', 'name' => 'Other Campus', 'status' => 'enabled']);
        $this->lesson($fixture, 'scheduled');
        EducationLesson::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $otherCampus->id,
            'lesson_no' => 'LES-OTHER',
            'class_id' => $fixture['class_id'],
            'course_id' => $fixture['course_id'],
            'teacher_id' => 1,
            'title' => 'Other campus lesson',
            'start_at' => '2026-06-12 10:00:00',
            'end_at' => '2026-06-12 11:00:00',
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 1,
            'status' => 'scheduled',
            'source_type' => 'manual',
            'class_name_snapshot' => 'Class',
            'course_name_snapshot' => 'Art',
            'teacher_name_snapshot' => 'Teacher',
        ]);

        $dashboard = make(AcademicReportRepository::class)->dashboard([
            'campus_id' => $fixture['campus_id'],
            'start_at' => '2026-06-01 00:00:00',
            'end_at' => '2026-06-30 23:59:59',
        ], $this->context($fixture['tenant_id'], EducationRoleCode::Principal, [$fixture['campus_id']]));

        self::assertSame(1, $dashboard['metrics']['active_student_count']);
        self::assertSame(1, $dashboard['metrics']['scheduled_lesson_count']);
    }

    public function testConsumptionSummaryUsesLedgerRows(): void
    {
        $fixture = $this->fixture('report_consumption');
        $student = $this->student($fixture, 'S101');
        $account = $this->account($fixture, (int) $student->id, '20.00');
        $this->consumption($fixture, (int) $student->id, (int) $account->id, 'decrease', '3.00', 'active', 'attendance');
        $this->consumption($fixture, (int) $student->id, (int) $account->id, 'increase', '1.00', 'active', 'rollback');
        $this->consumption($fixture, (int) $student->id, (int) $account->id, 'decrease', '2.00', 'reversed', 'attendance');

        $summary = make(AcademicReportRepository::class)->consumptionSummary([
            'start_at' => '2026-06-01 00:00:00',
            'end_at' => '2026-06-30 23:59:59',
        ], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]));

        self::assertSame('3.00', $summary['decrease_units']);
        self::assertSame('1.00', $summary['rollback_units']);
        self::assertSame('2.00', $summary['net_units']);
        self::assertSame(2, $summary['active_row_count']);
        self::assertSame(1, $summary['reversed_row_count']);
    }

    public function testAccountBalanceLevelMapping(): void
    {
        $fixture = $this->fixture('report_balance');
        $this->account($fixture, (int) $this->student($fixture, 'S201')->id, '0.00');
        $this->account($fixture, (int) $this->student($fixture, 'S202')->id, '2.00');
        $this->account($fixture, (int) $this->student($fixture, 'S203')->id, '12.00');
        $this->account($fixture, (int) $this->student($fixture, 'S204')->id, '10.00', Carbon::now()->subDay()->toDateTimeString());
        $this->account($fixture, (int) $this->student($fixture, 'S205')->id, '10.00', Carbon::now()->addDays(10)->toDateTimeString());

        $rows = make(AcademicReportRepository::class)->accountBalanceRows(
            [],
            1,
            20,
            $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']])
        );

        self::assertEqualsCanonicalizing(
            ['expired', 'expiring_soon', 'low', 'normal', 'zero'],
            array_column($rows['list'], 'balance_level')
        );
    }

    private function fixture(string $code): array
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant, 'main');
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => mb_strtoupper($code), 'name' => 'Art', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'CLS-' . mb_strtoupper($code), 'name' => 'Class', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);

        return ['tenant' => $tenant, 'tenant_id' => (int) $tenant->id, 'campus_id' => (int) $campus->id, 'course_id' => (int) $course->id, 'class_id' => (int) $class->id];
    }

    private function student(array $fixture, string $studentNo): EducationStudent
    {
        return EducationStudent::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'student_no' => $studentNo, 'name' => 'Student ' . $studentNo, 'status' => 'enabled']);
    }

    private function lesson(array $fixture, string $status): EducationLesson
    {
        return EducationLesson::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'lesson_no' => 'LES-' . $status,
            'class_id' => $fixture['class_id'],
            'course_id' => $fixture['course_id'],
            'teacher_id' => 1,
            'title' => 'Lesson ' . $status,
            'start_at' => '2026-06-12 10:00:00',
            'end_at' => '2026-06-12 11:00:00',
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 1,
            'status' => $status,
            'source_type' => 'manual',
            'class_name_snapshot' => 'Class',
            'course_name_snapshot' => 'Art',
            'teacher_name_snapshot' => 'Teacher',
        ]);
    }

    private function account(array $fixture, int $studentId, string $availableUnits, ?string $expiresAt = null): EducationStudentCourseAccount
    {
        $payload = [
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'student_id' => $studentId,
            'course_id' => $fixture['course_id'],
            'purchased_units' => '20.00',
            'bonus_units' => '0.00',
            'consumed_units' => '0.00',
            'adjusted_units' => '0.00',
            'refunded_units' => '0.00',
            'frozen_units' => '0.00',
            'available_units' => $availableUnits,
            'status' => 'active',
            'opened_at' => '2026-06-01 00:00:00',
        ];
        if ($expiresAt !== null) {
            $payload['expires_at'] = $expiresAt;
        }

        return EducationStudentCourseAccount::query()->create($payload);
    }

    private function consumption(array $fixture, int $studentId, int $accountId, string $direction, string $units, string $status, string $sourceType): EducationLessonConsumption
    {
        return EducationLessonConsumption::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'consumption_no' => uniqid('CON-', false),
            'account_id' => $accountId,
            'student_id' => $studentId,
            'course_id' => $fixture['course_id'],
            'lesson_id' => 0,
            'lesson_student_id' => 0,
            'attendance_id' => random_int(100000, 999999),
            'source_type' => $sourceType,
            'direction' => $direction,
            'units' => $units,
            'before_available_units' => '20.00',
            'after_available_units' => '17.00',
            'before_consumed_units' => '0.00',
            'after_consumed_units' => $units,
            'status' => $status,
            'created_at' => '2026-06-12 10:00:00',
        ]);
    }
}
