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
        Schema::create('edu_lesson_change_requests', static function (Blueprint $table): void {
            $table->comment('Education V2 lesson change requests');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('lesson_id')->comment('V1 lesson id');
            $table->string('change_type', 30)->comment('reschedule, suspend, cancel, replace_teacher, replace_classroom, substitute_teacher');
            $table->string('status', 20)->default('pending')->comment('pending, approved, rejected, applied, cancelled');
            $table->json('old_values_json')->comment('Snapshot before change');
            $table->json('new_values_json')->comment('Proposed values');
            $table->string('reason', 500)->comment('Change reason');
            $table->unsignedBigInteger('requested_by')->comment('Request user id');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('Reviewer user id');
            $table->timestamp('approved_at')->nullable()->comment('Review time');
            $table->timestamp('applied_at')->nullable()->comment('Apply time');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'lesson_id', 'status'], 'idx_edu_lesson_change_requests_tenant_lesson_status');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_lesson_change_requests_campus_status');
            $table->index(['tenant_id', 'requested_by', 'created_at'], 'idx_edu_lesson_change_requests_requested_by');
            $table->index('deleted_at', 'idx_edu_lesson_change_requests_deleted_at');
        });

        Schema::create('edu_lesson_change_logs', static function (Blueprint $table): void {
            $table->comment('Education V2 lesson change logs');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('lesson_id')->comment('V1 lesson id');
            $table->unsignedBigInteger('change_request_id')->nullable()->comment('Change request id');
            $table->string('change_type', 30)->comment('Change type');
            $table->json('before_json')->comment('Before snapshot');
            $table->json('after_json')->comment('After snapshot');
            $table->unsignedBigInteger('operator_id')->comment('Operator user id');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();

            $table->index(['tenant_id', 'lesson_id', 'created_at'], 'idx_edu_lesson_change_logs_tenant_lesson');
            $table->index(['tenant_id', 'change_request_id'], 'idx_edu_lesson_change_logs_request');
        });

        Schema::create('edu_makeup_entitlements', static function (Blueprint $table): void {
            $table->comment('Education V2 makeup entitlements');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('student_id')->comment('V1 student id');
            $table->unsignedBigInteger('course_id')->comment('V1 course id');
            $table->unsignedBigInteger('source_lesson_id')->comment('Leave source lesson id');
            $table->unsignedBigInteger('source_leave_request_id')->comment('V1 leave request id');
            $table->string('status', 20)->default('available')->comment('available, used, expired, cancelled');
            $table->timestamp('expires_at')->nullable()->comment('Expire time');
            $table->unsignedBigInteger('used_lesson_id')->nullable()->comment('Make-up lesson id');
            $table->timestamp('used_at')->nullable()->comment('Used time');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'source_leave_request_id', 'student_id'], 'uk_edu_makeup_entitlements_leave_student');
            $table->index(['tenant_id', 'student_id', 'status'], 'idx_edu_makeup_entitlements_student_status');
            $table->index(['tenant_id', 'expires_at', 'status'], 'idx_edu_makeup_entitlements_expire');
            $table->index('deleted_at', 'idx_edu_makeup_entitlements_deleted_at');
        });

        Schema::create('edu_makeup_records', static function (Blueprint $table): void {
            $table->comment('Education V2 makeup records');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('makeup_entitlement_id')->comment('Entitlement id');
            $table->unsignedBigInteger('student_id')->comment('V1 student id');
            $table->unsignedBigInteger('makeup_lesson_id')->comment('V1 lesson id used for make-up');
            $table->string('status', 20)->default('arranged')->comment('arranged, completed, cancelled');
            $table->unsignedBigInteger('arranged_by')->comment('Arranger user id');
            $table->timestamp('arranged_at')->comment('Arrange time');
            $table->timestamp('completed_at')->nullable()->comment('Completion time');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'makeup_entitlement_id', 'status'], 'uk_edu_makeup_records_entitlement_active');
            $table->index(['tenant_id', 'student_id', 'status'], 'idx_edu_makeup_records_student_status');
            $table->index(['tenant_id', 'makeup_lesson_id'], 'idx_edu_makeup_records_lesson');
            $table->index('deleted_at', 'idx_edu_makeup_records_deleted_at');
        });

        Schema::create('edu_lesson_consumption_reviews', static function (Blueprint $table): void {
            $table->comment('Education V2 lesson consumption reviews');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('lesson_id')->comment('V1 lesson id');
            $table->string('status', 20)->default('pending')->comment('pending, approved, rejected, cancelled');
            $table->unsignedBigInteger('submitted_by')->comment('Submitter user id');
            $table->timestamp('submitted_at')->comment('Submit time');
            $table->unsignedBigInteger('reviewed_by')->nullable()->comment('Reviewer user id');
            $table->timestamp('reviewed_at')->nullable()->comment('Review time');
            $table->string('review_note', 500)->nullable()->comment('Review note');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'lesson_id'], 'uk_edu_lesson_consumption_reviews_lesson');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_lesson_consumption_reviews_tenant_status');
            $table->index(['tenant_id', 'submitted_by', 'submitted_at'], 'idx_edu_lesson_consumption_reviews_submitter');
            $table->index('deleted_at', 'idx_edu_lesson_consumption_reviews_deleted_at');
        });

        Schema::create('edu_lesson_consumption_adjustments', static function (Blueprint $table): void {
            $table->comment('Education V2 lesson consumption adjustments');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('original_consumption_id')->comment('Original V1 consumption id');
            $table->unsignedBigInteger('adjustment_consumption_id')->nullable()->comment('Reverse or supplement consumption id');
            $table->unsignedBigInteger('student_id')->comment('V1 student id');
            $table->unsignedBigInteger('student_course_account_id')->comment('V1 course account id');
            $table->decimal('credits', 10, 2)->default(0)->comment('Positive or negative adjustment credits');
            $table->string('reason', 500)->comment('Adjustment reason');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();

            $table->index(['tenant_id', 'original_consumption_id'], 'idx_edu_lesson_consumption_adjustments_original');
            $table->index(['tenant_id', 'student_id', 'created_at'], 'idx_edu_lesson_consumption_adjustments_student');
            $table->index(['tenant_id', 'student_course_account_id', 'created_at'], 'idx_edu_lesson_consumption_adjustments_account');
        });

        Schema::create('edu_renewal_alerts', static function (Blueprint $table): void {
            $table->comment('Education V2 renewal alerts');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('student_id')->comment('V1 student id');
            $table->unsignedBigInteger('course_id')->comment('V1 course id');
            $table->unsignedBigInteger('student_course_account_id')->comment('V1 course account id');
            $table->string('alert_type', 30)->comment('low_balance, expire_soon, expired');
            $table->string('alert_level', 20)->default('normal')->comment('normal, warning, urgent');
            $table->string('status', 20)->default('open')->comment('open, converted, ignored, closed');
            $table->string('trigger_value', 60)->comment('Actual value causing alert');
            $table->string('threshold_value', 60)->comment('Configured threshold');
            $table->date('due_date')->nullable()->comment('Suggested follow date');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'student_course_account_id', 'alert_type', 'status'], 'uk_edu_renewal_alerts_open_account_type');
            $table->index(['tenant_id', 'campus_id', 'due_date', 'status'], 'idx_edu_renewal_alerts_tenant_due');
            $table->index(['tenant_id', 'student_id', 'status'], 'idx_edu_renewal_alerts_student_status');
            $table->index('deleted_at', 'idx_edu_renewal_alerts_deleted_at');
        });

        Schema::create('edu_renewal_tasks', static function (Blueprint $table): void {
            $table->comment('Education V2 renewal tasks');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('student_id')->comment('V1 student id');
            $table->unsignedBigInteger('course_id')->comment('V1 course id');
            $table->unsignedBigInteger('renewal_alert_id')->comment('Renewal alert id');
            $table->unsignedBigInteger('assignee_id')->nullable()->comment('Follow-up owner user id');
            $table->string('status', 20)->default('pending')->comment('pending, following, done, closed');
            $table->timestamp('next_follow_at')->nullable()->comment('Next follow-up time');
            $table->string('result', 500)->nullable()->comment('Latest result');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'assignee_id', 'status', 'next_follow_at'], 'idx_edu_renewal_tasks_assignee_status');
            $table->index(['tenant_id', 'renewal_alert_id'], 'idx_edu_renewal_tasks_alert');
            $table->index(['tenant_id', 'student_id', 'status'], 'idx_edu_renewal_tasks_student');
            $table->index('deleted_at', 'idx_edu_renewal_tasks_deleted_at');
        });

        Schema::create('edu_student_follow_records', static function (Blueprint $table): void {
            $table->comment('Education V2 student follow records');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('student_id')->comment('V1 student id');
            $table->unsignedBigInteger('renewal_task_id')->nullable()->comment('Renewal task id');
            $table->string('follow_type', 30)->comment('phone, wechat, offline, system');
            $table->text('content')->comment('Follow-up content');
            $table->timestamp('next_follow_at')->nullable()->comment('Next follow time');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();

            $table->index(['tenant_id', 'student_id', 'created_at'], 'idx_edu_student_follow_records_student_time');
            $table->index(['tenant_id', 'renewal_task_id', 'created_at'], 'idx_edu_student_follow_records_task_time');
        });

        Schema::create('edu_teacher_workload_records', static function (Blueprint $table): void {
            $table->comment('Education V2 teacher workload records');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('teacher_id')->comment('V1 teacher id');
            $table->unsignedBigInteger('lesson_id')->comment('V1 lesson id');
            $table->string('workload_type', 30)->comment('main, substitute, makeup, trial_support');
            $table->string('lesson_type', 30)->default('normal')->comment('normal, makeup, trial');
            $table->decimal('credits', 10, 2)->default(0)->comment('Workload credits');
            $table->unsignedInteger('student_count')->default(0)->comment('Lesson student count');
            $table->unsignedInteger('present_count')->default(0)->comment('Present count');
            $table->unsignedInteger('leave_count')->default(0)->comment('Leave count');
            $table->unsignedInteger('absent_count')->default(0)->comment('Absent count');
            $table->timestamp('recorded_at')->comment('Workload record time');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();

            $table->unique(['tenant_id', 'lesson_id', 'teacher_id', 'workload_type'], 'uk_edu_teacher_workload_records_lesson_teacher_type');
            $table->index(['tenant_id', 'campus_id', 'teacher_id', 'recorded_at'], 'idx_edu_teacher_workload_records_teacher_date');
            $table->index(['tenant_id', 'lesson_id'], 'idx_edu_teacher_workload_records_lesson');
        });

        Schema::create('edu_daily_operation_metrics', static function (Blueprint $table): void {
            $table->comment('Education V2 daily operation metrics');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->date('metric_date')->comment('Metric date');
            $table->unsignedInteger('lessons_count')->default(0)->comment('Lessons count');
            $table->unsignedInteger('pending_attendance_count')->default(0)->comment('Pending attendance lessons');
            $table->decimal('consumed_credits', 12, 2)->default(0)->comment('Consumed credits');
            $table->unsignedInteger('present_count')->default(0)->comment('Present count');
            $table->unsignedInteger('leave_count')->default(0)->comment('Leave count');
            $table->unsignedInteger('absent_count')->default(0)->comment('Absent count');
            $table->unsignedInteger('renewal_alert_count')->default(0)->comment('Open renewal alert count');
            $table->unsignedInteger('pending_review_count')->default(0)->comment('Pending consumption review count');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();

            $table->unique(['tenant_id', 'campus_id', 'metric_date'], 'uk_edu_daily_operation_metrics_campus_date');
            $table->index(['tenant_id', 'metric_date'], 'idx_edu_daily_operation_metrics_tenant_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_daily_operation_metrics');
        Schema::dropIfExists('edu_teacher_workload_records');
        Schema::dropIfExists('edu_student_follow_records');
        Schema::dropIfExists('edu_renewal_tasks');
        Schema::dropIfExists('edu_renewal_alerts');
        Schema::dropIfExists('edu_lesson_consumption_adjustments');
        Schema::dropIfExists('edu_lesson_consumption_reviews');
        Schema::dropIfExists('edu_makeup_records');
        Schema::dropIfExists('edu_makeup_entitlements');
        Schema::dropIfExists('edu_lesson_change_logs');
        Schema::dropIfExists('edu_lesson_change_requests');
    }
};
