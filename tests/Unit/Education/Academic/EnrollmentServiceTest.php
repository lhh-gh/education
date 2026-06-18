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
use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationLessonPackage;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Foundation\EducationAuditLog;
use App\Model\Education\Foundation\EducationFeatureFlag;
use App\Service\Education\Academic\EnrollmentService;

/**
 * @internal
 * @coversNothing
 */
final class EnrollmentServiceTest extends AcademicTestCase
{
    public function testCreateEnrollmentCreatesNewAccountTransactionally(): void
    {
        [$tenantId, $campusId, $student, $course, $package] = $this->enrollmentFixture();

        $result = make(EnrollmentService::class)->create($this->createPayload($campusId, $student, $course, $package), $this->context($tenantId), 901);

        self::assertSame('confirmed', $result['enrollment']->status);
        self::assertInstanceOf(EducationStudentCourseAccount::class, $result['account']);
        self::assertSame('24.00', $result['account']->available_units);
        self::assertTrue(EducationAuditLog::query()->where('action', 'education.academic.enrollment.confirmed')->exists());
    }

    public function testCreateEnrollmentIncrementsExistingAccount(): void
    {
        [$tenantId, $campusId, $student, $course, $package] = $this->enrollmentFixture();
        $service = make(EnrollmentService::class);

        $first = $service->create($this->createPayload($campusId, $student, $course, $package), $this->context($tenantId), 901);
        $second = $service->create($this->createPayload($campusId, $student, $course, $package), $this->context($tenantId), 902);

        self::assertSame((int) $first['account']->id, (int) $second['account']->id);
        self::assertSame('48.00', $second['account']->available_units);
        self::assertSame((int) $second['enrollment']->id, (int) $second['account']->last_enrollment_id);
    }

    public function testCreateWithFinanceGateOnLeavesEnrollmentPending(): void
    {
        [$tenantId, $campusId, $student, $course, $package] = $this->enrollmentFixture(financeGate: true);

        $result = make(EnrollmentService::class)->create($this->createPayload($campusId, $student, $course, $package), $this->context($tenantId), 901);

        self::assertSame('pending', $result['enrollment']->status);
        self::assertNull($result['account']);
        self::assertSame(0, EducationStudentCourseAccount::query()->count());
    }

    public function testConfirmIsIdempotentOnRepeatedCalls(): void
    {
        [$tenantId, $campusId, $student, $course, $package] = $this->enrollmentFixture(financeGate: true);
        $service = make(EnrollmentService::class);
        $pending = $service->create($this->createPayload($campusId, $student, $course, $package), $this->context($tenantId), 901);

        $first = $service->confirm((int) $pending['enrollment']->id, $this->context($tenantId), 902);
        $second = $service->confirm((int) $pending['enrollment']->id, $this->context($tenantId), 903);

        self::assertSame('confirmed', $first['enrollment']->status);
        self::assertSame((int) $first['account']->id, (int) $second['account']->id);
        self::assertSame('24.00', $second['account']->available_units);
    }

    public function testCancelPendingEnrollmentChangesNoAccountUnits(): void
    {
        [$tenantId, $campusId, $student, $course, $package] = $this->enrollmentFixture(financeGate: true);
        $service = make(EnrollmentService::class);
        $pending = $service->create($this->createPayload($campusId, $student, $course, $package), $this->context($tenantId), 901);

        $result = $service->cancel((int) $pending['enrollment']->id, 'Wrong package', $this->context($tenantId), 902);

        self::assertSame('cancelled', $result['enrollment']->status);
        self::assertNull($result['account']);
        self::assertSame(0, EducationStudentCourseAccount::query()->count());
    }

    public function testCreateRejectsDisabledPackage(): void
    {
        [$tenantId, $campusId, $student, $course, $package] = $this->enrollmentFixture(packageStatus: 'disabled');

        try {
            make(EnrollmentService::class)->create($this->createPayload($campusId, $student, $course, $package), $this->context($tenantId), 901);
            self::fail('Expected disabled package to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
            self::assertSame((int) $package->id, $exception->getResponse()->data['lesson_package_id']);
        }
    }

    public function testCancelEnrollmentReversesAvailableUnits(): void
    {
        [$tenantId, $campusId, $student, $course, $package] = $this->enrollmentFixture();
        $service = make(EnrollmentService::class);
        $created = $service->create($this->createPayload($campusId, $student, $course, $package), $this->context($tenantId), 901);

        $result = $service->cancel((int) $created['enrollment']->id, 'Wrong package', $this->context($tenantId), 902);

        self::assertSame('cancelled', $result['enrollment']->status);
        self::assertSame('0.00', $result['account']->available_units);
        self::assertSame('24.00', $result['account']->refunded_units);
    }

    public function testCancelRejectsWhenUnitsAlreadyConsumedOrFrozen(): void
    {
        [$tenantId, $campusId, $student, $course, $package] = $this->enrollmentFixture();
        $service = make(EnrollmentService::class);
        $created = $service->create($this->createPayload($campusId, $student, $course, $package), $this->context($tenantId), 901);
        $created['account']->fill(['available_units' => '10.00', 'frozen_units' => '14.00'])->save();

        try {
            $service->cancel((int) $created['enrollment']->id, 'Wrong package', $this->context($tenantId), 902);
            self::fail('Expected insufficient available units to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
            self::assertSame('10.00', $exception->getResponse()->data['available_units']);
        }

        $enrollment = EducationEnrollment::query()->find((int) $created['enrollment']->id);
        self::assertSame('confirmed', $enrollment?->status);
    }

    /**
     * @return array{int, int, EducationStudent, EducationCourse, EducationLessonPackage}
     */
    private function enrollmentFixture(bool $financeGate = false, string $packageStatus = 'enabled'): array
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
        $package = EducationLessonPackage::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'code' => 'ART-24',
            'name' => '24 Lessons',
            'lesson_units' => '20.00',
            'bonus_units' => '4.00',
            'total_units' => '24.00',
            'list_price' => '3600.00',
            'sale_price' => '3000.00',
            'validity_days' => 365,
            'status' => $packageStatus,
        ]);

        if ($financeGate) {
            EducationFeatureFlag::query()->create([
                'owner_type' => 'tenant',
                'tenant_id' => $tenant->id,
                'owner_key' => 'tenant:' . $tenant->id,
                'feature_code' => 'finance_payment_enabled',
                'feature_name' => 'Finance payment',
                'enabled' => true,
                'config' => [],
                'status' => 'enabled',
            ]);
        }

        return [(int) $tenant->id, (int) $campus->id, $student, $course, $package];
    }

    private function createPayload(int $campusId, EducationStudent $student, EducationCourse $course, EducationLessonPackage $package): array
    {
        return [
            'campus_id' => $campusId,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'lesson_package_id' => $package->id,
            'enrolled_at' => '2026-06-10 10:00:00',
            'remark' => 'Spring package',
        ];
    }
}
