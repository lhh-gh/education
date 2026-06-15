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
        Schema::create('edu_courses', static function (Blueprint $table): void {
            $table->comment('Education courses');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->string('code', 64)->comment('Course code inside campus');
            $table->string('name', 120)->comment('Course name');
            $table->string('category', 80)->nullable()->comment('Course category');
            $table->string('subject', 80)->nullable()->comment('Course subject');
            $table->unsignedInteger('unit_minutes')->default(60)->comment('Minutes represented by one lesson unit');
            $table->string('cover_url', 255)->nullable()->comment('Course cover URL');
            $table->text('description')->nullable()->comment('Course description');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'campus_id', 'code'], 'uk_edu_courses_tenant_campus_code');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_courses_tenant_campus_status');
            $table->index(['tenant_id', 'campus_id', 'name'], 'idx_edu_courses_tenant_campus_name');
            $table->index('deleted_at', 'idx_edu_courses_deleted_at');
        });

        Schema::create('edu_teacher_courses', static function (Blueprint $table): void {
            $table->comment('Education teacher course authorizations');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->unsignedBigInteger('teacher_id')->comment('V1-01 teacher id');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->timestamp('authorized_at')->nullable()->comment('Authorization time');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'course_id', 'teacher_id'], 'uk_edu_teacher_courses_tenant_course_teacher');
            $table->index(['tenant_id', 'teacher_id', 'status'], 'idx_edu_teacher_courses_tenant_teacher_status');
            $table->index(['tenant_id', 'course_id', 'status'], 'idx_edu_teacher_courses_tenant_course_status');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_teacher_courses_tenant_campus_status');
            $table->index('deleted_at', 'idx_edu_teacher_courses_deleted_at');
        });

        Schema::create('edu_lesson_packages', static function (Blueprint $table): void {
            $table->comment('Education lesson packages');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->string('code', 64)->comment('Package code inside campus');
            $table->string('name', 120)->comment('Package name');
            $table->decimal('lesson_units', 10, 2)->default(0)->comment('Purchased lesson units');
            $table->decimal('bonus_units', 10, 2)->default(0)->comment('Bonus lesson units');
            $table->decimal('total_units', 10, 2)->default(0)->comment('lesson_units + bonus_units');
            $table->decimal('list_price', 12, 2)->default(0)->comment('Original list price');
            $table->decimal('sale_price', 12, 2)->default(0)->comment('Default deal price');
            $table->unsignedInteger('validity_days')->nullable()->comment('Account validity days after enrollment');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'campus_id', 'code'], 'uk_edu_lesson_packages_tenant_campus_code');
            $table->index(['tenant_id', 'course_id', 'status'], 'idx_edu_lesson_packages_tenant_course_status');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_lesson_packages_tenant_campus_status');
            $table->index('deleted_at', 'idx_edu_lesson_packages_deleted_at');
        });

        Schema::create('edu_student_course_accounts', static function (Blueprint $table): void {
            $table->comment('Education student course accounts');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->unsignedBigInteger('student_id')->comment('V1-01 student id');
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->decimal('purchased_units', 10, 2)->default(0)->comment('Purchased lesson units from enrollments');
            $table->decimal('bonus_units', 10, 2)->default(0)->comment('Bonus lesson units from enrollments');
            $table->decimal('consumed_units', 10, 2)->default(0)->comment('Consumed lesson units, owned by V1-04');
            $table->decimal('adjusted_units', 10, 2)->default(0)->comment('Manual adjustment units, owned by V1-04');
            $table->decimal('refunded_units', 10, 2)->default(0)->comment('Units reversed by cancelled/refunded enrollments');
            $table->decimal('frozen_units', 10, 2)->default(0)->comment('Frozen lesson units');
            $table->decimal('available_units', 10, 2)->default(0)->comment('Remaining usable lesson units');
            $table->string('status', 20)->default('active')->comment('active, frozen, or closed');
            $table->unsignedBigInteger('first_enrollment_id')->nullable()->comment('First enrollment id');
            $table->unsignedBigInteger('last_enrollment_id')->nullable()->comment('Last enrollment id');
            $table->timestamp('opened_at')->nullable()->comment('Account opened time');
            $table->timestamp('expires_at')->nullable()->comment('Account expiry time');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'student_id', 'course_id'], 'uk_edu_student_course_accounts_tenant_student_course');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_student_course_accounts_tenant_campus_status');
            $table->index(['tenant_id', 'student_id'], 'idx_edu_student_course_accounts_tenant_student');
            $table->index(['tenant_id', 'course_id'], 'idx_edu_student_course_accounts_tenant_course');
            $table->index('expires_at', 'idx_edu_student_course_accounts_expires_at');
            $table->index('deleted_at', 'idx_edu_student_course_accounts_deleted_at');
        });

        Schema::create('edu_enrollments', static function (Blueprint $table): void {
            $table->comment('Education enrollments');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->string('enrollment_no', 64)->comment('Enrollment number');
            $table->unsignedBigInteger('student_id')->comment('V1-01 student id');
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->unsignedBigInteger('lesson_package_id')->comment('Lesson package id');
            $table->unsignedBigInteger('account_id')->nullable()->comment('Student course account id, set on confirmation');
            $table->string('student_name_snapshot', 120)->comment('Student name at enrollment time');
            $table->string('course_name_snapshot', 120)->comment('Course name at enrollment time');
            $table->string('package_name_snapshot', 120)->comment('Package name at enrollment time');
            $table->decimal('package_lesson_units', 10, 2)->default(0)->comment('Purchased units snapshot');
            $table->decimal('package_bonus_units', 10, 2)->default(0)->comment('Bonus units snapshot');
            $table->decimal('total_units', 10, 2)->default(0)->comment('Total units snapshot');
            $table->decimal('list_price', 12, 2)->default(0)->comment('List price snapshot');
            $table->decimal('deal_amount', 12, 2)->default(0)->comment('Deal amount snapshot');
            $table->string('status', 20)->default('pending')->comment('pending, confirmed, or cancelled');
            $table->timestamp('enrolled_at')->nullable()->comment('Enrollment business time');
            $table->timestamp('confirmed_at')->nullable()->comment('Confirmed time');
            $table->timestamp('materialized_at')->nullable()->comment('Account materialization time');
            $table->timestamp('cancelled_at')->nullable()->comment('Cancelled time');
            $table->string('cancel_reason', 500)->nullable()->comment('Cancellation reason');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'enrollment_no'], 'uk_edu_enrollments_tenant_enrollment_no');
            $table->index(['tenant_id', 'student_id', 'status'], 'idx_edu_enrollments_tenant_student_status');
            $table->index(['tenant_id', 'course_id', 'status'], 'idx_edu_enrollments_tenant_course_status');
            $table->index(['tenant_id', 'lesson_package_id'], 'idx_edu_enrollments_tenant_package');
            $table->index(['tenant_id', 'account_id'], 'idx_edu_enrollments_tenant_account');
            $table->index(['tenant_id', 'campus_id', 'enrolled_at'], 'idx_edu_enrollments_tenant_campus_enrolled');
            $table->index('deleted_at', 'idx_edu_enrollments_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_enrollments');
        Schema::dropIfExists('edu_student_course_accounts');
        Schema::dropIfExists('edu_lesson_packages');
        Schema::dropIfExists('edu_teacher_courses');
        Schema::dropIfExists('edu_courses');
    }
};
