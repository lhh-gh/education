# MineAdmin Education SaaS V11 Course Standards Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V11 课程教研与服务标准化 for course service packages, stage goals, ability points, trial lesson standards, delivery standards, service template sets, course materials, feedback records, quality metrics, standard versions, publish logs, localization overrides, and review records.

**Architecture:** V11 creates versioned course standards consumed by V3 admissions, V7 family service, V8 AI, V9 workflow, V10 growth, and V12 content. Published standards are immutable; edits create new versions and historical business records keep their referenced version ids.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, MineAdmin-Vue, Vue3, TypeScript, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. V11 course standards gates have passed.

---

## Scope Check

Included:

- Course service packages, stage goals, ability points, and stage-goal ability relations.
- Trial lesson standards, standard items, teaching delivery standards, and service template sets/items.
- Course materials as standard references, not the V12 full content center.
- Course feedback records and daily course quality metrics.
- Standard versions, publish logs, localization overrides, and review records.
- PC admin editors, version management, publish/review flow, and quality dashboard.

Excluded:

- Full learning content library, student works, material read records, and showcases; V12 owns them.
- Teacher/guardian mobile pages; V11 publishes standards for other modules to consume.
- Automated AI generation of standards.

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_110000_create_v11_course_standard_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Standards/StandardPublishStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Standards/StandardReviewStatus.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationCourseServicePackage.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationCourseStageGoal.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationCourseAbilityPoint.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationCourseStageGoalAbilityRelation.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationTrialLessonStandard.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationTrialLessonStandardItem.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationTeachingDeliveryStandard.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationServiceTemplateSet.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationServiceTemplateItem.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationCourseMaterial.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationCourseFeedbackRecord.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationCourseQualityMetricDaily.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationCourseStandardVersion.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationCourseStandardPublishLog.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationCourseLocalizationOverride.php
mineadmin-education-saas/backend/app/Model/Education/Standards/EducationCourseStandardReviewRecord.php
mineadmin-education-saas/backend/app/Repository/Education/Standards/ServicePackageRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Standards/StageGoalRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Standards/AbilityPointRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Standards/TrialStandardRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Standards/DeliveryStandardRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Standards/ServiceTemplateRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Standards/CourseMaterialRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Standards/CourseFeedbackRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Standards/CourseQualityRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Standards/StandardVersionRepository.php
mineadmin-education-saas/backend/app/Service/Education/Standards/ServicePackageService.php
mineadmin-education-saas/backend/app/Service/Education/Standards/StageGoalService.php
mineadmin-education-saas/backend/app/Service/Education/Standards/AbilityPointService.php
mineadmin-education-saas/backend/app/Service/Education/Standards/TrialStandardService.php
mineadmin-education-saas/backend/app/Service/Education/Standards/DeliveryStandardService.php
mineadmin-education-saas/backend/app/Service/Education/Standards/ServiceTemplateService.php
mineadmin-education-saas/backend/app/Service/Education/Standards/CourseMaterialService.php
mineadmin-education-saas/backend/app/Service/Education/Standards/CourseFeedbackService.php
mineadmin-education-saas/backend/app/Service/Education/Standards/CourseQualityService.php
mineadmin-education-saas/backend/app/Service/Education/Standards/StandardVersionService.php
mineadmin-education-saas/backend/app/Service/Education/Standards/StandardReviewService.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Standards/ServicePackageSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Standards/StageGoalSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Standards/AbilityPointSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Standards/TrialStandardSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Standards/DeliveryStandardSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Standards/ServiceTemplateSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Standards/CourseMaterialSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Standards/CourseFeedbackSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Standards/StandardPublishRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Standards/StandardReviewRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Standards/LocalizationOverrideSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Standards/ServicePackageController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Standards/StageGoalController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Standards/AbilityPointController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Standards/TrialStandardController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Standards/DeliveryStandardController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Standards/ServiceTemplateController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Standards/CourseMaterialController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Standards/CourseFeedbackController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Standards/CourseQualityController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Standards/StandardVersionController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Standards/StandardReviewController.php
mineadmin-education-saas/backend/app/Schema/Education/Standards/ServicePackageSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Standards/StageGoalSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Standards/AbilityPointSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Standards/TrialStandardSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Standards/DeliveryStandardSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Standards/ServiceTemplateSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Standards/CourseMaterialSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Standards/CourseQualitySchema.php
mineadmin-education-saas/backend/app/Schema/Education/Standards/StandardVersionSchema.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Standards/StandardsMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Standards/ServicePackageServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Standards/StageGoalServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Standards/TrialStandardServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Standards/StandardVersionServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Standards/StandardsAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Standards/StandardsPermissionIsolationAuditTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/standards/package.ts
mineadmin-education-saas/admin-web/src/api/education/standards/stage-goal.ts
mineadmin-education-saas/admin-web/src/api/education/standards/trial-standard.ts
mineadmin-education-saas/admin-web/src/api/education/standards/delivery-standard.ts
mineadmin-education-saas/admin-web/src/api/education/standards/template.ts
mineadmin-education-saas/admin-web/src/api/education/standards/material.ts
mineadmin-education-saas/admin-web/src/api/education/standards/quality.ts
mineadmin-education-saas/admin-web/src/api/education/standards/version.ts
mineadmin-education-saas/admin-web/src/views/education/standards/ServicePackageList.vue
mineadmin-education-saas/admin-web/src/views/education/standards/StageGoalEditor.vue
mineadmin-education-saas/admin-web/src/views/education/standards/AbilityPointList.vue
mineadmin-education-saas/admin-web/src/views/education/standards/TrialStandardEditor.vue
mineadmin-education-saas/admin-web/src/views/education/standards/DeliveryStandardEditor.vue
mineadmin-education-saas/admin-web/src/views/education/standards/ServiceTemplateList.vue
mineadmin-education-saas/admin-web/src/views/education/standards/CourseMaterialList.vue
mineadmin-education-saas/admin-web/src/views/education/standards/CourseFeedbackList.vue
mineadmin-education-saas/admin-web/src/views/education/standards/CourseQualityDashboard.vue
mineadmin-education-saas/admin-web/src/views/education/standards/StandardVersionList.vue
mineadmin-education-saas/admin-web/src/views/education/standards/StandardReviewList.vue
mineadmin-education-saas/admin-web/src/views/education/standards/__tests__/ServicePackageList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/standards/__tests__/StageGoalEditor.spec.ts
mineadmin-education-saas/admin-web/src/views/education/standards/__tests__/StandardVersionList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/standards/__tests__/CourseQualityDashboard.spec.ts
```

Modify PC:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

## Database Migration Design

Migration file:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_110000_create_v11_course_standard_tables.php
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
deleted_at timestamp null for mutable draft/config records
```

Foreign-key policy:

```text
Use service-level validation for V1 course, package, campus, user, material, and version references. Do not create physical foreign keys.
```

Rollback behavior:

```text
Drop tables in reverse order: edu_course_standard_review_records, edu_course_localization_overrides, edu_course_standard_publish_logs, edu_course_standard_versions, edu_course_quality_metrics_daily, edu_course_feedback_records, edu_course_materials, edu_service_template_items, edu_service_template_sets, edu_teaching_delivery_standards, edu_trial_lesson_standard_items, edu_trial_lesson_standards, edu_course_stage_goal_ability_relations, edu_course_ability_points, edu_course_stage_goals, edu_course_service_packages.
```

Table catalog:

| Table | Business columns | Indexes |
| --- | --- | --- |
| `edu_course_service_packages` | `package_code varchar(64) not null`, `package_name varchar(120) not null`, `course_id bigint unsigned not null`, `version_no int unsigned not null default 1`, `status varchar(20) not null default draft`, `guardian_visible tinyint(1) not null default 0`, `description text null` | `unique uk_edu_course_service_packages_code_version (tenant_id, package_code, version_no)`, `index idx_edu_course_service_packages_course_status (tenant_id, course_id, status)` |
| `edu_course_stage_goals` | `service_package_id bigint unsigned not null`, `goal_code varchar(64) not null`, `goal_name varchar(120) not null`, `goal_content text not null`, `sort_order int not null default 0`, `status varchar(20) not null default draft` | `unique uk_edu_course_stage_goals_package_code (tenant_id, service_package_id, goal_code)`, `index idx_edu_course_stage_goals_package_order (tenant_id, service_package_id, sort_order)` |
| `edu_course_ability_points` | `ability_code varchar(64) not null`, `ability_name varchar(120) not null`, `ability_group varchar(60) not null`, `description text null`, `status varchar(20) not null default enabled` | `unique uk_edu_course_ability_points_code (tenant_id, ability_code)`, `index idx_edu_course_ability_points_group (tenant_id, ability_group, status)` |
| `edu_course_stage_goal_ability_relations` | `stage_goal_id bigint unsigned not null`, `ability_point_id bigint unsigned not null`, `weight decimal(8,4) not null default 1.0000` | `unique uk_edu_stage_goal_ability (tenant_id, stage_goal_id, ability_point_id)`, `index idx_edu_stage_goal_ability_point (tenant_id, ability_point_id)` |
| `edu_trial_lesson_standards` | `course_id bigint unsigned not null`, `standard_code varchar(64) not null`, `standard_name varchar(120) not null`, `version_no int unsigned not null default 1`, `status varchar(20) not null default draft`, `guardian_visible tinyint(1) not null default 0` | `unique uk_edu_trial_lesson_standards_code_version (tenant_id, standard_code, version_no)`, `index idx_edu_trial_lesson_standards_course_status (tenant_id, course_id, status)` |
| `edu_trial_lesson_standard_items` | `trial_lesson_standard_id bigint unsigned not null`, `item_name varchar(120) not null`, `item_content text not null`, `score_weight decimal(8,4) null`, `sort_order int not null default 0` | `index idx_edu_trial_lesson_standard_items_standard (tenant_id, trial_lesson_standard_id, sort_order)` |
| `edu_teaching_delivery_standards` | `course_id bigint unsigned not null`, `standard_code varchar(64) not null`, `standard_name varchar(120) not null`, `lesson_type varchar(40) not null`, `content text not null`, `version_no int unsigned not null default 1`, `status varchar(20) not null default draft` | `unique uk_edu_teaching_delivery_standards_code_version (tenant_id, standard_code, version_no)`, `index idx_edu_teaching_delivery_standards_course_status (tenant_id, course_id, status)` |
| `edu_service_template_sets` | `template_set_code varchar(64) not null`, `template_set_name varchar(120) not null`, `course_id bigint unsigned null`, `status varchar(20) not null default draft`, `version_no int unsigned not null default 1` | `unique uk_edu_service_template_sets_code_version (tenant_id, template_set_code, version_no)`, `index idx_edu_service_template_sets_course_status (tenant_id, course_id, status)` |
| `edu_service_template_items` | `template_set_id bigint unsigned not null`, `item_type varchar(40) not null`, `item_title varchar(120) not null`, `item_content text not null`, `sort_order int not null default 0` | `index idx_edu_service_template_items_set (tenant_id, template_set_id, sort_order)` |
| `edu_course_materials` | `material_code varchar(64) not null`, `material_name varchar(160) not null`, `course_id bigint unsigned null`, `material_type varchar(40) not null`, `file_url varchar(255) null`, `status varchar(20) not null default draft`, `guardian_visible tinyint(1) not null default 0` | `unique uk_edu_course_materials_code (tenant_id, material_code)`, `index idx_edu_course_materials_course_status (tenant_id, course_id, status)` |
| `edu_course_feedback_records` | `course_id bigint unsigned not null`, `standard_version_id bigint unsigned null`, `feedback_type varchar(40) not null`, `score int unsigned null`, `content text not null`, `source_type varchar(40) null`, `source_id bigint unsigned null`, `submitted_by bigint unsigned not null` | `index idx_edu_course_feedback_records_course_time (tenant_id, course_id, created_at)`, `index idx_edu_course_feedback_records_version (tenant_id, standard_version_id)` |
| `edu_course_quality_metrics_daily` | `metric_date date not null`, `course_id bigint unsigned not null`, `feedback_count int unsigned not null default 0`, `average_score decimal(6,2) null`, `trial_feedback_count int unsigned not null default 0`, `delivery_feedback_count int unsigned not null default 0` | `unique uk_edu_course_quality_metrics_daily_course_date (tenant_id, campus_id, course_id, metric_date)`, `index idx_edu_course_quality_metrics_daily_date (tenant_id, metric_date)` |
| `edu_course_standard_versions` | `business_type varchar(60) not null`, `business_id bigint unsigned not null`, `version_no int unsigned not null`, `status varchar(20) not null default draft`, `snapshot_json json not null`, `published_by bigint unsigned null`, `published_at timestamp null` | `unique uk_edu_course_standard_versions_business_version (tenant_id, business_type, business_id, version_no)`, `index idx_edu_course_standard_versions_status (tenant_id, status)` |
| `edu_course_standard_publish_logs` | `standard_version_id bigint unsigned not null`, `business_type varchar(60) not null`, `business_id bigint unsigned not null`, `from_status varchar(20) not null`, `to_status varchar(20) not null`, `operator_id bigint unsigned not null`, `note varchar(500) null` | `index idx_edu_course_standard_publish_logs_version (tenant_id, standard_version_id, created_at)` |
| `edu_course_localization_overrides` | `standard_version_id bigint unsigned not null`, `campus_id bigint unsigned not null`, `override_json json not null`, `status varchar(20) not null default draft`, `published_at timestamp null` | `unique uk_edu_course_localization_overrides_version_campus (tenant_id, standard_version_id, campus_id)`, `index idx_edu_course_localization_overrides_campus (tenant_id, campus_id, status)` |
| `edu_course_standard_review_records` | `business_type varchar(60) not null`, `business_id bigint unsigned not null`, `standard_version_id bigint unsigned null`, `reviewer_id bigint unsigned not null`, `status varchar(20) not null default pending`, `review_note varchar(500) null`, `reviewed_at timestamp null` | `index idx_edu_course_standard_review_records_reviewer (tenant_id, reviewer_id, status)`, `index idx_edu_course_standard_review_records_business (tenant_id, business_type, business_id)` |

## MineAdmin Backend Module Design

Enums:

```text
StandardPublishStatus: draft, reviewing, published, withdrawn, archived
StandardReviewStatus: pending, approved, rejected
```

Layer tasks:

| Layer | Required implementation |
| --- | --- |
| Model | table names, fillable, JSON casts for snapshots/overrides, enum casts, soft deletes for mutable draft records |
| Repository | package/goal/ability/standard/template/material pages, version lookup, publish logs, review pages, quality metrics |
| Service | package and goal CRUD, ability relation sync, trial/delivery standard editors, template/material management, review/publish, version snapshot creation, localization override |
| Request | validate course ids, version numbers, publish status, review actions, guardian_visible flag, stage goal relations |
| Controller | admin endpoints with MineAdmin permissions, Result envelope, version immutability checks, audit logging |
| Schema | document package, stage goal, ability, trial, delivery, template, material, quality, version, and review payloads |

Versioning rules:

```text
Published standard versions cannot be overwritten in place.
Any edit to published standards creates a new draft version.
Historical business records keep referenced version ids.
Guardian-visible content must be explicitly marked `guardian_visible = 1` and published.
Campus localization override never mutates tenant-level published snapshot.
```

Audit rules:

```text
Write audit actions: education.standards.package.saved, standard.reviewed, standard.published, standard.withdrawn, localization.published, feedback.created, material.saved.
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
| `POST /admin/education/standards/service-packages` | `education:standards:package:save` | teaching admin | tenant + campus | yes |
| `POST /admin/education/standards/stage-goals` | `education:standards:stage-goal:save` | teaching admin | tenant + campus | yes |
| `POST /admin/education/standards/trial-standards` | `education:standards:trial:save` | teaching admin | tenant + campus | yes |
| `POST /admin/education/standards/delivery-standards` | `education:standards:delivery:save` | teaching admin | tenant + campus | yes |
| `POST /admin/education/standards/versions/{id}/publish` | `education:standards:version:publish` | teaching supervisor | tenant + campus | yes |
| `POST /admin/education/standards/reviews/{id}/review` | `education:standards:review:handle` | reviewer | tenant + campus | yes |
| `GET /admin/education/standards/quality-metrics` | `education:standards:quality:page` | manager | tenant + campus | no |

Endpoint-level request/response/failure catalog:

```json
[
  {
    "api": "POST /admin/education/standards/service-packages",
    "request": {"package_code": "ART-BASIC", "package_name": "Art Basic", "course_id": 301, "guardian_visible": true, "description": "basic package"},
    "success": {"code": 200, "message": "success", "data": {"service_package_id": 101, "status": "draft"}},
    "validation_failure": {"code": 422, "message": "package_code is required", "data": {"field": "package_code"}},
    "business_failure": {"code": 409, "message": "published service package cannot be overwritten", "data": {"service_package_id": 101}}
  },
  {
    "api": "POST /admin/education/standards/stage-goals",
    "request": {"service_package_id": 101, "goal_code": "S1", "goal_name": "Line basics", "goal_content": "control line", "ability_point_ids": [201]},
    "success": {"code": 200, "message": "success", "data": {"stage_goal_id": 301}},
    "validation_failure": {"code": 422, "message": "goal_name is required", "data": {"field": "goal_name"}},
    "business_failure": {"code": 404, "message": "service package not found in current context", "data": {"service_package_id": 101}}
  },
  {
    "api": "POST /admin/education/standards/versions/{id}/publish",
    "request": {"publish_note": "approved for new term"},
    "success": {"code": 200, "message": "success", "data": {"standard_version_id": 501, "status": "published"}},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 409, "message": "standard version requires approved review before publish", "data": {"standard_version_id": 501}}
  },
  {
    "api": "POST /admin/education/standards/reviews/{id}/review",
    "request": {"status": "approved", "review_note": "ok"},
    "success": {"code": 200, "message": "success", "data": {"review_record_id": 601, "status": "approved"}},
    "validation_failure": {"code": 422, "message": "status has an invalid value", "data": {"field": "status"}},
    "business_failure": {"code": 403, "message": "review record is assigned to another reviewer", "data": {"review_record_id": 601}}
  },
  {
    "api": "GET /admin/education/standards/quality-metrics",
    "request": {"start_date": "2026-06-01", "end_date": "2026-06-10", "course_id": 301},
    "success": {"code": 200, "message": "success", "data": {"list": [{"course_id": 301, "feedback_count": 10, "average_score": "4.60"}]}},
    "validation_failure": {"code": 422, "message": "start_date is required", "data": {"field": "start_date"}},
    "business_failure": {"code": 403, "message": "course is outside current campus scope", "data": {"course_id": 301}}
  }
]
```

## PC Admin Page Tasks

API clients:

```text
package.ts: pageServicePackages, saveServicePackage, createPackageVersion
stage-goal.ts: pageStageGoals, saveStageGoal, pageAbilityPoints, saveAbilityPoint, syncGoalAbilityPoints
trial-standard.ts: pageTrialStandards, saveTrialStandard, saveTrialStandardItems
delivery-standard.ts: pageDeliveryStandards, saveDeliveryStandard
template.ts: pageServiceTemplateSets, saveServiceTemplateSet, saveServiceTemplateItems
material.ts: pageCourseMaterials, saveCourseMaterial
quality.ts: pageCourseFeedbackRecords, getCourseQualityMetrics
version.ts: pageStandardVersions, publishStandardVersion, withdrawStandardVersion, reviewStandardVersion, saveLocalizationOverride
```

Routes and pages:

| Route | Route name | Page | Permission | Key UI work |
| --- | --- | --- | --- | --- |
| `/education/standards/packages` | `EducationStandardsServicePackageList` | `ServicePackageList.vue` | `education:standards:package:page` | package list, version action |
| `/education/standards/stage-goals` | `EducationStandardsStageGoalEditor` | `StageGoalEditor.vue` | `education:standards:stage-goal:page` | goal editor and ability relation |
| `/education/standards/ability-points` | `EducationStandardsAbilityPointList` | `AbilityPointList.vue` | `education:standards:ability:page` | ability CRUD |
| `/education/standards/trial` | `EducationStandardsTrialStandardEditor` | `TrialStandardEditor.vue` | `education:standards:trial:page` | trial standard item editor |
| `/education/standards/delivery` | `EducationStandardsDeliveryStandardEditor` | `DeliveryStandardEditor.vue` | `education:standards:delivery:page` | delivery standard editor |
| `/education/standards/templates` | `EducationStandardsServiceTemplateList` | `ServiceTemplateList.vue` | `education:standards:template:page` | template set/items |
| `/education/standards/materials` | `EducationStandardsCourseMaterialList` | `CourseMaterialList.vue` | `education:standards:material:page` | lightweight material references |
| `/education/standards/quality` | `EducationStandardsCourseQualityDashboard` | `CourseQualityDashboard.vue` | `education:standards:quality:page` | feedback and quality metrics |
| `/education/standards/versions` | `EducationStandardsStandardVersionList` | `StandardVersionList.vue` | `education:standards:version:page` | publish/withdraw/review/localization |

Required states:

```text
All V11 PC pages implement loading, empty, 403 permission, 422 validation, 409 immutable-version conflict, success refresh, guardian-visible indicator, and current version badge.
```

## Teacher / Guardian Mobile Page Tasks

This module has no direct teacher or guardian page because V11 standards are maintained by PC teaching admins. Teacher and guardian surfaces consume published V11 standards indirectly through V3, V7, V10, and V12. Mobile regression must confirm no unpublished or non-guardian-visible standard appears in teacher or guardian clients.

## Test Plan

Backend tests:

| Test file | Case | Assert |
| --- | --- | --- |
| `StandardsMigrationTest.php` | `test_standard_tables_indexes_and_version_columns_exist` | all V11 tables and version indexes exist |
| `ServicePackageServiceTest.php` | `test_published_package_edit_creates_new_version` | existing published snapshot unchanged; new draft version exists |
| `StageGoalServiceTest.php` | `test_ability_points_attach_to_stage_goal` | relation rows match provided ability ids |
| `TrialStandardServiceTest.php` | `test_trial_standard_items_are_ordered` | item sort_order persists |
| `StandardVersionServiceTest.php` | `test_publish_requires_approved_review_when_enabled` | publish returns documented 409 without approved review |
| `StandardVersionServiceTest.php` | `test_localization_override_does_not_mutate_published_snapshot` | tenant snapshot unchanged; campus override exists |
| `StandardsAdminApiTest.php` | `test_api_failures_match_catalog` | documented 422/403/409 payloads |
| `StandardsPermissionIsolationAuditTest.php` | `test_standard_mutations_require_permission_and_write_audit` | denied without permission; audit exists |

PC tests:

```text
ServicePackageList.spec.ts asserts published row shows immutable badge and edit creates version action.
StageGoalEditor.spec.ts asserts ability point relation payload matches selected points.
StandardVersionList.spec.ts asserts publish button disabled without approved review and 409 displays.
CourseQualityDashboard.spec.ts asserts course/date/campus filters are sent to quality APIs.
```

Mobile regression tests:

```text
Run V7 and V12 guardian visibility tests to ensure unpublished and guardian_visible=false standards are not exposed indirectly.
```

## Execution Commands

Backend:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter Education\\\\Standards
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
V11 migrations run successfully.
All Education\\Standards tests pass.
Code style and static analysis pass.
```

PC:

```bash
cd mineadmin-education-saas/admin-web
pnpm install
pnpm lint
pnpm test -- standards
pnpm build
```

Expected:

```text
Standards PC lint, tests, and build pass.
```

Mobile:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm install
pnpm lint
pnpm test -- family
pnpm test -- content
pnpm build:h5
```

Expected:

```text
Mobile regression tests for standard visibility pass and H5 build succeeds.
```

## Acceptance Gate

V11 is accepted only when:

```text
- Course service packages, goals, ability points, trial standards, delivery standards, templates, materials, feedback, and metrics can be maintained.
- Published standards cannot be overwritten in place.
- Editing published standards creates new draft versions.
- Review can block publish when required.
- Campus localization override does not mutate tenant-level published snapshot.
- Guardian-visible content must be explicitly marked and published.
- PC pages enforce immutable version states and permissions.
- All standard publish/review/material/feedback write operations are audited.
```

## Task Breakdown

### Task 1: Migration, Enums, and Models

- [x] Create migration with table catalog, version indexes, JSON snapshots, and reverse rollback.
- [x] Create enums and models listed in `File Structure`.
- [x] Write `StandardsMigrationTest.php`.
- [x] Run `php bin/hyperf.php migrate`; expected output is successful creation of all V11 standards tables.

### Task 2: Repositories and Services

- [x] Create repositories for packages, goals, abilities, standards, templates, materials, feedback, quality, and versions.
- [x] Create services for editors, review, publish, version snapshots, localization, and quality aggregation.
- [x] Write unit tests listed in `Test Plan`.
- [x] Run `composer test -- --filter Education\\\\Standards.*ServiceTest`; expected output is all standards service tests passing.

### Task 3: Requests, Schemas, Controllers, and API Tests

- [x] Create request classes with validation for course ids, goals, standards, versions, publish, review, localization, and quality filters.
- [x] Create schemas matching the API catalog.
- [x] Create admin controllers with permissions, Result envelope, immutable-version checks, and audit logging.
- [x] Write API, permission, isolation, version, and audit tests.
- [x] Run `composer test -- --filter Education\\\\Standards`; expected output is all V11 backend tests passing.

### Task 4: PC Admin

- [x] Create typed API clients.
- [x] Register standards routes and menus in `education.ts`.
- [x] Create package, goal, ability, trial, delivery, template, material, feedback, quality, version, and review pages.
- [x] Write PC tests listed in `Test Plan`.
- [x] Run `pnpm lint && pnpm test -- standards && pnpm build`; expected output is all PC gates passing.

### Task 5: Mobile Regression

- [x] Run V7 family visibility regression tests.
- [x] Run V12 content visibility regression tests after V12 exists.
- [x] Confirm no unpublished or non-guardian-visible standard is exposed indirectly.

### Task 6: V11 Final Gate

- [x] Run all backend, PC, and mobile commands in `Execution Commands`.
- [x] Confirm acceptance gate behavior with seeded courses, standards, versions, reviews, and localization overrides.
- [x] Update status index only after all gates pass during implementation.

## Self-Review

- Spec coverage: Covers service packages, stage goals, abilities, trial standards, delivery standards, templates, materials, feedback, quality metrics, versions, publish logs, localization, and reviews.
- MineAdmin fit: Uses MineAdmin 3.x backend paths and PC admin conventions; mobile exclusion and regression coverage are explicit.
- Code-level readiness: Migration fields, indexes, rollback, API failures, backend layer tasks, PC states, tests, commands, and acceptance gates are specified.
- Acceptance status: V11 course standards has been implemented and verified.
