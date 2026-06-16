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
        Schema::create('edu_leave_requests', static function (Blueprint $table): void {
            $table->comment('Education leave requests');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->string('leave_no', 64)->comment('Leave request number');
            $table->string('source', 20)->default('staff')->comment('staff, guardian, or teacher');
            $table->string('leave_type', 20)->default('other')->comment('sick, personal, school, or other');
            $table->unsignedBigInteger('lesson_id')->comment('Source lesson id');
            $table->unsignedBigInteger('lesson_student_id')->comment('Source lesson student id');
            $table->unsignedBigInteger('class_id')->comment('Class id');
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('account_id')->comment('Student course account id');
            $table->unsignedBigInteger('guardian_id')->nullable()->comment('Guardian id when source is guardian');
            $table->unsignedBigInteger('teacher_id')->nullable()->comment('Teacher id of source lesson');
            $table->string('reason', 500)->comment('Leave reason');
            $table->string('status', 30)->default('pending')->comment('pending, approved, rejected, cancelled, makeup_scheduled, or closed');
            $table->timestamp('requested_at')->nullable()->comment('Requested time');
            $table->timestamp('reviewed_at')->nullable()->comment('Review time');
            $table->unsignedBigInteger('reviewed_by')->nullable()->comment('Reviewer user id');
            $table->string('review_remark', 500)->nullable()->comment('Review remark');
            $table->timestamp('cancelled_at')->nullable()->comment('Cancellation time');
            $table->unsignedBigInteger('cancelled_by')->nullable()->comment('Cancellation operator user id');
            $table->string('cancel_reason', 500)->nullable()->comment('Cancellation reason');
            $table->boolean('makeup_required')->default(true)->comment('Whether make-up is required');
            $table->unsignedBigInteger('makeup_lesson_id')->nullable()->comment('Created make-up lesson id');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'leave_no'], 'uk_edu_leave_requests_tenant_no');
            $table->unique(['tenant_id', 'lesson_student_id'], 'uk_edu_leave_requests_tenant_lesson_student');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_leave_requests_tenant_campus_status');
            $table->index(['tenant_id', 'student_id', 'status'], 'idx_edu_leave_requests_tenant_student_status');
            $table->index(['tenant_id', 'lesson_id'], 'idx_edu_leave_requests_tenant_lesson');
            $table->index(['tenant_id', 'makeup_lesson_id'], 'idx_edu_leave_requests_tenant_makeup_lesson');
            $table->index('deleted_at', 'idx_edu_leave_requests_deleted_at');
        });

        Schema::create('edu_lesson_change_records', static function (Blueprint $table): void {
            $table->comment('Education lesson change records');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->string('change_no', 64)->comment('Lesson change number');
            $table->string('change_type', 20)->comment('makeup or reschedule');
            $table->string('status', 20)->default('confirmed')->comment('confirmed or cancelled');
            $table->unsignedBigInteger('leave_request_id')->nullable()->comment('Leave request id for make-up');
            $table->unsignedBigInteger('source_lesson_id')->comment('Original lesson id');
            $table->unsignedBigInteger('source_lesson_student_id')->nullable()->comment('Original lesson student id for make-up');
            $table->unsignedBigInteger('target_lesson_id')->nullable()->comment('Target lesson id for make-up or rescheduled source lesson id');
            $table->unsignedBigInteger('class_id')->comment('Class id');
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->unsignedBigInteger('student_id')->nullable()->comment('Student id for make-up');
            $table->unsignedBigInteger('account_id')->nullable()->comment('Student course account id for make-up');
            $table->unsignedBigInteger('source_teacher_id')->nullable()->comment('Source teacher id');
            $table->unsignedBigInteger('target_teacher_id')->nullable()->comment('Target teacher id');
            $table->unsignedBigInteger('source_classroom_id')->nullable()->comment('Source classroom id');
            $table->unsignedBigInteger('target_classroom_id')->nullable()->comment('Target classroom id');
            $table->timestamp('source_start_at')->nullable()->comment('Source lesson start time');
            $table->timestamp('source_end_at')->nullable()->comment('Source lesson end time');
            $table->timestamp('target_start_at')->nullable()->comment('Target lesson start time');
            $table->timestamp('target_end_at')->nullable()->comment('Target lesson end time');
            $table->decimal('lesson_units', 10, 2)->default(1)->comment('Lesson units');
            $table->string('reason', 500)->comment('Change reason');
            $table->timestamp('cancelled_at')->nullable()->comment('Cancellation time');
            $table->unsignedBigInteger('cancelled_by')->nullable()->comment('Cancellation operator user id');
            $table->string('cancel_reason', 500)->nullable()->comment('Cancellation reason');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'change_no'], 'uk_edu_lesson_change_records_tenant_no');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_lesson_change_records_tenant_campus_status');
            $table->index(['tenant_id', 'change_type', 'status'], 'idx_edu_lesson_change_records_tenant_type_status');
            $table->index(['tenant_id', 'source_lesson_id'], 'idx_edu_lesson_change_records_tenant_source_lesson');
            $table->index(['tenant_id', 'target_lesson_id'], 'idx_edu_lesson_change_records_tenant_target_lesson');
            $table->index(['tenant_id', 'leave_request_id'], 'idx_edu_lesson_change_records_tenant_leave');
            $table->index('deleted_at', 'idx_edu_lesson_change_records_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_lesson_change_records');
        Schema::dropIfExists('edu_leave_requests');
    }
};
