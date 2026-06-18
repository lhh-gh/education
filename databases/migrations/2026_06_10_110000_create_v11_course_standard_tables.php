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
        Schema::create('edu_course_service_packages', static function (Blueprint $table): void {
            $table->comment('Education V11 course service packages');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('package_code', 64)->comment('Package code');
            $table->string('package_name', 120)->comment('Package name');
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->unsignedInteger('version_no')->default(1)->comment('Version number');
            $table->string('status', 20)->default('draft')->comment('Publish status');
            $table->boolean('guardian_visible')->default(false)->comment('Guardian visible');
            $table->text('description')->nullable()->comment('Description');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'package_code', 'version_no'], 'uk_edu_course_service_packages_code_version');
            $table->index(['tenant_id', 'course_id', 'status'], 'idx_edu_course_service_packages_course_status');
            $table->index('deleted_at', 'idx_edu_course_service_packages_deleted_at');
        });

        Schema::create('edu_course_stage_goals', static function (Blueprint $table): void {
            $table->comment('Education V11 course stage goals');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('service_package_id')->comment('Service package id');
            $table->string('goal_code', 64)->comment('Goal code');
            $table->string('goal_name', 120)->comment('Goal name');
            $table->text('goal_content')->comment('Goal content');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            $table->string('status', 20)->default('draft')->comment('Goal status');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'service_package_id', 'goal_code'], 'uk_edu_course_stage_goals_package_code');
            $table->index(['tenant_id', 'service_package_id', 'sort_order'], 'idx_edu_course_stage_goals_package_order');
            $table->index('deleted_at', 'idx_edu_course_stage_goals_deleted_at');
        });

        Schema::create('edu_course_ability_points', static function (Blueprint $table): void {
            $table->comment('Education V11 course ability points');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('ability_code', 64)->comment('Ability code');
            $table->string('ability_name', 120)->comment('Ability name');
            $table->string('ability_group', 60)->comment('Ability group');
            $table->text('description')->nullable()->comment('Description');
            $table->string('status', 20)->default('enabled')->comment('Ability status');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'ability_code'], 'uk_edu_course_ability_points_code');
            $table->index(['tenant_id', 'ability_group', 'status'], 'idx_edu_course_ability_points_group');
            $table->index('deleted_at', 'idx_edu_course_ability_points_deleted_at');
        });

        Schema::create('edu_course_stage_goal_ability_relations', static function (Blueprint $table): void {
            $table->comment('Education V11 stage goal ability relations');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('stage_goal_id')->comment('Stage goal id');
            $table->unsignedBigInteger('ability_point_id')->comment('Ability point id');
            $table->decimal('weight', 8, 4)->default(1)->comment('Weight');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'stage_goal_id', 'ability_point_id'], 'uk_edu_stage_goal_ability');
            $table->index(['tenant_id', 'ability_point_id'], 'idx_edu_stage_goal_ability_point');
        });

        Schema::create('edu_trial_lesson_standards', static function (Blueprint $table): void {
            $table->comment('Education V11 trial lesson standards');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->string('standard_code', 64)->comment('Standard code');
            $table->string('standard_name', 120)->comment('Standard name');
            $table->unsignedInteger('version_no')->default(1)->comment('Version number');
            $table->string('status', 20)->default('draft')->comment('Publish status');
            $table->boolean('guardian_visible')->default(false)->comment('Guardian visible');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'standard_code', 'version_no'], 'uk_edu_trial_lesson_standards_code_version');
            $table->index(['tenant_id', 'course_id', 'status'], 'idx_edu_trial_lesson_standards_course_status');
            $table->index('deleted_at', 'idx_edu_trial_lesson_standards_deleted_at');
        });

        Schema::create('edu_trial_lesson_standard_items', static function (Blueprint $table): void {
            $table->comment('Education V11 trial lesson standard items');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('trial_lesson_standard_id')->comment('Trial lesson standard id');
            $table->string('item_name', 120)->comment('Item name');
            $table->text('item_content')->comment('Item content');
            $table->decimal('score_weight', 8, 4)->nullable()->comment('Score weight');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'trial_lesson_standard_id', 'sort_order'], 'idx_edu_trial_lesson_standard_items_standard');
        });

        Schema::create('edu_teaching_delivery_standards', static function (Blueprint $table): void {
            $table->comment('Education V11 teaching delivery standards');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->string('standard_code', 64)->comment('Standard code');
            $table->string('standard_name', 120)->comment('Standard name');
            $table->string('lesson_type', 40)->comment('Lesson type');
            $table->text('content')->comment('Content');
            $table->unsignedInteger('version_no')->default(1)->comment('Version number');
            $table->string('status', 20)->default('draft')->comment('Publish status');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'standard_code', 'version_no'], 'uk_edu_teaching_delivery_standards_code_version');
            $table->index(['tenant_id', 'course_id', 'status'], 'idx_edu_teaching_delivery_standards_course_status');
            $table->index('deleted_at', 'idx_edu_teaching_delivery_standards_deleted_at');
        });

        Schema::create('edu_service_template_sets', static function (Blueprint $table): void {
            $table->comment('Education V11 service template sets');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('template_set_code', 64)->comment('Template set code');
            $table->string('template_set_name', 120)->comment('Template set name');
            $table->unsignedBigInteger('course_id')->nullable()->comment('Course id');
            $table->string('status', 20)->default('draft')->comment('Publish status');
            $table->unsignedInteger('version_no')->default(1)->comment('Version number');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'template_set_code', 'version_no'], 'uk_edu_service_template_sets_code_version');
            $table->index(['tenant_id', 'course_id', 'status'], 'idx_edu_service_template_sets_course_status');
            $table->index('deleted_at', 'idx_edu_service_template_sets_deleted_at');
        });

        Schema::create('edu_service_template_items', static function (Blueprint $table): void {
            $table->comment('Education V11 service template items');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('template_set_id')->comment('Template set id');
            $table->string('item_type', 40)->comment('Item type');
            $table->string('item_title', 120)->comment('Item title');
            $table->text('item_content')->comment('Item content');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'template_set_id', 'sort_order'], 'idx_edu_service_template_items_set');
        });

        Schema::create('edu_course_materials', static function (Blueprint $table): void {
            $table->comment('Education V11 course materials');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('material_code', 64)->comment('Material code');
            $table->string('material_name', 160)->comment('Material name');
            $table->unsignedBigInteger('course_id')->nullable()->comment('Course id');
            $table->string('material_type', 40)->comment('Material type');
            $table->string('file_url', 255)->nullable()->comment('File URL');
            $table->string('status', 20)->default('draft')->comment('Material status');
            $table->boolean('guardian_visible')->default(false)->comment('Guardian visible');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'material_code'], 'uk_edu_course_materials_code');
            $table->index(['tenant_id', 'course_id', 'status'], 'idx_edu_course_materials_course_status');
            $table->index('deleted_at', 'idx_edu_course_materials_deleted_at');
        });

        Schema::create('edu_course_feedback_records', static function (Blueprint $table): void {
            $table->comment('Education V11 course feedback records');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->unsignedBigInteger('standard_version_id')->nullable()->comment('Standard version id');
            $table->string('feedback_type', 40)->comment('Feedback type');
            $table->unsignedInteger('score')->nullable()->comment('Score');
            $table->text('content')->comment('Content');
            $table->string('source_type', 40)->nullable()->comment('Source type');
            $table->unsignedBigInteger('source_id')->nullable()->comment('Source id');
            $table->unsignedBigInteger('submitted_by')->comment('Submitted by user id');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'course_id', 'created_at'], 'idx_edu_course_feedback_records_course_time');
            $table->index(['tenant_id', 'standard_version_id'], 'idx_edu_course_feedback_records_version');
        });

        Schema::create('edu_course_quality_metrics_daily', static function (Blueprint $table): void {
            $table->comment('Education V11 daily course quality metrics');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->date('metric_date')->comment('Metric date');
            $table->unsignedBigInteger('course_id')->comment('Course id');
            $table->unsignedInteger('feedback_count')->default(0)->comment('Feedback count');
            $table->decimal('average_score', 6, 2)->nullable()->comment('Average score');
            $table->unsignedInteger('trial_feedback_count')->default(0)->comment('Trial feedback count');
            $table->unsignedInteger('delivery_feedback_count')->default(0)->comment('Delivery feedback count');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'campus_id', 'course_id', 'metric_date'], 'uk_edu_course_quality_metrics_daily_course_date');
            $table->index(['tenant_id', 'metric_date'], 'idx_edu_course_quality_metrics_daily_date');
        });

        Schema::create('edu_course_standard_versions', static function (Blueprint $table): void {
            $table->comment('Education V11 course standard versions');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('business_type', 60)->comment('Business type');
            $table->unsignedBigInteger('business_id')->comment('Business id');
            $table->unsignedInteger('version_no')->comment('Version number');
            $table->string('status', 20)->default('draft')->comment('Publish status');
            $table->json('snapshot_json')->comment('Snapshot JSON');
            $table->unsignedBigInteger('published_by')->nullable()->comment('Published by user id');
            $table->timestamp('published_at')->nullable()->comment('Published time');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'business_type', 'business_id', 'version_no'], 'uk_edu_course_standard_versions_business_version');
            $table->index(['tenant_id', 'status'], 'idx_edu_course_standard_versions_status');
        });

        Schema::create('edu_course_standard_publish_logs', static function (Blueprint $table): void {
            $table->comment('Education V11 course standard publish logs');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('standard_version_id')->comment('Standard version id');
            $table->string('business_type', 60)->comment('Business type');
            $table->unsignedBigInteger('business_id')->comment('Business id');
            $table->string('from_status', 20)->comment('From status');
            $table->string('to_status', 20)->comment('To status');
            $table->unsignedBigInteger('operator_id')->comment('Operator user id');
            $table->string('note', 500)->nullable()->comment('Note');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'standard_version_id', 'created_at'], 'idx_edu_course_standard_publish_logs_version');
        });

        Schema::create('edu_course_localization_overrides', static function (Blueprint $table): void {
            $table->comment('Education V11 course localization overrides');
            $table->bigIncrements('id');
            self::scopeColumns($table, true);
            $table->unsignedBigInteger('standard_version_id')->comment('Standard version id');
            $table->json('override_json')->comment('Override JSON');
            $table->string('status', 20)->default('draft')->comment('Publish status');
            $table->timestamp('published_at')->nullable()->comment('Published time');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'standard_version_id', 'campus_id'], 'uk_edu_course_localization_overrides_version_campus');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_course_localization_overrides_campus');
            $table->index('deleted_at', 'idx_edu_course_localization_overrides_deleted_at');
        });

        Schema::create('edu_course_standard_review_records', static function (Blueprint $table): void {
            $table->comment('Education V11 course standard review records');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('business_type', 60)->comment('Business type');
            $table->unsignedBigInteger('business_id')->comment('Business id');
            $table->unsignedBigInteger('standard_version_id')->nullable()->comment('Standard version id');
            $table->unsignedBigInteger('reviewer_id')->comment('Reviewer user id');
            $table->string('status', 20)->default('pending')->comment('Review status');
            $table->string('review_note', 500)->nullable()->comment('Review note');
            $table->timestamp('reviewed_at')->nullable()->comment('Reviewed time');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'reviewer_id', 'status'], 'idx_edu_course_standard_review_records_reviewer');
            $table->index(['tenant_id', 'business_type', 'business_id'], 'idx_edu_course_standard_review_records_business');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_course_standard_review_records');
        Schema::dropIfExists('edu_course_localization_overrides');
        Schema::dropIfExists('edu_course_standard_publish_logs');
        Schema::dropIfExists('edu_course_standard_versions');
        Schema::dropIfExists('edu_course_quality_metrics_daily');
        Schema::dropIfExists('edu_course_feedback_records');
        Schema::dropIfExists('edu_course_materials');
        Schema::dropIfExists('edu_service_template_items');
        Schema::dropIfExists('edu_service_template_sets');
        Schema::dropIfExists('edu_teaching_delivery_standards');
        Schema::dropIfExists('edu_trial_lesson_standard_items');
        Schema::dropIfExists('edu_trial_lesson_standards');
        Schema::dropIfExists('edu_course_stage_goal_ability_relations');
        Schema::dropIfExists('edu_course_ability_points');
        Schema::dropIfExists('edu_course_stage_goals');
        Schema::dropIfExists('edu_course_service_packages');
    }

    private static function scopeColumns(Blueprint $table, bool $campusRequired = false): void
    {
        $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
        $campus = $table->unsignedBigInteger('campus_id')->comment('Campus id');

        if (! $campusRequired) {
            $campus->nullable();
        }
    }

    private static function auditColumns(Blueprint $table): void
    {
        $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
        $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
    }
};
