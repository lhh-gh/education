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

namespace HyperfTests\Feature\Education\Finance;

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class FinanceMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->financeMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testFinanceTablesExistWithIndexes(): void
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

    public function testFinanceTablesHaveTenantAndCampusColumns(): void
    {
        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasColumn($table, 'tenant_id'), "{$table} missing tenant_id");
            self::assertTrue(Schema::hasColumn($table, 'campus_id'), "{$table} missing campus_id");
        }
    }

    public function testFinanceColumnsMatchPlan(): void
    {
        foreach ($this->requiredColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertTrue(Schema::hasColumn($table, $column), "{$table} missing {$column}");
            }
        }
    }

    public function testFinanceTablesHaveIntegerMoneyColumns(): void
    {
        foreach ($this->integerMoneyColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertContains($this->columnType($table, $column), ['bigint', 'integer'], "{$table}.{$column} must be integer cents");
            }
        }
    }

    public function testJsonColumnsExist(): void
    {
        self::assertSame('json', $this->columnType('edu_payment_channels', 'config_json'));
        self::assertSame('json', $this->columnType('edu_payment_callbacks', 'raw_payload_json'));
        self::assertSame('json', $this->columnType('edu_refund_records', 'raw_payload_json'));
        self::assertSame('json', $this->columnType('edu_reconciliation_items', 'raw_row_json'));
    }

    public function testRollbackDropsFinanceTablesInReverseOrder(): void
    {
        $migration = $this->financeMigration();

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
            'edu_finance_orders' => [
                'id', 'tenant_id', 'campus_id', 'order_no', 'order_type', 'student_id', 'guardian_id',
                'enrollment_id', 'student_course_account_id', 'total_amount_cents', 'paid_amount_cents',
                'refund_amount_cents', 'discount_amount_cents', 'status', 'due_at', 'paid_at', 'remark',
                'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_finance_order_items' => [
                'id', 'tenant_id', 'campus_id', 'order_id', 'item_type', 'item_name', 'quantity',
                'unit_amount_cents', 'total_amount_cents', 'source_type', 'source_id',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_payment_channels' => [
                'id', 'tenant_id', 'campus_id', 'channel_code', 'channel_name', 'channel_type',
                'config_json', 'status', 'sort_order', 'created_by', 'updated_by', 'created_at',
                'updated_at', 'deleted_at',
            ],
            'edu_payment_records' => [
                'id', 'tenant_id', 'campus_id', 'order_id', 'payment_no', 'channel_code',
                'channel_trade_no', 'amount_cents', 'channel_fee_cents', 'status', 'paid_at',
                'payer_name', 'operator_id', 'remark', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_payment_callbacks' => [
                'id', 'tenant_id', 'campus_id', 'channel_code', 'channel_trade_no', 'payment_record_id',
                'raw_payload_json', 'signature_valid', 'processed', 'processed_at', 'error_message',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_refund_requests' => [
                'id', 'tenant_id', 'campus_id', 'order_id', 'payment_record_id', 'refund_no',
                'refund_amount_cents', 'reason', 'status', 'requested_by', 'reviewed_by', 'reviewed_at',
                'review_note', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_refund_records' => [
                'id', 'tenant_id', 'campus_id', 'refund_request_id', 'payment_record_id', 'refund_trade_no',
                'refund_amount_cents', 'status', 'refunded_at', 'raw_payload_json',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_receipts' => [
                'id', 'tenant_id', 'campus_id', 'receipt_no', 'order_id', 'student_id', 'amount_cents',
                'status', 'issued_by', 'issued_at', 'voided_by', 'voided_at', 'pdf_url',
                'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_reconciliation_batches' => [
                'id', 'tenant_id', 'campus_id', 'batch_no', 'channel_code', 'business_date', 'status',
                'total_count', 'matched_count', 'unmatched_count', 'total_amount_cents',
                'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_reconciliation_items' => [
                'id', 'tenant_id', 'campus_id', 'batch_id', 'channel_trade_no', 'payment_record_id',
                'amount_cents', 'trade_time', 'match_status', 'difference_cents', 'raw_row_json',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_finance_adjustments' => [
                'id', 'tenant_id', 'campus_id', 'order_id', 'adjustment_type', 'amount_cents',
                'reason', 'operator_id', 'source_type', 'source_id',
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
            'edu_finance_orders' => ['uk_edu_finance_orders_tenant_order_no', 'idx_edu_finance_orders_student_status', 'idx_edu_finance_orders_enrollment'],
            'edu_finance_order_items' => ['idx_edu_finance_order_items_order', 'idx_edu_finance_order_items_source'],
            'edu_payment_channels' => ['uk_edu_payment_channels_tenant_code', 'idx_edu_payment_channels_status'],
            'edu_payment_records' => ['uk_edu_payment_records_tenant_payment_no', 'uk_edu_payment_records_channel_trade_no', 'idx_edu_payment_records_order_status'],
            'edu_payment_callbacks' => ['uk_edu_payment_callbacks_channel_trade', 'idx_edu_payment_callbacks_processed'],
            'edu_refund_requests' => ['uk_edu_refund_requests_tenant_refund_no', 'idx_edu_refund_requests_status', 'idx_edu_refund_requests_order'],
            'edu_refund_records' => ['uk_edu_refund_records_request', 'idx_edu_refund_records_trade'],
            'edu_receipts' => ['uk_edu_receipts_tenant_receipt_no', 'idx_edu_receipts_order', 'idx_edu_receipts_student_status'],
            'edu_reconciliation_batches' => ['uk_edu_reconciliation_batches_channel_date', 'idx_edu_reconciliation_batches_status'],
            'edu_reconciliation_items' => ['idx_edu_reconciliation_items_batch', 'idx_edu_reconciliation_items_trade'],
            'edu_finance_adjustments' => ['idx_edu_finance_adjustments_order', 'idx_edu_finance_adjustments_source'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function integerMoneyColumns(): array
    {
        return [
            'edu_finance_orders' => ['total_amount_cents', 'paid_amount_cents', 'refund_amount_cents', 'discount_amount_cents'],
            'edu_finance_order_items' => ['unit_amount_cents', 'total_amount_cents'],
            'edu_payment_records' => ['amount_cents', 'channel_fee_cents'],
            'edu_refund_requests' => ['refund_amount_cents'],
            'edu_refund_records' => ['refund_amount_cents'],
            'edu_receipts' => ['amount_cents'],
            'edu_reconciliation_batches' => ['total_amount_cents'],
            'edu_reconciliation_items' => ['amount_cents', 'difference_cents'],
            'edu_finance_adjustments' => ['amount_cents'],
        ];
    }

    private function financeMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_040000_create_v4_finance_tables.php';
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
