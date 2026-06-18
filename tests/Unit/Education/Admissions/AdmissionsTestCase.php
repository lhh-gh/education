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
use App\Service\Education\Admissions\LeadService;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use HyperfTests\Unit\Education\Academic\AcademicTestCase;

abstract class AdmissionsTestCase extends AcademicTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureAdmissionsTables();
        $this->cleanAdmissionsData();
    }

    protected function leadFixture(EducationTenant $tenant, EducationCampus $campus, array $overrides = []): EducationLead
    {
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: $overrides['operator_id'] ?? 901);
        $lead = make(LeadService::class)->create(array_merge([
            'campus_id' => (int) $campus->id,
            'contact_name' => 'Guardian',
            'contact_mobile' => '138' . random_int(10000000, 99999999),
            'lead_students' => [
                ['name' => 'Student', 'gender' => 'unknown', 'grade' => 'Grade 1'],
            ],
        ], $overrides), $context);

        return EducationLead::query()->findOrFail((int) $lead['id']);
    }

    protected function packageFixture(EducationTenant $tenant, EducationCampus $campus, array $overrides = []): EducationLessonPackage
    {
        $course = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => $overrides['course_code'] ?? ('C' . uniqid()),
            'name' => $overrides['course_name'] ?? 'Admissions Course',
            'status' => 'enabled',
        ]);

        return EducationLessonPackage::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'code' => 'PKG' . uniqid(),
            'name' => 'Trial Convert Package',
            'lesson_units' => '20.00',
            'bonus_units' => '4.00',
            'total_units' => '24.00',
            'list_price' => '3600.00',
            'sale_price' => '3000.00',
            'validity_days' => 365,
            'status' => 'enabled',
        ], $overrides));
    }

    private function ensureAdmissionsTables(): void
    {
        foreach (['edu_lead_sources', 'edu_leads', 'edu_admission_metrics_daily'] as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }
            $migration = $this->admissionsMigration();
            $migration->down();
            $migration->up();

            return;
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
