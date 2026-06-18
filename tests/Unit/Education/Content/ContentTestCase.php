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

namespace HyperfTests\Unit\Education\Content;

use App\Model\Education\Content\EducationContentReviewRecord;
use App\Model\Education\Content\EducationLearningMaterial;
use App\Model\Education\Content\EducationLearningMaterialAttachment;
use App\Model\Education\Content\EducationLearningMaterialRelation;
use App\Model\Education\Content\EducationLearningMaterialVersion;
use App\Model\Education\Content\EducationLessonMaterialUsage;
use App\Model\Education\Content\EducationMaterialPublishLog;
use App\Model\Education\Content\EducationMaterialReadRecord;
use App\Model\Education\Content\EducationMaterialUsageMetricDaily;
use App\Model\Education\Content\EducationShowcaseItem;
use App\Model\Education\Content\EducationShowcaseReadRecord;
use App\Model\Education\Content\EducationStageAchievementShowcase;
use App\Model\Education\Content\EducationStudentWork;
use App\Model\Education\Content\EducationStudentWorkAttachment;
use App\Model\Education\Content\EducationStudentWorkMetricDaily;
use App\Model\Education\Content\EducationTeacherMaterialFavorite;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use HyperfTests\Unit\Education\Standards\StandardsTestCase;

abstract class ContentTestCase extends StandardsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureContentTables();
        $this->cleanContentData();
    }

    private function ensureContentTables(): void
    {
        if (Schema::hasTable('edu_learning_materials')) {
            return;
        }

        $migration = $this->contentMigration();
        $migration->down();
        $migration->up();
    }

    private function cleanContentData(): void
    {
        EducationStudentWorkMetricDaily::query()->whereRaw('1 = 1')->delete();
        EducationMaterialUsageMetricDaily::query()->whereRaw('1 = 1')->delete();
        EducationContentReviewRecord::query()->forceDelete();
        EducationMaterialPublishLog::query()->whereRaw('1 = 1')->delete();
        EducationShowcaseReadRecord::query()->whereRaw('1 = 1')->delete();
        EducationMaterialReadRecord::query()->whereRaw('1 = 1')->delete();
        EducationShowcaseItem::query()->whereRaw('1 = 1')->delete();
        EducationStageAchievementShowcase::query()->forceDelete();
        EducationStudentWorkAttachment::query()->whereRaw('1 = 1')->delete();
        EducationStudentWork::query()->forceDelete();
        EducationTeacherMaterialFavorite::query()->whereRaw('1 = 1')->delete();
        EducationLessonMaterialUsage::query()->whereRaw('1 = 1')->delete();
        EducationLearningMaterialRelation::query()->whereRaw('1 = 1')->delete();
        EducationLearningMaterialAttachment::query()->whereRaw('1 = 1')->delete();
        EducationLearningMaterialVersion::query()->whereRaw('1 = 1')->delete();
        EducationLearningMaterial::query()->forceDelete();
    }

    private function contentMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_120000_create_v12_learning_content_tables.php';
    }
}
