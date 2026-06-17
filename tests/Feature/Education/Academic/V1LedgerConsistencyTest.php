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

use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Service\Education\Academic\AcademicAcceptanceService;
use HyperfTests\Unit\Education\Academic\AcademicTestCase;

/**
 * @internal
 * @coversNothing
 */
final class V1LedgerConsistencyTest extends AcademicTestCase
{
    public function testAccountBalanceFormulaMatchesLedger(): void
    {
        $fixture = $this->fixture('ledger_match');
        $account = $fixture['account'];
        $this->consumption($fixture, 'decrease', '3.00', 'active', 'attendance', 1001);

        $ledger = make(AcademicAcceptanceService::class)->assertLedgerConsistency(
            $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]),
            $fixture['campus_id']
        );

        self::assertSame(1, $ledger['account_count']);
        self::assertSame(0, $ledger['mismatch_count']);
        self::assertSame('7.00', $account->refresh()->available_units);
    }

    public function testConsumptionRollbackReconcilesAccountBalance(): void
    {
        $fixture = $this->fixture('ledger_rollback');
        $account = $fixture['account'];
        $this->consumption($fixture, 'decrease', '3.00', 'reversed', 'attendance', 1002);
        $this->consumption($fixture, 'increase', '3.00', 'active', 'rollback', 1003);
        $account->consumed_units = '0.00';
        $account->available_units = '10.00';
        $account->save();

        $ledger = make(AcademicAcceptanceService::class)->assertLedgerConsistency(
            $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]),
            $fixture['campus_id']
        );

        self::assertSame(0, $ledger['mismatch_count']);
    }

    private function fixture(string $code): array
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant, 'main');
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => mb_strtoupper($code), 'name' => 'Art', 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S001', 'name' => 'Student', 'status' => 'enabled']);
        $account = EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'purchased_units' => '10.00',
            'bonus_units' => '0.00',
            'consumed_units' => '3.00',
            'adjusted_units' => '0.00',
            'refunded_units' => '0.00',
            'frozen_units' => '0.00',
            'available_units' => '7.00',
            'status' => 'active',
        ]);

        return [
            'tenant_id' => (int) $tenant->id,
            'campus_id' => (int) $campus->id,
            'course_id' => (int) $course->id,
            'student_id' => (int) $student->id,
            'account' => $account,
            'account_id' => (int) $account->id,
        ];
    }

    private function consumption(array $fixture, string $direction, string $units, string $status, string $sourceType, int $attendanceId): EducationLessonConsumption
    {
        return EducationLessonConsumption::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'consumption_no' => uniqid('CON-', false),
            'account_id' => $fixture['account_id'],
            'student_id' => $fixture['student_id'],
            'course_id' => $fixture['course_id'],
            'lesson_id' => 0,
            'lesson_student_id' => 0,
            'attendance_id' => $attendanceId,
            'source_type' => $sourceType,
            'direction' => $direction,
            'units' => $units,
            'before_available_units' => '10.00',
            'after_available_units' => '7.00',
            'before_consumed_units' => '0.00',
            'after_consumed_units' => $units,
            'status' => $status,
            'created_at' => '2026-06-12 10:00:00',
        ]);
    }
}
