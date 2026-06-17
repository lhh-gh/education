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

namespace HyperfTests\Feature\Education\Operations;

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class AcademicOperationMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->operationMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testOperationTablesExistWithIndexes(): void
    {
        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasTable($table), "{$table} table missing");
        }

        foreach ($this->requiredIndexes() as $table => $indexes) {
            foreach ($indexes as $index) {
                self::assertTrue($this->hasIndex($table, $index), "{$table} missing index {$index}");
            }
        }
    }

    public function testOperationTablesHaveTenantAndCampusColumns(): void
    {
        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasColumn($table, 'tenant_id'), "{$table} missing tenant_id");
            self::assertTrue(Schema::hasColumn($table, 'campus_id'), "{$table} missing campus_id");
        }
    }

    public function testOperationColumnsMatchPlan(): void
    {
        foreach ($this->requiredColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertTrue(Schema::hasColumn($table, $column), "{$table} missing {$column}");
            }
        }
    }

    public function testJsonAndDecimalColumnsExist(): void
    {
        foreach (['old_values_json', 'new_values_json'] as $column) {
            self::assertSame('json', $this->columnType('edu_lesson_change_requests', $column));
        }

        self::assertSame('json', $this->columnType('edu_lesson_change_logs', 'before_json'));
        self::assertSame('json', $this->columnType('edu_lesson_change_logs', 'after_json'));
        self::assertSame('decimal', $this->columnType('edu_lesson_consumption_adjustments', 'credits'));
        self::assertSame('decimal', $this->columnType('edu_teacher_workload_records', 'credits'));
        self::assertSame('decimal', $this->columnType('edu_daily_operation_metrics', 'consumed_credits'));
    }

    public function testRollbackDropsOperationTablesInReverseOrder(): void
    {
        $migration = $this->operationMigration();

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
            'edu_lesson_change_requests' => [
                'id',
                'tenant_id',
                'campus_id',
                'lesson_id',
                'change_type',
                'status',
                'old_values_json',
                'new_values_json',
                'reason',
                'requested_by',
                'approved_by',
                'approved_at',
                'applied_at',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_lesson_change_logs' => [
                'id',
                'tenant_id',
                'campus_id',
                'lesson_id',
                'change_request_id',
                'change_type',
                'before_json',
                'after_json',
                'operator_id',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ],
            'edu_makeup_entitlements' => [
                'id',
                'tenant_id',
                'campus_id',
                'student_id',
                'course_id',
                'source_lesson_id',
                'source_leave_request_id',
                'status',
                'expires_at',
                'used_lesson_id',
                'used_at',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_makeup_records' => [
                'id',
                'tenant_id',
                'campus_id',
                'makeup_entitlement_id',
                'student_id',
                'makeup_lesson_id',
                'status',
                'arranged_by',
                'arranged_at',
                'completed_at',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_lesson_consumption_reviews' => [
                'id',
                'tenant_id',
                'campus_id',
                'lesson_id',
                'status',
                'submitted_by',
                'submitted_at',
                'reviewed_by',
                'reviewed_at',
                'review_note',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_lesson_consumption_adjustments' => [
                'id',
                'tenant_id',
                'campus_id',
                'original_consumption_id',
                'adjustment_consumption_id',
                'student_id',
                'student_course_account_id',
                'credits',
                'reason',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ],
            'edu_renewal_alerts' => [
                'id',
                'tenant_id',
                'campus_id',
                'student_id',
                'course_id',
                'student_course_account_id',
                'alert_type',
                'alert_level',
                'status',
                'trigger_value',
                'threshold_value',
                'due_date',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_renewal_tasks' => [
                'id',
                'tenant_id',
                'campus_id',
                'student_id',
                'course_id',
                'renewal_alert_id',
                'assignee_id',
                'status',
                'next_follow_at',
                'result',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_student_follow_records' => [
                'id',
                'tenant_id',
                'campus_id',
                'student_id',
                'renewal_task_id',
                'follow_type',
                'content',
                'next_follow_at',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ],
            'edu_teacher_workload_records' => [
                'id',
                'tenant_id',
                'campus_id',
                'teacher_id',
                'lesson_id',
                'workload_type',
                'lesson_type',
                'credits',
                'student_count',
                'present_count',
                'leave_count',
                'absent_count',
                'recorded_at',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ],
            'edu_daily_operation_metrics' => [
                'id',
                'tenant_id',
                'campus_id',
                'metric_date',
                'lessons_count',
                'pending_attendance_count',
                'consumed_credits',
                'present_count',
                'leave_count',
                'absent_count',
                'renewal_alert_count',
                'pending_review_count',
                'created_by',
                'updated_by',
                'created_at',
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
            'edu_lesson_change_requests' => [
                'idx_edu_lesson_change_requests_tenant_lesson_status',
                'idx_edu_lesson_change_requests_campus_status',
                'idx_edu_lesson_change_requests_requested_by',
                'idx_edu_lesson_change_requests_deleted_at',
            ],
            'edu_lesson_change_logs' => [
                'idx_edu_lesson_change_logs_tenant_lesson',
                'idx_edu_lesson_change_logs_request',
            ],
            'edu_makeup_entitlements' => [
                'uk_edu_makeup_entitlements_leave_student',
                'idx_edu_makeup_entitlements_student_status',
                'idx_edu_makeup_entitlements_expire',
                'idx_edu_makeup_entitlements_deleted_at',
            ],
            'edu_makeup_records' => [
                'uk_edu_makeup_records_entitlement_active',
                'idx_edu_makeup_records_student_status',
                'idx_edu_makeup_records_lesson',
                'idx_edu_makeup_records_deleted_at',
            ],
            'edu_lesson_consumption_reviews' => [
                'uk_edu_lesson_consumption_reviews_lesson',
                'idx_edu_lesson_consumption_reviews_tenant_status',
                'idx_edu_lesson_consumption_reviews_submitter',
                'idx_edu_lesson_consumption_reviews_deleted_at',
            ],
            'edu_lesson_consumption_adjustments' => [
                'idx_edu_lesson_consumption_adjustments_original',
                'idx_edu_lesson_consumption_adjustments_student',
                'idx_edu_lesson_consumption_adjustments_account',
            ],
            'edu_renewal_alerts' => [
                'uk_edu_renewal_alerts_open_account_type',
                'idx_edu_renewal_alerts_tenant_due',
                'idx_edu_renewal_alerts_student_status',
                'idx_edu_renewal_alerts_deleted_at',
            ],
            'edu_renewal_tasks' => [
                'idx_edu_renewal_tasks_assignee_status',
                'idx_edu_renewal_tasks_alert',
                'idx_edu_renewal_tasks_student',
                'idx_edu_renewal_tasks_deleted_at',
            ],
            'edu_student_follow_records' => [
                'idx_edu_student_follow_records_student_time',
                'idx_edu_student_follow_records_task_time',
            ],
            'edu_teacher_workload_records' => [
                'uk_edu_teacher_workload_records_lesson_teacher_type',
                'idx_edu_teacher_workload_records_teacher_date',
                'idx_edu_teacher_workload_records_lesson',
            ],
            'edu_daily_operation_metrics' => [
                'uk_edu_daily_operation_metrics_campus_date',
                'idx_edu_daily_operation_metrics_tenant_date',
            ],
        ];
    }

    private function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getConnection()
            ->select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]);

        return $indexes !== [];
    }

    private function columnType(string $table, string $column): string
    {
        $rows = Schema::getConnection()->select(
            'SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );

        self::assertNotEmpty($rows, "{$table}.{$column} column metadata missing");

        return (string) $rows[0]->DATA_TYPE;
    }

    private function operationMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_020000_create_v2_academic_operation_tables.php';
    }
}
