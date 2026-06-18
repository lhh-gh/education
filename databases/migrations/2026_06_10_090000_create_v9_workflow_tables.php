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
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('edu_workflow_rules', static function (Blueprint $table): void {
            $table->comment('Education V9 workflow rules');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('rule_code', 64)->comment('Rule code');
            $table->string('rule_name', 120)->comment('Rule name');
            $table->string('event_type', 80)->comment('Event type');
            $table->string('status', 20)->default('disabled')->comment('Rule status');
            $table->integer('priority')->default(0)->comment('Rule priority');
            $table->unsignedInteger('dedupe_window_minutes')->default(1440)->comment('Dedupe window minutes');
            $table->string('description', 500)->nullable()->comment('Rule description');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'rule_code'], 'uk_edu_workflow_rules_code');
            $table->index(['tenant_id', 'event_type', 'status'], 'idx_edu_workflow_rules_event_status');
            $table->index('deleted_at', 'idx_edu_workflow_rules_deleted_at');
        });

        Schema::create('edu_workflow_rule_conditions', static function (Blueprint $table): void {
            $table->comment('Education V9 workflow rule conditions');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('rule_id')->comment('Workflow rule id');
            $table->string('condition_field', 120)->comment('Condition field');
            $table->string('operator', 30)->comment('Condition operator');
            $table->json('condition_value_json')->comment('Condition value JSON');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'rule_id', 'sort_order'], 'idx_edu_workflow_rule_conditions_rule');
        });

        Schema::create('edu_workflow_rule_actions', static function (Blueprint $table): void {
            $table->comment('Education V9 workflow rule actions');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('rule_id')->comment('Workflow rule id');
            $table->string('action_type', 40)->comment('Action type');
            $table->json('action_config_json')->comment('Action config JSON');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'rule_id', 'sort_order'], 'idx_edu_workflow_rule_actions_rule');
        });

        Schema::create('edu_workflow_tasks', static function (Blueprint $table): void {
            $table->comment('Education V9 workflow tasks');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('task_no', 64)->comment('Task number');
            $table->string('task_type', 60)->comment('Task type');
            $table->string('title', 160)->comment('Task title');
            $table->string('priority', 20)->default('normal')->comment('Task priority');
            $table->string('status', 20)->default('pending')->comment('Task status');
            $table->string('source_type', 60)->nullable()->comment('Source type');
            $table->unsignedBigInteger('source_id')->nullable()->comment('Source id');
            $table->string('dedupe_key', 160)->nullable()->comment('Dedupe key');
            $table->timestamp('due_at')->nullable()->comment('Due time');
            $table->timestamp('completed_at')->nullable()->comment('Completed time');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'task_no'], 'uk_edu_workflow_tasks_task_no');
            $table->unique(['tenant_id', 'dedupe_key', 'status'], 'uk_edu_workflow_tasks_dedupe_active');
            $table->index(['tenant_id', 'campus_id', 'status', 'due_at'], 'idx_edu_workflow_tasks_status_due');
            $table->index('deleted_at', 'idx_edu_workflow_tasks_deleted_at');
        });

        Schema::create('edu_workflow_task_assignees', static function (Blueprint $table): void {
            $table->comment('Education V9 workflow task assignees');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('workflow_task_id')->comment('Workflow task id');
            $table->unsignedBigInteger('user_id')->comment('Assignee user id');
            $table->string('assignee_type', 30)->default('owner')->comment('Assignee type');
            $table->string('status', 20)->default('pending')->comment('Assignee status');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'workflow_task_id', 'user_id'], 'uk_edu_workflow_task_assignees_task_user');
            $table->index(['tenant_id', 'user_id', 'status'], 'idx_edu_workflow_task_assignees_user_status');
        });

        Schema::create('edu_workflow_task_logs', static function (Blueprint $table): void {
            $table->comment('Education V9 workflow task logs');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('workflow_task_id')->comment('Workflow task id');
            $table->unsignedBigInteger('operator_id')->nullable()->comment('Operator user id');
            $table->string('action', 40)->comment('Log action');
            $table->string('before_status', 20)->nullable()->comment('Before status');
            $table->string('after_status', 20)->comment('After status');
            $table->string('content', 500)->nullable()->comment('Log content');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'workflow_task_id', 'created_at'], 'idx_edu_workflow_task_logs_task');
            $table->index(['tenant_id', 'operator_id', 'created_at'], 'idx_edu_workflow_task_logs_operator');
        });

        Schema::create('edu_workflow_task_comments', static function (Blueprint $table): void {
            $table->comment('Education V9 workflow task comments');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('workflow_task_id')->comment('Workflow task id');
            $table->unsignedBigInteger('commenter_user_id')->comment('Commenter user id');
            $table->text('content')->comment('Comment content');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'workflow_task_id', 'created_at'], 'idx_edu_workflow_task_comments_task');
        });

        Schema::create('edu_workflow_task_attachments', static function (Blueprint $table): void {
            $table->comment('Education V9 workflow task attachments');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('workflow_task_id')->comment('Workflow task id');
            $table->string('file_name', 160)->comment('File name');
            $table->string('file_url', 255)->comment('File URL');
            $table->unsignedBigInteger('file_size')->default(0)->comment('File size');
            $table->unsignedBigInteger('uploaded_by')->comment('Uploader user id');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'workflow_task_id'], 'idx_edu_workflow_task_attachments_task');
        });

        Schema::create('edu_workflow_sla_policies', static function (Blueprint $table): void {
            $table->comment('Education V9 workflow SLA policies');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('policy_code', 64)->comment('Policy code');
            $table->string('policy_name', 120)->comment('Policy name');
            $table->string('task_type', 60)->comment('Task type');
            $table->unsignedInteger('due_minutes')->comment('Due minutes');
            $table->string('status', 20)->default('enabled')->comment('Policy status');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'policy_code'], 'uk_edu_workflow_sla_policies_code');
            $table->index(['tenant_id', 'task_type', 'status'], 'idx_edu_workflow_sla_policies_task_type');
        });

        Schema::create('edu_workflow_escalation_policies', static function (Blueprint $table): void {
            $table->comment('Education V9 workflow escalation policies');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('policy_code', 64)->comment('Policy code');
            $table->string('task_type', 60)->comment('Task type');
            $table->unsignedInteger('overdue_minutes')->comment('Overdue minutes');
            $table->json('escalate_to_user_ids_json')->comment('Escalate user ids JSON');
            $table->string('status', 20)->default('enabled')->comment('Policy status');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'policy_code'], 'uk_edu_workflow_escalation_policies_code');
            $table->index(['tenant_id', 'task_type', 'status'], 'idx_edu_workflow_escalation_policies_task_type');
        });

        Schema::create('edu_operation_alerts', static function (Blueprint $table): void {
            $table->comment('Education V9 operation alerts');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('alert_no', 64)->comment('Alert number');
            $table->string('alert_type', 60)->comment('Alert type');
            $table->string('level', 20)->default('warning')->comment('Alert level');
            $table->string('status', 20)->default('open')->comment('Alert status');
            $table->string('title', 160)->comment('Alert title');
            $table->text('content')->comment('Alert content');
            $table->string('source_type', 60)->nullable()->comment('Source type');
            $table->unsignedBigInteger('source_id')->nullable()->comment('Source id');
            $table->string('dedupe_key', 160)->nullable()->comment('Dedupe key');
            $table->unsignedBigInteger('converted_task_id')->nullable()->comment('Converted task id');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'alert_no'], 'uk_edu_operation_alerts_no');
            $table->unique(['tenant_id', 'dedupe_key', 'status'], 'uk_edu_operation_alerts_dedupe_open');
            $table->index(['tenant_id', 'level', 'status'], 'idx_edu_operation_alerts_level_status');
        });

        Schema::create('edu_operation_alert_logs', static function (Blueprint $table): void {
            $table->comment('Education V9 operation alert logs');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('operation_alert_id')->comment('Operation alert id');
            $table->unsignedBigInteger('operator_id')->nullable()->comment('Operator user id');
            $table->string('action', 40)->comment('Log action');
            $table->string('before_status', 20)->nullable()->comment('Before status');
            $table->string('after_status', 20)->comment('After status');
            $table->string('content', 500)->nullable()->comment('Log content');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'operation_alert_id', 'created_at'], 'idx_edu_operation_alert_logs_alert');
        });

        Schema::create('edu_workflow_templates', static function (Blueprint $table): void {
            $table->comment('Education V9 workflow templates');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('template_code', 64)->comment('Template code');
            $table->string('template_name', 120)->comment('Template name');
            $table->string('task_type', 60)->comment('Task type');
            $table->json('template_json')->comment('Template JSON');
            $table->string('status', 20)->default('enabled')->comment('Template status');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'template_code'], 'uk_edu_workflow_templates_code');
            $table->index(['tenant_id', 'task_type', 'status'], 'idx_edu_workflow_templates_task_type');
            $table->index('deleted_at', 'idx_edu_workflow_templates_deleted_at');
        });

        Schema::create('edu_workflow_execution_logs', static function (Blueprint $table): void {
            $table->comment('Education V9 workflow execution logs');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('rule_id')->nullable()->comment('Workflow rule id');
            $table->string('event_type', 80)->comment('Event type');
            $table->string('dedupe_key', 160)->nullable()->comment('Dedupe key');
            $table->string('status', 20)->comment('Execution status');
            $table->json('result_json')->nullable()->comment('Execution result JSON');
            $table->string('error_message', 500)->nullable()->comment('Error message');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'rule_id', 'created_at'], 'idx_edu_workflow_execution_logs_rule');
            $table->index(['tenant_id', 'dedupe_key'], 'idx_edu_workflow_execution_logs_dedupe');
        });

        Schema::create('edu_workflow_metrics_daily', static function (Blueprint $table): void {
            $table->comment('Education V9 workflow daily metrics');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->date('metric_date')->comment('Metric date');
            $table->string('task_type', 60)->nullable()->comment('Task type');
            $table->unsignedInteger('created_count')->default(0)->comment('Created task count');
            $table->unsignedInteger('completed_count')->default(0)->comment('Completed task count');
            $table->unsignedInteger('overdue_count')->default(0)->comment('Overdue task count');
            $table->unsignedInteger('avg_complete_minutes')->nullable()->comment('Average completion minutes');
            $table->unsignedInteger('alert_count')->default(0)->comment('Alert count');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'campus_id', 'metric_date', 'task_type'], 'uk_edu_workflow_metrics_daily_scope');
            $table->index(['tenant_id', 'metric_date'], 'idx_edu_workflow_metrics_daily_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_workflow_metrics_daily');
        Schema::dropIfExists('edu_workflow_execution_logs');
        Schema::dropIfExists('edu_workflow_templates');
        Schema::dropIfExists('edu_operation_alert_logs');
        Schema::dropIfExists('edu_operation_alerts');
        Schema::dropIfExists('edu_workflow_escalation_policies');
        Schema::dropIfExists('edu_workflow_sla_policies');
        Schema::dropIfExists('edu_workflow_task_attachments');
        Schema::dropIfExists('edu_workflow_task_comments');
        Schema::dropIfExists('edu_workflow_task_logs');
        Schema::dropIfExists('edu_workflow_task_assignees');
        Schema::dropIfExists('edu_workflow_tasks');
        Schema::dropIfExists('edu_workflow_rule_actions');
        Schema::dropIfExists('edu_workflow_rule_conditions');
        Schema::dropIfExists('edu_workflow_rules');
    }

    private static function scopeColumns(Blueprint $table): void
    {
        $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
        $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
    }

    private static function auditColumns(Blueprint $table): void
    {
        $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
        $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
    }
};
