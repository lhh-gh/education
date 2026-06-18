# MineAdmin Education SaaS V10 Growth Conversion Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V10 招生增长与顾问智能转化 for growth workbench, lead scoring, score factors, follow-up strategies, suggestions, AI talk scripts, conversion funnels, channel costs, ROI metrics, consultant metrics, loss reasons, lead loss records, campaigns, and script templates.

**Architecture:** V10 builds on V3 Admissions and V8 AI. It adds scoring, suggestions, and analytics for internal consultants while keeping trial conversion inside V3 conversion flow and requiring human confirmation before external communication.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, Redis queue, AI provider adapter boundary from V8, MineAdmin-Vue, Vue3, TypeScript, uni-app, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted.

---

## Scope Check

Included:

- Growth workbench for consultant daily lead priorities.
- Lead scores, score factors, recalculation after V3 follow/trial/conversion events.
- Follow-up strategies and follow-up suggestions.
- AI talk script generation/review using V8 provider boundary and masked inputs.
- Conversion funnels, channel costs, channel ROI daily metrics, and consultant metrics.
- Loss reason dictionary and lead loss records.
- Growth campaigns and script templates for internal use.
- Teacher mobile trial preparation and trial feedback enhancement.

Excluded:

- Public marketing automation and community operations.
- Automatic discount promises, payment order creation, lead conversion, or guardian outbound messages.
- Replacing V3 lead conversion transaction.

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_100000_create_v10_growth_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Growth/LeadScoreLevel.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Growth/SuggestionStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Growth/AiTalkScriptStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Growth/CampaignStatus.php
mineadmin-education-saas/backend/app/Model/Education/Growth/EducationGrowthLeadScore.php
mineadmin-education-saas/backend/app/Model/Education/Growth/EducationGrowthLeadScoreFactor.php
mineadmin-education-saas/backend/app/Model/Education/Growth/EducationGrowthFollowupStrategy.php
mineadmin-education-saas/backend/app/Model/Education/Growth/EducationGrowthFollowupSuggestion.php
mineadmin-education-saas/backend/app/Model/Education/Growth/EducationGrowthAiTalkScript.php
mineadmin-education-saas/backend/app/Model/Education/Growth/EducationGrowthConversionFunnel.php
mineadmin-education-saas/backend/app/Model/Education/Growth/EducationGrowthChannelCost.php
mineadmin-education-saas/backend/app/Model/Education/Growth/EducationGrowthChannelRoiDaily.php
mineadmin-education-saas/backend/app/Model/Education/Growth/EducationGrowthConsultantMetricDaily.php
mineadmin-education-saas/backend/app/Model/Education/Growth/EducationGrowthLossReason.php
mineadmin-education-saas/backend/app/Model/Education/Growth/EducationGrowthLeadLossRecord.php
mineadmin-education-saas/backend/app/Model/Education/Growth/EducationGrowthCampaign.php
mineadmin-education-saas/backend/app/Model/Education/Growth/EducationGrowthScriptTemplate.php
mineadmin-education-saas/backend/app/Repository/Education/Growth/LeadScoreRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Growth/FollowupStrategyRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Growth/AiTalkScriptRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Growth/ConversionFunnelRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Growth/ChannelRoiRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Growth/ConsultantMetricRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Growth/LossReasonRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Growth/GrowthCampaignRepository.php
mineadmin-education-saas/backend/app/Service/Education/Growth/LeadScoreService.php
mineadmin-education-saas/backend/app/Service/Education/Growth/FollowupStrategyService.php
mineadmin-education-saas/backend/app/Service/Education/Growth/ConsultantAiService.php
mineadmin-education-saas/backend/app/Service/Education/Growth/TrialConversionService.php
mineadmin-education-saas/backend/app/Service/Education/Growth/ChannelRoiService.php
mineadmin-education-saas/backend/app/Service/Education/Growth/ConsultantMetricService.php
mineadmin-education-saas/backend/app/Service/Education/Growth/LossReasonService.php
mineadmin-education-saas/backend/app/Service/Education/Growth/ConversionFunnelService.php
mineadmin-education-saas/backend/app/Service/Education/Growth/GrowthWorkbenchService.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Growth/LeadScoreRecalculateRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Growth/FollowupStrategySaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Growth/AiTalkScriptGenerateRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Growth/ChannelCostSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Growth/LossReasonSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Growth/LeadLossRecordSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Growth/GrowthCampaignSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Growth/GrowthDashboardRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Growth/TeacherTrialFeedbackRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Growth/GrowthWorkbenchController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Growth/LeadScoreController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Growth/FollowupStrategyController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Growth/AiTalkScriptController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Growth/TrialConversionController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Growth/ChannelRoiController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Growth/ConsultantMetricController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Growth/LossReasonController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Growth/GrowthCampaignController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Growth/TeacherGrowthController.php
mineadmin-education-saas/backend/app/Schema/Education/Growth/GrowthWorkbenchSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Growth/LeadScoreSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Growth/FollowupStrategySchema.php
mineadmin-education-saas/backend/app/Schema/Education/Growth/AiTalkScriptSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Growth/TrialConversionSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Growth/ChannelRoiSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Growth/ConsultantMetricSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Growth/LossReasonSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Growth/GrowthCampaignSchema.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Growth/GrowthMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Growth/LeadScoreServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Growth/FollowupStrategyServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Growth/ConsultantAiServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Growth/ChannelRoiServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Growth/ConsultantMetricServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Growth/GrowthAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Growth/TeacherGrowthMobileApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Growth/GrowthPermissionIsolationAuditTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/growth/workbench.ts
mineadmin-education-saas/admin-web/src/api/education/growth/lead-score.ts
mineadmin-education-saas/admin-web/src/api/education/growth/followup.ts
mineadmin-education-saas/admin-web/src/api/education/growth/ai-script.ts
mineadmin-education-saas/admin-web/src/api/education/growth/channel-roi.ts
mineadmin-education-saas/admin-web/src/api/education/growth/consultant-metric.ts
mineadmin-education-saas/admin-web/src/api/education/growth/loss.ts
mineadmin-education-saas/admin-web/src/api/education/growth/campaign.ts
mineadmin-education-saas/admin-web/src/views/education/growth/GrowthWorkbench.vue
mineadmin-education-saas/admin-web/src/views/education/growth/LeadScoreList.vue
mineadmin-education-saas/admin-web/src/views/education/growth/AiTalkScriptWorkbench.vue
mineadmin-education-saas/admin-web/src/views/education/growth/FollowupStrategyList.vue
mineadmin-education-saas/admin-web/src/views/education/growth/TrialConversionList.vue
mineadmin-education-saas/admin-web/src/views/education/growth/ChannelRoiDashboard.vue
mineadmin-education-saas/admin-web/src/views/education/growth/ConsultantMetricDashboard.vue
mineadmin-education-saas/admin-web/src/views/education/growth/LossReasonReport.vue
mineadmin-education-saas/admin-web/src/views/education/growth/components/LeadScoreFactorDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/growth/components/AiTalkScriptEditor.vue
mineadmin-education-saas/admin-web/src/views/education/growth/components/FollowupSuggestionPanel.vue
mineadmin-education-saas/admin-web/src/views/education/growth/__tests__/GrowthWorkbench.spec.ts
mineadmin-education-saas/admin-web/src/views/education/growth/__tests__/AiTalkScriptWorkbench.spec.ts
mineadmin-education-saas/admin-web/src/views/education/growth/__tests__/ChannelRoiDashboard.spec.ts
mineadmin-education-saas/admin-web/src/views/education/growth/__tests__/ConsultantMetricDashboard.spec.ts
```

Modify PC:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Create mobile:

```text
mineadmin-education-saas/mobile-uniapp/src/api/growth/teacher.ts
mineadmin-education-saas/mobile-uniapp/pages/teacher/growth/trial-lessons.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/growth/trial-feedback.vue
mineadmin-education-saas/mobile-uniapp/tests/growth/teacher-growth.spec.ts
```

Modify mobile:

```text
mineadmin-education-saas/mobile-uniapp/pages.json
```

## Database Migration Design

Migration file:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_100000_create_v10_growth_tables.php
```

Money policy:

```text
Channel cost and converted revenue use bigint integer cents.
```

Shared columns:

```text
id bigint unsigned primary key auto increment
tenant_id bigint unsigned not null
campus_id bigint unsigned null
created_by bigint unsigned null
updated_by bigint unsigned null
created_at timestamp null
updated_at timestamp null
deleted_at timestamp null for mutable config/campaign records
```

Foreign-key policy:

```text
Use service-level validation for V3 leads, lead sources, follow records, trial lessons, conversions, users, and V8 AI generation tasks. Do not create physical foreign keys.
```

Rollback behavior:

```text
Drop tables in reverse order: edu_growth_script_templates, edu_growth_campaigns, edu_growth_lead_loss_records, edu_growth_loss_reasons, edu_growth_consultant_metrics_daily, edu_growth_channel_roi_daily, edu_growth_channel_costs, edu_growth_conversion_funnels, edu_growth_ai_talk_scripts, edu_growth_followup_suggestions, edu_growth_followup_strategies, edu_growth_lead_score_factors, edu_growth_lead_scores.
```

Table catalog:

| Table | Business columns | Indexes |
| --- | --- | --- |
| `edu_growth_lead_scores` | `lead_id bigint unsigned not null`, `score_date date not null`, `score int unsigned not null default 0`, `score_level varchar(20) not null`, `stage varchar(40) not null`, `owner_user_id bigint unsigned null`, `summary varchar(500) null` | `unique uk_edu_growth_lead_scores_lead_date (tenant_id, lead_id, score_date)`, `index idx_edu_growth_lead_scores_owner_level (tenant_id, owner_user_id, score_level)` |
| `edu_growth_lead_score_factors` | `lead_score_id bigint unsigned not null`, `factor_code varchar(64) not null`, `factor_name varchar(120) not null`, `factor_value varchar(120) not null`, `points int not null default 0`, `weight decimal(8,4) not null default 1.0000` | `index idx_edu_growth_lead_score_factors_score (tenant_id, lead_score_id)`, `index idx_edu_growth_lead_score_factors_code (tenant_id, factor_code)` |
| `edu_growth_followup_strategies` | `strategy_code varchar(64) not null`, `strategy_name varchar(120) not null`, `lead_stage varchar(40) not null`, `score_level varchar(20) null`, `suggestion_template text not null`, `next_follow_hours int unsigned not null default 24`, `status varchar(20) not null default enabled` | `unique uk_edu_growth_followup_strategies_code (tenant_id, strategy_code)`, `index idx_edu_growth_followup_strategies_stage (tenant_id, lead_stage, status)` |
| `edu_growth_followup_suggestions` | `lead_id bigint unsigned not null`, `strategy_id bigint unsigned null`, `owner_user_id bigint unsigned not null`, `suggestion_text text not null`, `status varchar(20) not null default pending`, `due_at timestamp null`, `handled_at timestamp null` | `index idx_edu_growth_followup_suggestions_owner_status (tenant_id, owner_user_id, status, due_at)`, `index idx_edu_growth_followup_suggestions_lead (tenant_id, lead_id, status)` |
| `edu_growth_ai_talk_scripts` | `lead_id bigint unsigned not null`, `generation_task_id bigint unsigned null`, `script_type varchar(60) not null`, `script_text longtext not null`, `status varchar(20) not null default draft`, `confirmed_by bigint unsigned null`, `confirmed_at timestamp null`, `masked_input_json json not null` | `index idx_edu_growth_ai_talk_scripts_lead (tenant_id, lead_id, status)`, `index idx_edu_growth_ai_talk_scripts_task (tenant_id, generation_task_id)` |
| `edu_growth_conversion_funnels` | `metric_date date not null`, `source_id bigint unsigned null`, `stage varchar(40) not null`, `lead_count int unsigned not null default 0`, `next_stage_count int unsigned not null default 0`, `conversion_rate decimal(8,4) null` | `unique uk_edu_growth_conversion_funnels_scope (tenant_id, campus_id, metric_date, source_id, stage)`, `index idx_edu_growth_conversion_funnels_date (tenant_id, metric_date)` |
| `edu_growth_channel_costs` | `source_id bigint unsigned not null`, `cost_date date not null`, `cost_type varchar(40) not null`, `amount_cents bigint unsigned not null default 0`, `remark varchar(500) null` | `index idx_edu_growth_channel_costs_source_date (tenant_id, source_id, cost_date)`, `index idx_edu_growth_channel_costs_date (tenant_id, cost_date)` |
| `edu_growth_channel_roi_daily` | `metric_date date not null`, `source_id bigint unsigned not null`, `lead_count int unsigned not null default 0`, `converted_count int unsigned not null default 0`, `cost_cents bigint unsigned not null default 0`, `converted_revenue_cents bigint unsigned not null default 0`, `roi decimal(10,4) null` | `unique uk_edu_growth_channel_roi_daily_source_date (tenant_id, campus_id, source_id, metric_date)`, `index idx_edu_growth_channel_roi_daily_date (tenant_id, metric_date)` |
| `edu_growth_consultant_metrics_daily` | `metric_date date not null`, `consultant_user_id bigint unsigned not null`, `assigned_leads_count int unsigned not null default 0`, `follow_count int unsigned not null default 0`, `trial_count int unsigned not null default 0`, `converted_count int unsigned not null default 0`, `lost_count int unsigned not null default 0` | `unique uk_edu_growth_consultant_metrics_daily_user_date (tenant_id, campus_id, consultant_user_id, metric_date)`, `index idx_edu_growth_consultant_metrics_daily_date (tenant_id, metric_date)` |
| `edu_growth_loss_reasons` | `reason_code varchar(64) not null`, `reason_name varchar(120) not null`, `reason_group varchar(60) not null`, `status varchar(20) not null default enabled`, `sort_order int not null default 0` | `unique uk_edu_growth_loss_reasons_code (tenant_id, reason_code)`, `index idx_edu_growth_loss_reasons_group (tenant_id, reason_group, status)` |
| `edu_growth_lead_loss_records` | `lead_id bigint unsigned not null`, `loss_reason_id bigint unsigned not null`, `lost_by bigint unsigned not null`, `lost_at timestamp not null`, `detail varchar(500) null` | `unique uk_edu_growth_lead_loss_records_lead (tenant_id, lead_id)`, `index idx_edu_growth_lead_loss_records_reason (tenant_id, loss_reason_id, lost_at)` |
| `edu_growth_campaigns` | `campaign_code varchar(64) not null`, `campaign_name varchar(160) not null`, `source_id bigint unsigned null`, `start_date date not null`, `end_date date null`, `budget_cents bigint unsigned not null default 0`, `status varchar(20) not null default draft`, `remark varchar(500) null` | `unique uk_edu_growth_campaigns_code (tenant_id, campaign_code)`, `index idx_edu_growth_campaigns_date_status (tenant_id, start_date, end_date, status)` |
| `edu_growth_script_templates` | `template_code varchar(64) not null`, `template_name varchar(120) not null`, `script_type varchar(60) not null`, `content text not null`, `status varchar(20) not null default enabled` | `unique uk_edu_growth_script_templates_code (tenant_id, template_code)`, `index idx_edu_growth_script_templates_type (tenant_id, script_type, status)` |

## MineAdmin Backend Module Design

Enums:

```text
LeadScoreLevel: low, medium, high, hot
SuggestionStatus: pending, accepted, ignored, expired
AiTalkScriptStatus: draft, confirmed, rejected, used
CampaignStatus: draft, active, paused, finished, archived
```

Layer tasks:

| Layer | Required implementation |
| --- | --- |
| Model | table names, fillable, integer money casts, JSON casts for masked AI inputs, enum casts, soft deletes for strategy/campaign/template records |
| Repository | lead score pages, suggestion lookup, script pages, funnel/ROI metrics, consultant metrics, loss reason pages |
| Service | score calculation from V3 events, strategy matching, suggestion creation, AI script generation via V8, trial feedback enhancement, ROI aggregation, consultant metrics, loss recording |
| Request | validate lead ids, strategy rules, script type, channel cost cents, loss reason, campaign dates, dashboard date ranges |
| Controller | admin and teacher mobile endpoints with permissions, Result envelope, V3/V8 integration checks, audit logging |
| Schema | document workbench, score, strategy, AI script, funnel, ROI, consultant, loss, campaign, and teacher trial feedback payloads |

Human confirmation rules:

```text
AI talk scripts are drafts until confirmed by a consultant.
No automatic discount promise is allowed in script templates or AI output.
No automatic payment order creation is allowed.
Trial conversion still calls V3 LeadConversionService and cannot be duplicated in V10.
Guardian contact happens manually outside V10 APIs unless later product scope adds explicit messaging with consent.
```

Audit rules:

```text
Write audit actions: education.growth.score.recalculated, strategy.saved, suggestion.accepted, ai_script.generated, ai_script.confirmed, channel_cost.saved, loss_record.created, campaign.saved.
```

## API Contract

Common headers:

```text
Authorization: Bearer <token>
X-Tenant-Id: <tenant id>
X-Campus-Id: <campus id>
```

Endpoint matrix:

| API | Permission | Caller | Isolation | Audit |
| --- | --- | --- | --- | --- |
| `GET /admin/education/growth/workbench` | `education:growth:workbench:view` | consultant/admin | owner or campus | no |
| `POST /admin/education/growth/leads/{leadId}/score/recalculate` | `education:growth:score:recalculate` | consultant/admin | owner or campus | yes |
| `POST /admin/education/growth/ai-talk-scripts/generate` | `education:growth:ai-script:generate` | consultant | owner or campus | yes |
| `POST /admin/education/growth/ai-talk-scripts/{id}/confirm` | `education:growth:ai-script:confirm` | consultant | owner or campus | yes |
| `POST /admin/education/growth/followup-strategies` | `education:growth:strategy:save` | admin | tenant + campus | yes |
| `GET /admin/education/growth/channel-roi` | `education:growth:channel-roi:page` | manager | tenant + campus | no |
| `GET /admin/education/growth/consultant-metrics` | `education:growth:consultant-metric:page` | manager | tenant + campus | no |
| `POST /admin/education/growth/leads/{leadId}/loss-records` | `education:growth:loss:create` | consultant | owner or campus | yes |
| `POST /mobile/education/growth/teacher/trial-feedback` | mobile teacher | teacher | assigned trial lesson | yes |

Endpoint-level request/response/failure catalog:

```json
[
  {
    "api": "POST /admin/education/growth/leads/{leadId}/score/recalculate",
    "request": {"reason": "after trial feedback"},
    "success": {"code": 200, "message": "success", "data": {"lead_id": 101, "score": 82, "score_level": "hot"}},
    "validation_failure": {"code": 422, "message": "leadId must be a positive integer", "data": {"field": "leadId"}},
    "business_failure": {"code": 403, "message": "lead is assigned to another consultant", "data": {"lead_id": 101}}
  },
  {
    "api": "POST /admin/education/growth/ai-talk-scripts/generate",
    "request": {"lead_id": 101, "script_type": "trial_invitation", "goal": "invite trial lesson"},
    "success": {"code": 200, "message": "success", "data": {"ai_talk_script_id": 201, "status": "draft"}},
    "validation_failure": {"code": 422, "message": "script_type is required", "data": {"field": "script_type"}},
    "business_failure": {"code": 422, "message": "ai script contains automatic discount promise", "data": {"blocked_phrase": "guaranteed discount"}}
  },
  {
    "api": "POST /admin/education/growth/ai-talk-scripts/{id}/confirm",
    "request": {"edited_script": "您好，想邀请孩子参加本周试听课。"},
    "success": {"code": 200, "message": "success", "data": {"ai_talk_script_id": 201, "status": "confirmed"}},
    "validation_failure": {"code": 422, "message": "edited_script is required", "data": {"field": "edited_script"}},
    "business_failure": {"code": 409, "message": "ai talk script is not draft", "data": {"id": 201, "status": "used"}}
  },
  {
    "api": "POST /admin/education/growth/followup-strategies",
    "request": {"strategy_code": "HOT-TRIAL", "strategy_name": "Hot lead trial invite", "lead_stage": "followed", "score_level": "hot", "suggestion_template": "Invite trial within 4 hours", "next_follow_hours": 4},
    "success": {"code": 200, "message": "success", "data": {"strategy_id": 301, "status": "enabled"}},
    "validation_failure": {"code": 422, "message": "strategy_code is required", "data": {"field": "strategy_code"}},
    "business_failure": {"code": 409, "message": "strategy code already exists", "data": {"strategy_code": "HOT-TRIAL"}}
  },
  {
    "api": "GET /admin/education/growth/channel-roi",
    "request": {"start_date": "2026-06-01", "end_date": "2026-06-10", "source_id": 10},
    "success": {"code": 200, "message": "success", "data": {"list": [{"source_id": 10, "cost_cents": 100000, "converted_revenue_cents": 500000, "roi": "5.0000"}]}},
    "validation_failure": {"code": 422, "message": "start_date is required", "data": {"field": "start_date"}},
    "business_failure": {"code": 403, "message": "source is outside current campus scope", "data": {"source_id": 10}}
  },
  {
    "api": "POST /admin/education/growth/leads/{leadId}/loss-records",
    "request": {"loss_reason_id": 11, "detail": "price objection"},
    "success": {"code": 200, "message": "success", "data": {"lead_id": 101, "status": "lost"}},
    "validation_failure": {"code": 422, "message": "loss_reason_id is required", "data": {"field": "loss_reason_id"}},
    "business_failure": {"code": 409, "message": "lead has already been converted", "data": {"lead_id": 101}}
  },
  {
    "api": "POST /mobile/education/growth/teacher/trial-feedback",
    "request": {"trial_lesson_id": 401, "classroom_performance": "focused", "course_recommendation": "beginner art", "teacher_note": "good fit"},
    "success": {"code": 200, "message": "success", "data": {"trial_feedback_id": 501}},
    "validation_failure": {"code": 422, "message": "trial_lesson_id is required", "data": {"field": "trial_lesson_id"}},
    "business_failure": {"code": 403, "message": "trial lesson is not assigned to current teacher", "data": {"trial_lesson_id": 401}}
  }
]
```

## PC Admin Page Tasks

API clients:

```text
workbench.ts: getGrowthWorkbench, acceptFollowupSuggestion, ignoreFollowupSuggestion
lead-score.ts: pageLeadScores, recalculateLeadScore, getLeadScoreFactors
followup.ts: pageFollowupStrategies, saveFollowupStrategy, pageFollowupSuggestions
ai-script.ts: generateAiTalkScript, pageAiTalkScripts, confirmAiTalkScript, rejectAiTalkScript
channel-roi.ts: saveChannelCost, getChannelRoi, getConversionFunnel
consultant-metric.ts: getConsultantMetrics
loss.ts: pageLossReasons, saveLossReason, createLeadLossRecord, getLossReasonReport
campaign.ts: pageGrowthCampaigns, saveGrowthCampaign
```

Routes and pages:

| Route | Route name | Page | Permission | Key UI work |
| --- | --- | --- | --- | --- |
| `/education/growth/workbench` | `EducationGrowthWorkbench` | `GrowthWorkbench.vue` | `education:growth:workbench:view` | hot leads, suggestions, script quick action |
| `/education/growth/lead-scores` | `EducationGrowthLeadScoreList` | `LeadScoreList.vue` | `education:growth:score:page` | score list, factors drawer |
| `/education/growth/ai-scripts` | `EducationGrowthAiTalkScriptWorkbench` | `AiTalkScriptWorkbench.vue` | `education:growth:ai-script:page` | generate/edit/confirm scripts |
| `/education/growth/followup-strategies` | `EducationGrowthFollowupStrategyList` | `FollowupStrategyList.vue` | `education:growth:strategy:page` | strategy CRUD |
| `/education/growth/trial-conversions` | `EducationGrowthTrialConversionList` | `TrialConversionList.vue` | `education:growth:trial-conversion:page` | trial readiness and link to V3 conversion |
| `/education/growth/channel-roi` | `EducationGrowthChannelRoiDashboard` | `ChannelRoiDashboard.vue` | `education:growth:channel-roi:page` | cost, revenue, ROI charts |
| `/education/growth/consultant-metrics` | `EducationGrowthConsultantMetricDashboard` | `ConsultantMetricDashboard.vue` | `education:growth:consultant-metric:page` | consultant workload/conversion |
| `/education/growth/loss-reasons` | `EducationGrowthLossReasonReport` | `LossReasonReport.vue` | `education:growth:loss:page` | loss reason report |

Required states:

```text
All V10 PC pages implement loading, empty, 403 permission, 422 validation, 409 lead state conflict, AI safety message, human-confirmation prompt, success refresh, and campus/owner filters.
```

## Teacher / Guardian Mobile Page Tasks

Teacher:

```text
API: mobile-uniapp/src/api/growth/teacher.ts
Pages: trial-lessons.vue, trial-feedback.vue
State: assigned trial list, lead/student summary, preparation tips, feedback form, submitted state, loading/empty/error states
Isolation: teacher can read and submit feedback only for assigned V3 trial lessons.
```

Guardian:

```text
This module has no direct guardian page. Consultants manually contact guardians after confirming suggestions/scripts. V10 must not send automatic guardian messages.
```

`pages.json`:

```text
Register teacher growth trial pages under teacher role.
```

## Test Plan

Backend tests:

| Test file | Case | Assert |
| --- | --- | --- |
| `GrowthMigrationTest.php` | `test_growth_tables_indexes_and_money_columns_exist` | all V10 tables and indexes exist; cost/revenue are integer cents |
| `LeadScoreServiceTest.php` | `test_score_updates_after_follow_and_trial_events` | lead score factors and score level update |
| `FollowupStrategyServiceTest.php` | `test_stage_strategy_creates_due_suggestion` | suggestion due_at follows strategy next_follow_hours |
| `ConsultantAiServiceTest.php` | `test_ai_script_input_masks_guardian_private_fields` | masked_input_json excludes full mobile |
| `ConsultantAiServiceTest.php` | `test_ai_script_blocks_automatic_discount_promise` | returns documented 422 |
| `ChannelRoiServiceTest.php` | `test_roi_aggregates_cost_and_converted_revenue` | ROI row values match costs and V3 converted revenue |
| `GrowthAdminApiTest.php` | `test_api_failures_match_catalog` | documented 422/403/409 payloads |
| `TeacherGrowthMobileApiTest.php` | `test_teacher_can_submit_assigned_trial_feedback_only` | assigned returns 200; unassigned returns 403 |
| `GrowthPermissionIsolationAuditTest.php` | `test_growth_mutations_require_permission_and_write_audit` | denied without permission; audit exists |

PC tests:

```text
GrowthWorkbench.spec.ts asserts suggestions accept/ignore actions and owner filters.
AiTalkScriptWorkbench.spec.ts asserts confirmed script requires edited text and blocks automatic discount wording.
ChannelRoiDashboard.spec.ts asserts cost/revenue/ROI filters are passed.
ConsultantMetricDashboard.spec.ts asserts consultant/date/campus filters are sent to APIs.
```

Mobile tests:

```text
teacher-growth.spec.ts asserts teacher trial list uses assigned trial lessons only and feedback submit handles unassigned 403.
```

## Execution Commands

Backend:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter Education\\\\Growth
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
V10 migrations run successfully.
All Education\\Growth tests pass.
Code style and static analysis pass.
```

PC:

```bash
cd mineadmin-education-saas/admin-web
pnpm install
pnpm lint
pnpm test -- growth
pnpm build
```

Expected:

```text
Growth PC lint, tests, and build pass.
```

Mobile:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm install
pnpm lint
pnpm test -- growth
pnpm build:h5
```

Expected:

```text
Teacher growth mobile lint, tests, and H5 build pass.
```

## Acceptance Gate

V10 is accepted only when:

```text
- Lead score and factor rows update after V3 follow-up and trial events.
- Growth workbench shows only leads in current owner/campus scope.
- Follow-up strategies create due suggestions without contacting guardians automatically.
- AI scripts mask private fields, remain drafts, and require consultant confirmation.
- V10 never creates payment orders or performs lead conversion directly.
- Trial conversion links to V3 conversion flow.
- Channel ROI and consultant metrics aggregate from V3/V4 sources using integer cents.
- Teacher mobile trial feedback remains assigned-teacher-only.
- All growth write operations are permission-checked and audited.
```

## Task Breakdown

### Task 1: Migration, Enums, and Models

- [x] Create migration with table catalog, integer cost/revenue fields, indexes, and reverse rollback.
- [x] Create enums and models listed in `File Structure`.
- [x] Write `GrowthMigrationTest.php`.
- [x] Run `php bin/hyperf.php migrate`; expected output is successful creation of all V10 growth tables.

### Task 2: Repositories and Services

- [x] Create repositories for scores, strategies, scripts, funnel, ROI, consultant metrics, loss reasons, and campaigns.
- [x] Create services for score calculation, suggestions, AI scripts, trial feedback, ROI, metrics, loss, funnel, and workbench.
- [x] Write unit tests listed in `Test Plan`.
- [x] Run `composer test -- --filter Education\\\\Growth.*ServiceTest`; expected output is all growth service tests passing.

### Task 3: Requests, Schemas, Controllers, and API Tests

- [x] Create request classes with lead, strategy, script, cost, loss, campaign, dashboard, and teacher trial feedback validation.
- [x] Create schemas matching the API catalog.
- [x] Create admin and teacher mobile controllers with permissions, Result envelope, V3/V8 integration, and audit logging.
- [x] Write API, permission, isolation, AI safety, and audit tests.
- [x] Run `composer test -- --filter Education\\\\Growth`; expected output is all V10 backend tests passing.

### Task 4: PC Admin

- [x] Create typed API clients.
- [x] Register growth routes and menus in `education.ts`.
- [x] Create workbench, score, AI script, strategy, trial conversion, ROI, consultant metric, and loss pages.
- [x] Write PC tests listed in `Test Plan`.
- [x] Run `pnpm lint && pnpm test -- growth && pnpm build`; expected output is all PC gates passing.

### Task 5: Teacher Mobile

- [x] Create teacher growth API client.
- [x] Register teacher growth pages in `pages.json`.
- [x] Implement trial lessons and trial feedback pages with assigned-teacher isolation.
- [x] Write `teacher-growth.spec.ts`.
- [x] Run `pnpm lint && pnpm test -- growth && pnpm build:h5`; expected output is all mobile gates passing.

### Task 6: V10 Final Gate

- [x] Run all backend, PC, and mobile commands in `Execution Commands`.
- [x] Confirm acceptance gate behavior with seeded leads, follow records, trial lessons, AI config, channel costs, and consultant data.
- [x] Update status index only after all gates pass during implementation.

## Self-Review

- Spec coverage: Covers workbench, lead scores, factors, strategies, suggestions, AI scripts, funnel, channel cost, ROI, consultant metrics, loss reasons, campaigns, PC, and teacher mobile.
- MineAdmin fit: Uses MineAdmin 3.x backend paths, PC route/API/page conventions, and uni-app teacher pages; guardian exclusion is explicit.
- Code-level readiness: Migration fields, indexes, rollback, API failures, backend layer tasks, PC/mobile states, tests, commands, and acceptance gates are specified.
- Implementation status: V10 code has been implemented and accepted through the final backend, PC, and mobile gates.
