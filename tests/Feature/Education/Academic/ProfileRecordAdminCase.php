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

use App\Model\Education\Academic\EducationClassroom;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationLessonPackage;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Academic\EducationTeacherCourse;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserCampusScope;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Permission\User;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use HyperfTests\Feature\Education\Foundation\EducationAdminControllerCase;

abstract class ProfileRecordAdminCase extends EducationAdminControllerCase
{
    protected function cleanEducationData(): void
    {
        $this->ensureProfileRecordTables();
        $this->ensureCourseAccountTables();
        EducationEnrollment::query()->forceDelete();
        EducationStudentCourseAccount::query()->forceDelete();
        EducationLessonPackage::query()->forceDelete();
        EducationTeacherCourse::query()->forceDelete();
        EducationCourse::query()->forceDelete();
        EducationStudentGuardian::query()->forceDelete();
        EducationTeacher::query()->forceDelete();
        EducationGuardian::query()->forceDelete();
        EducationStudent::query()->forceDelete();
        EducationClassroom::query()->forceDelete();
        User::query()->where('username', 'like', 'edu_ac_%')->delete();
        parent::cleanEducationData();
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

    protected function createTenantProfile(EducationTenant $tenant, string $roleCode = 'tenant_admin', ?EducationCampus $campus = null): EducationUserProfile
    {
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenant->id . ':' . $this->user->id,
            'tenant_id' => $tenant->id,
            'user_id' => $this->user->id,
            'role_code' => $roleCode,
            'display_name' => 'Education User',
            'status' => 'enabled',
            'current_campus_id' => $campus?->id,
        ]);

        if ($campus instanceof EducationCampus) {
            EducationUserCampusScope::query()->create([
                'tenant_id' => $tenant->id,
                'user_profile_id' => $profile->id,
                'user_id' => $this->user->id,
                'campus_id' => $campus->id,
            ]);
        }

        return $profile;
    }

    protected function tenantHeaders(EducationTenant $tenant, array $headers = []): array
    {
        return $this->authHeaders(array_merge(['X-Tenant-Id' => (string) $tenant->id], $headers));
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

    private function profileRecordMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_010100_create_v1_profile_record_tables.php';
    }

    private function courseAccountMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_010200_create_v1_course_account_tables.php';
    }
}
