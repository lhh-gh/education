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

namespace HyperfTests\Feature\Education\Growth;

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
use App\Model\Education\Growth\EducationGrowthAiTalkScript;
use App\Model\Education\Growth\EducationGrowthCampaign;
use App\Model\Education\Growth\EducationGrowthChannelCost;
use App\Model\Education\Growth\EducationGrowthChannelRoiDaily;
use App\Model\Education\Growth\EducationGrowthConsultantMetricDaily;
use App\Model\Education\Growth\EducationGrowthConversionFunnel;
use App\Model\Education\Growth\EducationGrowthFollowupStrategy;
use App\Model\Education\Growth\EducationGrowthFollowupSuggestion;
use App\Model\Education\Growth\EducationGrowthLeadLossRecord;
use App\Model\Education\Growth\EducationGrowthLeadScore;
use App\Model\Education\Growth\EducationGrowthLeadScoreFactor;
use App\Model\Education\Growth\EducationGrowthLossReason;
use App\Model\Education\Growth\EducationGrowthScriptTemplate;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use HyperfTests\Feature\Education\Family\FamilyApiCase;

abstract class GrowthApiCase extends FamilyApiCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureAdmissionsTables();
        $this->ensureGrowthTables();
        $this->cleanGrowthData();
        $this->cleanAdmissionsData();
    }

    protected function tearDown(): void
    {
        $this->cleanGrowthData();
        $this->cleanAdmissionsData();
        parent::tearDown();
    }

    /**
     * @return array<string, mixed>
     */
    protected function growthFixture(string $code, string $roleCode = 'tenant_admin'): array
    {
        $fixture = $this->familyFixture($code, $roleCode);
        $source = EducationLeadSource::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'code' => 'SRC' . uniqid(),
            'name' => 'Search Ads',
            'channel_type' => 'ads',
            'status' => 'enabled',
        ]);
        $lead = EducationLead::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'lead_no' => 'L' . uniqid(),
            'source_id' => $source->id,
            'contact_name' => 'Guardian',
            'contact_mobile' => '13812345678',
            'stage' => 'followed',
            'status' => 'active',
            'owner_user_id' => $this->user->id,
            'intention_level' => 'high',
        ]);
        $trial = EducationTrialLesson::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'lead_id' => $lead->id,
            'lead_student_id' => 1,
            'course_id' => $fixture['course_id'],
            'teacher_id' => $this->user->id,
            'start_time' => '2026-06-10 09:00:00',
            'end_time' => '2026-06-10 10:00:00',
            'status' => 'scheduled',
            'consultant_user_id' => $this->user->id,
        ]);

        return $fixture + [
            'source_id' => (int) $source->id,
            'lead_id' => (int) $lead->id,
            'trial_lesson_id' => (int) $trial->id,
        ];
    }

    private function ensureAdmissionsTables(): void
    {
        if (Schema::hasTable('edu_leads')) {
            return;
        }

        $migration = $this->admissionsMigration();
        $migration->down();
        $migration->up();
    }

    private function ensureGrowthTables(): void
    {
        if (Schema::hasTable('edu_growth_lead_scores')) {
            return;
        }

        $migration = $this->growthMigration();
        $migration->down();
        $migration->up();
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

    private function cleanGrowthData(): void
    {
        EducationGrowthScriptTemplate::query()->forceDelete();
        EducationGrowthCampaign::query()->forceDelete();
        EducationGrowthLeadLossRecord::query()->whereRaw('1 = 1')->delete();
        EducationGrowthLossReason::query()->forceDelete();
        EducationGrowthConsultantMetricDaily::query()->whereRaw('1 = 1')->delete();
        EducationGrowthChannelRoiDaily::query()->whereRaw('1 = 1')->delete();
        EducationGrowthChannelCost::query()->whereRaw('1 = 1')->delete();
        EducationGrowthConversionFunnel::query()->whereRaw('1 = 1')->delete();
        EducationGrowthAiTalkScript::query()->whereRaw('1 = 1')->delete();
        EducationGrowthFollowupSuggestion::query()->whereRaw('1 = 1')->delete();
        EducationGrowthFollowupStrategy::query()->forceDelete();
        EducationGrowthLeadScoreFactor::query()->whereRaw('1 = 1')->delete();
        EducationGrowthLeadScore::query()->whereRaw('1 = 1')->delete();
    }

    private function admissionsMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_030000_create_v3_admissions_tables.php';
    }

    private function growthMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_100000_create_v10_growth_tables.php';
    }
}
