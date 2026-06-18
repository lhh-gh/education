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
        Schema::create('edu_ai_model_configs', static function (Blueprint $table): void {
            $table->comment('Education V8 AI model configs');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('config_code', 64)->comment('Config code');
            $table->string('provider', 60)->comment('AI provider');
            $table->string('model_name', 120)->comment('Model name');
            $table->text('api_key_ciphertext')->nullable()->comment('Encrypted API key');
            $table->string('base_url', 255)->nullable()->comment('Provider base URL');
            $table->string('status', 20)->default('enabled')->comment('Config status');
            $table->decimal('default_temperature', 4, 2)->nullable()->comment('Default temperature');
            $table->unsignedInteger('daily_token_limit')->nullable()->comment('Daily token limit');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'config_code'], 'uk_edu_ai_model_configs_code');
            $table->index(['tenant_id', 'provider', 'status'], 'idx_edu_ai_model_configs_provider');
            $table->index('deleted_at', 'idx_edu_ai_model_configs_deleted_at');
        });

        Schema::create('edu_ai_feature_settings', static function (Blueprint $table): void {
            $table->comment('Education V8 AI feature settings');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('feature_code', 64)->comment('Feature code');
            $table->string('feature_name', 120)->comment('Feature name');
            $table->unsignedBigInteger('model_config_id')->comment('Model config id');
            $table->boolean('enabled')->default(false)->comment('Feature enabled');
            $table->boolean('review_required')->default(true)->comment('Review required');
            $table->string('safety_level', 20)->default('normal')->comment('Safety level');
            $table->json('config_json')->nullable()->comment('Feature config JSON');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'feature_code'], 'uk_edu_ai_feature_settings_code');
            $table->index(['tenant_id', 'enabled'], 'idx_edu_ai_feature_settings_enabled');
            $table->index('deleted_at', 'idx_edu_ai_feature_settings_deleted_at');
        });

        Schema::create('edu_ai_prompt_templates', static function (Blueprint $table): void {
            $table->comment('Education V8 AI prompt templates');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('template_code', 64)->comment('Template code');
            $table->string('feature_code', 64)->comment('Feature code');
            $table->string('template_name', 120)->comment('Template name');
            $table->unsignedInteger('version')->default(1)->comment('Prompt version');
            $table->text('system_prompt')->comment('System prompt');
            $table->text('user_prompt')->comment('User prompt');
            $table->string('status', 20)->default('draft')->comment('Prompt status');
            $table->timestamp('published_at')->nullable()->comment('Published time');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'template_code', 'version'], 'uk_edu_ai_prompt_templates_code_version');
            $table->index(['tenant_id', 'feature_code', 'status'], 'idx_edu_ai_prompt_templates_feature');
            $table->index('deleted_at', 'idx_edu_ai_prompt_templates_deleted_at');
        });

        Schema::create('edu_ai_generation_tasks', static function (Blueprint $table): void {
            $table->comment('Education V8 AI generation tasks');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('task_no', 64)->comment('Task number');
            $table->string('feature_code', 64)->comment('Feature code');
            $table->unsignedBigInteger('model_config_id')->comment('Model config id');
            $table->unsignedBigInteger('prompt_template_id')->comment('Prompt template id');
            $table->string('business_type', 60)->comment('Business type');
            $table->unsignedBigInteger('business_id')->nullable()->comment('Business id');
            $table->unsignedBigInteger('requester_user_id')->comment('Requester user id');
            $table->string('status', 20)->default('pending')->comment('Task status');
            $table->string('context_hash', 64)->comment('Permission-trimmed context hash');
            $table->timestamp('queued_at')->nullable()->comment('Queued time');
            $table->timestamp('started_at')->nullable()->comment('Started time');
            $table->timestamp('finished_at')->nullable()->comment('Finished time');
            $table->string('error_message', 500)->nullable()->comment('Error message');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'task_no'], 'uk_edu_ai_generation_tasks_no');
            $table->index(['tenant_id', 'feature_code', 'status'], 'idx_edu_ai_tasks_feature_status');
            $table->index(['tenant_id', 'requester_user_id', 'created_at'], 'idx_edu_ai_tasks_requester');
        });

        Schema::create('edu_ai_generation_results', static function (Blueprint $table): void {
            $table->comment('Education V8 AI generation results');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('generation_task_id')->comment('Generation task id');
            $table->longText('result_text')->comment('Generated text');
            $table->json('result_json')->nullable()->comment('Generated structured result');
            $table->string('safety_status', 20)->default('unchecked')->comment('Safety status');
            $table->string('review_status', 20)->default('pending')->comment('Review status');
            $table->boolean('visible_to_guardian')->default(false)->comment('Guardian visible');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'generation_task_id'], 'uk_edu_ai_generation_results_task');
            $table->index(['tenant_id', 'review_status'], 'idx_edu_ai_generation_results_review');
        });

        Schema::create('edu_ai_usage_logs', static function (Blueprint $table): void {
            $table->comment('Education V8 AI usage logs');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('generation_task_id')->nullable()->comment('Generation task id');
            $table->string('provider', 60)->comment('AI provider');
            $table->string('model_name', 120)->comment('Model name');
            $table->unsignedInteger('prompt_tokens')->default(0)->comment('Prompt tokens');
            $table->unsignedInteger('completion_tokens')->default(0)->comment('Completion tokens');
            $table->unsignedInteger('total_tokens')->default(0)->comment('Total tokens');
            $table->unsignedBigInteger('cost_cents')->default(0)->comment('Cost cents');
            $table->date('usage_date')->comment('Usage date');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'usage_date'], 'idx_edu_ai_usage_tenant_date');
            $table->index(['tenant_id', 'generation_task_id'], 'idx_edu_ai_usage_task');
        });

        Schema::create('edu_ai_review_logs', static function (Blueprint $table): void {
            $table->comment('Education V8 AI review logs');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('generation_result_id')->comment('Generation result id');
            $table->unsignedBigInteger('reviewer_user_id')->comment('Reviewer user id');
            $table->string('review_action', 20)->comment('Review action');
            $table->string('review_note', 500)->nullable()->comment('Review note');
            $table->timestamp('reviewed_at')->comment('Reviewed time');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'generation_result_id'], 'idx_edu_ai_review_logs_result');
            $table->index(['tenant_id', 'reviewer_user_id', 'reviewed_at'], 'idx_edu_ai_review_logs_reviewer');
        });

        Schema::create('edu_ai_risk_scores', static function (Blueprint $table): void {
            $table->comment('Education V8 AI risk scores');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('course_account_id')->nullable()->comment('Course account id');
            $table->date('score_date')->comment('Score date');
            $table->unsignedInteger('risk_score')->comment('Risk score');
            $table->string('risk_level', 20)->comment('Risk level');
            $table->string('summary', 500)->nullable()->comment('Risk summary');
            $table->unsignedBigInteger('generation_task_id')->nullable()->comment('Generation task id');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'student_id', 'score_date'], 'uk_edu_ai_risk_scores_student_date');
            $table->index(['tenant_id', 'risk_level', 'score_date'], 'idx_edu_ai_risk_scores_level');
        });

        Schema::create('edu_ai_risk_factors', static function (Blueprint $table): void {
            $table->comment('Education V8 AI risk factors');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('risk_score_id')->comment('Risk score id');
            $table->string('factor_code', 64)->comment('Factor code');
            $table->string('factor_name', 120)->comment('Factor name');
            $table->string('factor_value', 120)->comment('Factor value');
            $table->decimal('weight', 8, 4)->default(0)->comment('Factor weight');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'risk_score_id'], 'idx_edu_ai_risk_factors_score');
            $table->index(['tenant_id', 'factor_code'], 'idx_edu_ai_risk_factors_code');
        });

        Schema::create('edu_ai_data_question_logs', static function (Blueprint $table): void {
            $table->comment('Education V8 AI data question logs');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->text('question_text')->comment('Question text');
            $table->json('metric_codes_json')->comment('Metric codes JSON');
            $table->longText('answer_text')->nullable()->comment('Answer text');
            $table->unsignedBigInteger('requester_user_id')->comment('Requester user id');
            $table->string('status', 20)->default('pending')->comment('Question status');
            $table->string('error_message', 500)->nullable()->comment('Error message');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'requester_user_id', 'created_at'], 'idx_edu_ai_data_question_logs_requester');
            $table->index(['tenant_id', 'status'], 'idx_edu_ai_data_question_logs_status');
        });

        Schema::create('edu_ai_recommendation_tasks', static function (Blueprint $table): void {
            $table->comment('Education V8 AI recommendation tasks');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('recommendation_type', 60)->comment('Recommendation type');
            $table->string('target_type', 60)->comment('Target type');
            $table->unsignedBigInteger('target_id')->nullable()->comment('Target id');
            $table->unsignedBigInteger('assignee_user_id')->nullable()->comment('Assignee user id');
            $table->string('status', 20)->default('pending')->comment('Task status');
            $table->json('recommendation_json')->comment('Recommendation JSON');
            $table->timestamp('handled_at')->nullable()->comment('Handled time');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'assignee_user_id', 'status'], 'idx_edu_ai_recommendation_tasks_assignee_status');
            $table->index(['tenant_id', 'target_type', 'target_id'], 'idx_edu_ai_recommendation_tasks_target');
        });

        Schema::create('edu_ai_safety_events', static function (Blueprint $table): void {
            $table->comment('Education V8 AI safety events');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('generation_task_id')->nullable()->comment('Generation task id');
            $table->string('risk_level', 20)->comment('Risk level');
            $table->string('event_type', 60)->comment('Event type');
            $table->string('summary', 500)->comment('Event summary');
            $table->json('payload_json')->comment('Event payload JSON');
            $table->boolean('handled')->default(false)->comment('Handled');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'risk_level', 'created_at'], 'idx_edu_ai_safety_events_level');
            $table->index(['tenant_id', 'generation_task_id'], 'idx_edu_ai_safety_events_task');
        });

        Schema::create('edu_ai_metric_catalogs', static function (Blueprint $table): void {
            $table->comment('Education V8 AI metric catalogs');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('metric_code', 64)->comment('Metric code');
            $table->string('metric_name', 120)->comment('Metric name');
            $table->string('metric_group', 60)->comment('Metric group');
            $table->string('query_key', 120)->comment('Approved metric query key');
            $table->json('allowed_roles_json')->comment('Allowed roles JSON');
            $table->string('status', 20)->default('enabled')->comment('Metric status');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'metric_code'], 'uk_edu_ai_metric_catalogs_code');
            $table->index(['tenant_id', 'metric_group', 'status'], 'idx_edu_ai_metric_catalogs_group');
            $table->index('deleted_at', 'idx_edu_ai_metric_catalogs_deleted_at');
        });

        Schema::create('edu_ai_knowledge_documents', static function (Blueprint $table): void {
            $table->comment('Education V8 AI knowledge documents');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('document_code', 64)->comment('Document code');
            $table->string('title', 160)->comment('Document title');
            $table->longText('content')->comment('Document content');
            $table->string('scope_type', 40)->comment('Scope type');
            $table->json('scope_value_json')->nullable()->comment('Scope value JSON');
            $table->string('status', 20)->default('draft')->comment('Document status');
            $table->timestamp('published_at')->nullable()->comment('Published time');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'document_code'], 'uk_edu_ai_knowledge_documents_code');
            $table->index(['tenant_id', 'scope_type', 'status'], 'idx_edu_ai_knowledge_documents_scope');
            $table->index('deleted_at', 'idx_edu_ai_knowledge_documents_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_ai_knowledge_documents');
        Schema::dropIfExists('edu_ai_metric_catalogs');
        Schema::dropIfExists('edu_ai_safety_events');
        Schema::dropIfExists('edu_ai_recommendation_tasks');
        Schema::dropIfExists('edu_ai_data_question_logs');
        Schema::dropIfExists('edu_ai_risk_factors');
        Schema::dropIfExists('edu_ai_risk_scores');
        Schema::dropIfExists('edu_ai_review_logs');
        Schema::dropIfExists('edu_ai_usage_logs');
        Schema::dropIfExists('edu_ai_generation_results');
        Schema::dropIfExists('edu_ai_generation_tasks');
        Schema::dropIfExists('edu_ai_prompt_templates');
        Schema::dropIfExists('edu_ai_feature_settings');
        Schema::dropIfExists('edu_ai_model_configs');
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
