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
        Schema::create('edu_learning_materials', static function (Blueprint $table): void {
            $table->comment('Education V12 learning materials');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('material_code', 64)->comment('Material code');
            $table->string('material_name', 160)->comment('Material name');
            $table->unsignedBigInteger('course_id')->nullable()->comment('Course id');
            $table->string('material_type', 40)->comment('Material type');
            $table->string('status', 20)->default('draft')->comment('Publish status');
            $table->boolean('guardian_visible')->default(false)->comment('Guardian visible');
            $table->unsignedBigInteger('current_version_id')->nullable()->comment('Current version id');
            $table->string('summary', 500)->nullable()->comment('Summary');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'material_code'], 'uk_edu_learning_materials_code');
            $table->index(['tenant_id', 'course_id', 'status'], 'idx_edu_learning_materials_course_status');
            $table->index('deleted_at', 'idx_edu_learning_materials_deleted_at');
        });

        Schema::create('edu_learning_material_versions', static function (Blueprint $table): void {
            $table->comment('Education V12 learning material versions');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('material_id')->comment('Material id');
            $table->unsignedInteger('version_no')->comment('Version number');
            $table->string('title', 160)->comment('Version title');
            $table->longText('content')->nullable()->comment('Material content');
            $table->string('status', 20)->default('draft')->comment('Publish status');
            $table->json('snapshot_json')->comment('Snapshot JSON');
            $table->timestamp('published_at')->nullable()->comment('Published time');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'material_id', 'version_no'], 'uk_edu_learning_material_versions_material_version');
            $table->index(['tenant_id', 'status'], 'idx_edu_learning_material_versions_status');
        });

        Schema::create('edu_learning_material_attachments', static function (Blueprint $table): void {
            $table->comment('Education V12 learning material attachments');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('material_version_id')->comment('Material version id');
            $table->string('file_name', 160)->comment('File name');
            $table->string('file_url', 255)->comment('File URL');
            $table->string('file_type', 40)->comment('File type');
            $table->unsignedBigInteger('file_size')->default(0)->comment('File size');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'material_version_id', 'sort_order'], 'idx_edu_learning_material_attachments_version');
        });

        Schema::create('edu_learning_material_relations', static function (Blueprint $table): void {
            $table->comment('Education V12 learning material relations');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('material_id')->comment('Material id');
            $table->string('target_type', 60)->comment('Target type');
            $table->unsignedBigInteger('target_id')->comment('Target id');
            $table->string('relation_note', 500)->nullable()->comment('Relation note');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'material_id', 'target_type', 'target_id'], 'uk_edu_learning_material_relations_target');
            $table->index(['tenant_id', 'target_type', 'target_id'], 'idx_edu_learning_material_relations_lookup');
        });

        Schema::create('edu_lesson_material_usages', static function (Blueprint $table): void {
            $table->comment('Education V12 lesson material usages');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('lesson_id')->comment('Lesson id');
            $table->unsignedBigInteger('teacher_id')->comment('Teacher id');
            $table->unsignedBigInteger('material_id')->comment('Material id');
            $table->unsignedBigInteger('material_version_id')->comment('Material version id');
            $table->string('usage_type', 40)->comment('Usage type');
            $table->timestamp('used_at')->comment('Used time');
            $table->string('remark', 500)->nullable()->comment('Remark');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'lesson_id', 'teacher_id'], 'idx_edu_lesson_material_usages_lesson');
            $table->index(['tenant_id', 'material_id', 'used_at'], 'idx_edu_lesson_material_usages_material');
        });

        Schema::create('edu_teacher_material_favorites', static function (Blueprint $table): void {
            $table->comment('Education V12 teacher material favorites');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('teacher_id')->comment('Teacher id');
            $table->unsignedBigInteger('material_id')->comment('Material id');
            $table->timestamp('favorited_at')->comment('Favorited time');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'teacher_id', 'material_id'], 'uk_edu_teacher_material_favorites_teacher_material');
            $table->index(['tenant_id', 'teacher_id', 'favorited_at'], 'idx_edu_teacher_material_favorites_teacher');
        });

        Schema::create('edu_student_works', static function (Blueprint $table): void {
            $table->comment('Education V12 student works');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('lesson_id')->nullable()->comment('Lesson id');
            $table->unsignedBigInteger('teacher_id')->comment('Teacher id');
            $table->unsignedBigInteger('stage_goal_id')->nullable()->comment('Stage goal id');
            $table->string('title', 160)->comment('Title');
            $table->text('description')->nullable()->comment('Description');
            $table->string('status', 20)->default('draft')->comment('Publish status');
            $table->timestamp('published_at')->nullable()->comment('Published time');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'student_id', 'status'], 'idx_edu_student_works_student_status');
            $table->index(['tenant_id', 'teacher_id', 'created_at'], 'idx_edu_student_works_teacher');
            $table->index('deleted_at', 'idx_edu_student_works_deleted_at');
        });

        Schema::create('edu_student_work_attachments', static function (Blueprint $table): void {
            $table->comment('Education V12 student work attachments');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('student_work_id')->comment('Student work id');
            $table->string('file_name', 160)->comment('File name');
            $table->string('file_url', 255)->comment('File URL');
            $table->string('file_type', 40)->comment('File type');
            $table->unsignedBigInteger('file_size')->default(0)->comment('File size');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'student_work_id', 'sort_order'], 'idx_edu_student_work_attachments_work');
        });

        Schema::create('edu_stage_achievement_showcases', static function (Blueprint $table): void {
            $table->comment('Education V12 stage achievement showcases');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('stage_goal_id')->nullable()->comment('Stage goal id');
            $table->string('title', 160)->comment('Title');
            $table->text('summary')->nullable()->comment('Summary');
            $table->string('status', 20)->default('draft')->comment('Publish status');
            $table->timestamp('published_at')->nullable()->comment('Published time');
            $table->timestamp('withdrawn_at')->nullable()->comment('Withdrawn time');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'student_id', 'status'], 'idx_edu_stage_achievement_showcases_student_status');
            $table->index(['tenant_id', 'stage_goal_id'], 'idx_edu_stage_achievement_showcases_stage');
            $table->index('deleted_at', 'idx_edu_stage_achievement_showcases_deleted_at');
        });

        Schema::create('edu_showcase_items', static function (Blueprint $table): void {
            $table->comment('Education V12 showcase items');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('showcase_id')->comment('Showcase id');
            $table->string('item_type', 40)->comment('Item type');
            $table->unsignedBigInteger('student_work_id')->nullable()->comment('Student work id');
            $table->unsignedBigInteger('material_id')->nullable()->comment('Material id');
            $table->string('title', 160)->comment('Title');
            $table->text('content')->nullable()->comment('Content');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'showcase_id', 'sort_order'], 'idx_edu_showcase_items_showcase');
            $table->index(['tenant_id', 'student_work_id'], 'idx_edu_showcase_items_work');
        });

        Schema::create('edu_material_read_records', static function (Blueprint $table): void {
            $table->comment('Education V12 material read records');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('material_id')->comment('Material id');
            $table->unsignedBigInteger('material_version_id')->comment('Material version id');
            $table->unsignedBigInteger('student_id')->nullable()->comment('Student id');
            $table->unsignedBigInteger('guardian_user_id')->nullable()->comment('Guardian user id');
            $table->unsignedBigInteger('teacher_id')->nullable()->comment('Teacher id');
            $table->timestamp('read_at')->comment('Read time');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'material_version_id', 'student_id', 'guardian_user_id', 'teacher_id'], 'uk_edu_material_read_records_reader');
            $table->index(['tenant_id', 'material_id', 'read_at'], 'idx_edu_material_read_records_material');
        });

        Schema::create('edu_showcase_read_records', static function (Blueprint $table): void {
            $table->comment('Education V12 showcase read records');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('showcase_id')->comment('Showcase id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('guardian_user_id')->comment('Guardian user id');
            $table->timestamp('read_at')->comment('Read time');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'showcase_id', 'guardian_user_id'], 'uk_edu_showcase_read_records_reader');
            $table->index(['tenant_id', 'showcase_id', 'read_at'], 'idx_edu_showcase_read_records_showcase');
        });

        Schema::create('edu_material_publish_logs', static function (Blueprint $table): void {
            $table->comment('Education V12 material publish logs');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('material_id')->comment('Material id');
            $table->unsignedBigInteger('material_version_id')->comment('Material version id');
            $table->string('from_status', 20)->comment('From status');
            $table->string('to_status', 20)->comment('To status');
            $table->unsignedBigInteger('operator_id')->comment('Operator user id');
            $table->string('note', 500)->nullable()->comment('Note');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'material_id', 'created_at'], 'idx_edu_material_publish_logs_material');
        });

        Schema::create('edu_content_review_records', static function (Blueprint $table): void {
            $table->comment('Education V12 content review records');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('business_type', 60)->comment('Business type');
            $table->unsignedBigInteger('business_id')->comment('Business id');
            $table->unsignedBigInteger('reviewer_id')->comment('Reviewer user id');
            $table->string('status', 20)->default('pending')->comment('Review status');
            $table->string('review_note', 500)->nullable()->comment('Review note');
            $table->timestamp('reviewed_at')->nullable()->comment('Reviewed time');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->index(['tenant_id', 'reviewer_id', 'status'], 'idx_edu_content_review_records_reviewer');
            $table->index(['tenant_id', 'business_type', 'business_id'], 'idx_edu_content_review_records_business');
            $table->index('deleted_at', 'idx_edu_content_review_records_deleted_at');
        });

        Schema::create('edu_material_usage_metrics_daily', static function (Blueprint $table): void {
            $table->comment('Education V12 material usage metrics daily');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->date('metric_date')->comment('Metric date');
            $table->unsignedBigInteger('material_id')->nullable()->comment('Material id');
            $table->unsignedBigInteger('course_id')->nullable()->comment('Course id');
            $table->unsignedInteger('teacher_use_count')->default(0)->comment('Teacher use count');
            $table->unsignedInteger('guardian_read_count')->default(0)->comment('Guardian read count');
            $table->unsignedInteger('favorite_count')->default(0)->comment('Favorite count');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'campus_id', 'metric_date', 'material_id'], 'uk_edu_material_usage_metrics_daily_scope');
            $table->index(['tenant_id', 'metric_date'], 'idx_edu_material_usage_metrics_daily_date');
        });

        Schema::create('edu_student_work_metrics_daily', static function (Blueprint $table): void {
            $table->comment('Education V12 student work metrics daily');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->date('metric_date')->comment('Metric date');
            $table->unsignedBigInteger('student_id')->nullable()->comment('Student id');
            $table->unsignedBigInteger('teacher_id')->nullable()->comment('Teacher id');
            $table->unsignedInteger('created_count')->default(0)->comment('Created count');
            $table->unsignedInteger('published_count')->default(0)->comment('Published count');
            $table->unsignedInteger('showcase_count')->default(0)->comment('Showcase count');
            $table->unsignedInteger('guardian_read_count')->default(0)->comment('Guardian read count');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'campus_id', 'metric_date', 'student_id', 'teacher_id'], 'uk_edu_student_work_metrics_daily_scope');
            $table->index(['tenant_id', 'metric_date'], 'idx_edu_student_work_metrics_daily_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_student_work_metrics_daily');
        Schema::dropIfExists('edu_material_usage_metrics_daily');
        Schema::dropIfExists('edu_content_review_records');
        Schema::dropIfExists('edu_material_publish_logs');
        Schema::dropIfExists('edu_showcase_read_records');
        Schema::dropIfExists('edu_material_read_records');
        Schema::dropIfExists('edu_showcase_items');
        Schema::dropIfExists('edu_stage_achievement_showcases');
        Schema::dropIfExists('edu_student_work_attachments');
        Schema::dropIfExists('edu_student_works');
        Schema::dropIfExists('edu_teacher_material_favorites');
        Schema::dropIfExists('edu_lesson_material_usages');
        Schema::dropIfExists('edu_learning_material_relations');
        Schema::dropIfExists('edu_learning_material_attachments');
        Schema::dropIfExists('edu_learning_material_versions');
        Schema::dropIfExists('edu_learning_materials');
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
