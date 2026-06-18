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

namespace HyperfTests\Feature\Education\Family;

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class FamilyServiceMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->familyMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testFamilyTablesIndexesAndAttachmentColumnsExist(): void
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

        foreach (['file_name', 'file_url', 'file_size', 'uploaded_by'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_family_service_attachments', $column), "attachment missing {$column}");
        }
    }

    public function testFamilyTablesUseExpectedColumnTypes(): void
    {
        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasColumn($table, 'tenant_id'), "{$table} missing tenant_id");
        }

        foreach ($this->dateColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertSame('date', $this->columnType($table, $column), "{$table}.{$column} must be date");
            }
        }

        foreach ($this->textColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertSame('text', $this->columnType($table, $column), "{$table}.{$column} must be text");
            }
        }
    }

    public function testRollbackDropsFamilyTablesInReverseOrder(): void
    {
        $migration = $this->familyMigration();

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
            'edu_lesson_comments' => [
                'id', 'tenant_id', 'campus_id', 'lesson_id', 'student_id', 'teacher_id', 'content', 'status',
                'published_at', 'withdrawn_at', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_lesson_comment_templates' => [
                'id', 'tenant_id', 'campus_id', 'template_code', 'template_name', 'content', 'course_id', 'status',
                'sort_order', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_student_performance_tags' => [
                'id', 'tenant_id', 'campus_id', 'tag_code', 'tag_name', 'tag_type', 'status', 'sort_order',
                'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_lesson_comment_tag_relations' => [
                'id', 'tenant_id', 'campus_id', 'lesson_comment_id', 'performance_tag_id', 'student_id',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_homework_assignments' => [
                'id', 'tenant_id', 'campus_id', 'title', 'content', 'course_id', 'class_id', 'lesson_id',
                'status', 'publish_at', 'due_at', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_homework_targets' => [
                'id', 'tenant_id', 'campus_id', 'homework_assignment_id', 'student_id', 'guardian_id', 'status',
                'submitted_at', 'reviewed_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_homework_submissions' => [
                'id', 'tenant_id', 'campus_id', 'homework_target_id', 'student_id', 'guardian_id', 'content',
                'attachment_count', 'submitted_at', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_homework_reviews' => [
                'id', 'tenant_id', 'campus_id', 'homework_submission_id', 'teacher_id', 'score', 'content',
                'reviewed_at', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_growth_records' => [
                'id', 'tenant_id', 'campus_id', 'student_id', 'teacher_id', 'record_type', 'title', 'content',
                'status', 'published_at', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_learning_reports' => [
                'id', 'tenant_id', 'campus_id', 'student_id', 'report_title', 'report_period', 'status',
                'published_at', 'withdrawn_at', 'summary', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_learning_report_items' => [
                'id', 'tenant_id', 'campus_id', 'learning_report_id', 'item_type', 'title', 'content',
                'sort_order', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_family_messages' => [
                'id', 'tenant_id', 'campus_id', 'thread_id', 'student_id', 'sender_type', 'sender_user_id',
                'receiver_user_id', 'content', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_family_read_receipts' => [
                'id', 'tenant_id', 'campus_id', 'business_type', 'business_id', 'student_id', 'reader_type',
                'reader_user_id', 'read_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_service_quality_metrics' => [
                'id', 'tenant_id', 'campus_id', 'metric_date', 'teacher_id', 'student_id', 'comment_count',
                'homework_review_count', 'report_count', 'message_response_minutes', 'created_by', 'updated_by',
                'created_at', 'updated_at',
            ],
            'edu_family_service_attachments' => [
                'id', 'tenant_id', 'campus_id', 'business_type', 'business_id', 'student_id', 'file_name',
                'file_url', 'file_size', 'uploaded_by', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function requiredIndexes(): array
    {
        return [
            'edu_lesson_comments' => ['idx_edu_lesson_comments_lesson_student', 'idx_edu_lesson_comments_student_status'],
            'edu_lesson_comment_templates' => ['uk_edu_lesson_comment_templates_code', 'idx_edu_lesson_comment_templates_course'],
            'edu_student_performance_tags' => ['uk_edu_student_performance_tags_code', 'idx_edu_student_performance_tags_type'],
            'edu_lesson_comment_tag_relations' => ['uk_edu_lesson_comment_tag_relations_comment_tag', 'idx_edu_lesson_comment_tag_relations_student'],
            'edu_homework_assignments' => ['idx_edu_homework_assignments_course_status', 'idx_edu_homework_assignments_due'],
            'edu_homework_targets' => ['uk_edu_homework_targets_assignment_student', 'idx_edu_homework_targets_student_status'],
            'edu_homework_submissions' => ['idx_edu_homework_submissions_target', 'idx_edu_homework_submissions_student'],
            'edu_homework_reviews' => ['uk_edu_homework_reviews_submission', 'idx_edu_homework_reviews_teacher'],
            'edu_growth_records' => ['idx_edu_growth_records_student_status', 'idx_edu_growth_records_teacher'],
            'edu_learning_reports' => ['idx_edu_learning_reports_student_status', 'idx_edu_learning_reports_period'],
            'edu_learning_report_items' => ['idx_edu_learning_report_items_report'],
            'edu_family_messages' => ['idx_edu_family_messages_thread_time', 'idx_edu_family_messages_student'],
            'edu_family_read_receipts' => ['uk_edu_family_read_receipts_reader', 'idx_edu_family_read_receipts_student'],
            'edu_service_quality_metrics' => ['uk_edu_service_quality_metrics_scope_date', 'idx_edu_service_quality_metrics_teacher'],
            'edu_family_service_attachments' => ['idx_edu_family_service_attachments_business', 'idx_edu_family_service_attachments_student'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function dateColumns(): array
    {
        return [
            'edu_service_quality_metrics' => ['metric_date'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function textColumns(): array
    {
        return [
            'edu_lesson_comments' => ['content'],
            'edu_lesson_comment_templates' => ['content'],
            'edu_homework_assignments' => ['content'],
            'edu_homework_submissions' => ['content'],
            'edu_homework_reviews' => ['content'],
            'edu_growth_records' => ['content'],
            'edu_learning_reports' => ['summary'],
            'edu_learning_report_items' => ['content'],
            'edu_family_messages' => ['content'],
        ];
    }

    private function familyMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_070000_create_v7_family_service_tables.php';
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
