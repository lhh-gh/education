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

namespace HyperfTests\Feature\Education\Admissions;

use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationLessonPackage;
use App\Model\Education\Admissions\EducationAdmissionMetricDaily;
use App\Model\Education\Admissions\EducationAdmissionTask;
use App\Model\Education\Admissions\EducationLead;
use App\Model\Education\Admissions\EducationLeadAssignment;
use App\Model\Education\Admissions\EducationLeadConversionRecord;
use App\Model\Education\Admissions\EducationLeadFollowRecord;
use App\Model\Education\Admissions\EducationLeadGuardian;
use App\Model\Education\Admissions\EducationLeadSource;
use App\Model\Education\Admissions\EducationLeadStudent;
use App\Model\Education\Admissions\EducationTrialAttendance;
use App\Model\Education\Admissions\EducationTrialFeedback;
use App\Model\Education\Admissions\EducationTrialLesson;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Admissions\LeadService;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use HyperfTests\Feature\Education\Academic\ProfileRecordAdminCase;

abstract class AdmissionsApiCase extends ProfileRecordAdminCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureAdmissionsTables();
        $this->cleanAdmissionsData();
    }

    protected function admissionsLead(EducationTenant $tenant, EducationCampus $campus, array $overrides = []): EducationLead
    {
        $lead = make(LeadService::class)->create(array_merge([
            'campus_id' => (int) $campus->id,
            'contact_name' => 'Guardian',
            'contact_mobile' => '137' . random_int(10000000, 99999999),
            'lead_students' => [['name' => 'Student']],
        ], $overrides), $this->contextForFixture((int) $tenant->id, (int) $campus->id));

        return EducationLead::query()->findOrFail((int) $lead['id']);
    }

    protected function admissionsPackage(EducationTenant $tenant, EducationCampus $campus, array $overrides = []): EducationLessonPackage
    {
        $course = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'ADM' . uniqid(),
            'name' => 'Admissions Course',
            'status' => 'enabled',
        ]);

        return EducationLessonPackage::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'code' => 'PKG' . uniqid(),
            'name' => 'Admissions Package',
            'lesson_units' => '20.00',
            'bonus_units' => '4.00',
            'total_units' => '24.00',
            'list_price' => '3600.00',
            'sale_price' => '3000.00',
            'status' => 'enabled',
        ], $overrides));
    }

    protected function mobileHeaders(EducationTenant $tenant): array
    {
        return $this->tenantHeaders($tenant, ['X-Client-Type' => 'wechat_miniprogram']);
    }

    private function contextForFixture(int $tenantId, int $campusId): EducationUserContext
    {
        return new EducationUserContext(
            userId: (int) $this->user->id,
            tenantId: $tenantId,
            roleCode: EducationRoleCode::TenantAdmin,
            platformAccess: false,
            campusIds: [$campusId],
            currentCampusId: $campusId
        );
    }

    private function ensureAdmissionsTables(): void
    {
        if (! Schema::hasTable('edu_leads')) {
            $migration = $this->admissionsMigration();
            $migration->down();
            $migration->up();
        }
    }

    private function cleanAdmissionsData(): void
    {
        EducationAdmissionMetricDaily::query()->whereRaw('1 = 1')->delete();
        EducationAdmissionTask::query()->forceDelete();
        EducationLeadConversionRecord::query()->whereRaw('1 = 1')->delete();
        EducationTrialFeedback::query()->whereRaw('1 = 1')->delete();
        EducationTrialAttendance::query()->whereRaw('1 = 1')->delete();
        EducationTrialLesson::query()->forceDelete();
        EducationLeadFollowRecord::query()->whereRaw('1 = 1')->delete();
        EducationLeadAssignment::query()->forceDelete();
        EducationLeadStudent::query()->forceDelete();
        EducationLeadGuardian::query()->forceDelete();
        EducationLead::query()->forceDelete();
        EducationLeadSource::query()->forceDelete();
    }

    private function admissionsMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_030000_create_v3_admissions_tables.php';
    }
}
