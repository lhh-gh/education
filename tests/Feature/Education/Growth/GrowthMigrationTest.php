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

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class GrowthMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->growthMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testGrowthTablesIndexesAndMoneyColumnsExist(): void
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
    }

    public function testGrowthTablesUseExpectedColumnTypes(): void
    {
        foreach ($this->jsonColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertSame('json', $this->columnType($table, $column), "{$table}.{$column} must be json");
            }
        }

        foreach ($this->bigintMoneyColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertSame('bigint', $this->columnType($table, $column), "{$table}.{$column} must be bigint cents");
            }
        }
    }

    public function testRollbackDropsGrowthTablesInReverseOrder(): void
    {
        $migration = $this->growthMigration();

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
            'edu_growth_lead_scores' => [
                'id', 'tenant_id', 'campus_id', 'lead_id', 'score_date', 'score',
                'score_level', 'stage', 'owner_user_id', 'summary', 'created_by',
                'updated_by', 'created_at', 'updated_at',
            ],
            'edu_growth_lead_score_factors' => [
                'id', 'tenant_id', 'campus_id', 'lead_score_id', 'factor_code',
                'factor_name', 'factor_value', 'points', 'weight', 'created_by',
                'updated_by', 'created_at', 'updated_at',
            ],
            'edu_growth_followup_strategies' => [
                'id', 'tenant_id', 'campus_id', 'strategy_code', 'strategy_name',
                'lead_stage', 'score_level', 'suggestion_template', 'next_follow_hours',
                'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_growth_followup_suggestions' => [
                'id', 'tenant_id', 'campus_id', 'lead_id', 'strategy_id', 'owner_user_id',
                'suggestion_text', 'status', 'due_at', 'handled_at', 'created_by',
                'updated_by', 'created_at', 'updated_at',
            ],
            'edu_growth_ai_talk_scripts' => [
                'id', 'tenant_id', 'campus_id', 'lead_id', 'generation_task_id',
                'script_type', 'script_text', 'status', 'confirmed_by', 'confirmed_at',
                'masked_input_json', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_growth_conversion_funnels' => [
                'id', 'tenant_id', 'campus_id', 'metric_date', 'source_id', 'stage',
                'lead_count', 'next_stage_count', 'conversion_rate', 'created_by',
                'updated_by', 'created_at', 'updated_at',
            ],
            'edu_growth_channel_costs' => [
                'id', 'tenant_id', 'campus_id', 'source_id', 'cost_date', 'cost_type',
                'amount_cents', 'remark', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_growth_channel_roi_daily' => [
                'id', 'tenant_id', 'campus_id', 'metric_date', 'source_id', 'lead_count',
                'converted_count', 'cost_cents', 'converted_revenue_cents', 'roi',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_growth_consultant_metrics_daily' => [
                'id', 'tenant_id', 'campus_id', 'metric_date', 'consultant_user_id',
                'assigned_leads_count', 'follow_count', 'trial_count', 'converted_count',
                'lost_count', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_growth_loss_reasons' => [
                'id', 'tenant_id', 'campus_id', 'reason_code', 'reason_name',
                'reason_group', 'status', 'sort_order', 'created_by', 'updated_by',
                'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_growth_lead_loss_records' => [
                'id', 'tenant_id', 'campus_id', 'lead_id', 'loss_reason_id',
                'lost_by', 'lost_at', 'detail', 'created_by', 'updated_by',
                'created_at', 'updated_at',
            ],
            'edu_growth_campaigns' => [
                'id', 'tenant_id', 'campus_id', 'campaign_code', 'campaign_name',
                'source_id', 'start_date', 'end_date', 'budget_cents', 'status',
                'remark', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_growth_script_templates' => [
                'id', 'tenant_id', 'campus_id', 'template_code', 'template_name',
                'script_type', 'content', 'status', 'created_by', 'updated_by',
                'created_at', 'updated_at', 'deleted_at',
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function requiredIndexes(): array
    {
        return [
            'edu_growth_lead_scores' => ['uk_edu_growth_lead_scores_lead_date', 'idx_edu_growth_lead_scores_owner_level'],
            'edu_growth_lead_score_factors' => ['idx_edu_growth_lead_score_factors_score', 'idx_edu_growth_lead_score_factors_code'],
            'edu_growth_followup_strategies' => ['uk_edu_growth_followup_strategies_code', 'idx_edu_growth_followup_strategies_stage'],
            'edu_growth_followup_suggestions' => ['idx_edu_growth_followup_suggestions_owner_status', 'idx_edu_growth_followup_suggestions_lead'],
            'edu_growth_ai_talk_scripts' => ['idx_edu_growth_ai_talk_scripts_lead', 'idx_edu_growth_ai_talk_scripts_task'],
            'edu_growth_conversion_funnels' => ['uk_edu_growth_conversion_funnels_scope', 'idx_edu_growth_conversion_funnels_date'],
            'edu_growth_channel_costs' => ['idx_edu_growth_channel_costs_source_date', 'idx_edu_growth_channel_costs_date'],
            'edu_growth_channel_roi_daily' => ['uk_edu_growth_channel_roi_daily_source_date', 'idx_edu_growth_channel_roi_daily_date'],
            'edu_growth_consultant_metrics_daily' => ['uk_edu_growth_consultant_metrics_daily_user_date', 'idx_edu_growth_consultant_metrics_daily_date'],
            'edu_growth_loss_reasons' => ['uk_edu_growth_loss_reasons_code', 'idx_edu_growth_loss_reasons_group'],
            'edu_growth_lead_loss_records' => ['uk_edu_growth_lead_loss_records_lead', 'idx_edu_growth_lead_loss_records_reason'],
            'edu_growth_campaigns' => ['uk_edu_growth_campaigns_code', 'idx_edu_growth_campaigns_date_status'],
            'edu_growth_script_templates' => ['uk_edu_growth_script_templates_code', 'idx_edu_growth_script_templates_type'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function jsonColumns(): array
    {
        return [
            'edu_growth_ai_talk_scripts' => ['masked_input_json'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function bigintMoneyColumns(): array
    {
        return [
            'edu_growth_channel_costs' => ['amount_cents'],
            'edu_growth_channel_roi_daily' => ['cost_cents', 'converted_revenue_cents'],
            'edu_growth_campaigns' => ['budget_cents'],
        ];
    }

    private function growthMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_100000_create_v10_growth_tables.php';
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
