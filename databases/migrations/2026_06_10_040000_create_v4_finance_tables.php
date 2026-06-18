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
        Schema::create('edu_finance_orders', static function (Blueprint $table): void {
            $table->comment('Education V4 finance orders');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('order_no', 64)->comment('Finance order number');
            $table->string('order_type', 40)->comment('Order type');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('guardian_id')->nullable()->comment('Guardian id');
            $table->unsignedBigInteger('enrollment_id')->nullable()->comment('Enrollment id');
            $table->unsignedBigInteger('student_course_account_id')->nullable()->comment('Student course account id');
            $table->unsignedBigInteger('total_amount_cents')->default(0)->comment('Total amount in cents');
            $table->unsignedBigInteger('paid_amount_cents')->default(0)->comment('Paid amount in cents');
            $table->unsignedBigInteger('refund_amount_cents')->default(0)->comment('Refund amount in cents');
            $table->unsignedBigInteger('discount_amount_cents')->default(0)->comment('Discount amount in cents');
            $table->string('status', 20)->default('pending')->comment('Order status');
            $table->timestamp('due_at')->nullable()->comment('Due time');
            $table->timestamp('paid_at')->nullable()->comment('Paid time');
            $table->string('remark', 500)->nullable()->comment('Remark');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'order_no'], 'uk_edu_finance_orders_tenant_order_no');
            $table->index(['tenant_id', 'campus_id', 'student_id', 'status'], 'idx_edu_finance_orders_student_status');
            $table->index(['tenant_id', 'enrollment_id'], 'idx_edu_finance_orders_enrollment');
            $table->index('deleted_at', 'idx_edu_finance_orders_deleted_at');
        });

        Schema::create('edu_finance_order_items', static function (Blueprint $table): void {
            $table->comment('Education V4 finance order items');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('order_id')->comment('Finance order id');
            $table->string('item_type', 40)->comment('Item type');
            $table->string('item_name', 160)->comment('Item name');
            $table->decimal('quantity', 10, 2)->default('1.00')->comment('Quantity');
            $table->unsignedBigInteger('unit_amount_cents')->default(0)->comment('Unit amount in cents');
            $table->unsignedBigInteger('total_amount_cents')->default(0)->comment('Total amount in cents');
            $table->string('source_type', 40)->nullable()->comment('Source type');
            $table->unsignedBigInteger('source_id')->nullable()->comment('Source id');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'order_id'], 'idx_edu_finance_order_items_order');
            $table->index(['tenant_id', 'source_type', 'source_id'], 'idx_edu_finance_order_items_source');
        });

        Schema::create('edu_payment_channels', static function (Blueprint $table): void {
            $table->comment('Education V4 payment channels');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('channel_code', 64)->comment('Payment channel code');
            $table->string('channel_name', 120)->comment('Payment channel name');
            $table->string('channel_type', 30)->comment('Payment channel type');
            $table->json('config_json')->nullable()->comment('Payment channel config');
            $table->string('status', 20)->default('enabled')->comment('enabled, disabled');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'channel_code'], 'uk_edu_payment_channels_tenant_code');
            $table->index(['tenant_id', 'status'], 'idx_edu_payment_channels_status');
            $table->index('deleted_at', 'idx_edu_payment_channels_deleted_at');
        });

        Schema::create('edu_payment_records', static function (Blueprint $table): void {
            $table->comment('Education V4 payment records');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('order_id')->comment('Finance order id');
            $table->string('payment_no', 64)->comment('Payment number');
            $table->string('channel_code', 64)->comment('Payment channel code');
            $table->string('channel_trade_no', 120)->nullable()->comment('Channel trade number');
            $table->unsignedBigInteger('amount_cents')->comment('Payment amount in cents');
            $table->unsignedBigInteger('channel_fee_cents')->default(0)->comment('Channel fee in cents');
            $table->string('status', 20)->default('pending')->comment('Payment status');
            $table->timestamp('paid_at')->nullable()->comment('Paid time');
            $table->string('payer_name', 120)->nullable()->comment('Payer name');
            $table->unsignedBigInteger('operator_id')->nullable()->comment('Operator user id');
            $table->string('remark', 500)->nullable()->comment('Remark');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'payment_no'], 'uk_edu_payment_records_tenant_payment_no');
            $table->unique(['channel_code', 'channel_trade_no'], 'uk_edu_payment_records_channel_trade_no');
            $table->index(['tenant_id', 'order_id', 'status'], 'idx_edu_payment_records_order_status');
        });

        Schema::create('edu_payment_callbacks', static function (Blueprint $table): void {
            $table->comment('Education V4 payment callbacks');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('channel_code', 64)->comment('Payment channel code');
            $table->string('channel_trade_no', 120)->comment('Channel trade number');
            $table->unsignedBigInteger('payment_record_id')->nullable()->comment('Payment record id');
            $table->json('raw_payload_json')->comment('Raw callback payload');
            $table->boolean('signature_valid')->default(false)->comment('Signature valid flag');
            $table->boolean('processed')->default(false)->comment('Processed flag');
            $table->timestamp('processed_at')->nullable()->comment('Processed time');
            $table->string('error_message', 500)->nullable()->comment('Error message');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['channel_code', 'channel_trade_no'], 'uk_edu_payment_callbacks_channel_trade');
            $table->index(['tenant_id', 'processed', 'created_at'], 'idx_edu_payment_callbacks_processed');
        });

        Schema::create('edu_refund_requests', static function (Blueprint $table): void {
            $table->comment('Education V4 refund requests');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('order_id')->comment('Finance order id');
            $table->unsignedBigInteger('payment_record_id')->nullable()->comment('Payment record id');
            $table->string('refund_no', 64)->comment('Refund number');
            $table->unsignedBigInteger('refund_amount_cents')->comment('Refund amount in cents');
            $table->string('reason', 500)->comment('Refund reason');
            $table->string('status', 20)->default('pending')->comment('Refund status');
            $table->unsignedBigInteger('requested_by')->comment('Requester user id');
            $table->unsignedBigInteger('reviewed_by')->nullable()->comment('Reviewer user id');
            $table->timestamp('reviewed_at')->nullable()->comment('Reviewed time');
            $table->string('review_note', 500)->nullable()->comment('Review note');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'refund_no'], 'uk_edu_refund_requests_tenant_refund_no');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_refund_requests_status');
            $table->index(['tenant_id', 'order_id'], 'idx_edu_refund_requests_order');
            $table->index('deleted_at', 'idx_edu_refund_requests_deleted_at');
        });

        Schema::create('edu_refund_records', static function (Blueprint $table): void {
            $table->comment('Education V4 refund records');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('refund_request_id')->comment('Refund request id');
            $table->unsignedBigInteger('payment_record_id')->nullable()->comment('Payment record id');
            $table->string('refund_trade_no', 120)->nullable()->comment('Refund trade number');
            $table->unsignedBigInteger('refund_amount_cents')->comment('Refund amount in cents');
            $table->string('status', 20)->default('processing')->comment('Refund record status');
            $table->timestamp('refunded_at')->nullable()->comment('Refunded time');
            $table->json('raw_payload_json')->nullable()->comment('Raw refund payload');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'refund_request_id'], 'uk_edu_refund_records_request');
            $table->index(['tenant_id', 'refund_trade_no'], 'idx_edu_refund_records_trade');
        });

        Schema::create('edu_receipts', static function (Blueprint $table): void {
            $table->comment('Education V4 receipts');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('receipt_no', 64)->comment('Receipt number');
            $table->unsignedBigInteger('order_id')->comment('Finance order id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('amount_cents')->comment('Receipt amount in cents');
            $table->string('status', 20)->default('issued')->comment('Receipt status');
            $table->unsignedBigInteger('issued_by')->comment('Issuer user id');
            $table->timestamp('issued_at')->comment('Issued time');
            $table->unsignedBigInteger('voided_by')->nullable()->comment('Void user id');
            $table->timestamp('voided_at')->nullable()->comment('Voided time');
            $table->string('pdf_url', 255)->nullable()->comment('Receipt pdf url');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'receipt_no'], 'uk_edu_receipts_tenant_receipt_no');
            $table->index(['tenant_id', 'order_id'], 'idx_edu_receipts_order');
            $table->index(['tenant_id', 'student_id', 'status'], 'idx_edu_receipts_student_status');
            $table->index('deleted_at', 'idx_edu_receipts_deleted_at');
        });

        Schema::create('edu_reconciliation_batches', static function (Blueprint $table): void {
            $table->comment('Education V4 reconciliation batches');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('batch_no', 64)->comment('Batch number');
            $table->string('channel_code', 64)->comment('Payment channel code');
            $table->date('business_date')->comment('Business date');
            $table->string('status', 20)->default('imported')->comment('Reconciliation status');
            $table->unsignedInteger('total_count')->default(0)->comment('Total row count');
            $table->unsignedInteger('matched_count')->default(0)->comment('Matched count');
            $table->unsignedInteger('unmatched_count')->default(0)->comment('Unmatched count');
            $table->unsignedBigInteger('total_amount_cents')->default(0)->comment('Total amount in cents');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'channel_code', 'business_date'], 'uk_edu_reconciliation_batches_channel_date');
            $table->index(['tenant_id', 'status'], 'idx_edu_reconciliation_batches_status');
            $table->index('deleted_at', 'idx_edu_reconciliation_batches_deleted_at');
        });

        Schema::create('edu_reconciliation_items', static function (Blueprint $table): void {
            $table->comment('Education V4 reconciliation items');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('batch_id')->comment('Reconciliation batch id');
            $table->string('channel_trade_no', 120)->comment('Channel trade number');
            $table->unsignedBigInteger('payment_record_id')->nullable()->comment('Payment record id');
            $table->unsignedBigInteger('amount_cents')->comment('Channel row amount in cents');
            $table->timestamp('trade_time')->nullable()->comment('Trade time');
            $table->string('match_status', 20)->default('unmatched')->comment('Match status');
            $table->bigInteger('difference_cents')->default(0)->comment('Difference in cents');
            $table->json('raw_row_json')->comment('Raw reconciliation row');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'batch_id', 'match_status'], 'idx_edu_reconciliation_items_batch');
            $table->index(['tenant_id', 'channel_trade_no'], 'idx_edu_reconciliation_items_trade');
        });

        Schema::create('edu_finance_adjustments', static function (Blueprint $table): void {
            $table->comment('Education V4 finance adjustments');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('order_id')->comment('Finance order id');
            $table->string('adjustment_type', 40)->comment('Adjustment type');
            $table->bigInteger('amount_cents')->comment('Adjustment amount in cents');
            $table->string('reason', 500)->comment('Adjustment reason');
            $table->unsignedBigInteger('operator_id')->comment('Operator user id');
            $table->string('source_type', 40)->nullable()->comment('Source type');
            $table->unsignedBigInteger('source_id')->nullable()->comment('Source id');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'order_id', 'created_at'], 'idx_edu_finance_adjustments_order');
            $table->index(['tenant_id', 'source_type', 'source_id'], 'idx_edu_finance_adjustments_source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_finance_adjustments');
        Schema::dropIfExists('edu_reconciliation_items');
        Schema::dropIfExists('edu_reconciliation_batches');
        Schema::dropIfExists('edu_receipts');
        Schema::dropIfExists('edu_refund_records');
        Schema::dropIfExists('edu_refund_requests');
        Schema::dropIfExists('edu_payment_callbacks');
        Schema::dropIfExists('edu_payment_records');
        Schema::dropIfExists('edu_payment_channels');
        Schema::dropIfExists('edu_finance_order_items');
        Schema::dropIfExists('edu_finance_orders');
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
