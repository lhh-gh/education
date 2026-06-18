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

namespace HyperfTests\Feature\Education\Content;

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class ContentMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->contentMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testContentTablesIndexesAndVersionColumnsExist(): void
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

    public function testContentVersionsUseJsonSnapshots(): void
    {
        self::assertSame('json', $this->columnType('edu_learning_material_versions', 'snapshot_json'));
    }

    public function testRollbackDropsContentTablesInReverseOrder(): void
    {
        $migration = $this->contentMigration();

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
            'edu_learning_materials' => [
                'id', 'tenant_id', 'campus_id', 'material_code', 'material_name',
                'course_id', 'material_type', 'status', 'guardian_visible',
                'current_version_id', 'summary', 'created_by', 'updated_by',
                'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_learning_material_versions' => [
                'id', 'tenant_id', 'campus_id', 'material_id', 'version_no',
                'title', 'content', 'status', 'snapshot_json', 'published_at',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_learning_material_attachments' => [
                'id', 'tenant_id', 'campus_id', 'material_version_id', 'file_name',
                'file_url', 'file_type', 'file_size', 'sort_order', 'created_by',
                'updated_by', 'created_at', 'updated_at',
            ],
            'edu_learning_material_relations' => [
                'id', 'tenant_id', 'campus_id', 'material_id', 'target_type',
                'target_id', 'relation_note', 'created_by', 'updated_by',
                'created_at', 'updated_at',
            ],
            'edu_lesson_material_usages' => [
                'id', 'tenant_id', 'campus_id', 'lesson_id', 'teacher_id',
                'material_id', 'material_version_id', 'usage_type', 'used_at',
                'remark', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_teacher_material_favorites' => [
                'id', 'tenant_id', 'campus_id', 'teacher_id', 'material_id',
                'favorited_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_student_works' => [
                'id', 'tenant_id', 'campus_id', 'student_id', 'lesson_id',
                'teacher_id', 'stage_goal_id', 'title', 'description', 'status',
                'published_at', 'created_by', 'updated_by', 'created_at',
                'updated_at', 'deleted_at',
            ],
            'edu_student_work_attachments' => [
                'id', 'tenant_id', 'campus_id', 'student_work_id', 'file_name',
                'file_url', 'file_type', 'file_size', 'sort_order', 'created_by',
                'updated_by', 'created_at', 'updated_at',
            ],
            'edu_stage_achievement_showcases' => [
                'id', 'tenant_id', 'campus_id', 'student_id', 'stage_goal_id',
                'title', 'summary', 'status', 'published_at', 'withdrawn_at',
                'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_showcase_items' => [
                'id', 'tenant_id', 'campus_id', 'showcase_id', 'item_type',
                'student_work_id', 'material_id', 'title', 'content', 'sort_order',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_material_read_records' => [
                'id', 'tenant_id', 'campus_id', 'material_id', 'material_version_id',
                'student_id', 'guardian_user_id', 'teacher_id', 'read_at',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_showcase_read_records' => [
                'id', 'tenant_id', 'campus_id', 'showcase_id', 'student_id',
                'guardian_user_id', 'read_at', 'created_by', 'updated_by',
                'created_at', 'updated_at',
            ],
            'edu_material_publish_logs' => [
                'id', 'tenant_id', 'campus_id', 'material_id', 'material_version_id',
                'from_status', 'to_status', 'operator_id', 'note', 'created_by',
                'updated_by', 'created_at', 'updated_at',
            ],
            'edu_content_review_records' => [
                'id', 'tenant_id', 'campus_id', 'business_type', 'business_id',
                'reviewer_id', 'status', 'review_note', 'reviewed_at', 'created_by',
                'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_material_usage_metrics_daily' => [
                'id', 'tenant_id', 'campus_id', 'metric_date', 'material_id',
                'course_id', 'teacher_use_count', 'guardian_read_count',
                'favorite_count', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_student_work_metrics_daily' => [
                'id', 'tenant_id', 'campus_id', 'metric_date', 'student_id',
                'teacher_id', 'created_count', 'published_count', 'showcase_count',
                'guardian_read_count', 'created_by', 'updated_by', 'created_at',
                'updated_at',
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function requiredIndexes(): array
    {
        return [
            'edu_learning_materials' => ['uk_edu_learning_materials_code', 'idx_edu_learning_materials_course_status'],
            'edu_learning_material_versions' => ['uk_edu_learning_material_versions_material_version', 'idx_edu_learning_material_versions_status'],
            'edu_learning_material_attachments' => ['idx_edu_learning_material_attachments_version'],
            'edu_learning_material_relations' => ['uk_edu_learning_material_relations_target', 'idx_edu_learning_material_relations_lookup'],
            'edu_lesson_material_usages' => ['idx_edu_lesson_material_usages_lesson', 'idx_edu_lesson_material_usages_material'],
            'edu_teacher_material_favorites' => ['uk_edu_teacher_material_favorites_teacher_material', 'idx_edu_teacher_material_favorites_teacher'],
            'edu_student_works' => ['idx_edu_student_works_student_status', 'idx_edu_student_works_teacher'],
            'edu_student_work_attachments' => ['idx_edu_student_work_attachments_work'],
            'edu_stage_achievement_showcases' => ['idx_edu_stage_achievement_showcases_student_status', 'idx_edu_stage_achievement_showcases_stage'],
            'edu_showcase_items' => ['idx_edu_showcase_items_showcase', 'idx_edu_showcase_items_work'],
            'edu_material_read_records' => ['uk_edu_material_read_records_reader', 'idx_edu_material_read_records_material'],
            'edu_showcase_read_records' => ['uk_edu_showcase_read_records_reader', 'idx_edu_showcase_read_records_showcase'],
            'edu_material_publish_logs' => ['idx_edu_material_publish_logs_material'],
            'edu_content_review_records' => ['idx_edu_content_review_records_reviewer', 'idx_edu_content_review_records_business'],
            'edu_material_usage_metrics_daily' => ['uk_edu_material_usage_metrics_daily_scope', 'idx_edu_material_usage_metrics_daily_date'],
            'edu_student_work_metrics_daily' => ['uk_edu_student_work_metrics_daily_scope', 'idx_edu_student_work_metrics_daily_date'],
        ];
    }

    private function contentMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_120000_create_v12_learning_content_tables.php';
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
