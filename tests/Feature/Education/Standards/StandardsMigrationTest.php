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

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class StandardsMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->standardsMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testStandardTablesIndexesAndVersionColumnsExist(): void
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

    public function testStandardTablesUseJsonSnapshotsAndOverrides(): void
    {
        foreach ($this->jsonColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertSame('json', $this->columnType($table, $column), "{$table}.{$column} must be json");
            }
        }
    }

    public function testRollbackDropsStandardTablesInReverseOrder(): void
    {
        $migration = $this->standardsMigration();

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
            'edu_course_service_packages' => [
                'id', 'tenant_id', 'campus_id', 'package_code', 'package_name',
                'course_id', 'version_no', 'status', 'guardian_visible', 'description',
                'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_course_stage_goals' => [
                'id', 'tenant_id', 'campus_id', 'service_package_id', 'goal_code',
                'goal_name', 'goal_content', 'sort_order', 'status', 'created_by',
                'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_course_ability_points' => [
                'id', 'tenant_id', 'campus_id', 'ability_code', 'ability_name',
                'ability_group', 'description', 'status', 'created_by', 'updated_by',
                'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_course_stage_goal_ability_relations' => [
                'id', 'tenant_id', 'campus_id', 'stage_goal_id', 'ability_point_id',
                'weight', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_trial_lesson_standards' => [
                'id', 'tenant_id', 'campus_id', 'course_id', 'standard_code',
                'standard_name', 'version_no', 'status', 'guardian_visible',
                'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_trial_lesson_standard_items' => [
                'id', 'tenant_id', 'campus_id', 'trial_lesson_standard_id',
                'item_name', 'item_content', 'score_weight', 'sort_order',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_teaching_delivery_standards' => [
                'id', 'tenant_id', 'campus_id', 'course_id', 'standard_code',
                'standard_name', 'lesson_type', 'content', 'version_no', 'status',
                'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_service_template_sets' => [
                'id', 'tenant_id', 'campus_id', 'template_set_code', 'template_set_name',
                'course_id', 'status', 'version_no', 'created_by', 'updated_by',
                'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_service_template_items' => [
                'id', 'tenant_id', 'campus_id', 'template_set_id', 'item_type',
                'item_title', 'item_content', 'sort_order', 'created_by', 'updated_by',
                'created_at', 'updated_at',
            ],
            'edu_course_materials' => [
                'id', 'tenant_id', 'campus_id', 'material_code', 'material_name',
                'course_id', 'material_type', 'file_url', 'status', 'guardian_visible',
                'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_course_feedback_records' => [
                'id', 'tenant_id', 'campus_id', 'course_id', 'standard_version_id',
                'feedback_type', 'score', 'content', 'source_type', 'source_id',
                'submitted_by', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_course_quality_metrics_daily' => [
                'id', 'tenant_id', 'campus_id', 'metric_date', 'course_id',
                'feedback_count', 'average_score', 'trial_feedback_count',
                'delivery_feedback_count', 'created_by', 'updated_by',
                'created_at', 'updated_at',
            ],
            'edu_course_standard_versions' => [
                'id', 'tenant_id', 'campus_id', 'business_type', 'business_id',
                'version_no', 'status', 'snapshot_json', 'published_by',
                'published_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_course_standard_publish_logs' => [
                'id', 'tenant_id', 'campus_id', 'standard_version_id', 'business_type',
                'business_id', 'from_status', 'to_status', 'operator_id', 'note',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_course_localization_overrides' => [
                'id', 'tenant_id', 'campus_id', 'standard_version_id', 'override_json',
                'status', 'published_at', 'created_by', 'updated_by', 'created_at',
                'updated_at', 'deleted_at',
            ],
            'edu_course_standard_review_records' => [
                'id', 'tenant_id', 'campus_id', 'business_type', 'business_id',
                'standard_version_id', 'reviewer_id', 'status', 'review_note',
                'reviewed_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function requiredIndexes(): array
    {
        return [
            'edu_course_service_packages' => ['uk_edu_course_service_packages_code_version', 'idx_edu_course_service_packages_course_status'],
            'edu_course_stage_goals' => ['uk_edu_course_stage_goals_package_code', 'idx_edu_course_stage_goals_package_order'],
            'edu_course_ability_points' => ['uk_edu_course_ability_points_code', 'idx_edu_course_ability_points_group'],
            'edu_course_stage_goal_ability_relations' => ['uk_edu_stage_goal_ability', 'idx_edu_stage_goal_ability_point'],
            'edu_trial_lesson_standards' => ['uk_edu_trial_lesson_standards_code_version', 'idx_edu_trial_lesson_standards_course_status'],
            'edu_trial_lesson_standard_items' => ['idx_edu_trial_lesson_standard_items_standard'],
            'edu_teaching_delivery_standards' => ['uk_edu_teaching_delivery_standards_code_version', 'idx_edu_teaching_delivery_standards_course_status'],
            'edu_service_template_sets' => ['uk_edu_service_template_sets_code_version', 'idx_edu_service_template_sets_course_status'],
            'edu_service_template_items' => ['idx_edu_service_template_items_set'],
            'edu_course_materials' => ['uk_edu_course_materials_code', 'idx_edu_course_materials_course_status'],
            'edu_course_feedback_records' => ['idx_edu_course_feedback_records_course_time', 'idx_edu_course_feedback_records_version'],
            'edu_course_quality_metrics_daily' => ['uk_edu_course_quality_metrics_daily_course_date', 'idx_edu_course_quality_metrics_daily_date'],
            'edu_course_standard_versions' => ['uk_edu_course_standard_versions_business_version', 'idx_edu_course_standard_versions_status'],
            'edu_course_standard_publish_logs' => ['idx_edu_course_standard_publish_logs_version'],
            'edu_course_localization_overrides' => ['uk_edu_course_localization_overrides_version_campus', 'idx_edu_course_localization_overrides_campus'],
            'edu_course_standard_review_records' => ['idx_edu_course_standard_review_records_reviewer', 'idx_edu_course_standard_review_records_business'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function jsonColumns(): array
    {
        return [
            'edu_course_standard_versions' => ['snapshot_json'],
            'edu_course_localization_overrides' => ['override_json'],
        ];
    }

    private function standardsMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_110000_create_v11_course_standard_tables.php';
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
