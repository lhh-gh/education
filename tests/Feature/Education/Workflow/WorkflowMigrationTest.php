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

namespace HyperfTests\Feature\Education\Workflow;

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class WorkflowMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->workflowMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testWorkflowTablesIndexesAndDedupeKeysExist(): void
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

    public function testWorkflowTablesUseExpectedColumnTypes(): void
    {
        foreach ($this->jsonColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertSame('json', $this->columnType($table, $column), "{$table}.{$column} must be json");
            }
        }
    }

    public function testRollbackDropsWorkflowTablesInReverseOrder(): void
    {
        $migration = $this->workflowMigration();

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
            'edu_workflow_rules' => [
                'id', 'tenant_id', 'campus_id', 'rule_code', 'rule_name', 'event_type', 'status',
                'priority', 'dedupe_window_minutes', 'description', 'created_by', 'updated_by',
                'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_workflow_rule_conditions' => [
                'id', 'tenant_id', 'campus_id', 'rule_id', 'condition_field', 'operator',
                'condition_value_json', 'sort_order', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_workflow_rule_actions' => [
                'id', 'tenant_id', 'campus_id', 'rule_id', 'action_type', 'action_config_json',
                'sort_order', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_workflow_tasks' => [
                'id', 'tenant_id', 'campus_id', 'task_no', 'task_type', 'title', 'priority', 'status',
                'source_type', 'source_id', 'dedupe_key', 'due_at', 'completed_at', 'created_by',
                'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_workflow_task_assignees' => [
                'id', 'tenant_id', 'campus_id', 'workflow_task_id', 'user_id', 'assignee_type',
                'status', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_workflow_task_logs' => [
                'id', 'tenant_id', 'campus_id', 'workflow_task_id', 'operator_id', 'action',
                'before_status', 'after_status', 'content', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_workflow_task_comments' => [
                'id', 'tenant_id', 'campus_id', 'workflow_task_id', 'commenter_user_id', 'content',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_workflow_task_attachments' => [
                'id', 'tenant_id', 'campus_id', 'workflow_task_id', 'file_name', 'file_url',
                'file_size', 'uploaded_by', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_workflow_sla_policies' => [
                'id', 'tenant_id', 'campus_id', 'policy_code', 'policy_name', 'task_type',
                'due_minutes', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_workflow_escalation_policies' => [
                'id', 'tenant_id', 'campus_id', 'policy_code', 'task_type', 'overdue_minutes',
                'escalate_to_user_ids_json', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_operation_alerts' => [
                'id', 'tenant_id', 'campus_id', 'alert_no', 'alert_type', 'level', 'status',
                'title', 'content', 'source_type', 'source_id', 'dedupe_key', 'converted_task_id',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_operation_alert_logs' => [
                'id', 'tenant_id', 'campus_id', 'operation_alert_id', 'operator_id', 'action',
                'before_status', 'after_status', 'content', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_workflow_templates' => [
                'id', 'tenant_id', 'campus_id', 'template_code', 'template_name', 'task_type',
                'template_json', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
            ],
            'edu_workflow_execution_logs' => [
                'id', 'tenant_id', 'campus_id', 'rule_id', 'event_type', 'dedupe_key', 'status',
                'result_json', 'error_message', 'created_by', 'updated_by', 'created_at', 'updated_at',
            ],
            'edu_workflow_metrics_daily' => [
                'id', 'tenant_id', 'campus_id', 'metric_date', 'task_type', 'created_count',
                'completed_count', 'overdue_count', 'avg_complete_minutes', 'alert_count',
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
            'edu_workflow_rules' => ['uk_edu_workflow_rules_code', 'idx_edu_workflow_rules_event_status'],
            'edu_workflow_rule_conditions' => ['idx_edu_workflow_rule_conditions_rule'],
            'edu_workflow_rule_actions' => ['idx_edu_workflow_rule_actions_rule'],
            'edu_workflow_tasks' => ['uk_edu_workflow_tasks_task_no', 'uk_edu_workflow_tasks_dedupe_active', 'idx_edu_workflow_tasks_status_due'],
            'edu_workflow_task_assignees' => ['uk_edu_workflow_task_assignees_task_user', 'idx_edu_workflow_task_assignees_user_status'],
            'edu_workflow_task_logs' => ['idx_edu_workflow_task_logs_task', 'idx_edu_workflow_task_logs_operator'],
            'edu_workflow_task_comments' => ['idx_edu_workflow_task_comments_task'],
            'edu_workflow_task_attachments' => ['idx_edu_workflow_task_attachments_task'],
            'edu_workflow_sla_policies' => ['uk_edu_workflow_sla_policies_code', 'idx_edu_workflow_sla_policies_task_type'],
            'edu_workflow_escalation_policies' => ['uk_edu_workflow_escalation_policies_code', 'idx_edu_workflow_escalation_policies_task_type'],
            'edu_operation_alerts' => ['uk_edu_operation_alerts_no', 'uk_edu_operation_alerts_dedupe_open', 'idx_edu_operation_alerts_level_status'],
            'edu_operation_alert_logs' => ['idx_edu_operation_alert_logs_alert'],
            'edu_workflow_templates' => ['uk_edu_workflow_templates_code', 'idx_edu_workflow_templates_task_type'],
            'edu_workflow_execution_logs' => ['idx_edu_workflow_execution_logs_rule', 'idx_edu_workflow_execution_logs_dedupe'],
            'edu_workflow_metrics_daily' => ['uk_edu_workflow_metrics_daily_scope', 'idx_edu_workflow_metrics_daily_date'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function jsonColumns(): array
    {
        return [
            'edu_workflow_rule_conditions' => ['condition_value_json'],
            'edu_workflow_rule_actions' => ['action_config_json'],
            'edu_workflow_escalation_policies' => ['escalate_to_user_ids_json'],
            'edu_workflow_templates' => ['template_json'],
            'edu_workflow_execution_logs' => ['result_json'],
        ];
    }

    private function workflowMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_090000_create_v9_workflow_tables.php';
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
