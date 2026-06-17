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
        Schema::create('edu_org_units', static function (Blueprint $table): void {
            $table->comment('Education V6 group organization units');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('parent_id')->nullable()->comment('Parent org unit id');
            $table->string('code', 64)->comment('Org unit code');
            $table->string('name', 120)->comment('Org unit name');
            $table->string('unit_type', 40)->comment('Org unit type');
            $table->string('path', 500)->comment('Tree path');
            $table->unsignedInteger('level')->default(1)->comment('Tree level');
            $table->string('status', 20)->default('enabled')->comment('Org unit status');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code'], 'uk_edu_org_units_tenant_code');
            $table->index(['tenant_id', 'parent_id'], 'idx_edu_org_units_parent');
            $table->index(['tenant_id', 'path'], 'idx_edu_org_units_path');
            $table->index('deleted_at', 'idx_edu_org_units_deleted_at');
        });

        Schema::create('edu_campus_org_relations', static function (Blueprint $table): void {
            $table->comment('Education V6 campus org relations');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('org_unit_id')->comment('Org unit id');
            $table->string('relation_type', 40)->default('owned')->comment('Relation type');
            $table->date('effective_start')->nullable()->comment('Effective start date');
            $table->date('effective_end')->nullable()->comment('Effective end date');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'org_unit_id', 'campus_id'], 'uk_edu_campus_org_relations_org_campus');
            $table->index(['tenant_id', 'campus_id'], 'idx_edu_campus_org_relations_campus');
        });

        Schema::create('edu_data_permission_scopes', static function (Blueprint $table): void {
            $table->comment('Education V6 data permission scopes');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('scope_code', 64)->comment('Scope code');
            $table->string('scope_name', 120)->comment('Scope name');
            $table->string('scope_type', 40)->comment('Scope type');
            $table->json('scope_value_json')->comment('Scope value json');
            $table->string('status', 20)->default('enabled')->comment('Scope status');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'scope_code'], 'uk_edu_data_permission_scopes_code');
            $table->index(['tenant_id', 'scope_type', 'status'], 'idx_edu_data_permission_scopes_type');
            $table->index('deleted_at', 'idx_edu_data_permission_scopes_deleted_at');
        });

        Schema::create('edu_user_data_permissions', static function (Blueprint $table): void {
            $table->comment('Education V6 user data permissions');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('user_id')->comment('User id');
            $table->unsignedBigInteger('scope_id')->comment('Scope id');
            $table->string('scope_type', 40)->comment('Scope type');
            $table->date('effective_start')->nullable()->comment('Effective start date');
            $table->date('effective_end')->nullable()->comment('Effective end date');
            $table->string('status', 20)->default('enabled')->comment('Permission status');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'user_id', 'scope_id'], 'uk_edu_user_data_permissions_user_scope');
            $table->index(['tenant_id', 'user_id', 'scope_type', 'status'], 'idx_edu_user_data_permissions_user_type');
            $table->index('deleted_at', 'idx_edu_user_data_permissions_deleted_at');
        });

        Schema::create('edu_approval_templates', static function (Blueprint $table): void {
            $table->comment('Education V6 approval templates');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('template_code', 64)->comment('Template code');
            $table->string('template_name', 120)->comment('Template name');
            $table->string('business_type', 60)->comment('Business type');
            $table->string('status', 20)->default('enabled')->comment('Template status');
            $table->unsignedInteger('version')->default(1)->comment('Template version');
            $table->json('config_json')->nullable()->comment('Template config json');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'template_code', 'version'], 'uk_edu_approval_templates_code_version');
            $table->index(['tenant_id', 'business_type', 'status'], 'idx_edu_approval_templates_business');
            $table->index('deleted_at', 'idx_edu_approval_templates_deleted_at');
        });

        Schema::create('edu_approval_nodes', static function (Blueprint $table): void {
            $table->comment('Education V6 approval nodes');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('template_id')->comment('Template id');
            $table->string('node_code', 64)->comment('Node code');
            $table->string('node_name', 120)->comment('Node name');
            $table->unsignedInteger('sort_order')->comment('Sort order');
            $table->string('assignee_type', 40)->comment('Assignee type');
            $table->json('assignee_value_json')->comment('Assignee value json');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'template_id', 'node_code'], 'uk_edu_approval_nodes_template_node');
            $table->index(['tenant_id', 'template_id', 'sort_order'], 'idx_edu_approval_nodes_template_order');
        });

        Schema::create('edu_approval_instances', static function (Blueprint $table): void {
            $table->comment('Education V6 approval instances');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('template_id')->comment('Template id');
            $table->string('business_type', 60)->comment('Business type');
            $table->unsignedBigInteger('business_id')->comment('Business id');
            $table->string('status', 20)->default('pending')->comment('Approval status');
            $table->unsignedBigInteger('current_node_id')->nullable()->comment('Current node id');
            $table->unsignedBigInteger('initiator_id')->comment('Initiator user id');
            $table->json('payload_json')->comment('Payload json');
            $table->timestamp('completed_at')->nullable()->comment('Completed time');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'business_type', 'business_id'], 'uk_edu_approval_instances_business');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_approval_instances_status');
        });

        Schema::create('edu_approval_tasks', static function (Blueprint $table): void {
            $table->comment('Education V6 approval tasks');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('approval_instance_id')->comment('Approval instance id');
            $table->unsignedBigInteger('node_id')->comment('Node id');
            $table->unsignedBigInteger('assignee_user_id')->comment('Assignee user id');
            $table->string('status', 20)->default('pending')->comment('Task status');
            $table->timestamp('due_at')->nullable()->comment('Due time');
            $table->timestamp('completed_at')->nullable()->comment('Completed time');
            $table->string('result', 20)->nullable()->comment('Approval result');
            $table->string('comment', 500)->nullable()->comment('Approval comment');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'assignee_user_id', 'status'], 'idx_edu_approval_tasks_assignee_status');
            $table->index(['tenant_id', 'approval_instance_id', 'status'], 'idx_edu_approval_tasks_instance');
        });

        Schema::create('edu_approval_logs', static function (Blueprint $table): void {
            $table->comment('Education V6 approval logs');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('approval_instance_id')->comment('Approval instance id');
            $table->unsignedBigInteger('task_id')->nullable()->comment('Approval task id');
            $table->unsignedBigInteger('operator_id')->comment('Operator user id');
            $table->string('action', 40)->comment('Approval action');
            $table->string('before_status', 20)->nullable()->comment('Before status');
            $table->string('after_status', 20)->comment('After status');
            $table->string('comment', 500)->nullable()->comment('Approval comment');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'approval_instance_id', 'created_at'], 'idx_edu_approval_logs_instance');
            $table->index(['tenant_id', 'operator_id', 'created_at'], 'idx_edu_approval_logs_operator');
        });

        Schema::create('edu_contracts', static function (Blueprint $table): void {
            $table->comment('Education V6 contracts');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('contract_no', 64)->comment('Contract number');
            $table->string('contract_type', 40)->comment('Contract type');
            $table->string('title', 160)->comment('Contract title');
            $table->string('counterparty_name', 160)->comment('Counterparty name');
            $table->unsignedBigInteger('amount_cents')->default(0)->comment('Amount in cents');
            $table->string('status', 20)->default('draft')->comment('Contract status');
            $table->date('start_date')->nullable()->comment('Start date');
            $table->date('end_date')->nullable()->comment('End date');
            $table->unsignedBigInteger('owner_user_id')->nullable()->comment('Owner user id');
            $table->string('risk_level', 20)->default('normal')->comment('Risk level');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'contract_no'], 'uk_edu_contracts_tenant_no');
            $table->index(['tenant_id', 'campus_id', 'status', 'end_date'], 'idx_edu_contracts_status_expire');
            $table->index(['tenant_id', 'owner_user_id'], 'idx_edu_contracts_owner');
            $table->index('deleted_at', 'idx_edu_contracts_deleted_at');
        });

        Schema::create('edu_contract_parties', static function (Blueprint $table): void {
            $table->comment('Education V6 contract parties');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('contract_id')->comment('Contract id');
            $table->string('party_type', 40)->comment('Party type');
            $table->string('party_name', 160)->comment('Party name');
            $table->string('contact_name', 120)->nullable()->comment('Contact name');
            $table->string('contact_mobile', 30)->nullable()->comment('Contact mobile');
            $table->string('identity_no', 120)->nullable()->comment('Identity number');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'contract_id'], 'idx_edu_contract_parties_contract');
            $table->index(['tenant_id', 'party_name'], 'idx_edu_contract_parties_name');
        });

        Schema::create('edu_contract_attachments', static function (Blueprint $table): void {
            $table->comment('Education V6 contract attachments');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('contract_id')->comment('Contract id');
            $table->string('file_name', 160)->comment('File name');
            $table->string('file_url', 255)->comment('File url');
            $table->unsignedBigInteger('file_size')->default(0)->comment('File size');
            $table->unsignedBigInteger('uploaded_by')->comment('Uploaded by user id');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'contract_id'], 'idx_edu_contract_attachments_contract');
        });

        Schema::create('edu_contract_renewals', static function (Blueprint $table): void {
            $table->comment('Education V6 contract renewals');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('contract_id')->comment('Contract id');
            $table->string('renewal_type', 40)->comment('Renewal type');
            $table->string('status', 20)->default('pending')->comment('Renewal status');
            $table->date('due_date')->comment('Due date');
            $table->unsignedBigInteger('handled_by')->nullable()->comment('Handled by user id');
            $table->timestamp('handled_at')->nullable()->comment('Handled time');
            $table->string('result', 500)->nullable()->comment('Renewal result');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'campus_id', 'due_date', 'status'], 'idx_edu_contract_renewals_due');
            $table->index(['tenant_id', 'contract_id'], 'idx_edu_contract_renewals_contract');
        });

        Schema::create('edu_group_operation_metrics', static function (Blueprint $table): void {
            $table->comment('Education V6 group operation metrics');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->date('metric_date')->comment('Metric date');
            $table->unsignedBigInteger('org_unit_id')->nullable()->comment('Org unit id');
            $table->unsignedInteger('campus_count')->default(0)->comment('Campus count');
            $table->unsignedInteger('student_count')->default(0)->comment('Student count');
            $table->unsignedBigInteger('revenue_cents')->default(0)->comment('Revenue in cents');
            $table->decimal('consumed_credits', 12, 2)->default('0.00')->comment('Consumed credits');
            $table->unsignedInteger('renewal_alert_count')->default(0)->comment('Renewal alert count');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'org_unit_id', 'campus_id', 'metric_date'], 'uk_edu_group_operation_metrics_scope_date');
            $table->index(['tenant_id', 'metric_date'], 'idx_edu_group_operation_metrics_date');
        });

        Schema::create('edu_franchise_records', static function (Blueprint $table): void {
            $table->comment('Education V6 franchise records');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('franchise_code', 64)->comment('Franchise code');
            $table->string('franchise_name', 160)->comment('Franchise name');
            $table->string('contact_name', 120)->nullable()->comment('Contact name');
            $table->string('contact_mobile', 30)->nullable()->comment('Contact mobile');
            $table->string('region', 120)->nullable()->comment('Region');
            $table->string('status', 20)->default('potential')->comment('Franchise status');
            $table->unsignedBigInteger('signed_contract_id')->nullable()->comment('Signed contract id');
            $table->string('remark', 500)->nullable()->comment('Remark');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'franchise_code'], 'uk_edu_franchise_records_code');
            $table->index(['tenant_id', 'status'], 'idx_edu_franchise_records_status');
            $table->index('deleted_at', 'idx_edu_franchise_records_deleted_at');
        });

        Schema::create('edu_risk_audit_events', static function (Blueprint $table): void {
            $table->comment('Education V6 risk audit events');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('event_type', 60)->comment('Event type');
            $table->string('risk_level', 20)->comment('Risk level');
            $table->string('business_type', 60)->comment('Business type');
            $table->unsignedBigInteger('business_id')->nullable()->comment('Business id');
            $table->unsignedBigInteger('operator_id')->nullable()->comment('Operator user id');
            $table->string('summary', 300)->comment('Risk summary');
            $table->json('payload_json')->comment('Payload json');
            $table->boolean('handled')->default(false)->comment('Handled state');
            $table->unsignedBigInteger('handled_by')->nullable()->comment('Handled by user id');
            $table->timestamp('handled_at')->nullable()->comment('Handled time');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'risk_level', 'created_at'], 'idx_edu_risk_audit_events_level');
            $table->index(['tenant_id', 'business_type', 'business_id'], 'idx_edu_risk_audit_events_business');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_risk_audit_events');
        Schema::dropIfExists('edu_franchise_records');
        Schema::dropIfExists('edu_group_operation_metrics');
        Schema::dropIfExists('edu_contract_renewals');
        Schema::dropIfExists('edu_contract_attachments');
        Schema::dropIfExists('edu_contract_parties');
        Schema::dropIfExists('edu_contracts');
        Schema::dropIfExists('edu_approval_logs');
        Schema::dropIfExists('edu_approval_tasks');
        Schema::dropIfExists('edu_approval_instances');
        Schema::dropIfExists('edu_approval_nodes');
        Schema::dropIfExists('edu_approval_templates');
        Schema::dropIfExists('edu_user_data_permissions');
        Schema::dropIfExists('edu_data_permission_scopes');
        Schema::dropIfExists('edu_campus_org_relations');
        Schema::dropIfExists('edu_org_units');
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
