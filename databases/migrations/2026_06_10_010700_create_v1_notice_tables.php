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
        Schema::create('edu_notices', static function (Blueprint $table): void {
            $table->comment('Education notices');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Target campus id when target is campus, class, or student');
            $table->string('notice_no', 64)->comment('Notice number');
            $table->string('notice_type', 30)->default('academic')->comment('academic, activity, fee, or system');
            $table->string('target_type', 30)->default('all')->comment('all, campus, class, or student');
            $table->unsignedBigInteger('target_id')->nullable()->comment('Campus, class, or student id depending on target_type');
            $table->string('title', 160)->comment('Notice title');
            $table->text('content')->comment('Notice content');
            $table->string('priority', 20)->default('normal')->comment('normal, important, or urgent');
            $table->string('status', 20)->default('draft')->comment('draft, published, or withdrawn');
            $table->timestamp('published_at')->nullable()->comment('Publish time');
            $table->unsignedBigInteger('published_by')->nullable()->comment('Publisher user id');
            $table->timestamp('withdrawn_at')->nullable()->comment('Withdraw time');
            $table->unsignedBigInteger('withdrawn_by')->nullable()->comment('Withdraw operator user id');
            $table->string('withdraw_reason', 500)->nullable()->comment('Withdraw reason');
            $table->timestamp('expire_at')->nullable()->comment('Expiry time');
            $table->unsignedInteger('receipt_count')->default(0)->comment('Generated receipt count');
            $table->unsignedInteger('read_count')->default(0)->comment('Read receipt count');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'notice_no'], 'uk_edu_notices_tenant_no');
            $table->index(['tenant_id', 'status', 'published_at'], 'idx_edu_notices_tenant_status_publish');
            $table->index(['tenant_id', 'target_type', 'target_id'], 'idx_edu_notices_tenant_target');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_notices_tenant_campus_status');
            $table->index(['tenant_id', 'notice_type', 'priority'], 'idx_edu_notices_tenant_type_priority');
            $table->index('expire_at', 'idx_edu_notices_expire_at');
            $table->index('deleted_at', 'idx_edu_notices_deleted_at');
        });

        Schema::create('edu_notice_receipts', static function (Blueprint $table): void {
            $table->comment('Education notice receipts');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Student campus id');
            $table->unsignedBigInteger('notice_id')->comment('Notice id');
            $table->unsignedBigInteger('guardian_id')->comment('Guardian id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->string('relation', 30)->nullable()->comment('Snapshot relation from student guardian binding');
            $table->string('guardian_name_snapshot', 120)->comment('Guardian name snapshot');
            $table->string('student_name_snapshot', 120)->comment('Student name snapshot');
            $table->string('status', 20)->default('unread')->comment('unread or read');
            $table->timestamp('delivered_at')->nullable()->comment('Receipt creation or delivery time');
            $table->timestamp('read_at')->nullable()->comment('Read time');
            $table->unsignedBigInteger('read_by_profile_id')->nullable()->comment('Mobile guardian profile id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'notice_id', 'guardian_id', 'student_id'], 'uk_edu_notice_receipts_tenant_notice_guardian_student');
            $table->index(['tenant_id', 'guardian_id', 'status'], 'idx_edu_notice_receipts_tenant_guardian_status');
            $table->index(['tenant_id', 'student_id', 'status'], 'idx_edu_notice_receipts_tenant_student_status');
            $table->index(['tenant_id', 'notice_id', 'status'], 'idx_edu_notice_receipts_tenant_notice_status');
            $table->index('read_at', 'idx_edu_notice_receipts_read_at');
            $table->index('deleted_at', 'idx_edu_notice_receipts_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_notice_receipts');
        Schema::dropIfExists('edu_notices');
    }
};
