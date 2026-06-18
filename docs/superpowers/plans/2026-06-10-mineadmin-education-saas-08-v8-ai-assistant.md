# MineAdmin Education SaaS V8 AI Assistant Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V8 AI 教务助手与智能分析 for AI model configs, feature settings, prompt templates, generation tasks/results, usage logs, review logs, renewal risk scores, data Q&A logs, recommendations, safety events, metric catalog, and internal knowledge documents.

**Architecture:** V8 runs AI work asynchronously through auditable tasks. Services build permission-trimmed context, call provider adapters, store drafts for human review, and prevent AI from directly publishing guardian-visible content or mutating finance/payroll/enrollment/consumption records.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, Redis queue, AI provider adapter boundary, MineAdmin-Vue, Vue3, TypeScript, uni-app, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. V8 AI assistant gates have passed.

---

## Scope Check

Included:

- AI model configs, encrypted API key storage fields, and feature settings.
- Prompt templates and prompt versions for comment drafts, report drafts, renewal risk, data Q&A, and recommendations.
- Generation tasks/results, usage logs, review logs, safety events, and cost tracking.
- Renewal risk scores and score factors.
- Data question logs backed by metric catalog only, not raw SQL.
- Recommendation tasks for internal users.
- Internal knowledge documents for scoped Q&A.
- PC admin config, prompt, task, review, risk, data Q&A, recommendation, usage, and safety pages.
- Teacher mobile lesson comment draft request, draft review, and edit-before-save pages.

Excluded:

- Autonomous guardian messaging or direct publish to family/content modules.
- Direct creation or mutation of orders, refunds, payroll, enrollments, course accounts, attendance, consumption, or contracts.
- Raw SQL generation or unrestricted database querying.

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_080000_create_v8_ai_tables.php
mineadmin-education-saas/backend/app/Contract/Education/Ai/AiProviderInterface.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Ai/AiTaskStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Ai/AiReviewStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Ai/AiSafetyLevel.php
mineadmin-education-saas/backend/app/Model/Education/Ai/EducationAiModelConfig.php
mineadmin-education-saas/backend/app/Model/Education/Ai/EducationAiFeatureSetting.php
mineadmin-education-saas/backend/app/Model/Education/Ai/EducationAiPromptTemplate.php
mineadmin-education-saas/backend/app/Model/Education/Ai/EducationAiGenerationTask.php
mineadmin-education-saas/backend/app/Model/Education/Ai/EducationAiGenerationResult.php
mineadmin-education-saas/backend/app/Model/Education/Ai/EducationAiUsageLog.php
mineadmin-education-saas/backend/app/Model/Education/Ai/EducationAiReviewLog.php
mineadmin-education-saas/backend/app/Model/Education/Ai/EducationAiRiskScore.php
mineadmin-education-saas/backend/app/Model/Education/Ai/EducationAiRiskFactor.php
mineadmin-education-saas/backend/app/Model/Education/Ai/EducationAiDataQuestionLog.php
mineadmin-education-saas/backend/app/Model/Education/Ai/EducationAiRecommendationTask.php
mineadmin-education-saas/backend/app/Model/Education/Ai/EducationAiSafetyEvent.php
mineadmin-education-saas/backend/app/Model/Education/Ai/EducationAiMetricCatalog.php
mineadmin-education-saas/backend/app/Model/Education/Ai/EducationAiKnowledgeDocument.php
mineadmin-education-saas/backend/app/Repository/Education/Ai/AiConfigRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Ai/PromptTemplateRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Ai/AiGenerationRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Ai/AiUsageRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Ai/AiReviewRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Ai/RiskPredictionRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Ai/DataQuestionRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Ai/AiRecommendationRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Ai/AiSafetyRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Ai/AiMetricCatalogRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Ai/AiKnowledgeRepository.php
mineadmin-education-saas/backend/app/Service/Education/Ai/AiConfigService.php
mineadmin-education-saas/backend/app/Service/Education/Ai/PromptTemplateService.php
mineadmin-education-saas/backend/app/Service/Education/Ai/AiContextBuilderService.php
mineadmin-education-saas/backend/app/Service/Education/Ai/AiGenerationService.php
mineadmin-education-saas/backend/app/Service/Education/Ai/AiReviewService.php
mineadmin-education-saas/backend/app/Service/Education/Ai/RiskPredictionService.php
mineadmin-education-saas/backend/app/Service/Education/Ai/DataQuestionService.php
mineadmin-education-saas/backend/app/Service/Education/Ai/AiRecommendationService.php
mineadmin-education-saas/backend/app/Service/Education/Ai/AiUsageService.php
mineadmin-education-saas/backend/app/Service/Education/Ai/AiSafetyService.php
mineadmin-education-saas/backend/app/Service/Education/Ai/AiKnowledgeService.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Ai/AiModelConfigSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Ai/AiFeatureSettingSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Ai/PromptTemplateSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Ai/AiGenerationTaskCreateRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Ai/AiReviewRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Ai/DataQuestionRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Ai/AiMetricCatalogSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Ai/AiKnowledgeDocumentSaveRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Ai/TeacherCommentDraftRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Ai/AiModelConfigController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Ai/PromptTemplateController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Ai/AiGenerationController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Ai/AiReviewController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Ai/RiskPredictionController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Ai/DataQuestionController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Ai/AiRecommendationController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Ai/AiUsageController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Ai/AiSafetyController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Ai/AiKnowledgeController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Ai/TeacherAiController.php
mineadmin-education-saas/backend/app/Schema/Education/Ai/AiConfigSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Ai/PromptTemplateSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Ai/AiGenerationSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Ai/AiReviewSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Ai/RiskPredictionSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Ai/DataQuestionSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Ai/AiRecommendationSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Ai/AiUsageSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Ai/AiSafetySchema.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Ai/AiMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Ai/AiContextBuilderServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Ai/AiGenerationServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Ai/AiReviewServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Ai/RiskPredictionServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Ai/DataQuestionServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Ai/AiSafetyServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Ai/AiAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Ai/TeacherAiMobileApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Ai/AiPermissionIsolationAuditTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/ai/config.ts
mineadmin-education-saas/admin-web/src/api/education/ai/prompt.ts
mineadmin-education-saas/admin-web/src/api/education/ai/generation.ts
mineadmin-education-saas/admin-web/src/api/education/ai/risk.ts
mineadmin-education-saas/admin-web/src/api/education/ai/data-question.ts
mineadmin-education-saas/admin-web/src/api/education/ai/recommendation.ts
mineadmin-education-saas/admin-web/src/api/education/ai/usage.ts
mineadmin-education-saas/admin-web/src/api/education/ai/safety.ts
mineadmin-education-saas/admin-web/src/views/education/ai/AiModelConfigList.vue
mineadmin-education-saas/admin-web/src/views/education/ai/PromptTemplateList.vue
mineadmin-education-saas/admin-web/src/views/education/ai/GenerationTaskList.vue
mineadmin-education-saas/admin-web/src/views/education/ai/AiReviewList.vue
mineadmin-education-saas/admin-web/src/views/education/ai/RiskScoreList.vue
mineadmin-education-saas/admin-web/src/views/education/ai/DataQuestionWorkbench.vue
mineadmin-education-saas/admin-web/src/views/education/ai/AiRecommendationList.vue
mineadmin-education-saas/admin-web/src/views/education/ai/UsageDashboard.vue
mineadmin-education-saas/admin-web/src/views/education/ai/SafetyEventList.vue
mineadmin-education-saas/admin-web/src/views/education/ai/__tests__/GenerationTaskList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/ai/__tests__/AiReviewList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/ai/__tests__/DataQuestionWorkbench.spec.ts
mineadmin-education-saas/admin-web/src/views/education/ai/__tests__/UsageDashboard.spec.ts
```

Modify PC:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Create mobile:

```text
mineadmin-education-saas/mobile-uniapp/src/api/ai/teacher.ts
mineadmin-education-saas/mobile-uniapp/pages/teacher/ai/comment-drafts.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/ai/comment-draft-detail.vue
mineadmin-education-saas/mobile-uniapp/tests/ai/teacher-ai.spec.ts
```

Modify mobile:

```text
mineadmin-education-saas/mobile-uniapp/pages.json
```

## Database Migration Design

Migration file:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_080000_create_v8_ai_tables.php
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
deleted_at timestamp null for mutable config/template/document records
```

Foreign-key policy:

```text
Use service-level validation for users, students, lessons, reports, prompts, tasks, and knowledge documents. Do not create physical foreign keys.
```

Rollback behavior:

```text
Drop tables in reverse order: edu_ai_knowledge_documents, edu_ai_metric_catalogs, edu_ai_safety_events, edu_ai_recommendation_tasks, edu_ai_data_question_logs, edu_ai_risk_factors, edu_ai_risk_scores, edu_ai_review_logs, edu_ai_usage_logs, edu_ai_generation_results, edu_ai_generation_tasks, edu_ai_prompt_templates, edu_ai_feature_settings, edu_ai_model_configs.
```

Table catalog:

| Table | Business columns | Indexes |
| --- | --- | --- |
| `edu_ai_model_configs` | `config_code varchar(64) not null`, `provider varchar(60) not null`, `model_name varchar(120) not null`, `api_key_ciphertext text null`, `base_url varchar(255) null`, `status varchar(20) not null default enabled`, `default_temperature decimal(4,2) null`, `daily_token_limit int unsigned null` | `unique uk_edu_ai_model_configs_code (tenant_id, config_code)`, `index idx_edu_ai_model_configs_provider (tenant_id, provider, status)` |
| `edu_ai_feature_settings` | `feature_code varchar(64) not null`, `feature_name varchar(120) not null`, `model_config_id bigint unsigned not null`, `enabled tinyint(1) not null default 0`, `review_required tinyint(1) not null default 1`, `safety_level varchar(20) not null default normal`, `config_json json null` | `unique uk_edu_ai_feature_settings_code (tenant_id, feature_code)`, `index idx_edu_ai_feature_settings_enabled (tenant_id, enabled)` |
| `edu_ai_prompt_templates` | `template_code varchar(64) not null`, `feature_code varchar(64) not null`, `template_name varchar(120) not null`, `version int unsigned not null default 1`, `system_prompt text not null`, `user_prompt text not null`, `status varchar(20) not null default draft`, `published_at timestamp null` | `unique uk_edu_ai_prompt_templates_code_version (tenant_id, template_code, version)`, `index idx_edu_ai_prompt_templates_feature (tenant_id, feature_code, status)` |
| `edu_ai_generation_tasks` | `task_no varchar(64) not null`, `feature_code varchar(64) not null`, `model_config_id bigint unsigned not null`, `prompt_template_id bigint unsigned not null`, `business_type varchar(60) not null`, `business_id bigint unsigned null`, `requester_user_id bigint unsigned not null`, `status varchar(20) not null default pending`, `context_hash varchar(64) not null`, `queued_at timestamp null`, `started_at timestamp null`, `finished_at timestamp null`, `error_message varchar(500) null` | `unique uk_edu_ai_generation_tasks_no (tenant_id, task_no)`, `index idx_edu_ai_tasks_feature_status (tenant_id, feature_code, status)`, `index idx_edu_ai_tasks_requester (tenant_id, requester_user_id, created_at)` |
| `edu_ai_generation_results` | `generation_task_id bigint unsigned not null`, `result_text longtext not null`, `result_json json null`, `safety_status varchar(20) not null default unchecked`, `review_status varchar(20) not null default pending`, `visible_to_guardian tinyint(1) not null default 0` | `unique uk_edu_ai_generation_results_task (tenant_id, generation_task_id)`, `index idx_edu_ai_generation_results_review (tenant_id, review_status)` |
| `edu_ai_usage_logs` | `generation_task_id bigint unsigned null`, `provider varchar(60) not null`, `model_name varchar(120) not null`, `prompt_tokens int unsigned not null default 0`, `completion_tokens int unsigned not null default 0`, `total_tokens int unsigned not null default 0`, `cost_cents bigint unsigned not null default 0`, `usage_date date not null` | `index idx_edu_ai_usage_tenant_date (tenant_id, usage_date)`, `index idx_edu_ai_usage_task (tenant_id, generation_task_id)` |
| `edu_ai_review_logs` | `generation_result_id bigint unsigned not null`, `reviewer_user_id bigint unsigned not null`, `review_action varchar(20) not null`, `review_note varchar(500) null`, `reviewed_at timestamp not null` | `index idx_edu_ai_review_logs_result (tenant_id, generation_result_id)`, `index idx_edu_ai_review_logs_reviewer (tenant_id, reviewer_user_id, reviewed_at)` |
| `edu_ai_risk_scores` | `student_id bigint unsigned not null`, `course_account_id bigint unsigned null`, `score_date date not null`, `risk_score int unsigned not null`, `risk_level varchar(20) not null`, `summary varchar(500) null`, `generation_task_id bigint unsigned null` | `unique uk_edu_ai_risk_scores_student_date (tenant_id, student_id, score_date)`, `index idx_edu_ai_risk_scores_level (tenant_id, risk_level, score_date)` |
| `edu_ai_risk_factors` | `risk_score_id bigint unsigned not null`, `factor_code varchar(64) not null`, `factor_name varchar(120) not null`, `factor_value varchar(120) not null`, `weight decimal(8,4) not null default 0.0000` | `index idx_edu_ai_risk_factors_score (tenant_id, risk_score_id)`, `index idx_edu_ai_risk_factors_code (tenant_id, factor_code)` |
| `edu_ai_data_question_logs` | `question_text text not null`, `metric_codes_json json not null`, `answer_text longtext null`, `requester_user_id bigint unsigned not null`, `status varchar(20) not null default pending`, `error_message varchar(500) null` | `index idx_edu_ai_data_question_logs_requester (tenant_id, requester_user_id, created_at)`, `index idx_edu_ai_data_question_logs_status (tenant_id, status)` |
| `edu_ai_recommendation_tasks` | `recommendation_type varchar(60) not null`, `target_type varchar(60) not null`, `target_id bigint unsigned null`, `assignee_user_id bigint unsigned null`, `status varchar(20) not null default pending`, `recommendation_json json not null`, `handled_at timestamp null` | `index idx_edu_ai_recommendation_tasks_assignee_status (tenant_id, assignee_user_id, status)`, `index idx_edu_ai_recommendation_tasks_target (tenant_id, target_type, target_id)` |
| `edu_ai_safety_events` | `generation_task_id bigint unsigned null`, `risk_level varchar(20) not null`, `event_type varchar(60) not null`, `summary varchar(500) not null`, `payload_json json not null`, `handled tinyint(1) not null default 0` | `index idx_edu_ai_safety_events_level (tenant_id, risk_level, created_at)`, `index idx_edu_ai_safety_events_task (tenant_id, generation_task_id)` |
| `edu_ai_metric_catalogs` | `metric_code varchar(64) not null`, `metric_name varchar(120) not null`, `metric_group varchar(60) not null`, `query_key varchar(120) not null`, `allowed_roles_json json not null`, `status varchar(20) not null default enabled` | `unique uk_edu_ai_metric_catalogs_code (tenant_id, metric_code)`, `index idx_edu_ai_metric_catalogs_group (tenant_id, metric_group, status)` |
| `edu_ai_knowledge_documents` | `document_code varchar(64) not null`, `title varchar(160) not null`, `content longtext not null`, `scope_type varchar(40) not null`, `scope_value_json json null`, `status varchar(20) not null default draft`, `published_at timestamp null` | `unique uk_edu_ai_knowledge_documents_code (tenant_id, document_code)`, `index idx_edu_ai_knowledge_documents_scope (tenant_id, scope_type, status)` |

`edu_ai_recommendation_tasks` is a domain source record holding AI recommendation context only. Operational lifecycle (SLA timing, escalation, overdue marking, alert conversion) is owned by V9 `edu_workflow_tasks`, which links to this record by `source_type = ai_recommendation` and `source_id`. This module does not run SLA or escalation; it exposes recommendation task status that V9 reads and updates through this module's own service.

## MineAdmin Backend Module Design

Enums:

```text
AiTaskStatus: pending, queued, running, succeeded, failed, blocked
AiReviewStatus: pending, approved, rejected, edited
AiSafetyLevel: normal, warning, high, blocked
```

Layer tasks:

| Layer | Required implementation |
| --- | --- |
| Contract | `AiProviderInterface` defines `generate(array $messages, array $options): AiProviderResult` without exposing provider-specific code to business services |
| Model | table names, fillable, JSON casts, encrypted API key field handling, enum casts, no API key in serialized output |
| Repository | config/template lookup, task queue lookup, review pages, risk scores, metric catalog filtering, safety event pages |
| Service | context trimming, task creation, queue dispatch, provider call, safety check, usage logging, review, risk scoring, data Q&A from metric catalog |
| Request | validate feature code, prompt template, review action, metric codes, knowledge scope, teacher draft input |
| Controller | admin and teacher mobile endpoints with permissions, Result envelope, audit logging, and no direct business mutation |
| Schema | document AI config, prompt, generation, review, risk, data Q&A, usage, safety, and teacher draft payloads |

Safety rules:

```text
AI drafts never publish directly to guardians.
AI cannot create orders, refunds, payroll records, enrollments, attendance, consumption, contracts, or course accounts.
Data Q&A uses `edu_ai_metric_catalogs.query_key` and approved metric services only, never raw SQL.
Usage logs store token and cost metadata but never API keys or full secrets.
Safety blocked output writes `edu_ai_safety_events` and marks generation task blocked.
```

Audit rules:

```text
Write audit actions: education.ai.config.saved, prompt.published, generation.requested, generation.completed, review.approved, review.rejected, data_question.asked, recommendation.handled, safety.blocked.
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
| `POST /admin/education/ai/model-configs` | `education:ai:model-config:save` | admin | tenant | yes |
| `POST /admin/education/ai/prompt-templates` | `education:ai:prompt:save` | admin | tenant | yes |
| `POST /admin/education/ai/generation-tasks` | `education:ai:generation:create` | admin | tenant + campus + feature scope | yes |
| `POST /admin/education/ai/generation-results/{id}/approve` | `education:ai:review:approve` | reviewer | tenant + campus | yes |
| `GET /admin/education/ai/risk-scores/page` | `education:ai:risk-score:page` | admin | tenant + campus | no |
| `POST /admin/education/ai/data-questions` | `education:ai:data-question:create` | admin | metric role scope | yes |
| `GET /admin/education/ai/usage/summary` | `education:ai:usage:summary` | admin | tenant | no |
| `GET /admin/education/ai/safety-events/page` | `education:ai:safety:page` | admin | tenant | no |
| `POST /mobile/education/ai/teacher/lesson-comment-drafts` | mobile teacher | teacher | assigned lesson/student | yes |

Endpoint-level request/response/failure catalog:

```json
[
  {
    "api": "POST /admin/education/ai/model-configs",
    "request": {"config_code": "default-gpt", "provider": "openai", "model_name": "gpt-4.1-mini", "api_key": "encrypted-at-save", "status": "enabled"},
    "success": {"code": 200, "message": "success", "data": {"id": 101, "config_code": "default-gpt", "api_key_visible": false}},
    "validation_failure": {"code": 422, "message": "model_name is required", "data": {"field": "model_name"}},
    "business_failure": {"code": 409, "message": "model config code already exists", "data": {"config_code": "default-gpt"}}
  },
  {
    "api": "POST /admin/education/ai/generation-tasks",
    "request": {"feature_code": "lesson_comment", "business_type": "lesson_student", "business_id": 88011201, "prompt_variables": {"tone": "warm"}},
    "success": {"code": 200, "message": "success", "data": {"task_id": 201, "status": "queued"}},
    "validation_failure": {"code": 422, "message": "feature_code is required", "data": {"field": "feature_code"}},
    "business_failure": {"code": 403, "message": "business context is outside current permission scope", "data": {"business_id": 88011201}}
  },
  {
    "api": "POST /admin/education/ai/generation-results/{id}/approve",
    "request": {"review_note": "edited and approved", "edited_text": "final internal draft"},
    "success": {"code": 200, "message": "success", "data": {"generation_result_id": 301, "review_status": "approved"}},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 409, "message": "ai result was blocked by safety policy", "data": {"generation_result_id": 301}}
  },
  {
    "api": "POST /admin/education/ai/data-questions",
    "request": {"question_text": "本月各校区续费预警数量", "metric_codes": ["renewal_alert_count"], "date_range": ["2026-06-01", "2026-06-10"]},
    "success": {"code": 200, "message": "success", "data": {"question_log_id": 401, "status": "succeeded", "answer_text": "校区A 12个，校区B 8个"}},
    "validation_failure": {"code": 422, "message": "metric_codes is required", "data": {"field": "metric_codes"}},
    "business_failure": {"code": 403, "message": "metric is not allowed for current role", "data": {"metric_code": "revenue_cents"}}
  },
  {
    "api": "POST /mobile/education/ai/teacher/lesson-comment-drafts",
    "request": {"lesson_id": 8801, "student_id": 1201, "keywords": ["active", "needs practice"]},
    "success": {"code": 200, "message": "success", "data": {"task_id": 501, "status": "queued"}},
    "validation_failure": {"code": 422, "message": "lesson_id is required", "data": {"field": "lesson_id"}},
    "business_failure": {"code": 403, "message": "teacher is not assigned to this lesson student", "data": {"lesson_id": 8801, "student_id": 1201}}
  },
  {
    "api": "GET /admin/education/ai/usage/summary",
    "request": {"start_date": "2026-06-01", "end_date": "2026-06-10", "feature_code": "lesson_comment"},
    "success": {"code": 200, "message": "success", "data": {"total_tokens": 12000, "cost_cents": 300}},
    "validation_failure": {"code": 422, "message": "start_date is required", "data": {"field": "start_date"}},
    "business_failure": {"code": 403, "message": "ai usage access denied", "data": {}}
  },
  {
    "api": "GET /admin/education/ai/safety-events/page",
    "request": {"page": 1, "pageSize": 20, "risk_level": "high"},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 701, "risk_level": "high", "event_type": "unsafe_output"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "risk_level has an invalid value", "data": {"field": "risk_level"}},
    "business_failure": {"code": 403, "message": "safety event access denied", "data": {}}
  }
]
```

## PC Admin Page Tasks

API clients:

```text
config.ts: pageModelConfigs, saveModelConfig, saveFeatureSetting
prompt.ts: pagePromptTemplates, savePromptTemplate, publishPromptTemplate
generation.ts: createGenerationTask, pageGenerationTasks, getGenerationResult
risk.ts: pageRiskScores, getRiskScoreDetail
data-question.ts: askDataQuestion, pageDataQuestionLogs, pageMetricCatalogs
recommendation.ts: pageRecommendationTasks, markRecommendationHandled
usage.ts: getUsageSummary, pageUsageLogs
safety.ts: pageSafetyEvents, markSafetyHandled
```

Routes and pages:

| Route | Route name | Page | Permission | Key UI work |
| --- | --- | --- | --- | --- |
| `/education/ai/model-configs` | `EducationAiModelConfigList` | `AiModelConfigList.vue` | `education:ai:model-config:page` | config CRUD, API key masked |
| `/education/ai/prompts` | `EducationAiPromptTemplateList` | `PromptTemplateList.vue` | `education:ai:prompt:page` | versioned prompts, publish |
| `/education/ai/generation-tasks` | `EducationAiGenerationTaskList` | `GenerationTaskList.vue` | `education:ai:generation:page` | task status, result drawer |
| `/education/ai/reviews` | `EducationAiReviewList` | `AiReviewList.vue` | `education:ai:review:page` | approve/reject/edit AI drafts |
| `/education/ai/risk-scores` | `EducationAiRiskScoreList` | `RiskScoreList.vue` | `education:ai:risk-score:page` | risk level filters and factors |
| `/education/ai/data-questions` | `EducationAiDataQuestionWorkbench` | `DataQuestionWorkbench.vue` | `education:ai:data-question:create` | metric-only Q&A |
| `/education/ai/recommendations` | `EducationAiRecommendationList` | `AiRecommendationList.vue` | `education:ai:recommendation:page` | internal recommendation tasks |
| `/education/ai/usage` | `EducationAiUsageDashboard` | `UsageDashboard.vue` | `education:ai:usage:summary` | token/cost dashboard |
| `/education/ai/safety-events` | `EducationAiSafetyEventList` | `SafetyEventList.vue` | `education:ai:safety:page` | safety event list and handled marker |

Required states:

```text
All AI PC pages implement loading, empty, 403 permission, 422 validation, 409 safety/config state conflict, masked secret display, success refresh, and queue polling for task status.
```

## Teacher / Guardian Mobile Page Tasks

Teacher:

```text
API: mobile-uniapp/src/api/ai/teacher.ts
Pages: comment-drafts.vue, comment-draft-detail.vue
State: assigned lesson/student selector, keyword input, queued/running/succeeded/blocked statuses, draft text editor, save to V7 comment draft, loading/empty/error states
Isolation: teacher can request drafts only for assigned lesson students.
```

Guardian:

```text
This module has no direct guardian AI page. Guardian-visible content must be reviewed and published through V7 family service or V12 content workflows.
```

`pages.json`:

```text
Register teacher AI pages under teacher role and require teacher profile context.
```

## Test Plan

Backend tests:

| Test file | Case | Assert |
| --- | --- | --- |
| `AiMigrationTest.php` | `test_ai_tables_indexes_and_secret_fields_exist` | all V8 tables exist; API key field is ciphertext only |
| `AiContextBuilderServiceTest.php` | `test_teacher_context_excludes_unassigned_students` | context contains assigned student only |
| `AiGenerationServiceTest.php` | `test_generation_task_is_queued_and_usage_logged` | task, result, usage log exist |
| `AiReviewServiceTest.php` | `test_unreviewed_result_cannot_be_guardian_visible` | visible_to_guardian remains false before review |
| `RiskPredictionServiceTest.php` | `test_risk_score_and_factors_are_persisted` | score and factor rows exist |
| `DataQuestionServiceTest.php` | `test_data_question_uses_metric_catalog_not_raw_sql` | raw SQL input is rejected; metric service is called |
| `AiSafetyServiceTest.php` | `test_unsafe_output_is_blocked_and_logged` | task blocked and safety event exists |
| `TeacherAiMobileApiTest.php` | `test_teacher_can_request_draft_only_for_assigned_lesson_student` | assigned returns queued; unassigned returns 403 |
| `AiPermissionIsolationAuditTest.php` | `test_ai_mutations_require_permission_and_write_audit` | denied without permission; audit exists after allowed write |

PC tests:

```text
GenerationTaskList.spec.ts asserts queued/running/succeeded/blocked status polling.
AiReviewList.spec.ts asserts approve button disabled for safety blocked result.
DataQuestionWorkbench.spec.ts asserts metric codes are selected from catalog and raw SQL text is rejected.
UsageDashboard.spec.ts asserts cost and token filters are passed to usage APIs.
```

Mobile tests:

```text
teacher-ai.spec.ts asserts teacher draft request requires assigned lesson student, blocked result displays safety message, and edited draft saves into V7 comment draft flow.
```

## Execution Commands

Backend:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter Education\\\\Ai
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
V8 migrations run successfully.
All Education\\Ai tests pass.
Code style and static analysis pass.
```

PC:

```bash
cd mineadmin-education-saas/admin-web
pnpm install
pnpm lint
pnpm test -- ai
pnpm build
```

Expected:

```text
AI PC lint, tests, and build pass.
```

Mobile:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm install
pnpm lint
pnpm test -- ai
pnpm build:h5
```

Expected:

```text
Teacher AI mobile lint, tests, and H5 build pass.
```

## Acceptance Gate

V8 is accepted only when:

```text
- AI configs and prompt templates are manageable and API keys are never exposed in responses/logs.
- Generation tasks are asynchronous, auditable, and usage/cost logged.
- AI context is permission-trimmed by tenant, campus, role, teacher, and guardian boundaries.
- AI outputs require human review before use in guardian-visible workflows.
- Safety blocked outputs are stored as safety events and cannot be approved.
- Data Q&A uses metric catalog services only, not raw SQL.
- Teacher mobile can request comment drafts only for assigned lesson students.
- AI services cannot mutate orders, refunds, payroll, enrollments, course accounts, attendance, consumption, or contracts.
```

## Task Breakdown

### Task 1: Migration, Contract, Enums, and Models

- [x] Create migration with table catalog, indexes, encrypted API key field, JSON fields, and reverse rollback.
- [x] Create `AiProviderInterface`, enums, and models listed in `File Structure`.
- [x] Write `AiMigrationTest.php`.
- [x] Run `php bin/hyperf.php migrate`; expected output is successful creation of all V8 AI tables.

### Task 2: Repositories and Services

- [x] Create repositories for configs, prompts, generation, usage, reviews, risks, data questions, recommendations, safety, metrics, and knowledge.
- [x] Create services for context building, generation, safety, usage, review, risk scoring, data Q&A, recommendation, and knowledge.
- [x] Write unit tests listed in `Test Plan`.
- [x] Run `composer test -- --filter Education\\\\Ai.*ServiceTest`; expected output is all AI service tests passing.

### Task 3: Requests, Schemas, Controllers, and API Tests

- [x] Create request classes with AI feature, prompt, review, metric, knowledge, and teacher draft validation.
- [x] Create schemas matching the API catalog.
- [x] Create admin and teacher mobile controllers with permissions, Result envelope, safety checks, and audit logging.
- [x] Write API, permission, isolation, safety, and audit tests.
- [x] Run `composer test -- --filter Education\\\\Ai`; expected output is all V8 backend tests passing.

### Task 4: PC Admin

- [x] Create typed API clients.
- [x] Register AI routes and menus in `education.ts`.
- [x] Create config, prompt, generation, review, risk, data Q&A, recommendation, usage, and safety pages.
- [x] Write PC tests listed in `Test Plan`.
- [x] Run `pnpm lint && pnpm test -- ai && pnpm build`; expected output is all PC gates passing.

### Task 5: Teacher Mobile

- [x] Create teacher AI API client.
- [x] Register teacher AI pages in `pages.json`.
- [x] Implement comment draft list/detail with queue states and safety blocked state.
- [x] Write `teacher-ai.spec.ts`.
- [x] Run `pnpm lint && pnpm test -- ai && pnpm build:h5`; expected output is all mobile gates passing.

### Task 6: V8 Final Gate

- [x] Run all backend, PC, and mobile commands in `Execution Commands`.
- [x] Confirm acceptance gate behavior with seeded teacher, student, prompt, model config, safety event, and metric catalog data.
- [x] Update status index only after all gates pass during implementation.

## Self-Review

- Spec coverage: Covers AI configs, prompts, generation, usage, review, safety, risk scoring, data Q&A, recommendations, knowledge, PC admin, and teacher mobile drafts.
- MineAdmin fit: Uses MineAdmin 3.x backend paths, PC route/API/page conventions, and uni-app teacher pages.
- Code-level readiness: Migration fields, indexes, rollback, API failures, backend layer tasks, PC/mobile states, tests, commands, and acceptance gates are specified.
- Acceptance status: V8 AI assistant has been implemented and verified.
