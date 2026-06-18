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

namespace HyperfTests\Unit\Education\Admissions;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Admissions\EducationAdmissionTask;
use App\Model\Education\Admissions\EducationLead;
use App\Model\Education\Admissions\EducationLeadConversionRecord;
use App\Service\Education\Admissions\LeadConversionService;

/**
 * @internal
 * @coversNothing
 */
final class LeadConversionServiceTest extends AdmissionsTestCase
{
    public function testConvertLeadCreatesV1RecordsInTransaction(): void
    {
        $tenant = $this->tenant('adm_convert');
        $campus = $this->campus($tenant);
        $lead = $this->leadFixture($tenant, $campus, ['contact_mobile' => '13900000001']);
        $package = $this->packageFixture($tenant, $campus);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 9100);
        EducationAdmissionTask::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lead_id' => $lead->id,
            'task_type' => 'follow',
            'title' => 'Follow lead',
            'assignee_user_id' => 9100,
            'status' => 'pending',
        ]);

        $result = make(LeadConversionService::class)->convert((int) $lead->id, [
            'student_name' => 'Converted Student',
            'guardian_name' => 'Converted Guardian',
            'lesson_package_id' => (int) $package->id,
            'paid_amount' => '3000.00',
            'enrolled_at' => '2026-06-13',
        ], $context);

        self::assertTrue(EducationStudent::query()->whereKey($result['student_id'])->exists());
        self::assertTrue(EducationGuardian::query()->whereKey($result['guardian_id'])->exists());
        self::assertSame('confirmed', EducationEnrollment::query()->find($result['enrollment_id'])->status);
        self::assertSame('24.00', EducationStudentCourseAccount::query()->find($result['student_course_account_id'])->available_units);
        self::assertSame('converted', EducationLead::query()->find($lead->id)->status);
        self::assertTrue(EducationLeadConversionRecord::query()->where('tenant_id', $tenant->id)->where('lead_id', $lead->id)->exists());
        self::assertSame('done', EducationAdmissionTask::query()->where('lead_id', $lead->id)->first()->status);
    }

    public function testConversionRollsBackWhenPackageDisabled(): void
    {
        $tenant = $this->tenant('adm_convert_rollback');
        $campus = $this->campus($tenant);
        $lead = $this->leadFixture($tenant, $campus, ['contact_mobile' => '13900000002']);
        $package = $this->packageFixture($tenant, $campus, ['status' => 'disabled']);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 9100);

        try {
            make(LeadConversionService::class)->convert((int) $lead->id, [
                'student_name' => 'Should Roll Back',
                'guardian_name' => 'Should Roll Back',
                'lesson_package_id' => (int) $package->id,
                'paid_amount' => '3000.00',
            ], $context);
            self::fail('Expected disabled package to abort conversion.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
        }

        self::assertSame(0, EducationStudent::query()->where('tenant_id', $tenant->id)->count());
        self::assertSame(0, EducationGuardian::query()->where('tenant_id', $tenant->id)->count());
        self::assertSame(0, EducationEnrollment::query()->where('tenant_id', $tenant->id)->count());
        self::assertSame(0, EducationStudentCourseAccount::query()->where('tenant_id', $tenant->id)->count());
        self::assertSame(0, EducationLeadConversionRecord::query()->where('tenant_id', $tenant->id)->count());
        self::assertSame('active', EducationLead::query()->find($lead->id)->status);
    }
}
