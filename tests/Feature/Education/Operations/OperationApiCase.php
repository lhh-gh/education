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

namespace HyperfTests\Feature\Education\Operations;

use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserCampusScope;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Education\Operations\EducationDailyOperationMetric;
use App\Model\Education\Operations\EducationLessonChangeLog;
use App\Model\Education\Operations\EducationLessonChangeRequest;
use App\Model\Education\Operations\EducationLessonConsumptionAdjustment;
use App\Model\Education\Operations\EducationLessonConsumptionReview;
use App\Model\Education\Operations\EducationMakeupEntitlement;
use App\Model\Education\Operations\EducationMakeupRecord;
use App\Model\Education\Operations\EducationRenewalAlert;
use App\Model\Education\Operations\EducationRenewalTask;
use App\Model\Education\Operations\EducationStudentFollowRecord;
use App\Model\Education\Operations\EducationTeacherWorkloadRecord;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use HyperfTests\Feature\Education\Academic\ProfileRecordAdminCase;

abstract class OperationApiCase extends ProfileRecordAdminCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureOperationTables();
        $this->cleanOperationData();
    }

    protected function fixture(string $code): array
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant);
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => mb_strtoupper($code), 'name' => 'Art', 'status' => 'enabled']);
        $lesson = EducationLesson::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lesson_no' => 'L' . uniqid(),
            'class_id' => 1001,
            'course_id' => $course->id,
            'teacher_id' => $this->user->id,
            'classroom_id' => 3001,
            'title' => 'Lesson',
            'start_at' => '2026-06-10 10:00:00',
            'end_at' => '2026-06-10 11:00:00',
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 1,
            'status' => 'scheduled',
            'source_type' => 'manual',
            'class_name_snapshot' => 'Class A',
            'course_name_snapshot' => 'Art',
            'teacher_name_snapshot' => 'Teacher',
        ]);

        return ['tenant' => $tenant, 'campus' => $campus, 'course' => $course, 'lesson' => $lesson];
    }

    protected function createMobileProfile(EducationTenant $tenant, EducationCampus $campus, string $role): void
    {
        $mobile = '136' . random_int(10000000, 99999999);
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenant->id . ':' . $this->user->id,
            'tenant_id' => $tenant->id,
            'user_id' => $this->user->id,
            'role_code' => $role,
            'display_name' => 'Mobile User',
            'mobile' => $mobile,
            'status' => 'enabled',
            'current_campus_id' => $campus->id,
        ]);
        EducationUserCampusScope::query()->create(['tenant_id' => $tenant->id, 'user_profile_id' => $profile->id, 'user_id' => $this->user->id, 'campus_id' => $campus->id]);
    }

    protected function mobileHeaders(EducationTenant $tenant): array
    {
        return $this->tenantHeaders($tenant, ['X-Client-Type' => 'wechat_miniprogram']);
    }

    protected function account(EducationTenant $tenant, EducationCampus $campus, int $studentId = 8001): EducationStudentCourseAccount
    {
        return EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => $studentId,
            'course_id' => 6001,
            'purchased_units' => '10.00',
            'bonus_units' => '0.00',
            'consumed_units' => '9.00',
            'adjusted_units' => '0.00',
            'refunded_units' => '0.00',
            'frozen_units' => '0.00',
            'available_units' => '1.00',
            'status' => 'active',
            'opened_at' => '2026-01-01 00:00:00',
        ]);
    }

    protected function consumption(EducationTenant $tenant, EducationCampus $campus, EducationStudentCourseAccount $account, EducationLesson $lesson, string $status = 'active'): EducationLessonConsumption
    {
        return EducationLessonConsumption::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'consumption_no' => 'CON' . uniqid(),
            'account_id' => $account->id,
            'student_id' => $account->student_id,
            'course_id' => $account->course_id,
            'lesson_id' => $lesson->id,
            'lesson_student_id' => 9001,
            'attendance_id' => 9101,
            'source_type' => 'attendance',
            'direction' => 'decrease',
            'units' => '1.00',
            'before_available_units' => '2.00',
            'after_available_units' => '1.00',
            'before_consumed_units' => '8.00',
            'after_consumed_units' => '9.00',
            'status' => $status,
        ]);
    }

    protected function boundStudent(EducationTenant $tenant, EducationCampus $campus): EducationStudent
    {
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S' . uniqid(), 'name' => 'Student', 'status' => 'enabled']);
        $profile = EducationUserProfile::query()
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $this->user->id)
            ->where('role_code', 'guardian')
            ->first();
        $guardian = $profile instanceof EducationUserProfile
            ? EducationGuardian::query()->create(['tenant_id' => $tenant->id, 'name' => 'Guardian', 'mobile' => $profile->mobile, 'status' => 'enabled'])
            : null;
        EducationStudentGuardian::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_id' => $student->id, 'guardian_id' => (int) ($guardian?->id ?? $this->user->id), 'relation' => 'parent']);

        return $student;
    }

    private function ensureOperationTables(): void
    {
        if (! Schema::hasTable('edu_lesson_change_requests')) {
            $migration = $this->operationMigration();
            $migration->down();
            $migration->up();
        }
    }

    private function cleanOperationData(): void
    {
        EducationDailyOperationMetric::query()->whereRaw('1 = 1')->delete();
        EducationTeacherWorkloadRecord::query()->whereRaw('1 = 1')->delete();
        EducationStudentFollowRecord::query()->whereRaw('1 = 1')->delete();
        EducationRenewalTask::query()->forceDelete();
        EducationRenewalAlert::query()->forceDelete();
        EducationLessonConsumptionAdjustment::query()->whereRaw('1 = 1')->delete();
        EducationLessonConsumptionReview::query()->forceDelete();
        EducationMakeupRecord::query()->forceDelete();
        EducationMakeupEntitlement::query()->forceDelete();
        EducationLessonChangeLog::query()->whereRaw('1 = 1')->delete();
        EducationLessonChangeRequest::query()->forceDelete();
    }

    private function operationMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_020000_create_v2_academic_operation_tables.php';
    }
}
