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

namespace HyperfTests\Feature\Education\Group;

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class GroupMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->groupMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testGroupTablesIndexesAndJsonColumnsExist(): void
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

        foreach ($this->jsonColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertSame('json', $this->columnType($table, $column), "{$table}.{$column} must be json");
            }
        }
    }

    public function testGroupTablesHaveTenantCampusAndIntegerMoneyColumns(): void
    {
        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasColumn($table, 'tenant_id'), "{$table} missing tenant_id");
            self::assertTrue(Schema::hasColumn($table, 'campus_id'), "{$table} missing campus_id");
        }

        foreach ($this->integerMoneyColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertContains($this->columnType($table, $column), ['bigint', 'integer'], "{$table}.{$column} must be integer cents");
            }
        }
    }

    public function testRollbackDropsGroupTablesInReverseOrder(): void
    {
        $migration = $this->groupMigration();

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
            'edu_org_units' => [
                'id', 'tenant_id', 'campus_id', 'parent_id', 'code', 'name', 'unit_type', 'path', 'level',
                'status', 'sort_order', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_campus_org_relations' => [
                'id', 'tenant_id', 'campus_id', 'org_unit_id', 'relation_type', 'effective_start', 'effective_end',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_data_permission_scopes' => [
                'id', 'tenant_id', 'campus_id', 'scope_code', 'scope_name', 'scope_type', 'scope_value_json',
                'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_user_data_permissions' => [
                'id', 'tenant_id', 'campus_id', 'user_id', 'scope_id', 'scope_type', 'effective_start', 'effective_end',
                'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_approval_templates' => [
                'id', 'tenant_id', 'campus_id', 'template_code', 'template_name', 'business_type', 'status',
                'version', 'config_json', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_approval_nodes' => [
                'id', 'tenant_id', 'campus_id', 'template_id', 'node_code', 'node_name', 'sort_order',
                'assignee_type', 'assignee_value_json', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_approval_instances' => [
                'id', 'tenant_id', 'campus_id', 'template_id', 'business_type', 'business_id', 'status',
                'current_node_id', 'initiator_id', 'payload_json', 'completed_at', 'created_by', 'updated_by',
                'created_at', 'updated_at',
            ],
            'edu_approval_tasks' => [
                'id', 'tenant_id', 'campus_id', 'approval_instance_id', 'node_id', 'assignee_user_id',
                'status', 'due_at', 'completed_at', 'result', 'comment', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_approval_logs' => [
                'id', 'tenant_id', 'campus_id', 'approval_instance_id', 'task_id', 'operator_id', 'action',
                'before_status', 'after_status', 'comment', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_contracts' => [
                'id', 'tenant_id', 'campus_id', 'contract_no', 'contract_type', 'title', 'counterparty_name',
                'amount_cents', 'status', 'start_date', 'end_date', 'owner_user_id', 'risk_level',
                'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_contract_parties' => [
                'id', 'tenant_id', 'campus_id', 'contract_id', 'party_type', 'party_name', 'contact_name',
                'contact_mobile', 'identity_no', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_contract_attachments' => [
                'id', 'tenant_id', 'campus_id', 'contract_id', 'file_name', 'file_url', 'file_size',
                'uploaded_by', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_contract_renewals' => [
                'id', 'tenant_id', 'campus_id', 'contract_id', 'renewal_type', 'status', 'due_date',
                'handled_by', 'handled_at', 'result', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_group_operation_metrics' => [
                'id', 'tenant_id', 'campus_id', 'metric_date', 'org_unit_id', 'campus_count', 'student_count',
                'revenue_cents', 'consumed_credits', 'renewal_alert_count', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_franchise_records' => [
                'id', 'tenant_id', 'campus_id', 'franchise_code', 'franchise_name', 'contact_name', 'contact_mobile',
                'region', 'status', 'signed_contract_id', 'remark', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_risk_audit_events' => [
                'id', 'tenant_id', 'campus_id', 'event_type', 'risk_level', 'business_type', 'business_id',
                'operator_id', 'summary', 'payload_json', 'handled', 'handled_by', 'handled_at',
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
            'edu_org_units' => ['uk_edu_org_units_tenant_code', 'idx_edu_org_units_parent', 'idx_edu_org_units_path'],
            'edu_campus_org_relations' => ['uk_edu_campus_org_relations_org_campus', 'idx_edu_campus_org_relations_campus'],
            'edu_data_permission_scopes' => ['uk_edu_data_permission_scopes_code', 'idx_edu_data_permission_scopes_type'],
            'edu_user_data_permissions' => ['uk_edu_user_data_permissions_user_scope', 'idx_edu_user_data_permissions_user_type'],
            'edu_approval_templates' => ['uk_edu_approval_templates_code_version', 'idx_edu_approval_templates_business'],
            'edu_approval_nodes' => ['uk_edu_approval_nodes_template_node', 'idx_edu_approval_nodes_template_order'],
            'edu_approval_instances' => ['uk_edu_approval_instances_business', 'idx_edu_approval_instances_status'],
            'edu_approval_tasks' => ['idx_edu_approval_tasks_assignee_status', 'idx_edu_approval_tasks_instance'],
            'edu_approval_logs' => ['idx_edu_approval_logs_instance', 'idx_edu_approval_logs_operator'],
            'edu_contracts' => ['uk_edu_contracts_tenant_no', 'idx_edu_contracts_status_expire', 'idx_edu_contracts_owner'],
            'edu_contract_parties' => ['idx_edu_contract_parties_contract', 'idx_edu_contract_parties_name'],
            'edu_contract_attachments' => ['idx_edu_contract_attachments_contract'],
            'edu_contract_renewals' => ['idx_edu_contract_renewals_due', 'idx_edu_contract_renewals_contract'],
            'edu_group_operation_metrics' => ['uk_edu_group_operation_metrics_scope_date', 'idx_edu_group_operation_metrics_date'],
            'edu_franchise_records' => ['uk_edu_franchise_records_code', 'idx_edu_franchise_records_status'],
            'edu_risk_audit_events' => ['idx_edu_risk_audit_events_level', 'idx_edu_risk_audit_events_business'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function jsonColumns(): array
    {
        return [
            'edu_data_permission_scopes' => ['scope_value_json'],
            'edu_approval_templates' => ['config_json'],
            'edu_approval_nodes' => ['assignee_value_json'],
            'edu_approval_instances' => ['payload_json'],
            'edu_risk_audit_events' => ['payload_json'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function integerMoneyColumns(): array
    {
        return [
            'edu_contracts' => ['amount_cents'],
            'edu_group_operation_metrics' => ['revenue_cents'],
        ];
    }

    private function groupMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_060000_create_v6_group_tables.php';
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
