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

use App\Model\Education\Academic\EducationAccountAdjustment;
use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationClassroom;
use App\Model\Education\Academic\EducationClassStudent;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationLeaveRequest;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonAttendance;
use App\Model\Education\Academic\EducationLessonChangeRecord;
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationLessonPackage;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationNotice;
use App\Model\Education\Academic\EducationNoticeReceipt;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Academic\EducationTeacherCourse;
use App\Model\Education\Foundation\EducationAuditLog;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationFeatureFlag;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserCampusScope;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Model\Permission\User;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

abstract class AcademicTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureProfileRecordTables();
        $this->ensureCourseAccountTables();
        $this->ensureClassScheduleTables();
        $this->ensureAttendanceConsumptionTables();
        $this->ensureLeaveChangeTables();
        $this->ensureNoticeTables();
        $this->cleanEducationData();
    }

    protected function tenant(string $code = 'tenant'): EducationTenant
    {
        return EducationTenant::query()->create([
            'name' => ucfirst(str_replace('_', ' ', $code)),
            'code' => $code,
            'status' => 'enabled',
        ]);
    }

    protected function campus(EducationTenant $tenant, string $code = 'main'): EducationCampus
    {
        return EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => ucfirst(str_replace('_', ' ', $code)),
            'code' => $code,
            'status' => 'enabled',
        ]);
    }

    protected function context(
        int $tenantId,
        EducationRoleCode $roleCode = EducationRoleCode::TenantAdmin,
        array $campusIds = [],
        int $userId = 9001
    ): EducationUserContext {
        return new EducationUserContext(
            userId: $userId,
            tenantId: $tenantId,
            roleCode: $roleCode,
            platformAccess: $roleCode->isPlatform(),
            campusIds: $campusIds,
            currentCampusId: $campusIds[0] ?? null
        );
    }

    private function ensureProfileRecordTables(): void
    {
        foreach (['edu_classrooms', 'edu_students', 'edu_guardians', 'edu_student_guardians', 'edu_teachers'] as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->profileRecordMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    private function ensureCourseAccountTables(): void
    {
        foreach (['edu_courses', 'edu_teacher_courses', 'edu_lesson_packages', 'edu_student_course_accounts', 'edu_enrollments'] as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->courseAccountMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    private function cleanEducationData(): void
    {
        EducationAccountAdjustment::query()->forceDelete();
        EducationLessonChangeRecord::query()->forceDelete();
        EducationLeaveRequest::query()->forceDelete();
        EducationNoticeReceipt::query()->forceDelete();
        EducationNotice::query()->forceDelete();
        EducationLessonConsumption::query()->forceDelete();
        EducationLessonAttendance::query()->forceDelete();
        EducationLessonStudent::query()->forceDelete();
        EducationLesson::query()->forceDelete();
        EducationClassStudent::query()->forceDelete();
        EducationClass::query()->forceDelete();
        EducationEnrollment::query()->forceDelete();
        EducationStudentCourseAccount::query()->forceDelete();
        EducationLessonPackage::query()->forceDelete();
        EducationTeacherCourse::query()->forceDelete();
        EducationCourse::query()->forceDelete();
        EducationFeatureFlag::query()->forceDelete();
        EducationAuditLog::query()->whereRaw('1 = 1')->delete();
        EducationStudentGuardian::query()->forceDelete();
        EducationTeacher::query()->forceDelete();
        EducationGuardian::query()->forceDelete();
        EducationStudent::query()->forceDelete();
        EducationClassroom::query()->forceDelete();
        EducationUserCampusScope::query()->delete();
        EducationUserProfile::query()->forceDelete();
        EducationCampus::query()->forceDelete();
        EducationTenant::query()->forceDelete();
        User::query()->where('username', 'like', 'edu_ac_%')->delete();
    }

    private function profileRecordMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_010100_create_v1_profile_record_tables.php';
    }

    private function courseAccountMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_010200_create_v1_course_account_tables.php';
    }

    private function ensureClassScheduleTables(): void
    {
        foreach (['edu_classes', 'edu_class_students', 'edu_lessons', 'edu_lesson_students'] as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->classScheduleMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    private function classScheduleMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_010300_create_v1_class_lesson_tables.php';
    }

    private function ensureAttendanceConsumptionTables(): void
    {
        foreach (['edu_lesson_attendances', 'edu_lesson_consumptions', 'edu_account_adjustments'] as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->attendanceConsumptionMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    private function attendanceConsumptionMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_010400_create_v1_attendance_consumption_tables.php';
    }

    private function ensureLeaveChangeTables(): void
    {
        foreach (['edu_leave_requests', 'edu_lesson_change_records'] as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->leaveChangeMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    private function leaveChangeMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_010500_create_v1_leave_change_tables.php';
    }

    private function ensureNoticeTables(): void
    {
        foreach (['edu_notices', 'edu_notice_receipts'] as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->noticeMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    private function noticeMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_010700_create_v1_notice_tables.php';
    }
}
