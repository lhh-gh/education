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
        Schema::create('edu_lead_sources', static function (Blueprint $table): void {
            $table->comment('Education V3 lead sources');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->string('code', 64)->comment('Source code');
            $table->string('name', 120)->comment('Source name');
            $table->string('channel_type', 40)->comment('Channel type');
            $table->unsignedBigInteger('default_consultant_id')->nullable()->comment('Default consultant user id');
            $table->string('status', 20)->default('enabled')->comment('enabled, disabled');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            $table->string('remark', 500)->nullable()->comment('Remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code'], 'uk_edu_lead_sources_tenant_code');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_lead_sources_status');
            $table->index('deleted_at', 'idx_edu_lead_sources_deleted_at');
        });

        Schema::create('edu_leads', static function (Blueprint $table): void {
            $table->comment('Education V3 leads');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->string('lead_no', 64)->comment('Lead number');
            $table->unsignedBigInteger('source_id')->nullable()->comment('Lead source id');
            $table->string('contact_name', 120)->comment('Contact name');
            $table->string('contact_mobile', 30)->comment('Contact mobile');
            $table->string('contact_wechat', 80)->nullable()->comment('Contact WeChat');
            $table->string('stage', 30)->default('new')->comment('Lead stage');
            $table->string('status', 20)->default('active')->comment('Lead status');
            $table->unsignedBigInteger('owner_user_id')->nullable()->comment('Owner user id');
            $table->unsignedBigInteger('intention_course_id')->nullable()->comment('Intention course id');
            $table->string('intention_level', 20)->default('medium')->comment('Intention level');
            $table->timestamp('next_follow_at')->nullable()->comment('Next follow-up time');
            $table->timestamp('last_follow_at')->nullable()->comment('Last follow-up time');
            $table->string('remark', 500)->nullable()->comment('Remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'lead_no'], 'uk_edu_leads_tenant_lead_no');
            $table->index(['tenant_id', 'contact_mobile'], 'idx_edu_leads_tenant_mobile');
            $table->index(['tenant_id', 'campus_id', 'stage', 'owner_user_id'], 'idx_edu_leads_stage_owner');
            $table->index(['tenant_id', 'next_follow_at', 'status'], 'idx_edu_leads_next_follow');
            $table->index('deleted_at', 'idx_edu_leads_deleted_at');
        });

        Schema::create('edu_lead_guardians', static function (Blueprint $table): void {
            $table->comment('Education V3 lead guardians');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('lead_id')->comment('Lead id');
            $table->string('name', 120)->comment('Guardian name');
            $table->string('mobile', 30)->comment('Guardian mobile');
            $table->string('relation', 30)->comment('Relation');
            $table->string('wechat', 80)->nullable()->comment('WeChat');
            $table->boolean('is_primary')->default(false)->comment('Primary guardian flag');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'lead_id'], 'idx_edu_lead_guardians_lead');
            $table->index(['tenant_id', 'mobile'], 'idx_edu_lead_guardians_mobile');
            $table->index('deleted_at', 'idx_edu_lead_guardians_deleted_at');
        });

        Schema::create('edu_lead_students', static function (Blueprint $table): void {
            $table->comment('Education V3 lead students');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('lead_id')->comment('Lead id');
            $table->string('name', 120)->comment('Student name');
            $table->string('gender', 20)->default('unknown')->comment('Gender');
            $table->date('birthday')->nullable()->comment('Birthday');
            $table->string('grade', 60)->nullable()->comment('Grade');
            $table->string('school', 120)->nullable()->comment('School');
            $table->unsignedBigInteger('intention_course_id')->nullable()->comment('Intention course id');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'lead_id'], 'idx_edu_lead_students_lead');
            $table->index(['tenant_id', 'name'], 'idx_edu_lead_students_name');
            $table->index('deleted_at', 'idx_edu_lead_students_deleted_at');
        });

        Schema::create('edu_lead_assignments', static function (Blueprint $table): void {
            $table->comment('Education V3 lead assignments');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('lead_id')->comment('Lead id');
            $table->unsignedBigInteger('from_user_id')->nullable()->comment('Previous owner user id');
            $table->unsignedBigInteger('to_user_id')->comment('New owner user id');
            $table->string('status', 20)->default('active')->comment('active, replaced, cancelled');
            $table->timestamp('assigned_at')->comment('Assigned time');
            $table->string('reason', 500)->nullable()->comment('Assignment reason');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'lead_id', 'status'], 'idx_edu_lead_assignments_lead');
            $table->index(['tenant_id', 'to_user_id', 'status'], 'idx_edu_lead_assignments_owner');
            $table->index('deleted_at', 'idx_edu_lead_assignments_deleted_at');
        });

        Schema::create('edu_lead_follow_records', static function (Blueprint $table): void {
            $table->comment('Education V3 lead follow records');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('lead_id')->comment('Lead id');
            $table->string('follow_type', 30)->comment('Follow type');
            $table->text('content')->comment('Follow content');
            $table->timestamp('next_follow_at')->nullable()->comment('Next follow-up time');
            $table->string('result', 40)->default('continued')->comment('Follow result');
            $table->unsignedBigInteger('operator_user_id')->comment('Operator user id');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();

            $table->index(['tenant_id', 'lead_id', 'created_at'], 'idx_edu_lead_follow_records_lead_time');
            $table->index(['tenant_id', 'operator_user_id', 'created_at'], 'idx_edu_lead_follow_records_operator');
        });

        Schema::create('edu_trial_lessons', static function (Blueprint $table): void {
            $table->comment('Education V3 trial lessons');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('lead_id')->comment('Lead id');
            $table->unsignedBigInteger('lead_student_id')->comment('Lead student id');
            $table->unsignedBigInteger('course_id')->comment('V1 course id');
            $table->unsignedBigInteger('teacher_id')->comment('V1 teacher id');
            $table->unsignedBigInteger('classroom_id')->nullable()->comment('V1 classroom id');
            $table->timestamp('start_time')->comment('Start time');
            $table->timestamp('end_time')->comment('End time');
            $table->string('status', 20)->default('scheduled')->comment('Trial lesson status');
            $table->unsignedBigInteger('consultant_user_id')->nullable()->comment('Consultant user id');
            $table->string('remark', 500)->nullable()->comment('Remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'campus_id', 'start_time', 'status'], 'idx_edu_trial_lessons_time');
            $table->index(['tenant_id', 'teacher_id', 'start_time', 'end_time'], 'idx_edu_trial_lessons_teacher_time');
            $table->index(['tenant_id', 'lead_id'], 'idx_edu_trial_lessons_lead');
            $table->index('deleted_at', 'idx_edu_trial_lessons_deleted_at');
        });

        Schema::create('edu_trial_attendances', static function (Blueprint $table): void {
            $table->comment('Education V3 trial attendances');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('trial_lesson_id')->comment('Trial lesson id');
            $table->unsignedBigInteger('lead_student_id')->comment('Lead student id');
            $table->string('attendance_status', 20)->comment('Attendance status');
            $table->unsignedBigInteger('checked_by')->comment('Check user id');
            $table->timestamp('checked_at')->comment('Check time');
            $table->string('remark', 500)->nullable()->comment('Remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();

            $table->unique(['tenant_id', 'trial_lesson_id', 'lead_student_id'], 'uk_edu_trial_attendances_lesson_student');
            $table->index(['tenant_id', 'attendance_status'], 'idx_edu_trial_attendances_status');
        });

        Schema::create('edu_trial_feedbacks', static function (Blueprint $table): void {
            $table->comment('Education V3 trial feedbacks');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('trial_lesson_id')->comment('Trial lesson id');
            $table->unsignedBigInteger('lead_id')->comment('Lead id');
            $table->string('feedback_type', 30)->comment('teacher, consultant');
            $table->unsignedBigInteger('teacher_id')->nullable()->comment('V1 teacher id');
            $table->unsignedBigInteger('consultant_user_id')->nullable()->comment('Consultant user id');
            $table->unsignedInteger('score')->nullable()->comment('Feedback score');
            $table->text('content')->comment('Feedback content');
            $table->unsignedBigInteger('recommend_course_id')->nullable()->comment('Recommended V1 course id');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();

            $table->index(['tenant_id', 'trial_lesson_id', 'feedback_type'], 'idx_edu_trial_feedbacks_lesson');
            $table->index(['tenant_id', 'lead_id'], 'idx_edu_trial_feedbacks_lead');
        });

        Schema::create('edu_lead_conversion_records', static function (Blueprint $table): void {
            $table->comment('Education V3 lead conversion records');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('lead_id')->comment('Lead id');
            $table->unsignedBigInteger('student_id')->comment('V1 student id');
            $table->unsignedBigInteger('guardian_id')->comment('V1 guardian id');
            $table->unsignedBigInteger('enrollment_id')->comment('V1 enrollment id');
            $table->unsignedBigInteger('student_course_account_id')->comment('V1 course account id');
            $table->string('status', 20)->default('success')->comment('Conversion status');
            $table->unsignedBigInteger('converted_by')->comment('Converter user id');
            $table->timestamp('converted_at')->comment('Converted time');
            $table->json('payload_json')->comment('Conversion payload');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();

            $table->unique(['tenant_id', 'lead_id'], 'uk_edu_lead_conversion_records_lead');
            $table->index(['tenant_id', 'student_id'], 'idx_edu_lead_conversion_records_student');
        });

        Schema::create('edu_admission_tasks', static function (Blueprint $table): void {
            $table->comment('Education V3 admission tasks');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->unsignedBigInteger('lead_id')->nullable()->comment('Lead id');
            $table->string('task_type', 40)->comment('Task type');
            $table->string('title', 160)->comment('Task title');
            $table->unsignedBigInteger('assignee_user_id')->comment('Assignee user id');
            $table->string('status', 20)->default('pending')->comment('Task status');
            $table->timestamp('due_at')->nullable()->comment('Due time');
            $table->timestamp('completed_at')->nullable()->comment('Completed time');
            $table->string('result', 500)->nullable()->comment('Task result');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'assignee_user_id', 'due_at', 'status'], 'idx_edu_admission_tasks_owner_due');
            $table->index(['tenant_id', 'lead_id'], 'idx_edu_admission_tasks_lead');
            $table->index('deleted_at', 'idx_edu_admission_tasks_deleted_at');
        });

        Schema::create('edu_admission_metrics_daily', static function (Blueprint $table): void {
            $table->comment('Education V3 admission daily metrics');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id');
            $table->date('metric_date')->comment('Metric date');
            $table->unsignedBigInteger('source_id')->nullable()->comment('Lead source id');
            $table->unsignedBigInteger('consultant_user_id')->nullable()->comment('Consultant user id');
            $table->unsignedInteger('new_leads_count')->default(0)->comment('New leads count');
            $table->unsignedInteger('follow_count')->default(0)->comment('Follow count');
            $table->unsignedInteger('trial_count')->default(0)->comment('Trial count');
            $table->unsignedInteger('trial_attended_count')->default(0)->comment('Trial attended count');
            $table->unsignedInteger('converted_count')->default(0)->comment('Converted count');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();

            $table->unique(['tenant_id', 'campus_id', 'metric_date', 'source_id', 'consultant_user_id'], 'uk_edu_admission_metrics_daily_scope');
            $table->index(['tenant_id', 'metric_date'], 'idx_edu_admission_metrics_daily_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_admission_metrics_daily');
        Schema::dropIfExists('edu_admission_tasks');
        Schema::dropIfExists('edu_lead_conversion_records');
        Schema::dropIfExists('edu_trial_feedbacks');
        Schema::dropIfExists('edu_trial_attendances');
        Schema::dropIfExists('edu_trial_lessons');
        Schema::dropIfExists('edu_lead_follow_records');
        Schema::dropIfExists('edu_lead_assignments');
        Schema::dropIfExists('edu_lead_students');
        Schema::dropIfExists('edu_lead_guardians');
        Schema::dropIfExists('edu_leads');
        Schema::dropIfExists('edu_lead_sources');
    }
};
