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

namespace HyperfTests\Unit\Education\Operations;

use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationLeaveRequest;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
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
use HyperfTests\Unit\Education\Academic\AcademicTestCase;

abstract class OperationsTestCase extends AcademicTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureOperationTables();
        $this->cleanOperationData();
    }

    protected function lessonFixture(EducationTenant $tenant, EducationCampus $campus, array $overrides = []): EducationLesson
    {
        $course = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => $overrides['course_code'] ?? ('course_' . uniqid()),
            'name' => 'Art',
            'status' => 'enabled',
        ]);

        return EducationLesson::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lesson_no' => 'L' . uniqid(),
            'class_id' => 1001,
            'course_id' => $course->id,
            'teacher_id' => 2001,
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
        ], $overrides));
    }

    protected function leaveFixture(EducationTenant $tenant, EducationCampus $campus, EducationLesson $lesson): EducationLeaveRequest
    {
        return EducationLeaveRequest::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'leave_no' => 'LV' . uniqid(),
            'source' => 'guardian',
            'leave_type' => 'sick',
            'lesson_id' => $lesson->id,
            'lesson_student_id' => 9001,
            'class_id' => $lesson->class_id,
            'course_id' => $lesson->course_id,
            'student_id' => 8001,
            'account_id' => 7001,
            'reason' => 'sick',
            'status' => 'approved',
            'requested_at' => '2026-06-10 09:00:00',
            'reviewed_at' => '2026-06-10 09:30:00',
            'makeup_required' => true,
        ]);
    }

    protected function accountFixture(EducationTenant $tenant, EducationCampus $campus, array $overrides = []): EducationStudentCourseAccount
    {
        return EducationStudentCourseAccount::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => 8001,
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
            'expires_at' => '2026-06-20 23:59:59',
        ], $overrides));
    }

    protected function consumptionFixture(EducationTenant $tenant, EducationCampus $campus, EducationStudentCourseAccount $account, EducationLesson $lesson): EducationLessonConsumption
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
            'status' => 'active',
        ]);
    }

    private function ensureOperationTables(): void
    {
        foreach (['edu_lesson_change_requests', 'edu_daily_operation_metrics'] as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }
            $migration = $this->operationMigration();
            $migration->down();
            $migration->up();

            return;
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
