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
        Schema::create('edu_classes', static function (Blueprint $table): void {
            $table->comment('Education classes');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->unsignedBigInteger('course_id')->comment('V1-02 course id');
            $table->unsignedBigInteger('main_teacher_id')->nullable()->comment('Default teacher id');
            $table->unsignedBigInteger('classroom_id')->nullable()->comment('Default classroom id');
            $table->string('code', 64)->comment('Class code inside campus');
            $table->string('name', 120)->comment('Class name');
            $table->string('class_type', 20)->default('group')->comment('group or one_to_one');
            $table->unsignedInteger('max_students')->default(0)->comment('Maximum student count, 0 means unlimited');
            $table->date('start_date')->nullable()->comment('Class start date');
            $table->date('end_date')->nullable()->comment('Class end date');
            $table->decimal('lesson_units', 10, 2)->default(1)->comment('Default lesson units per lesson');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->string('schedule_note', 500)->nullable()->comment('Scheduling note');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'campus_id', 'code'], 'uk_edu_classes_tenant_campus_code');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_classes_tenant_campus_status');
            $table->index(['tenant_id', 'course_id', 'status'], 'idx_edu_classes_tenant_course_status');
            $table->index(['tenant_id', 'main_teacher_id', 'status'], 'idx_edu_classes_tenant_teacher_status');
            $table->index('deleted_at', 'idx_edu_classes_deleted_at');
        });

        Schema::create('edu_class_students', static function (Blueprint $table): void {
            $table->comment('Education class students');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->unsignedBigInteger('class_id')->comment('Class id');
            $table->unsignedBigInteger('course_id')->comment('Course id snapshot from class');
            $table->unsignedBigInteger('student_id')->comment('V1-01 student id');
            $table->unsignedBigInteger('account_id')->comment('V1-02 student course account id');
            $table->string('student_name_snapshot', 120)->comment('Student name at join time');
            $table->string('student_no_snapshot', 64)->comment('Student number at join time');
            $table->string('status', 20)->default('active')->comment('active, paused, or left');
            $table->timestamp('joined_at')->nullable()->comment('Joined time');
            $table->timestamp('left_at')->nullable()->comment('Left time');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'class_id', 'student_id'], 'uk_edu_class_students_tenant_class_student');
            $table->index(['tenant_id', 'student_id', 'status'], 'idx_edu_class_students_tenant_student_status');
            $table->index(['tenant_id', 'class_id', 'status'], 'idx_edu_class_students_tenant_class_status');
            $table->index(['tenant_id', 'account_id'], 'idx_edu_class_students_tenant_account');
            $table->index('deleted_at', 'idx_edu_class_students_deleted_at');
        });

        Schema::create('edu_lessons', static function (Blueprint $table): void {
            $table->comment('Education lessons');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->string('lesson_no', 64)->comment('Lesson number');
            $table->unsignedBigInteger('class_id')->comment('Class id');
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->unsignedBigInteger('teacher_id')->comment('Teacher id');
            $table->unsignedBigInteger('classroom_id')->nullable()->comment('Classroom id');
            $table->string('title', 160)->comment('Lesson title');
            $table->timestamp('start_at')->comment('Lesson start time');
            $table->timestamp('end_at')->comment('Lesson end time');
            $table->unsignedInteger('duration_minutes')->default(0)->comment('Duration in minutes');
            $table->decimal('lesson_units', 10, 2)->default(1)->comment('Lesson units to consume in V1-04');
            $table->unsignedInteger('student_count')->default(0)->comment('Snapshot student count');
            $table->string('status', 20)->default('scheduled')->comment('scheduled, cancelled, or completed');
            $table->string('source_type', 20)->default('manual')->comment('manual or batch');
            $table->string('schedule_batch_no', 64)->nullable()->comment('Batch schedule number');
            $table->string('class_name_snapshot', 120)->comment('Class name snapshot');
            $table->string('course_name_snapshot', 120)->comment('Course name snapshot');
            $table->string('teacher_name_snapshot', 120)->comment('Teacher name snapshot');
            $table->string('classroom_name_snapshot', 120)->nullable()->comment('Classroom name snapshot');
            $table->timestamp('cancelled_at')->nullable()->comment('Cancellation time');
            $table->string('cancel_reason', 500)->nullable()->comment('Cancellation reason');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'lesson_no'], 'uk_edu_lessons_tenant_lesson_no');
            $table->index(['tenant_id', 'campus_id', 'start_at', 'end_at'], 'idx_edu_lessons_tenant_campus_time');
            $table->index(['tenant_id', 'teacher_id', 'start_at', 'end_at'], 'idx_edu_lessons_tenant_teacher_time');
            $table->index(['tenant_id', 'classroom_id', 'start_at', 'end_at'], 'idx_edu_lessons_tenant_classroom_time');
            $table->index(['tenant_id', 'class_id', 'start_at', 'end_at'], 'idx_edu_lessons_tenant_class_time');
            $table->index(['tenant_id', 'status', 'start_at'], 'idx_edu_lessons_tenant_status_time');
            $table->index('schedule_batch_no', 'idx_edu_lessons_schedule_batch_no');
            $table->index('deleted_at', 'idx_edu_lessons_deleted_at');
        });

        Schema::create('edu_lesson_students', static function (Blueprint $table): void {
            $table->comment('Education lesson student snapshots');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->unsignedBigInteger('lesson_id')->comment('Lesson id');
            $table->unsignedBigInteger('class_id')->comment('Class id');
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('account_id')->comment('Student course account id');
            $table->string('student_name_snapshot', 120)->comment('Student name snapshot');
            $table->string('student_no_snapshot', 64)->comment('Student number snapshot');
            $table->decimal('lesson_units', 10, 2)->default(1)->comment('Planned lesson units');
            $table->string('status', 20)->default('planned')->comment('planned or cancelled');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'lesson_id', 'student_id'], 'uk_edu_lesson_students_tenant_lesson_student');
            $table->index(['tenant_id', 'student_id'], 'idx_edu_lesson_students_tenant_student');
            $table->index(['tenant_id', 'lesson_id', 'status'], 'idx_edu_lesson_students_tenant_lesson_status');
            $table->index(['tenant_id', 'account_id'], 'idx_edu_lesson_students_tenant_account');
            $table->index('deleted_at', 'idx_edu_lesson_students_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_lesson_students');
        Schema::dropIfExists('edu_lessons');
        Schema::dropIfExists('edu_class_students');
        Schema::dropIfExists('edu_classes');
    }
};
