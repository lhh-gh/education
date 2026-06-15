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

use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Repository\Education\Academic\StudentCourseAccountRepository;

/**
 * @internal
 * @coversNothing
 */
final class StudentCourseAccountRepositoryTest extends AcademicTestCase
{
    public function testFindByStudentCourseForUpdateReturnsAccount(): void
    {
        [$tenantId, $campusId, $studentId, $courseId] = $this->accountFixture();
        $account = EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'student_id' => $studentId,
            'course_id' => $courseId,
            'available_units' => '12.00',
            'status' => 'active',
        ]);

        $found = make(StudentCourseAccountRepository::class)
            ->findByStudentCourseForUpdate($tenantId, $campusId, $studentId, $courseId);

        self::assertSame((int) $account->id, (int) $found?->id);
    }

    public function testLedgerReturnsEnrollmentAndCancelRows(): void
    {
        [$tenantId, $campusId, $studentId, $courseId] = $this->accountFixture();
        $account = EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'student_id' => $studentId,
            'course_id' => $courseId,
            'available_units' => '0.00',
            'refunded_units' => '24.00',
            'status' => 'active',
        ]);
        EducationEnrollment::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'enrollment_no' => 'ENR-001',
            'student_id' => $studentId,
            'course_id' => $courseId,
            'lesson_package_id' => 401,
            'account_id' => $account->id,
            'student_name_snapshot' => 'Student',
            'course_name_snapshot' => 'Course',
            'package_name_snapshot' => 'Package',
            'package_lesson_units' => '20.00',
            'package_bonus_units' => '4.00',
            'total_units' => '24.00',
            'list_price' => '3600.00',
            'deal_amount' => '3000.00',
            'status' => 'cancelled',
            'confirmed_at' => '2026-06-10 10:00:00',
            'materialized_at' => '2026-06-10 10:00:00',
            'cancelled_at' => '2026-06-11 10:00:00',
        ]);

        $ledger = make(StudentCourseAccountRepository::class)->ledger(
            (int) $account->id,
            ['page' => 1, 'pageSize' => 20],
            $this->context($tenantId)
        );

        self::assertSame(2, $ledger['total']);
        self::assertSame(['enrollment_cancel', 'enrollment'], array_column($ledger['list'], 'source_type'));
    }

    /**
     * @return array{int, int, int, int}
     */
    private function accountFixture(): array
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $student = EducationStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_no' => 'S001',
            'name' => 'Student',
            'status' => 'enabled',
        ]);
        $course = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'status' => 'enabled',
        ]);

        return [(int) $tenant->id, (int) $campus->id, (int) $student->id, (int) $course->id];
    }
}
