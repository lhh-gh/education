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

namespace HyperfTests\Unit\Education\Growth;

use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
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
use HyperfTests\Unit\Education\Admissions\AdmissionsTestCase;

abstract class GrowthTestCase extends AdmissionsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureGrowthTables();
        $this->cleanGrowthData();
    }

    /**
     * @return array{0: EducationTenant, 1: EducationCampus}
     */
    protected function tenantCampus(string $code): array
    {
        $tenant = $this->tenant($code);

        return [$tenant, $this->campus($tenant, $code . '_campus')];
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

    private function growthMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_100000_create_v10_growth_tables.php';
    }
}
