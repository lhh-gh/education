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

namespace HyperfTests\Feature\Education\Standards;

use App\Model\Education\Standards\EducationCourseAbilityPoint;
use App\Model\Education\Standards\EducationCourseFeedbackRecord;
use App\Model\Education\Standards\EducationCourseLocalizationOverride;
use App\Model\Education\Standards\EducationCourseMaterial;
use App\Model\Education\Standards\EducationCourseQualityMetricDaily;
use App\Model\Education\Standards\EducationCourseServicePackage;
use App\Model\Education\Standards\EducationCourseStageGoal;
use App\Model\Education\Standards\EducationCourseStageGoalAbilityRelation;
use App\Model\Education\Standards\EducationCourseStandardPublishLog;
use App\Model\Education\Standards\EducationCourseStandardReviewRecord;
use App\Model\Education\Standards\EducationCourseStandardVersion;
use App\Model\Education\Standards\EducationServiceTemplateItem;
use App\Model\Education\Standards\EducationServiceTemplateSet;
use App\Model\Education\Standards\EducationTeachingDeliveryStandard;
use App\Model\Education\Standards\EducationTrialLessonStandard;
use App\Model\Education\Standards\EducationTrialLessonStandardItem;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use HyperfTests\Feature\Education\Family\FamilyApiCase;

abstract class StandardsApiCase extends FamilyApiCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureStandardTables();
        $this->cleanStandardData();
    }

    protected function tearDown(): void
    {
        $this->cleanStandardData();
        parent::tearDown();
    }

    /**
     * @return array<string, mixed>
     */
    protected function standardsFixture(string $code, string $roleCode = 'tenant_admin'): array
    {
        return $this->familyFixture($code, $roleCode);
    }

    private function ensureStandardTables(): void
    {
        if (Schema::hasTable('edu_course_service_packages')) {
            return;
        }

        $migration = $this->standardsMigration();
        $migration->down();
        $migration->up();
    }

    private function cleanStandardData(): void
    {
        EducationCourseStandardReviewRecord::query()->whereRaw('1 = 1')->delete();
        EducationCourseLocalizationOverride::query()->forceDelete();
        EducationCourseStandardPublishLog::query()->whereRaw('1 = 1')->delete();
        EducationCourseStandardVersion::query()->whereRaw('1 = 1')->delete();
        EducationCourseQualityMetricDaily::query()->whereRaw('1 = 1')->delete();
        EducationCourseFeedbackRecord::query()->whereRaw('1 = 1')->delete();
        EducationCourseMaterial::query()->forceDelete();
        EducationServiceTemplateItem::query()->whereRaw('1 = 1')->delete();
        EducationServiceTemplateSet::query()->forceDelete();
        EducationTeachingDeliveryStandard::query()->forceDelete();
        EducationTrialLessonStandardItem::query()->whereRaw('1 = 1')->delete();
        EducationTrialLessonStandard::query()->forceDelete();
        EducationCourseStageGoalAbilityRelation::query()->whereRaw('1 = 1')->delete();
        EducationCourseAbilityPoint::query()->forceDelete();
        EducationCourseStageGoal::query()->forceDelete();
        EducationCourseServicePackage::query()->forceDelete();
    }

    private function standardsMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_110000_create_v11_course_standard_tables.php';
    }
}
