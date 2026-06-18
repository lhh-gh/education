# MineAdmin Education SaaS V12 Learning Content Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V12 轻量学习内容中心 for learning materials, versions, attachments, relations, lesson material usage, teacher favorites, student works, work attachments, stage achievement showcases, showcase items, read records, publish logs, content review, and usage metrics.

**Architecture:** V12 links learning content to V11 course standards and V1/V7 student learning workflows. Materials and showcases are publication-controlled content with role-scoped visibility; this is a lightweight content center, not a full LMS.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, object storage, MineAdmin-Vue, Vue3, TypeScript, uni-app, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted.

---

## Scope Check

Included:

- Learning materials, material versions, attachments, relations to courses/standards/stage goals.
- Lesson material usages and teacher material favorites.
- Student works, work attachments, stage achievement showcases, and showcase items.
- Guardian material/showcase views and read records.
- Material publish logs, content review records, material usage metrics, and student work metrics.
- PC material library, version, attachment, relation, student work, showcase, review, and dashboard pages.
- Teacher mobile material search/favorite/reference and student work upload pages.
- Guardian mobile published material and showcase view pages.

Excluded:

- Online exams, auto grading, live classes, adaptive learning paths, and content marketplace.
- Long-form LMS course player.
- Direct AI content generation without V8 review.

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_120000_create_v12_learning_content_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Content/ContentPublishStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Content/ContentReviewStatus.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationLearningMaterial.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationLearningMaterialVersion.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationLearningMaterialAttachment.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationLearningMaterialRelation.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationLessonMaterialUsage.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationTeacherMaterialFavorite.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationStudentWork.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationStudentWorkAttachment.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationStageAchievementShowcase.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationShowcaseItem.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationMaterialReadRecord.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationShowcaseReadRecord.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationMaterialPublishLog.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationContentReviewRecord.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationMaterialUsageMetricDaily.php
mineadmin-education-saas/backend/app/Model/Education/Content/EducationStudentWorkMetricDaily.php
mineadmin-education-saas/backend/app/Repository/Education/Content/LearningMaterialRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Content/MaterialVersionRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Content/MaterialAttachmentRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Content/MaterialRelationRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Content/LessonMaterialUsageRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Content/TeacherFavoriteRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Content/StudentWorkRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Content/ShowcaseRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Content/ContentReviewRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Content/ContentMetricRepository.php
mineadmin-education-saas/backend/app/Service/Education/Content/LearningMaterialService.php
mineadmin-education-saas/backend/app/Service/Education/Content/MaterialVersionService.php
mineadmin-education-saas/backend/app/Service/Education/Content/MaterialAttachmentService.php
mineadmin-education-saas/backend/app/Service/Education/Content/MaterialRelationService.php
mineadmin-education-saas/backend/app/Service/Education/Content/LessonMaterialUsageService.php
mineadmin-education-saas/backend/app/Service/Education/Content/TeacherFavoriteService.php
mineadmin-education-saas/backend/app/Service/Education/Content/StudentWorkService.php
mineadmin-education-saas/backend/app/Service/Education/Content/ShowcaseService.php
mineadmin-education-saas/backend/app/Service/Education/Content/ContentReviewService.php
mineadmin-education-saas/backend/app/Service/Education/Content/MaterialReadService.php
mineadmin-education-saas/backend/app/Service/Education/Content/ContentMetricService.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Content/LearningMaterialSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Content/MaterialVersionSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Content/MaterialRelationSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Content/MaterialPublishRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Content/StudentWorkPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Content/ShowcaseSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Content/ContentReviewRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Content/ContentMetricRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Content/TeacherMaterialPageRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Content/TeacherLessonMaterialUsageRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Content/TeacherStudentWorkSaveRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Content/GuardianContentPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Content/LearningMaterialController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Content/MaterialVersionController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Content/MaterialAttachmentController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Content/MaterialRelationController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Content/StudentWorkController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Content/ShowcaseController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Content/ContentReviewController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Content/ContentMetricController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Content/TeacherContentController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Content/GuardianContentController.php
mineadmin-education-saas/backend/app/Schema/Education/Content/LearningMaterialSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Content/MaterialVersionSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Content/MaterialAttachmentSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Content/MaterialRelationSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Content/LessonMaterialUsageSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Content/StudentWorkSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Content/ShowcaseSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Content/ContentReviewSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Content/ContentMetricSchema.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Content/ContentMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Content/LearningMaterialServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Content/MaterialVersionServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Content/StudentWorkServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Content/ShowcaseServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Content/ContentAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Content/TeacherContentMobileApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Content/GuardianContentMobileApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Content/ContentPermissionIsolationAuditTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/content/material.ts
mineadmin-education-saas/admin-web/src/api/education/content/version.ts
mineadmin-education-saas/admin-web/src/api/education/content/relation.ts
mineadmin-education-saas/admin-web/src/api/education/content/student-work.ts
mineadmin-education-saas/admin-web/src/api/education/content/showcase.ts
mineadmin-education-saas/admin-web/src/api/education/content/review.ts
mineadmin-education-saas/admin-web/src/api/education/content/metric.ts
mineadmin-education-saas/admin-web/src/views/education/content/LearningMaterialList.vue
mineadmin-education-saas/admin-web/src/views/education/content/MaterialVersionList.vue
mineadmin-education-saas/admin-web/src/views/education/content/MaterialAttachmentList.vue
mineadmin-education-saas/admin-web/src/views/education/content/MaterialRelationEditor.vue
mineadmin-education-saas/admin-web/src/views/education/content/StudentWorkList.vue
mineadmin-education-saas/admin-web/src/views/education/content/ShowcaseList.vue
mineadmin-education-saas/admin-web/src/views/education/content/ContentReviewList.vue
mineadmin-education-saas/admin-web/src/views/education/content/MaterialUsageDashboard.vue
mineadmin-education-saas/admin-web/src/views/education/content/components/LearningMaterialForm.vue
mineadmin-education-saas/admin-web/src/views/education/content/components/MaterialVersionDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/content/components/ShowcaseEditor.vue
mineadmin-education-saas/admin-web/src/views/education/content/__tests__/LearningMaterialList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/content/__tests__/ContentReviewList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/content/__tests__/ShowcaseList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/content/__tests__/MaterialUsageDashboard.spec.ts
```

Modify PC:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Create mobile:

```text
mineadmin-education-saas/mobile-uniapp/src/api/content/teacher.ts
mineadmin-education-saas/mobile-uniapp/src/api/content/guardian.ts
mineadmin-education-saas/mobile-uniapp/pages/teacher/content/materials.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/content/material-detail.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/content/favorites.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/content/lesson-material-usage.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/content/student-work-form.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/content/materials.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/content/material-detail.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/content/showcases.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/content/showcase-detail.vue
mineadmin-education-saas/mobile-uniapp/tests/content/teacher-content.spec.ts
mineadmin-education-saas/mobile-uniapp/tests/content/guardian-content.spec.ts
```

Modify mobile:

```text
mineadmin-education-saas/mobile-uniapp/pages.json
```

## Database Migration Design

Migration file:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_120000_create_v12_learning_content_tables.php
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
deleted_at timestamp null for mutable content/review records
```

Foreign-key policy:

```text
Use service-level validation for V1 course, lesson, teacher, student, guardian, V11 standard/stage references, and object-storage attachments. Do not create physical foreign keys.
```

Rollback behavior:

```text
Drop tables in reverse order: edu_student_work_metrics_daily, edu_material_usage_metrics_daily, edu_content_review_records, edu_material_publish_logs, edu_showcase_read_records, edu_material_read_records, edu_showcase_items, edu_stage_achievement_showcases, edu_student_work_attachments, edu_student_works, edu_teacher_material_favorites, edu_lesson_material_usages, edu_learning_material_relations, edu_learning_material_attachments, edu_learning_material_versions, edu_learning_materials.
```

Table catalog:

| Table | Business columns | Indexes |
| --- | --- | --- |
| `edu_learning_materials` | `material_code varchar(64) not null`, `material_name varchar(160) not null`, `course_id bigint unsigned null`, `material_type varchar(40) not null`, `status varchar(20) not null default draft`, `guardian_visible tinyint(1) not null default 0`, `current_version_id bigint unsigned null`, `summary varchar(500) null` | `unique uk_edu_learning_materials_code (tenant_id, material_code)`, `index idx_edu_learning_materials_course_status (tenant_id, course_id, status)` |
| `edu_learning_material_versions` | `material_id bigint unsigned not null`, `version_no int unsigned not null`, `title varchar(160) not null`, `content longtext null`, `status varchar(20) not null default draft`, `snapshot_json json not null`, `published_at timestamp null` | `unique uk_edu_learning_material_versions_material_version (tenant_id, material_id, version_no)`, `index idx_edu_learning_material_versions_status (tenant_id, status)` |
| `edu_learning_material_attachments` | `material_version_id bigint unsigned not null`, `file_name varchar(160) not null`, `file_url varchar(255) not null`, `file_type varchar(40) not null`, `file_size bigint unsigned not null default 0`, `sort_order int not null default 0` | `index idx_edu_learning_material_attachments_version (tenant_id, material_version_id, sort_order)` |
| `edu_learning_material_relations` | `material_id bigint unsigned not null`, `target_type varchar(60) not null`, `target_id bigint unsigned not null`, `relation_note varchar(500) null` | `unique uk_edu_learning_material_relations_target (tenant_id, material_id, target_type, target_id)`, `index idx_edu_learning_material_relations_lookup (tenant_id, target_type, target_id)` |
| `edu_lesson_material_usages` | `lesson_id bigint unsigned not null`, `teacher_id bigint unsigned not null`, `material_id bigint unsigned not null`, `material_version_id bigint unsigned not null`, `usage_type varchar(40) not null`, `used_at timestamp not null`, `remark varchar(500) null` | `index idx_edu_lesson_material_usages_lesson (tenant_id, lesson_id, teacher_id)`, `index idx_edu_lesson_material_usages_material (tenant_id, material_id, used_at)` |
| `edu_teacher_material_favorites` | `teacher_id bigint unsigned not null`, `material_id bigint unsigned not null`, `favorited_at timestamp not null` | `unique uk_edu_teacher_material_favorites_teacher_material (tenant_id, teacher_id, material_id)`, `index idx_edu_teacher_material_favorites_teacher (tenant_id, teacher_id, favorited_at)` |
| `edu_student_works` | `student_id bigint unsigned not null`, `lesson_id bigint unsigned null`, `teacher_id bigint unsigned not null`, `stage_goal_id bigint unsigned null`, `title varchar(160) not null`, `description text null`, `status varchar(20) not null default draft`, `published_at timestamp null` | `index idx_edu_student_works_student_status (tenant_id, student_id, status)`, `index idx_edu_student_works_teacher (tenant_id, teacher_id, created_at)` |
| `edu_student_work_attachments` | `student_work_id bigint unsigned not null`, `file_name varchar(160) not null`, `file_url varchar(255) not null`, `file_type varchar(40) not null`, `file_size bigint unsigned not null default 0`, `sort_order int not null default 0` | `index idx_edu_student_work_attachments_work (tenant_id, student_work_id, sort_order)` |
| `edu_stage_achievement_showcases` | `student_id bigint unsigned not null`, `stage_goal_id bigint unsigned null`, `title varchar(160) not null`, `summary text null`, `status varchar(20) not null default draft`, `published_at timestamp null`, `withdrawn_at timestamp null` | `index idx_edu_stage_achievement_showcases_student_status (tenant_id, student_id, status)`, `index idx_edu_stage_achievement_showcases_stage (tenant_id, stage_goal_id)` |
| `edu_showcase_items` | `showcase_id bigint unsigned not null`, `item_type varchar(40) not null`, `student_work_id bigint unsigned null`, `material_id bigint unsigned null`, `title varchar(160) not null`, `content text null`, `sort_order int not null default 0` | `index idx_edu_showcase_items_showcase (tenant_id, showcase_id, sort_order)`, `index idx_edu_showcase_items_work (tenant_id, student_work_id)` |
| `edu_material_read_records` | `material_id bigint unsigned not null`, `material_version_id bigint unsigned not null`, `student_id bigint unsigned null`, `guardian_user_id bigint unsigned null`, `teacher_id bigint unsigned null`, `read_at timestamp not null` | `unique uk_edu_material_read_records_reader (tenant_id, material_version_id, student_id, guardian_user_id, teacher_id)`, `index idx_edu_material_read_records_material (tenant_id, material_id, read_at)` |
| `edu_showcase_read_records` | `showcase_id bigint unsigned not null`, `student_id bigint unsigned not null`, `guardian_user_id bigint unsigned not null`, `read_at timestamp not null` | `unique uk_edu_showcase_read_records_reader (tenant_id, showcase_id, guardian_user_id)`, `index idx_edu_showcase_read_records_showcase (tenant_id, showcase_id, read_at)` |
| `edu_material_publish_logs` | `material_id bigint unsigned not null`, `material_version_id bigint unsigned not null`, `from_status varchar(20) not null`, `to_status varchar(20) not null`, `operator_id bigint unsigned not null`, `note varchar(500) null` | `index idx_edu_material_publish_logs_material (tenant_id, material_id, created_at)` |
| `edu_content_review_records` | `business_type varchar(60) not null`, `business_id bigint unsigned not null`, `reviewer_id bigint unsigned not null`, `status varchar(20) not null default pending`, `review_note varchar(500) null`, `reviewed_at timestamp null` | `index idx_edu_content_review_records_reviewer (tenant_id, reviewer_id, status)`, `index idx_edu_content_review_records_business (tenant_id, business_type, business_id)` |
| `edu_material_usage_metrics_daily` | `metric_date date not null`, `material_id bigint unsigned null`, `course_id bigint unsigned null`, `teacher_use_count int unsigned not null default 0`, `guardian_read_count int unsigned not null default 0`, `favorite_count int unsigned not null default 0` | `unique uk_edu_material_usage_metrics_daily_scope (tenant_id, campus_id, metric_date, material_id)`, `index idx_edu_material_usage_metrics_daily_date (tenant_id, metric_date)` |
| `edu_student_work_metrics_daily` | `metric_date date not null`, `student_id bigint unsigned null`, `teacher_id bigint unsigned null`, `created_count int unsigned not null default 0`, `published_count int unsigned not null default 0`, `showcase_count int unsigned not null default 0`, `guardian_read_count int unsigned not null default 0` | `unique uk_edu_student_work_metrics_daily_scope (tenant_id, campus_id, metric_date, student_id, teacher_id)`, `index idx_edu_student_work_metrics_daily_date (tenant_id, metric_date)` |

## MineAdmin Backend Module Design

Enums:

```text
ContentPublishStatus: draft, reviewing, published, withdrawn, archived
ContentReviewStatus: pending, approved, rejected
```

Layer tasks:

| Layer | Required implementation |
| --- | --- |
| Model | table names, fillable, JSON snapshot casts, enum casts, attachment metadata casts, soft deletes for mutable content/review records |
| Repository | material/version/attachment/relation pages, teacher authorized material search, guardian published filters, read records, metrics |
| Service | material CRUD, versioning, publish/withdraw, review, relation sync, teacher usage/favorite, student work upload, showcase publish, read records, metrics |
| Request | validate course/material/version/status, attachment metadata, relation targets, teacher usage, student work, showcase items, guardian student |
| Controller | admin controllers plus teacher/guardian API controllers with role isolation, Result envelope, and audit logging |
| Schema | document material, version, attachment, relation, usage, work, showcase, review, metrics, and mobile payloads |

Visibility and LMS boundary:

```text
Guardian-visible material must be published and guardian_visible = 1.
Published material versions are immutable.
Teacher can use only authorized course/class materials.
Guardian can read only content visible to bound students.
No online exams, auto grading, live classes, adaptive paths, or content marketplace are implemented.
```

Audit rules:

```text
Write audit actions: education.content.material.saved, version.created, material.published, material.withdrawn, usage.created, favorite.saved, student_work.created, showcase.published, review.handled, read_record.created.
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
| `POST /admin/education/content/materials` | `education:content:material:save` | content admin | tenant + campus | yes |
| `POST /admin/education/content/materials/{id}/versions` | `education:content:version:create` | content admin | tenant + campus | yes |
| `POST /admin/education/content/materials/{id}/publish` | `education:content:material:publish` | content reviewer | tenant + campus | yes |
| `POST /admin/education/content/reviews/{id}/review` | `education:content:review:handle` | reviewer | tenant + campus | yes |
| `GET /mobile/education/content/teacher/materials` | mobile teacher | teacher | authorized course/class | no |
| `POST /mobile/education/content/teacher/lesson-material-usages` | mobile teacher | teacher | assigned lesson + authorized material | yes |
| `POST /mobile/education/content/teacher/student-works` | mobile teacher | teacher | assigned student | yes |
| `GET /mobile/education/content/guardian/students/{studentId}/materials` | mobile guardian | guardian | bound student + published | read receipt |
| `GET /mobile/education/content/guardian/students/{studentId}/showcases` | mobile guardian | guardian | bound student + published | read receipt |
| `GET /admin/education/content/material-usage-metrics` | `education:content:metric:page` | admin | tenant + campus | no |

Endpoint-level request/response/failure catalog:

```json
[
  {
    "api": "POST /admin/education/content/materials",
    "request": {"material_code": "ART-LINE-001", "material_name": "Line Practice", "course_id": 301, "material_type": "worksheet", "guardian_visible": true},
    "success": {"code": 200, "message": "success", "data": {"material_id": 101, "status": "draft"}},
    "validation_failure": {"code": 422, "message": "material_code is required", "data": {"field": "material_code"}},
    "business_failure": {"code": 409, "message": "material code already exists", "data": {"material_code": "ART-LINE-001"}}
  },
  {
    "api": "POST /admin/education/content/materials/{id}/publish",
    "request": {"publish_note": "approved"},
    "success": {"code": 200, "message": "success", "data": {"material_id": 101, "current_version_id": 201, "status": "published"}},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 409, "message": "material requires approved review before publish", "data": {"material_id": 101}}
  },
  {
    "api": "GET /mobile/education/content/teacher/materials",
    "request": {"course_id": 301, "keyword": "line"},
    "success": {"code": 200, "message": "success", "data": {"list": [{"material_id": 101, "material_name": "Line Practice"}]}},
    "validation_failure": {"code": 422, "message": "course_id must be a positive integer", "data": {"field": "course_id"}},
    "business_failure": {"code": 403, "message": "teacher is not authorized for this course", "data": {"course_id": 301}}
  },
  {
    "api": "POST /mobile/education/content/teacher/lesson-material-usages",
    "request": {"lesson_id": 8801, "material_id": 101, "usage_type": "pre_class"},
    "success": {"code": 200, "message": "success", "data": {"lesson_material_usage_id": 301}},
    "validation_failure": {"code": 422, "message": "lesson_id is required", "data": {"field": "lesson_id"}},
    "business_failure": {"code": 403, "message": "lesson is not assigned to current teacher", "data": {"lesson_id": 8801}}
  },
  {
    "api": "POST /mobile/education/content/teacher/student-works",
    "request": {"student_id": 1201, "lesson_id": 8801, "title": "Line work", "attachment_ids": [9001]},
    "success": {"code": 200, "message": "success", "data": {"student_work_id": 401, "status": "draft"}},
    "validation_failure": {"code": 422, "message": "title is required", "data": {"field": "title"}},
    "business_failure": {"code": 403, "message": "student is not assigned to current teacher", "data": {"student_id": 1201}}
  },
  {
    "api": "GET /mobile/education/content/guardian/students/{studentId}/materials",
    "request": {"page": 1, "pageSize": 20},
    "success": {"code": 200, "message": "success", "data": {"list": [{"material_id": 101, "status": "published"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "studentId must be a positive integer", "data": {"field": "studentId"}},
    "business_failure": {"code": 403, "message": "material is not visible to guardian", "data": {"material_id": 101}}
  },
  {
    "api": "GET /mobile/education/content/guardian/students/{studentId}/showcases",
    "request": {"page": 1, "pageSize": 20},
    "success": {"code": 200, "message": "success", "data": {"list": [{"showcase_id": 501, "title": "Stage 1 Achievement"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "studentId must be a positive integer", "data": {"field": "studentId"}},
    "business_failure": {"code": 403, "message": "student is not bound to current guardian", "data": {"student_id": 1201}}
  }
]
```

## PC Admin Page Tasks

API clients:

```text
material.ts: pageLearningMaterials, saveLearningMaterial, publishLearningMaterial, withdrawLearningMaterial
version.ts: pageMaterialVersions, createMaterialVersion, getMaterialVersionDetail
relation.ts: saveMaterialRelations, pageMaterialRelations
student-work.ts: pageStudentWorks, publishStudentWork, withdrawStudentWork
showcase.ts: pageShowcases, saveShowcase, publishShowcase, withdrawShowcase
review.ts: pageContentReviews, reviewContent
metric.ts: getMaterialUsageMetrics, getStudentWorkMetrics
```

Routes and pages:

| Route | Route name | Page | Permission | Key UI work |
| --- | --- | --- | --- | --- |
| `/education/content/materials` | `EducationContentLearningMaterialList` | `LearningMaterialList.vue` | `education:content:material:page` | material list, publish/withdraw |
| `/education/content/material-versions` | `EducationContentMaterialVersionList` | `MaterialVersionList.vue` | `education:content:version:page` | version drawer and immutable badge |
| `/education/content/attachments` | `EducationContentMaterialAttachmentList` | `MaterialAttachmentList.vue` | `education:content:attachment:page` | attachment list |
| `/education/content/relations` | `EducationContentMaterialRelationEditor` | `MaterialRelationEditor.vue` | `education:content:relation:page` | course/standard/stage relation editor |
| `/education/content/student-works` | `EducationContentStudentWorkList` | `StudentWorkList.vue` | `education:content:student-work:page` | work list and publish |
| `/education/content/showcases` | `EducationContentShowcaseList` | `ShowcaseList.vue` | `education:content:showcase:page` | showcase editor |
| `/education/content/reviews` | `EducationContentReviewList` | `ContentReviewList.vue` | `education:content:review:page` | review approve/reject |
| `/education/content/metrics` | `EducationContentMaterialUsageDashboard` | `MaterialUsageDashboard.vue` | `education:content:metric:page` | usage/read/work metrics |

Required states:

```text
All V12 PC pages implement loading, empty, 403 permission, 422 validation, 409 immutable-version/review conflict, attachment permission error, success refresh, and guardian-visible indicator.
```

## Teacher / Guardian Mobile Page Tasks

Teacher:

```text
API: mobile-uniapp/src/api/content/teacher.ts
Pages: materials.vue, material-detail.vue, favorites.vue, lesson-material-usage.vue, student-work-form.vue
State: material search, course filter, favorite toggle, detail attachments, lesson usage submit, student work upload, loading/empty/error/submitted states
Isolation: teacher can access only authorized course/class materials and assigned lesson/student work.
```

Guardian:

```text
API: mobile-uniapp/src/api/content/guardian.ts
Pages: materials.vue, material-detail.vue, showcases.vue, showcase-detail.vue
State: selected student, published material list/detail, published showcase list/detail, read state, hidden/withdrawn content not shown, loading/empty/error states
Isolation: guardian can access only published content visible to bound students.
```

`pages.json`:

```text
Register V12 teacher content pages under teacher role and guardian content pages under guardian role with selected student requirement.
```

## Test Plan

Backend tests:

| Test file | Case | Assert |
| --- | --- | --- |
| `ContentMigrationTest.php` | `test_content_tables_indexes_and_version_columns_exist` | all V12 tables and indexes exist |
| `LearningMaterialServiceTest.php` | `test_material_requires_review_before_publish_when_enabled` | publish returns documented 409 without approved review |
| `MaterialVersionServiceTest.php` | `test_published_version_is_immutable` | editing creates new draft version |
| `StudentWorkServiceTest.php` | `test_teacher_uploads_work_for_assigned_student_only` | assigned succeeds; unassigned returns 403 |
| `ShowcaseServiceTest.php` | `test_guardian_sees_published_showcase_for_bound_student_only` | bound published visible; withdrawn hidden |
| `GuardianContentMobileApiTest.php` | `test_guardian_material_read_record_created_once` | one read record after repeated reads |
| `TeacherContentMobileApiTest.php` | `test_teacher_material_access_requires_course_authorization` | unauthorized course returns 403 |
| `ContentAdminApiTest.php` | `test_api_failures_match_catalog` | documented 422/403/409 payloads |
| `ContentPermissionIsolationAuditTest.php` | `test_content_mutations_require_permission_and_write_audit` | denied without permission; audit exists |

PC tests:

```text
LearningMaterialList.spec.ts asserts guardian-visible indicator and publish 409 review error.
ContentReviewList.spec.ts asserts approve/reject state and review note validation.
ShowcaseList.spec.ts asserts published showcase cannot be edited in place.
MaterialUsageDashboard.spec.ts asserts material/course/date/campus filters are sent.
```

Mobile tests:

```text
teacher-content.spec.ts asserts material search respects course authorization, favorite toggle persists, and student work submit handles 403.
guardian-content.spec.ts asserts withdrawn materials/showcases are hidden, read record is called once, and unbound student returns 403 state.
```

## Execution Commands

Backend:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter Education\\\\Content
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
V12 migrations run successfully.
All Education\\Content tests pass.
Code style and static analysis pass.
```

PC:

```bash
cd mineadmin-education-saas/admin-web
pnpm install
pnpm lint
pnpm test -- content
pnpm build
```

Expected:

```text
Content PC lint, tests, and build pass.
```

Mobile:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm install
pnpm lint
pnpm test -- content
pnpm build:h5
```

Expected:

```text
Teacher and guardian content mobile lint, tests, and H5 build pass.
```

## Acceptance Gate

V12 is accepted only when:

```text
- Materials can be versioned, attached, related, reviewed, published, and withdrawn.
- Published versions are immutable.
- Teachers can search/favorite/reference only authorized materials.
- Teachers can upload student works only for assigned students.
- Guardians can view only published, guardian-visible, bound-student materials and showcases.
- Read records are created once for material and showcase reads.
- Content review can block publish.
- Usage and student work metrics aggregate daily.
- No online exam, auto grading, live class, adaptive learning, or marketplace behavior is introduced.
- All content write operations are permission-checked and audited.
```

## Task Breakdown

### Task 1: Migration, Enums, and Models

- [x] Create migration with table catalog, version indexes, attachment fields, read-record unique keys, and reverse rollback.
- [x] Create enums and models listed in `File Structure`.
- [x] Write `ContentMigrationTest.php`.
- [x] Run `php bin/hyperf.php migrate`; expected output is successful creation of all V12 content tables.

### Task 2: Repositories and Services

- [x] Create repositories for materials, versions, attachments, relations, usage, favorites, works, showcases, reviews, reads, and metrics.
- [x] Create services for material versioning, publish/review, relation sync, teacher usage/favorite, student work, showcase, read records, and metrics.
- [x] Write unit tests listed in `Test Plan`.
- [x] Run `composer test -- --filter Education\\\\Content.*ServiceTest`; expected output is all content service tests passing.

### Task 3: Requests, Schemas, Controllers, and API Tests

- [x] Create admin and mobile request classes with validation for material, version, relation, publish, review, usage, work, showcase, and guardian student ids.
- [x] Create schemas matching the API catalog.
- [x] Create admin, teacher, and guardian controllers with permissions, Result envelope, role isolation, read receipts, and audit logging.
- [x] Write API, permission, isolation, visibility, read record, and audit tests.
- [x] Run `composer test -- --filter Education\\\\Content`; expected output is all V12 backend tests passing.

### Task 4: PC Admin

- [x] Create typed API clients.
- [x] Register content routes and menus in `education.ts`.
- [x] Create material, version, attachment, relation, student work, showcase, review, and metrics pages.
- [x] Write PC tests listed in `Test Plan`.
- [x] Run `pnpm lint && pnpm test -- content && pnpm build`; expected output is all PC gates passing.

### Task 5: Teacher and Guardian Mobile

- [x] Create teacher and guardian content API clients.
- [x] Register V12 mobile pages in `pages.json`.
- [x] Implement teacher material search/detail/favorite/usage/student-work pages.
- [x] Implement guardian material/showcase list/detail pages with read records.
- [x] Write mobile tests listed in `Test Plan`.
- [x] Run `pnpm lint && pnpm test -- content && pnpm build:h5`; expected output is all mobile gates passing.

### Task 6: V12 Final Gate

- [x] Run all backend, PC, and mobile commands in `Execution Commands`.
- [x] Confirm acceptance gate behavior with seeded materials, versions, attachments, teachers, guardians, works, showcases, and reviews.
- [x] Update status index only after all gates pass during implementation.

## Self-Review

- Spec coverage: Covers materials, versions, attachments, relations, lesson usage, favorites, student works, showcases, read records, publish logs, review, metrics, PC, teacher mobile, and guardian mobile.
- MineAdmin fit: Uses MineAdmin 3.x backend paths, PC route/API/page conventions, and uni-app teacher/guardian pages.
- Code-level readiness: Migration fields, indexes, rollback, API failures, backend layer tasks, PC/mobile states, tests, commands, and acceptance gates are specified.
- Implementation status: V12 learning content has been implemented and accepted through backend, PC, and mobile gates.
