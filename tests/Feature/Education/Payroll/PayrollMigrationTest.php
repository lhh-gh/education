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

namespace HyperfTests\Feature\Education\Payroll;

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class PayrollMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->payrollMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testPayrollTablesAndIntegerMoneyColumnsExist(): void
    {
        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasTable($table), "{$table} table missing");
        }

        foreach ($this->integerMoneyColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertContains($this->columnType($table, $column), ['bigint', 'integer'], "{$table}.{$column} must be integer cents");
            }
        }
    }

    public function testPayrollTablesHaveTenantCampusColumnsAndIndexes(): void
    {
        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasColumn($table, 'tenant_id'), "{$table} missing tenant_id");
            self::assertTrue(Schema::hasColumn($table, 'campus_id'), "{$table} missing campus_id");
        }

        foreach ($this->requiredIndexes() as $table => $indexes) {
            foreach ($indexes as $index) {
                self::assertTrue($this->hasIndex($table, $index), "{$table} missing index {$index}");
            }
        }
    }

    public function testPayrollColumnsAndJsonSnapshotsMatchPlan(): void
    {
        foreach ($this->requiredColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertTrue(Schema::hasColumn($table, $column), "{$table} missing {$column}");
            }
        }

        self::assertSame('json', $this->columnType('edu_teacher_salary_rules', 'campus_scope_json'));
        self::assertSame('json', $this->columnType('edu_teacher_salary_rule_items', 'condition_json'));
        self::assertSame('json', $this->columnType('edu_teacher_salary_slips', 'workload_snapshot_json'));
        self::assertSame('json', $this->columnType('edu_teacher_salary_items', 'snapshot_json'));
    }

    public function testRollbackDropsPayrollTablesInReverseOrder(): void
    {
        $migration = $this->payrollMigration();

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
            'edu_teacher_salary_rules' => [
                'id', 'tenant_id', 'campus_id', 'rule_code', 'rule_name', 'campus_scope_json', 'teacher_grade',
                'status', 'effective_start', 'effective_end', 'priority', 'remark',
                'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_teacher_salary_rule_items' => [
                'id', 'tenant_id', 'campus_id', 'rule_id', 'item_type', 'workload_type', 'course_id', 'class_type',
                'calculation_method', 'unit_amount_cents', 'rate', 'condition_json', 'sort_order',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_teacher_salary_batches' => [
                'id', 'tenant_id', 'campus_id', 'batch_no', 'salary_month', 'status', 'source_start', 'source_end',
                'teacher_count', 'total_amount_cents', 'calculated_by', 'calculated_at', 'submitted_at', 'approved_at',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_teacher_salary_slips' => [
                'id', 'tenant_id', 'campus_id', 'batch_id', 'teacher_id', 'salary_month', 'status',
                'workload_snapshot_json', 'gross_amount_cents', 'adjustment_amount_cents', 'payable_amount_cents',
                'paid_amount_cents', 'approved_at', 'paid_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_teacher_salary_items' => [
                'id', 'tenant_id', 'campus_id', 'salary_slip_id', 'teacher_id', 'source_workload_id', 'item_type',
                'item_name', 'quantity', 'unit_amount_cents', 'amount_cents', 'rule_item_id', 'snapshot_json',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_teacher_salary_adjustments' => [
                'id', 'tenant_id', 'campus_id', 'salary_slip_id', 'teacher_id', 'adjustment_type', 'amount_cents',
                'reason', 'operator_id', 'approved_by', 'approved_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_teacher_salary_reviews' => [
                'id', 'tenant_id', 'campus_id', 'batch_id', 'salary_slip_id', 'review_level', 'reviewer_id',
                'status', 'review_note', 'reviewed_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_teacher_salary_payments' => [
                'id', 'tenant_id', 'campus_id', 'salary_slip_id', 'teacher_id', 'payment_no', 'paid_amount_cents',
                'payment_method', 'paid_at', 'operator_id', 'remark', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_teacher_workload_disputes' => [
                'id', 'tenant_id', 'campus_id', 'teacher_id', 'source_workload_id', 'salary_slip_id', 'dispute_type',
                'content', 'status', 'reviewed_by', 'reviewed_at', 'review_note',
                'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_teacher_performance_metrics' => [
                'id', 'tenant_id', 'campus_id', 'metric_month', 'teacher_id', 'lesson_count', 'workload_credits',
                'student_count', 'attendance_rate', 'family_service_count', 'dispute_count', 'salary_amount_cents',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function requiredIndexes(): array
    {
        return [
            'edu_teacher_salary_rules' => ['uk_edu_teacher_salary_rules_tenant_code', 'idx_edu_teacher_salary_rules_status'],
            'edu_teacher_salary_rule_items' => ['idx_edu_teacher_salary_rule_items_rule', 'idx_edu_teacher_salary_rule_items_course'],
            'edu_teacher_salary_batches' => ['uk_edu_teacher_salary_batches_tenant_no', 'uk_edu_teacher_salary_batches_month_campus', 'idx_edu_teacher_salary_batches_status'],
            'edu_teacher_salary_slips' => ['uk_edu_teacher_salary_slips_batch_teacher', 'idx_edu_teacher_salary_slips_teacher_month', 'idx_edu_teacher_salary_slips_status'],
            'edu_teacher_salary_items' => ['idx_edu_teacher_salary_items_slip', 'idx_edu_teacher_salary_items_workload'],
            'edu_teacher_salary_adjustments' => ['idx_edu_teacher_salary_adjustments_slip', 'idx_edu_teacher_salary_adjustments_teacher'],
            'edu_teacher_salary_reviews' => ['idx_edu_teacher_salary_reviews_reviewer', 'idx_edu_teacher_salary_reviews_batch'],
            'edu_teacher_salary_payments' => ['uk_edu_teacher_salary_payments_tenant_no', 'idx_edu_teacher_salary_payments_slip', 'idx_edu_teacher_salary_payments_teacher'],
            'edu_teacher_workload_disputes' => ['idx_edu_teacher_workload_disputes_teacher_status', 'idx_edu_teacher_workload_disputes_workload'],
            'edu_teacher_performance_metrics' => ['uk_edu_teacher_performance_metrics_month_teacher', 'idx_edu_teacher_performance_metrics_teacher'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function integerMoneyColumns(): array
    {
        return [
            'edu_teacher_salary_rule_items' => ['unit_amount_cents'],
            'edu_teacher_salary_batches' => ['total_amount_cents'],
            'edu_teacher_salary_slips' => ['gross_amount_cents', 'adjustment_amount_cents', 'payable_amount_cents', 'paid_amount_cents'],
            'edu_teacher_salary_items' => ['unit_amount_cents', 'amount_cents'],
            'edu_teacher_salary_adjustments' => ['amount_cents'],
            'edu_teacher_salary_payments' => ['paid_amount_cents'],
            'edu_teacher_performance_metrics' => ['salary_amount_cents'],
        ];
    }

    private function payrollMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_050000_create_v5_payroll_tables.php';
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
