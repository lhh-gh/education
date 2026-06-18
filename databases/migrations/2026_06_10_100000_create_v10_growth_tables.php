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
        Schema::create('edu_growth_lead_scores', static function (Blueprint $table): void {
            $table->comment('Education V10 growth lead scores');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('lead_id')->comment('V3 lead id');
            $table->date('score_date')->comment('Score date');
            $table->unsignedInteger('score')->default(0)->comment('Lead score');
            $table->string('score_level', 20)->comment('Score level');
            $table->string('stage', 40)->comment('Lead stage');
            $table->unsignedBigInteger('owner_user_id')->nullable()->comment('Owner user id');
            $table->string('summary', 500)->nullable()->comment('Score summary');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'lead_id', 'score_date'], 'uk_edu_growth_lead_scores_lead_date');
            $table->index(['tenant_id', 'owner_user_id', 'score_level'], 'idx_edu_growth_lead_scores_owner_level');
        });

        Schema::create('edu_growth_lead_score_factors', static function (Blueprint $table): void {
            $table->comment('Education V10 growth lead score factors');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('lead_score_id')->comment('Lead score id');
            $table->string('factor_code', 64)->comment('Factor code');
            $table->string('factor_name', 120)->comment('Factor name');
            $table->string('factor_value', 120)->comment('Factor value');
            $table->integer('points')->default(0)->comment('Factor points');
            $table->decimal('weight', 8, 4)->default(1)->comment('Factor weight');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'lead_score_id'], 'idx_edu_growth_lead_score_factors_score');
            $table->index(['tenant_id', 'factor_code'], 'idx_edu_growth_lead_score_factors_code');
        });

        Schema::create('edu_growth_followup_strategies', static function (Blueprint $table): void {
            $table->comment('Education V10 growth follow-up strategies');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('strategy_code', 64)->comment('Strategy code');
            $table->string('strategy_name', 120)->comment('Strategy name');
            $table->string('lead_stage', 40)->comment('Lead stage');
            $table->string('score_level', 20)->nullable()->comment('Score level');
            $table->text('suggestion_template')->comment('Suggestion template');
            $table->unsignedInteger('next_follow_hours')->default(24)->comment('Next follow hours');
            $table->string('status', 20)->default('enabled')->comment('Strategy status');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'strategy_code'], 'uk_edu_growth_followup_strategies_code');
            $table->index(['tenant_id', 'lead_stage', 'status'], 'idx_edu_growth_followup_strategies_stage');
            $table->index('deleted_at', 'idx_edu_growth_followup_strategies_deleted_at');
        });

        Schema::create('edu_growth_followup_suggestions', static function (Blueprint $table): void {
            $table->comment('Education V10 growth follow-up suggestions');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('lead_id')->comment('V3 lead id');
            $table->unsignedBigInteger('strategy_id')->nullable()->comment('Strategy id');
            $table->unsignedBigInteger('owner_user_id')->comment('Owner user id');
            $table->text('suggestion_text')->comment('Suggestion text');
            $table->string('status', 20)->default('pending')->comment('Suggestion status');
            $table->timestamp('due_at')->nullable()->comment('Due time');
            $table->timestamp('handled_at')->nullable()->comment('Handled time');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'owner_user_id', 'status', 'due_at'], 'idx_edu_growth_followup_suggestions_owner_status');
            $table->index(['tenant_id', 'lead_id', 'status'], 'idx_edu_growth_followup_suggestions_lead');
        });

        Schema::create('edu_growth_ai_talk_scripts', static function (Blueprint $table): void {
            $table->comment('Education V10 growth AI talk scripts');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('lead_id')->comment('V3 lead id');
            $table->unsignedBigInteger('generation_task_id')->nullable()->comment('V8 AI generation task id');
            $table->string('script_type', 60)->comment('Script type');
            $table->longText('script_text')->comment('Script text');
            $table->string('status', 20)->default('draft')->comment('Script status');
            $table->unsignedBigInteger('confirmed_by')->nullable()->comment('Confirmed by user id');
            $table->timestamp('confirmed_at')->nullable()->comment('Confirmed time');
            $table->json('masked_input_json')->comment('Masked input JSON');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'lead_id', 'status'], 'idx_edu_growth_ai_talk_scripts_lead');
            $table->index(['tenant_id', 'generation_task_id'], 'idx_edu_growth_ai_talk_scripts_task');
        });

        Schema::create('edu_growth_conversion_funnels', static function (Blueprint $table): void {
            $table->comment('Education V10 growth conversion funnels');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->date('metric_date')->comment('Metric date');
            $table->unsignedBigInteger('source_id')->nullable()->comment('Lead source id');
            $table->string('stage', 40)->comment('Funnel stage');
            $table->unsignedInteger('lead_count')->default(0)->comment('Lead count');
            $table->unsignedInteger('next_stage_count')->default(0)->comment('Next stage count');
            $table->decimal('conversion_rate', 8, 4)->nullable()->comment('Conversion rate');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'campus_id', 'metric_date', 'source_id', 'stage'], 'uk_edu_growth_conversion_funnels_scope');
            $table->index(['tenant_id', 'metric_date'], 'idx_edu_growth_conversion_funnels_date');
        });

        Schema::create('edu_growth_channel_costs', static function (Blueprint $table): void {
            $table->comment('Education V10 growth channel costs');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('source_id')->comment('Lead source id');
            $table->date('cost_date')->comment('Cost date');
            $table->string('cost_type', 40)->comment('Cost type');
            $table->unsignedBigInteger('amount_cents')->default(0)->comment('Amount cents');
            $table->string('remark', 500)->nullable()->comment('Remark');
            self::auditColumns($table);
            $table->datetimes();

            $table->index(['tenant_id', 'source_id', 'cost_date'], 'idx_edu_growth_channel_costs_source_date');
            $table->index(['tenant_id', 'cost_date'], 'idx_edu_growth_channel_costs_date');
        });

        Schema::create('edu_growth_channel_roi_daily', static function (Blueprint $table): void {
            $table->comment('Education V10 growth daily channel ROI');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->date('metric_date')->comment('Metric date');
            $table->unsignedBigInteger('source_id')->comment('Lead source id');
            $table->unsignedInteger('lead_count')->default(0)->comment('Lead count');
            $table->unsignedInteger('converted_count')->default(0)->comment('Converted count');
            $table->unsignedBigInteger('cost_cents')->default(0)->comment('Cost cents');
            $table->unsignedBigInteger('converted_revenue_cents')->default(0)->comment('Converted revenue cents');
            $table->decimal('roi', 10, 4)->nullable()->comment('ROI');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'campus_id', 'source_id', 'metric_date'], 'uk_edu_growth_channel_roi_daily_source_date');
            $table->index(['tenant_id', 'metric_date'], 'idx_edu_growth_channel_roi_daily_date');
        });

        Schema::create('edu_growth_consultant_metrics_daily', static function (Blueprint $table): void {
            $table->comment('Education V10 growth daily consultant metrics');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->date('metric_date')->comment('Metric date');
            $table->unsignedBigInteger('consultant_user_id')->comment('Consultant user id');
            $table->unsignedInteger('assigned_leads_count')->default(0)->comment('Assigned leads count');
            $table->unsignedInteger('follow_count')->default(0)->comment('Follow count');
            $table->unsignedInteger('trial_count')->default(0)->comment('Trial count');
            $table->unsignedInteger('converted_count')->default(0)->comment('Converted count');
            $table->unsignedInteger('lost_count')->default(0)->comment('Lost count');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'campus_id', 'consultant_user_id', 'metric_date'], 'uk_edu_growth_consultant_metrics_daily_user_date');
            $table->index(['tenant_id', 'metric_date'], 'idx_edu_growth_consultant_metrics_daily_date');
        });

        Schema::create('edu_growth_loss_reasons', static function (Blueprint $table): void {
            $table->comment('Education V10 growth loss reasons');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('reason_code', 64)->comment('Reason code');
            $table->string('reason_name', 120)->comment('Reason name');
            $table->string('reason_group', 60)->comment('Reason group');
            $table->string('status', 20)->default('enabled')->comment('Reason status');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'reason_code'], 'uk_edu_growth_loss_reasons_code');
            $table->index(['tenant_id', 'reason_group', 'status'], 'idx_edu_growth_loss_reasons_group');
            $table->index('deleted_at', 'idx_edu_growth_loss_reasons_deleted_at');
        });

        Schema::create('edu_growth_lead_loss_records', static function (Blueprint $table): void {
            $table->comment('Education V10 growth lead loss records');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->unsignedBigInteger('lead_id')->comment('V3 lead id');
            $table->unsignedBigInteger('loss_reason_id')->comment('Loss reason id');
            $table->unsignedBigInteger('lost_by')->comment('Lost by user id');
            $table->timestamp('lost_at')->comment('Lost time');
            $table->string('detail', 500)->nullable()->comment('Loss detail');
            self::auditColumns($table);
            $table->datetimes();

            $table->unique(['tenant_id', 'lead_id'], 'uk_edu_growth_lead_loss_records_lead');
            $table->index(['tenant_id', 'loss_reason_id', 'lost_at'], 'idx_edu_growth_lead_loss_records_reason');
        });

        Schema::create('edu_growth_campaigns', static function (Blueprint $table): void {
            $table->comment('Education V10 growth campaigns');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('campaign_code', 64)->comment('Campaign code');
            $table->string('campaign_name', 160)->comment('Campaign name');
            $table->unsignedBigInteger('source_id')->nullable()->comment('Lead source id');
            $table->date('start_date')->comment('Start date');
            $table->date('end_date')->nullable()->comment('End date');
            $table->unsignedBigInteger('budget_cents')->default(0)->comment('Budget cents');
            $table->string('status', 20)->default('draft')->comment('Campaign status');
            $table->string('remark', 500)->nullable()->comment('Remark');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'campaign_code'], 'uk_edu_growth_campaigns_code');
            $table->index(['tenant_id', 'start_date', 'end_date', 'status'], 'idx_edu_growth_campaigns_date_status');
            $table->index('deleted_at', 'idx_edu_growth_campaigns_deleted_at');
        });

        Schema::create('edu_growth_script_templates', static function (Blueprint $table): void {
            $table->comment('Education V10 growth script templates');
            $table->bigIncrements('id');
            self::scopeColumns($table);
            $table->string('template_code', 64)->comment('Template code');
            $table->string('template_name', 120)->comment('Template name');
            $table->string('script_type', 60)->comment('Script type');
            $table->text('content')->comment('Template content');
            $table->string('status', 20)->default('enabled')->comment('Template status');
            self::auditColumns($table);
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'template_code'], 'uk_edu_growth_script_templates_code');
            $table->index(['tenant_id', 'script_type', 'status'], 'idx_edu_growth_script_templates_type');
            $table->index('deleted_at', 'idx_edu_growth_script_templates_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_growth_script_templates');
        Schema::dropIfExists('edu_growth_campaigns');
        Schema::dropIfExists('edu_growth_lead_loss_records');
        Schema::dropIfExists('edu_growth_loss_reasons');
        Schema::dropIfExists('edu_growth_consultant_metrics_daily');
        Schema::dropIfExists('edu_growth_channel_roi_daily');
        Schema::dropIfExists('edu_growth_channel_costs');
        Schema::dropIfExists('edu_growth_conversion_funnels');
        Schema::dropIfExists('edu_growth_ai_talk_scripts');
        Schema::dropIfExists('edu_growth_followup_suggestions');
        Schema::dropIfExists('edu_growth_followup_strategies');
        Schema::dropIfExists('edu_growth_lead_score_factors');
        Schema::dropIfExists('edu_growth_lead_scores');
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
