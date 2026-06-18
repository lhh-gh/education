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

namespace HyperfTests\Unit\Education\Ai;

use App\Model\Education\Ai\EducationAiDataQuestionLog;
use App\Model\Education\Ai\EducationAiFeatureSetting;
use App\Model\Education\Ai\EducationAiGenerationResult;
use App\Model\Education\Ai\EducationAiGenerationTask;
use App\Model\Education\Ai\EducationAiKnowledgeDocument;
use App\Model\Education\Ai\EducationAiMetricCatalog;
use App\Model\Education\Ai\EducationAiModelConfig;
use App\Model\Education\Ai\EducationAiPromptTemplate;
use App\Model\Education\Ai\EducationAiRecommendationTask;
use App\Model\Education\Ai\EducationAiReviewLog;
use App\Model\Education\Ai\EducationAiRiskFactor;
use App\Model\Education\Ai\EducationAiRiskScore;
use App\Model\Education\Ai\EducationAiSafetyEvent;
use App\Model\Education\Ai\EducationAiUsageLog;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use HyperfTests\Unit\Education\Family\FamilyTestCase;

abstract class AiTestCase extends FamilyTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureAiTables();
        $this->cleanAiData();
    }

    /**
     * @return array<string, mixed>
     */
    protected function aiFixture(string $code): array
    {
        $fixture = $this->familyFixture($code);
        $modelConfig = EducationAiModelConfig::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'config_code' => 'default-' . $code,
            'provider' => 'fake',
            'model_name' => 'fake-model',
            'api_key_ciphertext' => 'ciphertext',
            'status' => 'enabled',
            'default_temperature' => '0.30',
            'daily_token_limit' => 10000,
        ]);
        $feature = EducationAiFeatureSetting::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'feature_code' => 'lesson_comment',
            'feature_name' => 'Lesson comment draft',
            'model_config_id' => $modelConfig->id,
            'enabled' => true,
            'review_required' => true,
            'safety_level' => 'normal',
            'config_json' => ['max_tokens' => 512],
        ]);
        $prompt = EducationAiPromptTemplate::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'template_code' => 'comment-' . $code,
            'feature_code' => 'lesson_comment',
            'template_name' => 'Comment prompt',
            'version' => 1,
            'system_prompt' => 'Write a safe teacher draft.',
            'user_prompt' => 'Student keywords: {{keywords}}',
            'status' => 'published',
            'published_at' => '2026-06-10 08:00:00',
        ]);
        $metric = EducationAiMetricCatalog::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'metric_code' => 'renewal_alert_count',
            'metric_name' => 'Renewal alert count',
            'metric_group' => 'operations',
            'query_key' => 'renewal_alert_count',
            'allowed_roles_json' => ['tenant_admin', 'campus_admin'],
            'status' => 'enabled',
        ]);

        return $fixture + [
            'model_config_id' => (int) $modelConfig->id,
            'feature_setting_id' => (int) $feature->id,
            'prompt_template_id' => (int) $prompt->id,
            'metric_catalog_id' => (int) $metric->id,
        ];
    }

    private function ensureAiTables(): void
    {
        if (Schema::hasTable('edu_ai_model_configs')) {
            return;
        }

        $migration = $this->aiMigration();
        $migration->down();
        $migration->up();
    }

    private function cleanAiData(): void
    {
        EducationAiKnowledgeDocument::query()->forceDelete();
        EducationAiMetricCatalog::query()->forceDelete();
        EducationAiSafetyEvent::query()->whereRaw('1 = 1')->delete();
        EducationAiRecommendationTask::query()->whereRaw('1 = 1')->delete();
        EducationAiDataQuestionLog::query()->whereRaw('1 = 1')->delete();
        EducationAiRiskFactor::query()->whereRaw('1 = 1')->delete();
        EducationAiRiskScore::query()->whereRaw('1 = 1')->delete();
        EducationAiReviewLog::query()->whereRaw('1 = 1')->delete();
        EducationAiUsageLog::query()->whereRaw('1 = 1')->delete();
        EducationAiGenerationResult::query()->whereRaw('1 = 1')->delete();
        EducationAiGenerationTask::query()->whereRaw('1 = 1')->delete();
        EducationAiPromptTemplate::query()->forceDelete();
        EducationAiFeatureSetting::query()->forceDelete();
        EducationAiModelConfig::query()->forceDelete();
    }

    private function aiMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_080000_create_v8_ai_tables.php';
    }
}
