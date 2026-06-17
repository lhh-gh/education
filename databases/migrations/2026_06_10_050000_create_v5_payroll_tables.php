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
        Schema::create('edu_teacher_salary_rules', static function (Blueprint $table): void {
            $table->comment('Education V5 teacher salary rules');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('rule_code', 64)->comment('Salary rule code');
            $table->string('rule_name', 120)->comment('Salary rule name');
            $table->json('campus_scope_json')->nullable()->comment('Campus scope json');
            $table->string('teacher_grade', 40)->nullable()->comment('Teacher grade');
            $table->string('status', 20)->default('enabled')->comment('Rule status');
            $table->date('effective_start')->comment('Effective start date');
            $table->date('effective_end')->nullable()->comment('Effective end date');
            $table->integer('priority')->default(0)->comment('Matching priority');
            $table->string('remark', 500)->nullable()->comment('Remark');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'rule_code'], 'uk_edu_teacher_salary_rules_tenant_code');
            $table->index(['tenant_id', 'campus_id', 'status', 'effective_start'], 'idx_edu_teacher_salary_rules_status');
            $table->index('deleted_at', 'idx_edu_teacher_salary_rules_deleted_at');
        });

        Schema::create('edu_teacher_salary_rule_items', static function (Blueprint $table): void {
            $table->comment('Education V5 teacher salary rule items');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('rule_id')->comment('Salary rule id');
            $table->string('item_type', 40)->comment('Salary item type');
            $table->string('workload_type', 40)->nullable()->comment('Workload type');
            $table->unsignedBigInteger('course_id')->nullable()->comment('Course id');
            $table->string('class_type', 40)->nullable()->comment('Class type');
            $table->string('calculation_method', 40)->comment('Calculation method');
            $table->unsignedBigInteger('unit_amount_cents')->default(0)->comment('Unit amount in cents');
            $table->decimal('rate', 8, 4)->nullable()->comment('Calculation rate');
            $table->json('condition_json')->nullable()->comment('Condition json');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'rule_id', 'item_type'], 'idx_edu_teacher_salary_rule_items_rule');
            $table->index(['tenant_id', 'course_id'], 'idx_edu_teacher_salary_rule_items_course');
        });

        Schema::create('edu_teacher_salary_batches', static function (Blueprint $table): void {
            $table->comment('Education V5 teacher salary batches');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('batch_no', 64)->comment('Salary batch number');
            $table->char('salary_month', 7)->comment('Salary month');
            $table->string('status', 20)->default('draft')->comment('Batch status');
            $table->date('source_start')->comment('Source workload start date');
            $table->date('source_end')->comment('Source workload end date');
            $table->unsignedInteger('teacher_count')->default(0)->comment('Teacher count');
            $table->unsignedBigInteger('total_amount_cents')->default(0)->comment('Total amount in cents');
            $table->unsignedBigInteger('calculated_by')->nullable()->comment('Calculated by user id');
            $table->timestamp('calculated_at')->nullable()->comment('Calculated time');
            $table->timestamp('submitted_at')->nullable()->comment('Submitted time');
            $table->timestamp('approved_at')->nullable()->comment('Approved time');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'batch_no'], 'uk_edu_teacher_salary_batches_tenant_no');
            $table->unique(['tenant_id', 'campus_id', 'salary_month'], 'uk_edu_teacher_salary_batches_month_campus');
            $table->index(['tenant_id', 'status'], 'idx_edu_teacher_salary_batches_status');
        });

        Schema::create('edu_teacher_salary_slips', static function (Blueprint $table): void {
            $table->comment('Education V5 teacher salary slips');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('batch_id')->comment('Salary batch id');
            $table->unsignedBigInteger('teacher_id')->comment('Teacher id');
            $table->char('salary_month', 7)->comment('Salary month');
            $table->string('status', 20)->default('draft')->comment('Salary slip status');
            $table->json('workload_snapshot_json')->comment('Workload snapshot json');
            $table->unsignedBigInteger('gross_amount_cents')->default(0)->comment('Gross amount in cents');
            $table->bigInteger('adjustment_amount_cents')->default(0)->comment('Adjustment amount in cents');
            $table->unsignedBigInteger('payable_amount_cents')->default(0)->comment('Payable amount in cents');
            $table->unsignedBigInteger('paid_amount_cents')->default(0)->comment('Paid amount in cents');
            $table->timestamp('approved_at')->nullable()->comment('Approved time');
            $table->timestamp('paid_at')->nullable()->comment('Paid time');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'batch_id', 'teacher_id'], 'uk_edu_teacher_salary_slips_batch_teacher');
            $table->index(['tenant_id', 'teacher_id', 'salary_month'], 'idx_edu_teacher_salary_slips_teacher_month');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_teacher_salary_slips_status');
        });

        Schema::create('edu_teacher_salary_items', static function (Blueprint $table): void {
            $table->comment('Education V5 teacher salary items');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('salary_slip_id')->comment('Salary slip id');
            $table->unsignedBigInteger('teacher_id')->comment('Teacher id');
            $table->unsignedBigInteger('source_workload_id')->nullable()->comment('Source workload id');
            $table->string('item_type', 40)->comment('Salary item type');
            $table->string('item_name', 160)->comment('Salary item name');
            $table->decimal('quantity', 10, 2)->default('0.00')->comment('Quantity');
            $table->unsignedBigInteger('unit_amount_cents')->default(0)->comment('Unit amount in cents');
            $table->bigInteger('amount_cents')->default(0)->comment('Amount in cents');
            $table->unsignedBigInteger('rule_item_id')->nullable()->comment('Salary rule item id');
            $table->json('snapshot_json')->comment('Salary item snapshot json');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'salary_slip_id'], 'idx_edu_teacher_salary_items_slip');
            $table->index(['tenant_id', 'source_workload_id'], 'idx_edu_teacher_salary_items_workload');
        });

        Schema::create('edu_teacher_salary_adjustments', static function (Blueprint $table): void {
            $table->comment('Education V5 teacher salary adjustments');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('salary_slip_id')->comment('Salary slip id');
            $table->unsignedBigInteger('teacher_id')->comment('Teacher id');
            $table->string('adjustment_type', 40)->comment('Adjustment type');
            $table->bigInteger('amount_cents')->comment('Adjustment amount in cents');
            $table->string('reason', 500)->comment('Adjustment reason');
            $table->unsignedBigInteger('operator_id')->comment('Operator user id');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('Approved by user id');
            $table->timestamp('approved_at')->nullable()->comment('Approved time');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'salary_slip_id'], 'idx_edu_teacher_salary_adjustments_slip');
            $table->index(['tenant_id', 'teacher_id', 'created_at'], 'idx_edu_teacher_salary_adjustments_teacher');
        });

        Schema::create('edu_teacher_salary_reviews', static function (Blueprint $table): void {
            $table->comment('Education V5 teacher salary reviews');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('batch_id')->comment('Salary batch id');
            $table->unsignedBigInteger('salary_slip_id')->nullable()->comment('Salary slip id');
            $table->unsignedInteger('review_level')->default(1)->comment('Review level');
            $table->unsignedBigInteger('reviewer_id')->comment('Reviewer user id');
            $table->string('status', 20)->default('pending')->comment('Review status');
            $table->string('review_note', 500)->nullable()->comment('Review note');
            $table->timestamp('reviewed_at')->nullable()->comment('Reviewed time');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'reviewer_id', 'status'], 'idx_edu_teacher_salary_reviews_reviewer');
            $table->index(['tenant_id', 'batch_id', 'status'], 'idx_edu_teacher_salary_reviews_batch');
        });

        Schema::create('edu_teacher_salary_payments', static function (Blueprint $table): void {
            $table->comment('Education V5 teacher salary payments');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('salary_slip_id')->comment('Salary slip id');
            $table->unsignedBigInteger('teacher_id')->comment('Teacher id');
            $table->string('payment_no', 64)->comment('Payment number');
            $table->unsignedBigInteger('paid_amount_cents')->comment('Paid amount in cents');
            $table->string('payment_method', 40)->comment('Payment method');
            $table->timestamp('paid_at')->comment('Paid time');
            $table->unsignedBigInteger('operator_id')->comment('Operator user id');
            $table->string('remark', 500)->nullable()->comment('Remark');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'payment_no'], 'uk_edu_teacher_salary_payments_tenant_no');
            $table->index(['tenant_id', 'salary_slip_id'], 'idx_edu_teacher_salary_payments_slip');
            $table->index(['tenant_id', 'teacher_id', 'paid_at'], 'idx_edu_teacher_salary_payments_teacher');
        });

        Schema::create('edu_teacher_workload_disputes', static function (Blueprint $table): void {
            $table->comment('Education V5 teacher workload disputes');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('teacher_id')->comment('Teacher id');
            $table->unsignedBigInteger('source_workload_id')->comment('Source workload id');
            $table->unsignedBigInteger('salary_slip_id')->nullable()->comment('Salary slip id');
            $table->string('dispute_type', 40)->comment('Dispute type');
            $table->text('content')->comment('Dispute content');
            $table->string('status', 20)->default('pending')->comment('Dispute status');
            $table->unsignedBigInteger('reviewed_by')->nullable()->comment('Reviewed by user id');
            $table->timestamp('reviewed_at')->nullable()->comment('Reviewed time');
            $table->string('review_note', 500)->nullable()->comment('Review note');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'teacher_id', 'status'], 'idx_edu_teacher_workload_disputes_teacher_status');
            $table->index(['tenant_id', 'source_workload_id'], 'idx_edu_teacher_workload_disputes_workload');
            $table->index('deleted_at', 'idx_edu_teacher_workload_disputes_deleted_at');
        });

        Schema::create('edu_teacher_performance_metrics', static function (Blueprint $table): void {
            $table->comment('Education V5 teacher performance metrics');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->char('metric_month', 7)->comment('Metric month');
            $table->unsignedBigInteger('teacher_id')->comment('Teacher id');
            $table->unsignedInteger('lesson_count')->default(0)->comment('Lesson count');
            $table->decimal('workload_credits', 10, 2)->default('0.00')->comment('Workload credits');
            $table->unsignedInteger('student_count')->default(0)->comment('Student count');
            $table->decimal('attendance_rate', 6, 4)->nullable()->comment('Attendance rate');
            $table->unsignedInteger('family_service_count')->default(0)->comment('Family service count');
            $table->unsignedInteger('dispute_count')->default(0)->comment('Dispute count');
            $table->unsignedBigInteger('salary_amount_cents')->default(0)->comment('Salary amount in cents');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'campus_id', 'metric_month', 'teacher_id'], 'uk_edu_teacher_performance_metrics_month_teacher');
            $table->index(['tenant_id', 'teacher_id', 'metric_month'], 'idx_edu_teacher_performance_metrics_teacher');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_teacher_performance_metrics');
        Schema::dropIfExists('edu_teacher_workload_disputes');
        Schema::dropIfExists('edu_teacher_salary_payments');
        Schema::dropIfExists('edu_teacher_salary_reviews');
        Schema::dropIfExists('edu_teacher_salary_adjustments');
        Schema::dropIfExists('edu_teacher_salary_items');
        Schema::dropIfExists('edu_teacher_salary_slips');
        Schema::dropIfExists('edu_teacher_salary_batches');
        Schema::dropIfExists('edu_teacher_salary_rule_items');
        Schema::dropIfExists('edu_teacher_salary_rules');
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
