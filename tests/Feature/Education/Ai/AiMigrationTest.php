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

namespace HyperfTests\Feature\Education\Ai;

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class AiMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->aiMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testAiTablesIndexesAndSecretFieldsExist(): void
    {
        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasTable($table), "{$table} table missing");
        }

        foreach ($this->requiredColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertTrue(Schema::hasColumn($table, $column), "{$table} missing {$column}");
            }
        }

        foreach ($this->requiredIndexes() as $table => $indexes) {
            foreach ($indexes as $index) {
                self::assertTrue($this->hasIndex($table, $index), "{$table} missing index {$index}");
            }
        }

        self::assertTrue(Schema::hasColumn('edu_ai_model_configs', 'api_key_ciphertext'));
        self::assertFalse(Schema::hasColumn('edu_ai_model_configs', 'api_key'));
    }

    public function testAiTablesUseExpectedColumnTypes(): void
    {
        foreach ($this->jsonColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertSame('json', $this->columnType($table, $column), "{$table}.{$column} must be json");
            }
        }

        foreach ($this->longTextColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertSame('longtext', $this->columnType($table, $column), "{$table}.{$column} must be longtext");
            }
        }
    }

    public function testRollbackDropsAiTablesInReverseOrder(): void
    {
        $migration = $this->aiMigration();

        $migration->down();

        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertFalse(Schema::hasTable($table), "{$table} table should be rolled back");
        }

        $migration->up();

        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasTable($table), "{$table} table should be re-created");
        }
    }

    /**
     * @return array<string, list<string>>
     */
    private function requiredColumns(): array
    {
        return [
            'edu_ai_model_configs' => [
                'id', 'tenant_id', 'campus_id', 'config_code', 'provider', 'model_name', 'api_key_ciphertext',
                'base_url', 'status', 'default_temperature', 'daily_token_limit', 'created_by', 'updated_by',
                'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_ai_feature_settings' => [
                'id', 'tenant_id', 'campus_id', 'feature_code', 'feature_name', 'model_config_id', 'enabled',
                'review_required', 'safety_level', 'config_json', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_ai_prompt_templates' => [
                'id', 'tenant_id', 'campus_id', 'template_code', 'feature_code', 'template_name', 'version',
                'system_prompt', 'user_prompt', 'status', 'published_at', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_ai_generation_tasks' => [
                'id', 'tenant_id', 'campus_id', 'task_no', 'feature_code', 'model_config_id', 'prompt_template_id',
                'business_type', 'business_id', 'requester_user_id', 'status', 'context_hash', 'queued_at',
                'started_at', 'finished_at', 'error_message', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_ai_generation_results' => [
                'id', 'tenant_id', 'campus_id', 'generation_task_id', 'result_text', 'result_json', 'safety_status',
                'review_status', 'visible_to_guardian', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_ai_usage_logs' => [
                'id', 'tenant_id', 'campus_id', 'generation_task_id', 'provider', 'model_name', 'prompt_tokens',
                'completion_tokens', 'total_tokens', 'cost_cents', 'usage_date', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_ai_review_logs' => [
                'id', 'tenant_id', 'campus_id', 'generation_result_id', 'reviewer_user_id', 'review_action',
                'review_note', 'reviewed_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_ai_risk_scores' => [
                'id', 'tenant_id', 'campus_id', 'student_id', 'course_account_id', 'score_date', 'risk_score',
                'risk_level', 'summary', 'generation_task_id', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_ai_risk_factors' => [
                'id', 'tenant_id', 'campus_id', 'risk_score_id', 'factor_code', 'factor_name', 'factor_value',
                'weight', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_ai_data_question_logs' => [
                'id', 'tenant_id', 'campus_id', 'question_text', 'metric_codes_json', 'answer_text',
                'requester_user_id', 'status', 'error_message', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_ai_recommendation_tasks' => [
                'id', 'tenant_id', 'campus_id', 'recommendation_type', 'target_type', 'target_id',
                'assignee_user_id', 'status', 'recommendation_json', 'handled_at', 'created_by', 'updated_by',
                'created_at', 'updated_at',
            ],
            'edu_ai_safety_events' => [
                'id', 'tenant_id', 'campus_id', 'generation_task_id', 'risk_level', 'event_type', 'summary',
                'payload_json', 'handled', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_ai_metric_catalogs' => [
                'id', 'tenant_id', 'campus_id', 'metric_code', 'metric_name', 'metric_group', 'query_key',
                'allowed_roles_json', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_ai_knowledge_documents' => [
                'id', 'tenant_id', 'campus_id', 'document_code', 'title', 'content', 'scope_type',
                'scope_value_json', 'status', 'published_at', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function requiredIndexes(): array
    {
        return [
            'edu_ai_model_configs' => ['uk_edu_ai_model_configs_code', 'idx_edu_ai_model_configs_provider'],
            'edu_ai_feature_settings' => ['uk_edu_ai_feature_settings_code', 'idx_edu_ai_feature_settings_enabled'],
            'edu_ai_prompt_templates' => ['uk_edu_ai_prompt_templates_code_version', 'idx_edu_ai_prompt_templates_feature'],
            'edu_ai_generation_tasks' => ['uk_edu_ai_generation_tasks_no', 'idx_edu_ai_tasks_feature_status', 'idx_edu_ai_tasks_requester'],
            'edu_ai_generation_results' => ['uk_edu_ai_generation_results_task', 'idx_edu_ai_generation_results_review'],
            'edu_ai_usage_logs' => ['idx_edu_ai_usage_tenant_date', 'idx_edu_ai_usage_task'],
            'edu_ai_review_logs' => ['idx_edu_ai_review_logs_result', 'idx_edu_ai_review_logs_reviewer'],
            'edu_ai_risk_scores' => ['uk_edu_ai_risk_scores_student_date', 'idx_edu_ai_risk_scores_level'],
            'edu_ai_risk_factors' => ['idx_edu_ai_risk_factors_score', 'idx_edu_ai_risk_factors_code'],
            'edu_ai_data_question_logs' => ['idx_edu_ai_data_question_logs_requester', 'idx_edu_ai_data_question_logs_status'],
            'edu_ai_recommendation_tasks' => ['idx_edu_ai_recommendation_tasks_assignee_status', 'idx_edu_ai_recommendation_tasks_target'],
            'edu_ai_safety_events' => ['idx_edu_ai_safety_events_level', 'idx_edu_ai_safety_events_task'],
            'edu_ai_metric_catalogs' => ['uk_edu_ai_metric_catalogs_code', 'idx_edu_ai_metric_catalogs_group'],
            'edu_ai_knowledge_documents' => ['uk_edu_ai_knowledge_documents_code', 'idx_edu_ai_knowledge_documents_scope'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function jsonColumns(): array
    {
        return [
            'edu_ai_feature_settings' => ['config_json'],
            'edu_ai_generation_results' => ['result_json'],
            'edu_ai_data_question_logs' => ['metric_codes_json'],
            'edu_ai_recommendation_tasks' => ['recommendation_json'],
            'edu_ai_safety_events' => ['payload_json'],
            'edu_ai_metric_catalogs' => ['allowed_roles_json'],
            'edu_ai_knowledge_documents' => ['scope_value_json'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function longTextColumns(): array
    {
        return [
            'edu_ai_generation_results' => ['result_text'],
            'edu_ai_data_question_logs' => ['answer_text'],
            'edu_ai_knowledge_documents' => ['content'],
        ];
    }

    private function aiMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_080000_create_v8_ai_tables.php';
    }

    private function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $database = (string) $connection->getDatabaseName();
        $rows = $connection->select(
            'select index_name from information_schema.statistics where table_schema = ? and table_name = ? and index_name = ? limit 1',
            [$database, $table, $index]
        );

        return $rows !== [];
    }

    private function columnType(string $table, string $column): string
    {
        $connection = Schema::getConnection();
        $database = (string) $connection->getDatabaseName();
        $rows = $connection->select(
            'select data_type from information_schema.columns where table_schema = ? and table_name = ? and column_name = ? limit 1',
            [$database, $table, $column]
        );

        self::assertNotEmpty($rows, "{$table}.{$column} missing");

        return (string) $rows[0]->DATA_TYPE;
    }
}
