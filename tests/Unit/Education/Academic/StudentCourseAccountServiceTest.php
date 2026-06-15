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
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationLessonPackage;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Service\Education\Academic\EnrollmentService;
use App\Service\Education\Academic\StudentCourseAccountService;

/**
 * @internal
 * @coversNothing
 */
final class StudentCourseAccountServiceTest extends AcademicTestCase
{
    public function testChangeStatusToClosedRequiresZeroAvailableUnits(): void
    {
        [$tenantId, $campusId, $student, $course] = $this->studentCourseFixture();
        $account = EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'available_units' => '1.00',
            'status' => 'active',
        ]);

        try {
            make(StudentCourseAccountService::class)->changeStatus((int) $account->id, 'closed', $this->context($tenantId), 901);
            self::fail('Expected closing account with available units to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    public function testAccountBalanceInvariantIsPreservedAfterEnrollmentAndCancel(): void
    {
        [$tenantId, $campusId, $student, $course] = $this->studentCourseFixture();
        $package = EducationLessonPackage::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'course_id' => $course->id,
            'code' => 'ART-24',
            'name' => '24 Lessons',
            'lesson_units' => '20.00',
            'bonus_units' => '4.00',
            'total_units' => '24.00',
            'list_price' => '3600.00',
            'sale_price' => '3000.00',
            'status' => 'enabled',
        ]);
        $service = make(EnrollmentService::class);
        $created = $service->create([
            'campus_id' => $campusId,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'lesson_package_id' => $package->id,
        ], $this->context($tenantId), 901);
        $cancelled = $service->cancel((int) $created['enrollment']->id, 'Wrong package', $this->context($tenantId), 902);

        $account = $cancelled['account'];
        $available = (float) $account->purchased_units
            + (float) $account->bonus_units
            + (float) $account->adjusted_units
            - (float) $account->consumed_units
            - (float) $account->refunded_units
            - (float) $account->frozen_units;

        self::assertSame(number_format($available, 2, '.', ''), $account->available_units);
    }

    /**
     * @return array{int, int, EducationStudent, EducationCourse}
     */
    private function studentCourseFixture(): array
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $student = EducationStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_no' => 'S001',
            'name' => 'Student Zhang',
            'status' => 'enabled',
        ]);
        $course = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'status' => 'enabled',
        ]);

        return [(int) $tenant->id, (int) $campus->id, $student, $course];
    }
}
