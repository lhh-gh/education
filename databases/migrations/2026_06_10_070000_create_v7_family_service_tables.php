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
        Schema::create('edu_lesson_comments', static function (Blueprint $table): void {
            $table->comment('Education V7 lesson comments');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('lesson_id')->comment('Lesson id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('teacher_id')->comment('Teacher id');
            $table->text('content')->comment('Comment content');
            $table->string('status', 20)->default('draft')->comment('Publish status');
            $table->timestamp('published_at')->nullable()->comment('Published time');
            $table->timestamp('withdrawn_at')->nullable()->comment('Withdrawn time');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'lesson_id', 'student_id'], 'idx_edu_lesson_comments_lesson_student');
            $table->index(['tenant_id', 'student_id', 'status'], 'idx_edu_lesson_comments_student_status');
            $table->index('deleted_at', 'idx_edu_lesson_comments_deleted_at');
        });

        Schema::create('edu_lesson_comment_templates', static function (Blueprint $table): void {
            $table->comment('Education V7 lesson comment templates');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('template_code', 64)->comment('Template code');
            $table->string('template_name', 120)->comment('Template name');
            $table->text('content')->comment('Template content');
            $table->unsignedBigInteger('course_id')->nullable()->comment('Course id');
            $table->string('status', 20)->default('enabled')->comment('Template status');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'template_code'], 'uk_edu_lesson_comment_templates_code');
            $table->index(['tenant_id', 'course_id', 'status'], 'idx_edu_lesson_comment_templates_course');
            $table->index('deleted_at', 'idx_edu_lesson_comment_templates_deleted_at');
        });

        Schema::create('edu_student_performance_tags', static function (Blueprint $table): void {
            $table->comment('Education V7 student performance tags');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('tag_code', 64)->comment('Tag code');
            $table->string('tag_name', 80)->comment('Tag name');
            $table->string('tag_type', 40)->comment('Tag type');
            $table->string('status', 20)->default('enabled')->comment('Tag status');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'tag_code'], 'uk_edu_student_performance_tags_code');
            $table->index(['tenant_id', 'tag_type', 'status'], 'idx_edu_student_performance_tags_type');
            $table->index('deleted_at', 'idx_edu_student_performance_tags_deleted_at');
        });

        Schema::create('edu_lesson_comment_tag_relations', static function (Blueprint $table): void {
            $table->comment('Education V7 lesson comment tag relations');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('lesson_comment_id')->comment('Lesson comment id');
            $table->unsignedBigInteger('performance_tag_id')->comment('Performance tag id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(
                ['tenant_id', 'lesson_comment_id', 'performance_tag_id'],
                'uk_edu_lesson_comment_tag_relations_comment_tag'
            );
            $table->index(['tenant_id', 'student_id'], 'idx_edu_lesson_comment_tag_relations_student');
        });

        Schema::create('edu_homework_assignments', static function (Blueprint $table): void {
            $table->comment('Education V7 homework assignments');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('title', 160)->comment('Homework title');
            $table->text('content')->comment('Homework content');
            $table->unsignedBigInteger('course_id')->nullable()->comment('Course id');
            $table->unsignedBigInteger('class_id')->nullable()->comment('Class id');
            $table->unsignedBigInteger('lesson_id')->nullable()->comment('Lesson id');
            $table->string('status', 20)->default('draft')->comment('Homework status');
            $table->timestamp('publish_at')->nullable()->comment('Publish time');
            $table->timestamp('due_at')->nullable()->comment('Due time');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'campus_id', 'course_id', 'status'], 'idx_edu_homework_assignments_course_status');
            $table->index(['tenant_id', 'due_at', 'status'], 'idx_edu_homework_assignments_due');
            $table->index('deleted_at', 'idx_edu_homework_assignments_deleted_at');
        });

        Schema::create('edu_homework_targets', static function (Blueprint $table): void {
            $table->comment('Education V7 homework targets');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('homework_assignment_id')->comment('Homework assignment id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('guardian_id')->nullable()->comment('Guardian user id');
            $table->string('status', 20)->default('assigned')->comment('Target status');
            $table->timestamp('submitted_at')->nullable()->comment('Submitted time');
            $table->timestamp('reviewed_at')->nullable()->comment('Reviewed time');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'homework_assignment_id', 'student_id'], 'uk_edu_homework_targets_assignment_student');
            $table->index(['tenant_id', 'student_id', 'status'], 'idx_edu_homework_targets_student_status');
        });

        Schema::create('edu_homework_submissions', static function (Blueprint $table): void {
            $table->comment('Education V7 homework submissions');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('homework_target_id')->comment('Homework target id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('guardian_id')->comment('Guardian user id');
            $table->text('content')->nullable()->comment('Submission content');
            $table->unsignedInteger('attachment_count')->default(0)->comment('Attachment count');
            $table->timestamp('submitted_at')->comment('Submitted time');
            $table->string('status', 20)->default('submitted')->comment('Submission status');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'homework_target_id'], 'idx_edu_homework_submissions_target');
            $table->index(['tenant_id', 'student_id', 'submitted_at'], 'idx_edu_homework_submissions_student');
            $table->index('deleted_at', 'idx_edu_homework_submissions_deleted_at');
        });

        Schema::create('edu_homework_reviews', static function (Blueprint $table): void {
            $table->comment('Education V7 homework reviews');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('homework_submission_id')->comment('Homework submission id');
            $table->unsignedBigInteger('teacher_id')->comment('Teacher id');
            $table->unsignedInteger('score')->nullable()->comment('Review score');
            $table->text('content')->comment('Review content');
            $table->timestamp('reviewed_at')->comment('Reviewed time');
            $table->string('status', 20)->default('reviewed')->comment('Review status');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'homework_submission_id'], 'uk_edu_homework_reviews_submission');
            $table->index(['tenant_id', 'teacher_id', 'reviewed_at'], 'idx_edu_homework_reviews_teacher');
            $table->index('deleted_at', 'idx_edu_homework_reviews_deleted_at');
        });

        Schema::create('edu_growth_records', static function (Blueprint $table): void {
            $table->comment('Education V7 growth records');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('teacher_id')->nullable()->comment('Teacher id');
            $table->string('record_type', 40)->comment('Record type');
            $table->string('title', 160)->comment('Record title');
            $table->text('content')->comment('Record content');
            $table->string('status', 20)->default('draft')->comment('Publish status');
            $table->timestamp('published_at')->nullable()->comment('Published time');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'student_id', 'status'], 'idx_edu_growth_records_student_status');
            $table->index(['tenant_id', 'teacher_id', 'created_at'], 'idx_edu_growth_records_teacher');
            $table->index('deleted_at', 'idx_edu_growth_records_deleted_at');
        });

        Schema::create('edu_learning_reports', static function (Blueprint $table): void {
            $table->comment('Education V7 learning reports');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->string('report_title', 160)->comment('Report title');
            $table->string('report_period', 60)->comment('Report period');
            $table->string('status', 20)->default('draft')->comment('Publish status');
            $table->timestamp('published_at')->nullable()->comment('Published time');
            $table->timestamp('withdrawn_at')->nullable()->comment('Withdrawn time');
            $table->text('summary')->nullable()->comment('Report summary');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'student_id', 'status'], 'idx_edu_learning_reports_student_status');
            $table->index(['tenant_id', 'campus_id', 'report_period'], 'idx_edu_learning_reports_period');
            $table->index('deleted_at', 'idx_edu_learning_reports_deleted_at');
        });

        Schema::create('edu_learning_report_items', static function (Blueprint $table): void {
            $table->comment('Education V7 learning report items');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('learning_report_id')->comment('Learning report id');
            $table->string('item_type', 40)->comment('Item type');
            $table->string('title', 160)->comment('Item title');
            $table->text('content')->comment('Item content');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'learning_report_id', 'sort_order'], 'idx_edu_learning_report_items_report');
        });

        Schema::create('edu_family_messages', static function (Blueprint $table): void {
            $table->comment('Education V7 family messages');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('thread_id', 64)->comment('Thread id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->string('sender_type', 20)->comment('Sender type');
            $table->unsignedBigInteger('sender_user_id')->comment('Sender user id');
            $table->unsignedBigInteger('receiver_user_id')->nullable()->comment('Receiver user id');
            $table->text('content')->comment('Message content');
            $table->string('status', 20)->default('sent')->comment('Message status');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'thread_id', 'created_at'], 'idx_edu_family_messages_thread_time');
            $table->index(['tenant_id', 'student_id', 'created_at'], 'idx_edu_family_messages_student');
            $table->index('deleted_at', 'idx_edu_family_messages_deleted_at');
        });

        Schema::create('edu_family_read_receipts', static function (Blueprint $table): void {
            $table->comment('Education V7 family read receipts');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('business_type', 40)->comment('Business type');
            $table->unsignedBigInteger('business_id')->comment('Business id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->string('reader_type', 20)->comment('Reader type');
            $table->unsignedBigInteger('reader_user_id')->comment('Reader user id');
            $table->timestamp('read_at')->comment('Read time');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(
                ['tenant_id', 'business_type', 'business_id', 'reader_type', 'reader_user_id'],
                'uk_edu_family_read_receipts_reader'
            );
            $table->index(['tenant_id', 'student_id', 'read_at'], 'idx_edu_family_read_receipts_student');
        });

        Schema::create('edu_service_quality_metrics', static function (Blueprint $table): void {
            $table->comment('Education V7 service quality metrics');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->date('metric_date')->comment('Metric date');
            $table->unsignedBigInteger('teacher_id')->nullable()->comment('Teacher id');
            $table->unsignedBigInteger('student_id')->nullable()->comment('Student id');
            $table->unsignedInteger('comment_count')->default(0)->comment('Comment count');
            $table->unsignedInteger('homework_review_count')->default(0)->comment('Homework review count');
            $table->unsignedInteger('report_count')->default(0)->comment('Report count');
            $table->unsignedInteger('message_response_minutes')->nullable()->comment('Message response minutes');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(
                ['tenant_id', 'campus_id', 'metric_date', 'teacher_id', 'student_id'],
                'uk_edu_service_quality_metrics_scope_date'
            );
            $table->index(['tenant_id', 'teacher_id', 'metric_date'], 'idx_edu_service_quality_metrics_teacher');
        });

        Schema::create('edu_family_service_attachments', static function (Blueprint $table): void {
            $table->comment('Education V7 family service attachments');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('business_type', 40)->comment('Business type');
            $table->unsignedBigInteger('business_id')->comment('Business id');
            $table->unsignedBigInteger('student_id')->nullable()->comment('Student id');
            $table->string('file_name', 160)->comment('File name');
            $table->string('file_url', 255)->comment('File url');
            $table->unsignedBigInteger('file_size')->default(0)->comment('File size');
            $table->unsignedBigInteger('uploaded_by')->comment('Uploaded by user id');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'business_type', 'business_id'], 'idx_edu_family_service_attachments_business');
            $table->index(['tenant_id', 'student_id'], 'idx_edu_family_service_attachments_student');
            $table->index('deleted_at', 'idx_edu_family_service_attachments_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_family_service_attachments');
        Schema::dropIfExists('edu_service_quality_metrics');
        Schema::dropIfExists('edu_family_read_receipts');
        Schema::dropIfExists('edu_family_messages');
        Schema::dropIfExists('edu_learning_report_items');
        Schema::dropIfExists('edu_learning_reports');
        Schema::dropIfExists('edu_growth_records');
        Schema::dropIfExists('edu_homework_reviews');
        Schema::dropIfExists('edu_homework_submissions');
        Schema::dropIfExists('edu_homework_targets');
        Schema::dropIfExists('edu_homework_assignments');
        Schema::dropIfExists('edu_lesson_comment_tag_relations');
        Schema::dropIfExists('edu_student_performance_tags');
        Schema::dropIfExists('edu_lesson_comment_templates');
        Schema::dropIfExists('edu_lesson_comments');
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
