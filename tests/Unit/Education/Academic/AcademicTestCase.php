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

use App\Model\Education\Academic\EducationClassroom;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Foundation\EducationAuditLog;
use App\Model\Education\Foundation\EducationCampus;
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

    private function cleanEducationData(): void
    {
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
}
