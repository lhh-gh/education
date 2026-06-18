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
        Schema::create('edu_lesson_attendances', static function (Blueprint $table): void {
            $table->comment('Education lesson attendances');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->unsignedBigInteger('lesson_id')->comment('V1-03 lesson id');
            $table->unsignedBigInteger('lesson_student_id')->comment('V1-03 lesson student id');
            $table->unsignedBigInteger('class_id')->comment('Class id');
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('account_id')->comment('V1-02 student course account id');
            $table->string('attendance_status', 20)->comment('present, late, absent, or leave');
            $table->string('consume_policy', 20)->default('no_consume')->comment('consume or no_consume');
            $table->decimal('planned_units', 10, 2)->default(0)->comment('Planned lesson units from lesson student');
            $table->decimal('consumed_units', 10, 2)->default(0)->comment('Actual consumed units');
            $table->string('consumption_status', 20)->default('none')->comment('none, active, or reversed');
            $table->timestamp('submitted_at')->nullable()->comment('Attendance submitted time');
            $table->unsignedBigInteger('submitted_by')->nullable()->comment('Submitter user id');
            $table->string('attendance_batch_no', 64)->comment('Attendance submission batch number');
            $table->string('remark', 500)->nullable()->comment('Attendance remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'lesson_student_id'], 'uk_edu_lesson_attendances_tenant_lesson_student');
            $table->index(['tenant_id', 'lesson_id'], 'idx_edu_lesson_attendances_tenant_lesson');
            $table->index(['tenant_id', 'student_id'], 'idx_edu_lesson_attendances_tenant_student');
            $table->index(['tenant_id', 'account_id'], 'idx_edu_lesson_attendances_tenant_account');
            $table->index('attendance_batch_no', 'idx_edu_lesson_attendances_batch_no');
            $table->index('deleted_at', 'idx_edu_lesson_attendances_deleted_at');
        });

        Schema::create('edu_lesson_consumptions', static function (Blueprint $table): void {
            $table->comment('Education lesson consumptions');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->string('consumption_no', 64)->comment('Consumption number');
            $table->unsignedBigInteger('account_id')->comment('Student course account id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->unsignedBigInteger('lesson_id')->comment('Lesson id');
            $table->unsignedBigInteger('lesson_student_id')->comment('Lesson student id');
            $table->unsignedBigInteger('attendance_id')->comment('Attendance id');
            $table->string('source_type', 30)->default('attendance')->comment('attendance or rollback');
            $table->string('direction', 20)->default('decrease')->comment('decrease or increase');
            $table->decimal('units', 10, 2)->default(0)->comment('Consumption units');
            $table->decimal('before_available_units', 10, 2)->default(0)->comment('Account available units before change');
            $table->decimal('after_available_units', 10, 2)->default(0)->comment('Account available units after change');
            $table->decimal('before_consumed_units', 10, 2)->default(0)->comment('Account consumed units before change');
            $table->decimal('after_consumed_units', 10, 2)->default(0)->comment('Account consumed units after change');
            $table->string('status', 20)->default('active')->comment('active or reversed');
            $table->unsignedBigInteger('original_consumption_id')->nullable()->comment('Original consumption id when this is rollback');
            $table->timestamp('reversed_at')->nullable()->comment('Original row reversed time');
            $table->unsignedBigInteger('reversed_by')->nullable()->comment('Reversing operator user id');
            $table->string('reason', 500)->nullable()->comment('Rollback or consumption reason');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'consumption_no'], 'uk_edu_lesson_consumptions_tenant_no');
            $table->unique(['tenant_id', 'attendance_id', 'source_type'], 'uk_edu_lesson_consumptions_tenant_attendance_source');
            $table->unique(['tenant_id', 'original_consumption_id', 'source_type'], 'uk_edu_lesson_consumptions_tenant_original_source');
            $table->index(['tenant_id', 'account_id', 'created_at'], 'idx_edu_lesson_consumptions_tenant_account_time');
            $table->index(['tenant_id', 'lesson_id'], 'idx_edu_lesson_consumptions_tenant_lesson');
            $table->index(['tenant_id', 'student_id', 'course_id'], 'idx_edu_lesson_consumptions_tenant_student_course');
            $table->index(['tenant_id', 'status'], 'idx_edu_lesson_consumptions_tenant_status');
            $table->index('deleted_at', 'idx_edu_lesson_consumptions_deleted_at');
        });

        Schema::create('edu_account_adjustments', static function (Blueprint $table): void {
            $table->comment('Education account adjustments');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->string('adjustment_no', 64)->comment('Adjustment number');
            $table->unsignedBigInteger('account_id')->comment('Student course account id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->string('adjustment_type', 30)->default('supplement_deduction')->comment('supplement_deduction or rollback');
            $table->string('direction', 20)->default('decrease')->comment('decrease or increase');
            $table->decimal('units', 10, 2)->default(0)->comment('Adjustment units');
            $table->decimal('before_available_units', 10, 2)->default(0)->comment('Account available units before change');
            $table->decimal('after_available_units', 10, 2)->default(0)->comment('Account available units after change');
            $table->decimal('before_adjusted_units', 10, 2)->default(0)->comment('Account adjusted units before change');
            $table->decimal('after_adjusted_units', 10, 2)->default(0)->comment('Account adjusted units after change');
            $table->string('status', 20)->default('confirmed')->comment('confirmed or rolled_back');
            $table->unsignedBigInteger('original_adjustment_id')->nullable()->comment('Original adjustment id when this is rollback');
            $table->timestamp('rolled_back_at')->nullable()->comment('Original row rolled back time');
            $table->unsignedBigInteger('rolled_back_by')->nullable()->comment('Rollback operator user id');
            $table->string('reason', 500)->comment('Deduction or rollback reason');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'adjustment_no'], 'uk_edu_account_adjustments_tenant_no');
            $table->unique(['tenant_id', 'original_adjustment_id', 'adjustment_type'], 'uk_edu_account_adjustments_tenant_original_type');
            $table->index(['tenant_id', 'account_id', 'created_at'], 'idx_edu_account_adjustments_tenant_account_time');
            $table->index(['tenant_id', 'student_id', 'course_id'], 'idx_edu_account_adjustments_tenant_student_course');
            $table->index(['tenant_id', 'status'], 'idx_edu_account_adjustments_tenant_status');
            $table->index('deleted_at', 'idx_edu_account_adjustments_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_account_adjustments');
        Schema::dropIfExists('edu_lesson_consumptions');
        Schema::dropIfExists('edu_lesson_attendances');
    }
};
