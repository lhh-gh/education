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
        Schema::create('edu_classrooms', static function (Blueprint $table): void {
            $table->comment('Education classrooms');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->string('code', 64)->comment('Classroom code inside campus');
            $table->string('name', 120)->comment('Classroom name');
            $table->unsignedInteger('capacity')->default(0)->comment('Seat capacity');
            $table->string('location', 120)->nullable()->comment('Location or room number');
            $table->json('equipment')->nullable()->comment('Equipment metadata');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'campus_id', 'code'], 'uk_edu_classrooms_tenant_campus_code');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_classrooms_tenant_campus_status');
            $table->index(['tenant_id', 'campus_id', 'name'], 'idx_edu_classrooms_tenant_campus_name');
            $table->index('deleted_at', 'idx_edu_classrooms_deleted_at');
        });

        Schema::create('edu_students', static function (Blueprint $table): void {
            $table->comment('Education students');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Primary campus id');
            $table->string('student_no', 64)->comment('Student number inside tenant');
            $table->string('name', 120)->comment('Student name');
            $table->string('gender', 20)->default('unknown')->comment('male, female, or unknown');
            $table->date('birthday')->nullable()->comment('Student birthday');
            $table->string('mobile', 30)->nullable()->comment('Student contact mobile');
            $table->string('school', 120)->nullable()->comment('Current school');
            $table->string('grade', 60)->nullable()->comment('Current grade');
            $table->string('source', 80)->nullable()->comment('Student source');
            $table->string('avatar', 255)->nullable()->comment('Avatar URL');
            $table->date('enrolled_at')->nullable()->comment('First enrollment date');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'student_no'], 'uk_edu_students_tenant_student_no');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_students_tenant_campus_status');
            $table->index(['tenant_id', 'name', 'mobile'], 'idx_edu_students_tenant_name_mobile');
            $table->index(['tenant_id', 'campus_id', 'name'], 'idx_edu_students_tenant_campus_name');
            $table->index('deleted_at', 'idx_edu_students_deleted_at');
        });

        Schema::create('edu_guardians', static function (Blueprint $table): void {
            $table->comment('Education guardians');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->string('name', 120)->comment('Guardian name');
            $table->string('mobile', 30)->comment('Guardian mobile');
            $table->string('gender', 20)->default('unknown')->comment('male, female, or unknown');
            $table->string('openid', 80)->nullable()->comment('WeChat openid');
            $table->string('unionid', 80)->nullable()->comment('WeChat unionid');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'mobile'], 'uk_edu_guardians_tenant_mobile');
            $table->unique(['tenant_id', 'openid'], 'uk_edu_guardians_tenant_openid');
            $table->unique(['tenant_id', 'unionid'], 'uk_edu_guardians_tenant_unionid');
            $table->index(['tenant_id', 'status'], 'idx_edu_guardians_tenant_status');
            $table->index(['tenant_id', 'name', 'mobile'], 'idx_edu_guardians_tenant_name_mobile');
            $table->index('deleted_at', 'idx_edu_guardians_deleted_at');
        });

        Schema::create('edu_student_guardians', static function (Blueprint $table): void {
            $table->comment('Education student guardian relations');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('guardian_id')->comment('Guardian id');
            $table->string('relation', 30)->default('guardian')->comment('father, mother, guardian, or other');
            $table->boolean('is_primary')->default(false)->comment('Primary contact for student');
            $table->boolean('can_receive_notice')->default(true)->comment('Can receive notices');
            $table->boolean('can_submit_leave')->default(true)->comment('Can submit leave requests');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'student_id', 'guardian_id'], 'uk_edu_student_guardians_tenant_student_guardian');
            $table->index(['tenant_id', 'student_id'], 'idx_edu_student_guardians_tenant_student');
            $table->index(['tenant_id', 'guardian_id'], 'idx_edu_student_guardians_tenant_guardian');
            $table->index(['tenant_id', 'relation'], 'idx_edu_student_guardians_tenant_relation');
            $table->index('deleted_at', 'idx_edu_student_guardians_deleted_at');
        });

        Schema::create('edu_teachers', static function (Blueprint $table): void {
            $table->comment('Education teachers');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Primary campus id');
            $table->unsignedBigInteger('user_profile_id')->nullable()->comment('Education user profile id for teacher mobile login');
            $table->string('teacher_no', 64)->comment('Teacher number inside tenant');
            $table->string('name', 120)->comment('Teacher name');
            $table->string('mobile', 30)->nullable()->comment('Teacher mobile');
            $table->string('gender', 20)->default('unknown')->comment('male, female, or unknown');
            $table->date('birthday')->nullable()->comment('Teacher birthday');
            $table->string('title', 80)->nullable()->comment('Teacher title');
            $table->date('hire_date')->nullable()->comment('Hire date');
            $table->string('avatar', 255)->nullable()->comment('Avatar URL');
            $table->text('introduction')->nullable()->comment('Teacher introduction');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'teacher_no'], 'uk_edu_teachers_tenant_teacher_no');
            $table->unique('user_profile_id', 'uk_edu_teachers_user_profile');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_teachers_tenant_campus_status');
            $table->index(['tenant_id', 'name', 'mobile'], 'idx_edu_teachers_tenant_name_mobile');
            $table->index('deleted_at', 'idx_edu_teachers_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_student_guardians');
        Schema::dropIfExists('edu_teachers');
        Schema::dropIfExists('edu_guardians');
        Schema::dropIfExists('edu_students');
        Schema::dropIfExists('edu_classrooms');
    }
};
