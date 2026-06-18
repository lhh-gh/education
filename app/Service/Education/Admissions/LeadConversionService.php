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

namespace App\Service\Education\Admissions;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationLessonPackage;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Admissions\EducationLead;
use App\Model\Education\Admissions\EducationLeadConversionRecord;
use App\Model\Education\Admissions\EducationLeadGuardian;
use App\Model\Education\Admissions\EducationLeadStudent;
use App\Model\Education\Foundation\EducationAuditLog;
use App\Repository\Education\Admissions\AdmissionTaskRepository;
use App\Repository\Education\Admissions\LeadConversionRepository;
use App\Repository\Education\Admissions\LeadRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class LeadConversionService
{
    public function __construct(
        private readonly LeadRepository $leadRepository,
        private readonly LeadConversionRepository $conversionRepository,
        private readonly AdmissionTaskRepository $taskRepository
    ) {}

    public function convert(int $leadId, array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($leadId, $data, $context): array {
            $lead = $this->leadRepository->lockScoped($leadId, $context);
            if (! $lead instanceof EducationLead) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'lead not found in current context', ['lead_id' => $leadId]);
            }
            if ($lead->status === 'converted' || $this->conversionRepository->findByLead($context->tenantId, $leadId) instanceof EducationLeadConversionRecord) {
                throw new BusinessException(ResultCode::CONFLICT, 'lead has already been converted', ['lead_id' => $leadId]);
            }
            if ($lead->campus_id === null) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'lead campus is required for conversion', ['lead_id' => $leadId]);
            }

            $package = $this->enabledPackage((int) $data['lesson_package_id'], $context->tenantId, (int) $lead->campus_id);
            $course = EducationCourse::query()
                ->whereKey((int) $package->course_id)
                ->where('tenant_id', $context->tenantId)
                ->where('campus_id', (int) $lead->campus_id)
                ->first();
            if (! $course instanceof EducationCourse) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'course is disabled', ['course_id' => (int) $package->course_id]);
            }

            $beforeLead = ['status' => $lead->status, 'stage' => $lead->stage];
            $guardian = $this->guardian($lead, $data, $context);
            $student = $this->student($lead, $data, $context);
            EducationStudentGuardian::query()->firstOrCreate([
                'tenant_id' => $context->tenantId,
                'student_id' => (int) $student->id,
                'guardian_id' => (int) $guardian->id,
            ], [
                'relation' => $data['guardian_relation'] ?? 'parent',
                'is_primary' => true,
                'can_receive_notice' => true,
                'can_submit_leave' => true,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            $now = Carbon::now();
            $enrollment = EducationEnrollment::query()->create([
                'tenant_id' => $context->tenantId,
                'campus_id' => (int) $lead->campus_id,
                'enrollment_no' => $this->nextEnrollmentNo($context->tenantId, (int) $lead->campus_id),
                'student_id' => (int) $student->id,
                'course_id' => (int) $course->id,
                'lesson_package_id' => (int) $package->id,
                'student_name_snapshot' => $student->name,
                'course_name_snapshot' => $course->name,
                'package_name_snapshot' => $package->name,
                'package_lesson_units' => $this->decimal($package->lesson_units),
                'package_bonus_units' => $this->decimal($package->bonus_units),
                'total_units' => $this->decimal($package->total_units),
                'list_price' => $this->decimal($package->list_price),
                'deal_amount' => $this->decimal($data['paid_amount'] ?? $package->sale_price),
                'status' => 'confirmed',
                'enrolled_at' => $data['enrolled_at'] ?? $now->toDateString(),
                'confirmed_at' => $now->toDateTimeString(),
                'materialized_at' => $now->toDateTimeString(),
                'remark' => $data['remark'] ?? 'lead conversion',
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            $account = $this->materializeAccount($enrollment, $package, $context);
            $enrollment->update(['account_id' => (int) $account->id, 'updated_by' => $context->userId]);

            $record = $this->conversionRepository->create([
                'tenant_id' => $context->tenantId,
                'campus_id' => (int) $lead->campus_id,
                'lead_id' => $leadId,
                'student_id' => (int) $student->id,
                'guardian_id' => (int) $guardian->id,
                'enrollment_id' => (int) $enrollment->id,
                'student_course_account_id' => (int) $account->id,
                'status' => 'success',
                'converted_by' => $context->userId,
                'converted_at' => $now->toDateTimeString(),
                'payload_json' => $data,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            $lead->update(['stage' => 'converted', 'status' => 'converted', 'updated_by' => $context->userId]);
            $this->taskRepository->completeByLead($context->tenantId, $leadId, $context->userId);
            EducationAuditLog::query()->create([
                'tenant_id' => $context->tenantId,
                'campus_id' => (int) $lead->campus_id,
                'actor_user_id' => $context->userId,
                'actor_type' => 'admin',
                'actor_role_code' => $context->roleCode->value,
                'module' => 'admissions',
                'resource' => 'lead',
                'action' => 'education.admissions.lead.converted',
                'business_type' => 'lead',
                'business_id' => (string) $leadId,
                'summary' => 'Lead converted',
                'before_snapshot' => $beforeLead,
                'after_snapshot' => ['student_id' => (int) $student->id, 'enrollment_id' => (int) $enrollment->id],
                'metadata' => ['conversion_record_id' => (int) $record->id],
            ]);

            return [
                'lead_id' => $leadId,
                'student_id' => (int) $student->id,
                'guardian_id' => (int) $guardian->id,
                'enrollment_id' => (int) $enrollment->id,
                'student_course_account_id' => (int) $account->id,
                'conversion_record_id' => (int) $record->id,
            ];
        });
    }

    private function enabledPackage(int $packageId, int $tenantId, int $campusId): EducationLessonPackage
    {
        $package = EducationLessonPackage::query()
            ->whereKey($packageId)
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('status', 'enabled')
            ->first();
        if (! $package instanceof EducationLessonPackage) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'lesson package is disabled', ['lesson_package_id' => $packageId]);
        }

        return $package;
    }

    private function guardian(EducationLead $lead, array $data, EducationUserContext $context): EducationGuardian
    {
        $leadGuardian = EducationLeadGuardian::query()
            ->where('tenant_id', $context->tenantId)
            ->where('lead_id', (int) $lead->id)
            ->orderByDesc('is_primary')
            ->first();
        $leadGuardianMobile = $leadGuardian instanceof EducationLeadGuardian ? $leadGuardian->mobile : $lead->contact_mobile;
        $leadGuardianName = $leadGuardian instanceof EducationLeadGuardian ? $leadGuardian->name : $lead->contact_name;
        $mobile = trim((string) ($data['guardian_mobile'] ?? $leadGuardianMobile));
        $guardian = EducationGuardian::query()->where('tenant_id', $context->tenantId)->where('mobile', $mobile)->first();
        if ($guardian instanceof EducationGuardian) {
            return $guardian;
        }

        return EducationGuardian::query()->create([
            'tenant_id' => $context->tenantId,
            'name' => trim((string) ($data['guardian_name'] ?? $leadGuardianName)),
            'mobile' => $mobile,
            'gender' => 'unknown',
            'status' => 'enabled',
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);
    }

    private function student(EducationLead $lead, array $data, EducationUserContext $context): EducationStudent
    {
        $leadStudent = EducationLeadStudent::query()
            ->where('tenant_id', $context->tenantId)
            ->where('lead_id', (int) $lead->id)
            ->orderBy('id')
            ->first();

        $leadStudentName = $leadStudent instanceof EducationLeadStudent ? $leadStudent->name : $lead->contact_name;
        $leadStudentGender = $leadStudent instanceof EducationLeadStudent ? $leadStudent->gender : 'unknown';
        $leadStudentBirthday = $leadStudent instanceof EducationLeadStudent ? $leadStudent->birthday : null;
        $leadStudentSchool = $leadStudent instanceof EducationLeadStudent ? $leadStudent->school : null;
        $leadStudentGrade = $leadStudent instanceof EducationLeadStudent ? $leadStudent->grade : null;

        return EducationStudent::query()->create([
            'tenant_id' => $context->tenantId,
            'campus_id' => (int) $lead->campus_id,
            'student_no' => $this->nextStudentNo($context->tenantId, (int) $lead->campus_id),
            'name' => trim((string) ($data['student_name'] ?? $leadStudentName)),
            'gender' => $data['student_gender'] ?? $leadStudentGender,
            'birthday' => $data['student_birthday'] ?? $leadStudentBirthday,
            'mobile' => $lead->contact_mobile,
            'school' => $data['school'] ?? $leadStudentSchool,
            'grade' => $data['grade'] ?? $leadStudentGrade,
            'source' => 'lead_conversion',
            'enrolled_at' => $data['enrolled_at'] ?? Carbon::now()->toDateString(),
            'status' => 'enabled',
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);
    }

    private function materializeAccount(EducationEnrollment $enrollment, EducationLessonPackage $package, EducationUserContext $context): EducationStudentCourseAccount
    {
        $account = EducationStudentCourseAccount::query()
            ->where('tenant_id', $enrollment->tenant_id)
            ->where('campus_id', $enrollment->campus_id)
            ->where('student_id', $enrollment->student_id)
            ->where('course_id', $enrollment->course_id)
            ->lockForUpdate()
            ->first();
        $expiresAt = $package->validity_days !== null
            ? Carbon::now()->addDays((int) $package->validity_days)->toDateTimeString()
            : null;
        if (! $account instanceof EducationStudentCourseAccount) {
            return EducationStudentCourseAccount::query()->create([
                'tenant_id' => (int) $enrollment->tenant_id,
                'campus_id' => (int) $enrollment->campus_id,
                'student_id' => (int) $enrollment->student_id,
                'course_id' => (int) $enrollment->course_id,
                'purchased_units' => $this->decimal($enrollment->package_lesson_units),
                'bonus_units' => $this->decimal($enrollment->package_bonus_units),
                'consumed_units' => '0.00',
                'adjusted_units' => '0.00',
                'refunded_units' => '0.00',
                'frozen_units' => '0.00',
                'available_units' => $this->decimal($enrollment->total_units),
                'status' => 'active',
                'first_enrollment_id' => (int) $enrollment->id,
                'last_enrollment_id' => (int) $enrollment->id,
                'opened_at' => Carbon::now()->toDateTimeString(),
                'expires_at' => $expiresAt,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
        }

        $account->fill([
            'purchased_units' => $this->decimal((float) $account->purchased_units + (float) $enrollment->package_lesson_units),
            'bonus_units' => $this->decimal((float) $account->bonus_units + (float) $enrollment->package_bonus_units),
            'available_units' => $this->decimal((float) $account->available_units + (float) $enrollment->total_units),
            'last_enrollment_id' => (int) $enrollment->id,
            'updated_by' => $context->userId,
        ]);
        $account->save();

        return $account->refresh();
    }

    private function nextEnrollmentNo(int $tenantId, int $campusId): string
    {
        return 'ENR' . Carbon::now()->format('YmdHis') . str_pad((string) ($tenantId % 10000), 4, '0', \STR_PAD_LEFT) . str_pad((string) ($campusId % 1000), 3, '0', \STR_PAD_LEFT) . random_int(1000, 9999);
    }

    private function nextStudentNo(int $tenantId, int $campusId): string
    {
        return 'STU' . Carbon::now()->format('YmdHis') . str_pad((string) ($tenantId % 10000), 4, '0', \STR_PAD_LEFT) . str_pad((string) ($campusId % 1000), 3, '0', \STR_PAD_LEFT) . random_int(1000, 9999);
    }

    private function decimal(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
