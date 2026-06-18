# MineAdmin Education SaaS V7 Family Service Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V7 家校服务与学习反馈 for lesson comments, templates, performance tags, homework, submissions, reviews, growth records, learning reports, family messages, read receipts, attachments, and service quality metrics.

**Architecture:** V7 builds on V1 lessons/students and V2 operations. Teachers create service content, admins govern templates and quality metrics, and guardians view only published content for bound students.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, object storage, Redis queue, MineAdmin-Vue, Vue3, TypeScript, uni-app, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. Final backend, PC, and mobile gates passed.

---

## Scope Check

Included:

- Lesson comments, comment templates, performance tags, and tag relations.
- Homework assignments, assignment targets, guardian submissions, and teacher reviews.
- Growth records and learning reports with published/withdrawn visibility.
- Family message threads, read receipts, and attachment permission checks.
- Service quality metrics for comments, reports, homework, messages, and response timeliness.
- PC admin template, homework, report, growth, message monitor, and quality dashboard pages.
- Teacher mobile lesson comment, homework review, growth record, and message reply pages.
- Guardian mobile comment view, homework submit, report view, growth record view, and message thread pages.

Excluded:

- AI-generated comment drafts; V8 owns AI draft generation.
- Reusable learning material library; V12 owns content center.
- Public social/community features.

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_070000_create_v7_family_service_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Family/PublishStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Family/HomeworkStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Family/MessageStatus.php
mineadmin-education-saas/backend/app/Model/Education/Family/EducationLessonComment.php
mineadmin-education-saas/backend/app/Model/Education/Family/EducationLessonCommentTemplate.php
mineadmin-education-saas/backend/app/Model/Education/Family/EducationStudentPerformanceTag.php
mineadmin-education-saas/backend/app/Model/Education/Family/EducationLessonCommentTagRelation.php
mineadmin-education-saas/backend/app/Model/Education/Family/EducationHomeworkAssignment.php
mineadmin-education-saas/backend/app/Model/Education/Family/EducationHomeworkTarget.php
mineadmin-education-saas/backend/app/Model/Education/Family/EducationHomeworkSubmission.php
mineadmin-education-saas/backend/app/Model/Education/Family/EducationHomeworkReview.php
mineadmin-education-saas/backend/app/Model/Education/Family/EducationGrowthRecord.php
mineadmin-education-saas/backend/app/Model/Education/Family/EducationLearningReport.php
mineadmin-education-saas/backend/app/Model/Education/Family/EducationLearningReportItem.php
mineadmin-education-saas/backend/app/Model/Education/Family/EducationFamilyMessage.php
mineadmin-education-saas/backend/app/Model/Education/Family/EducationFamilyReadReceipt.php
mineadmin-education-saas/backend/app/Model/Education/Family/EducationServiceQualityMetric.php
mineadmin-education-saas/backend/app/Model/Education/Family/EducationFamilyServiceAttachment.php
mineadmin-education-saas/backend/app/Repository/Education/Family/LessonCommentRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Family/HomeworkRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Family/GrowthRecordRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Family/LearningReportRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Family/FamilyMessageRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Family/ReadReceiptRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Family/ServiceQualityRepository.php
mineadmin-education-saas/backend/app/Service/Education/Family/LessonCommentService.php
mineadmin-education-saas/backend/app/Service/Education/Family/CommentTemplateService.php
mineadmin-education-saas/backend/app/Service/Education/Family/HomeworkService.php
mineadmin-education-saas/backend/app/Service/Education/Family/HomeworkReviewService.php
mineadmin-education-saas/backend/app/Service/Education/Family/GrowthRecordService.php
mineadmin-education-saas/backend/app/Service/Education/Family/LearningReportService.php
mineadmin-education-saas/backend/app/Service/Education/Family/FamilyMessageService.php
mineadmin-education-saas/backend/app/Service/Education/Family/AttachmentAccessService.php
mineadmin-education-saas/backend/app/Service/Education/Family/ServiceQualityService.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Family/CommentTemplateSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Family/PerformanceTagSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Family/HomeworkAssignmentSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Family/LearningReportSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Family/FamilyMessagePageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Family/ServiceQualityRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Family/TeacherLessonCommentSaveRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Family/TeacherHomeworkReviewRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Family/TeacherGrowthRecordSaveRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Family/GuardianHomeworkSubmissionRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Family/FamilyMessageSendRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Family/CommentTemplateController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Family/PerformanceTagController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Family/HomeworkAssignmentController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Family/LearningReportController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Family/GrowthRecordController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Family/FamilyMessageMonitorController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Family/ServiceQualityController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Family/TeacherFamilyController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Family/GuardianFamilyController.php
mineadmin-education-saas/backend/app/Schema/Education/Family/LessonCommentSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Family/HomeworkSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Family/GrowthRecordSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Family/LearningReportSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Family/FamilyMessageSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Family/ServiceQualitySchema.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Family/FamilyServiceMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Family/LessonCommentServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Family/HomeworkServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Family/LearningReportServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Family/FamilyMessageServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Family/TeacherFamilyMobileApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Family/GuardianFamilyMobileApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Family/FamilyAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Family/FamilyPermissionIsolationAuditTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/family/comment.ts
mineadmin-education-saas/admin-web/src/api/education/family/homework.ts
mineadmin-education-saas/admin-web/src/api/education/family/report.ts
mineadmin-education-saas/admin-web/src/api/education/family/message.ts
mineadmin-education-saas/admin-web/src/api/education/family/quality.ts
mineadmin-education-saas/admin-web/src/views/education/family/CommentTemplateList.vue
mineadmin-education-saas/admin-web/src/views/education/family/PerformanceTagList.vue
mineadmin-education-saas/admin-web/src/views/education/family/HomeworkAssignmentList.vue
mineadmin-education-saas/admin-web/src/views/education/family/LearningReportList.vue
mineadmin-education-saas/admin-web/src/views/education/family/GrowthRecordList.vue
mineadmin-education-saas/admin-web/src/views/education/family/FamilyMessageMonitor.vue
mineadmin-education-saas/admin-web/src/views/education/family/ServiceQualityDashboard.vue
mineadmin-education-saas/admin-web/src/views/education/family/components/HomeworkAssignmentForm.vue
mineadmin-education-saas/admin-web/src/views/education/family/components/LearningReportEditor.vue
mineadmin-education-saas/admin-web/src/views/education/family/components/FamilyMessageThreadDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/family/__tests__/HomeworkAssignmentList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/family/__tests__/LearningReportList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/family/__tests__/FamilyMessageMonitor.spec.ts
mineadmin-education-saas/admin-web/src/views/education/family/__tests__/ServiceQualityDashboard.spec.ts
```

Modify PC:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Create mobile:

```text
mineadmin-education-saas/mobile-uniapp/src/api/family/teacher.ts
mineadmin-education-saas/mobile-uniapp/src/api/family/guardian.ts
mineadmin-education-saas/mobile-uniapp/pages/teacher/family/lesson-comments.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/family/comment-form.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/family/homework-reviews.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/family/growth-record-form.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/family/messages.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/family/lesson-comments.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/family/homework.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/family/homework-submit.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/family/learning-reports.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/family/growth-records.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/family/messages.vue
mineadmin-education-saas/mobile-uniapp/tests/family/teacher-family.spec.ts
mineadmin-education-saas/mobile-uniapp/tests/family/guardian-family.spec.ts
```

Modify mobile:

```text
mineadmin-education-saas/mobile-uniapp/pages.json
```

## Database Migration Design

Migration file:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_070000_create_v7_family_service_tables.php
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
deleted_at timestamp null for mutable content/message records
```

Foreign-key policy:

```text
Use service-level validation for lesson, student, guardian, teacher, homework, report, attachment, and user references. Do not create physical foreign keys.
```

Rollback behavior:

```text
Drop tables in reverse order: edu_family_service_attachments, edu_service_quality_metrics, edu_family_read_receipts, edu_family_messages, edu_learning_report_items, edu_learning_reports, edu_growth_records, edu_homework_reviews, edu_homework_submissions, edu_homework_targets, edu_homework_assignments, edu_lesson_comment_tag_relations, edu_student_performance_tags, edu_lesson_comment_templates, edu_lesson_comments.
```

Table catalog:

| Table | Business columns | Indexes |
| --- | --- | --- |
| `edu_lesson_comments` | `lesson_id bigint unsigned not null`, `student_id bigint unsigned not null`, `teacher_id bigint unsigned not null`, `content text not null`, `status varchar(20) not null default draft`, `published_at timestamp null`, `withdrawn_at timestamp null` | `index idx_edu_lesson_comments_lesson_student (tenant_id, lesson_id, student_id)`, `index idx_edu_lesson_comments_student_status (tenant_id, student_id, status)` |
| `edu_lesson_comment_templates` | `template_code varchar(64) not null`, `template_name varchar(120) not null`, `content text not null`, `course_id bigint unsigned null`, `status varchar(20) not null default enabled`, `sort_order int not null default 0` | `unique uk_edu_lesson_comment_templates_code (tenant_id, template_code)`, `index idx_edu_lesson_comment_templates_course (tenant_id, course_id, status)` |
| `edu_student_performance_tags` | `tag_code varchar(64) not null`, `tag_name varchar(80) not null`, `tag_type varchar(40) not null`, `status varchar(20) not null default enabled`, `sort_order int not null default 0` | `unique uk_edu_student_performance_tags_code (tenant_id, tag_code)`, `index idx_edu_student_performance_tags_type (tenant_id, tag_type, status)` |
| `edu_lesson_comment_tag_relations` | `lesson_comment_id bigint unsigned not null`, `performance_tag_id bigint unsigned not null`, `student_id bigint unsigned not null` | `unique uk_edu_lesson_comment_tag_relations_comment_tag (tenant_id, lesson_comment_id, performance_tag_id)`, `index idx_edu_lesson_comment_tag_relations_student (tenant_id, student_id)` |
| `edu_homework_assignments` | `title varchar(160) not null`, `content text not null`, `course_id bigint unsigned null`, `class_id bigint unsigned null`, `lesson_id bigint unsigned null`, `status varchar(20) not null default draft`, `publish_at timestamp null`, `due_at timestamp null` | `index idx_edu_homework_assignments_course_status (tenant_id, campus_id, course_id, status)`, `index idx_edu_homework_assignments_due (tenant_id, due_at, status)` |
| `edu_homework_targets` | `homework_assignment_id bigint unsigned not null`, `student_id bigint unsigned not null`, `guardian_id bigint unsigned null`, `status varchar(20) not null default assigned`, `submitted_at timestamp null`, `reviewed_at timestamp null` | `unique uk_edu_homework_targets_assignment_student (tenant_id, homework_assignment_id, student_id)`, `index idx_edu_homework_targets_student_status (tenant_id, student_id, status)` |
| `edu_homework_submissions` | `homework_target_id bigint unsigned not null`, `student_id bigint unsigned not null`, `guardian_id bigint unsigned not null`, `content text null`, `attachment_count int unsigned not null default 0`, `submitted_at timestamp not null`, `status varchar(20) not null default submitted` | `index idx_edu_homework_submissions_target (tenant_id, homework_target_id)`, `index idx_edu_homework_submissions_student (tenant_id, student_id, submitted_at)` |
| `edu_homework_reviews` | `homework_submission_id bigint unsigned not null`, `teacher_id bigint unsigned not null`, `score int unsigned null`, `content text not null`, `reviewed_at timestamp not null`, `status varchar(20) not null default reviewed` | `unique uk_edu_homework_reviews_submission (tenant_id, homework_submission_id)`, `index idx_edu_homework_reviews_teacher (tenant_id, teacher_id, reviewed_at)` |
| `edu_growth_records` | `student_id bigint unsigned not null`, `teacher_id bigint unsigned null`, `record_type varchar(40) not null`, `title varchar(160) not null`, `content text not null`, `status varchar(20) not null default draft`, `published_at timestamp null` | `index idx_edu_growth_records_student_status (tenant_id, student_id, status)`, `index idx_edu_growth_records_teacher (tenant_id, teacher_id, created_at)` |
| `edu_learning_reports` | `student_id bigint unsigned not null`, `report_title varchar(160) not null`, `report_period varchar(60) not null`, `status varchar(20) not null default draft`, `published_at timestamp null`, `withdrawn_at timestamp null`, `summary text null` | `index idx_edu_learning_reports_student_status (tenant_id, student_id, status)`, `index idx_edu_learning_reports_period (tenant_id, campus_id, report_period)` |
| `edu_learning_report_items` | `learning_report_id bigint unsigned not null`, `item_type varchar(40) not null`, `title varchar(160) not null`, `content text not null`, `sort_order int not null default 0` | `index idx_edu_learning_report_items_report (tenant_id, learning_report_id, sort_order)` |
| `edu_family_messages` | `thread_id varchar(64) not null`, `student_id bigint unsigned not null`, `sender_type varchar(20) not null`, `sender_user_id bigint unsigned not null`, `receiver_user_id bigint unsigned null`, `content text not null`, `status varchar(20) not null default sent` | `index idx_edu_family_messages_thread_time (tenant_id, thread_id, created_at)`, `index idx_edu_family_messages_student (tenant_id, student_id, created_at)` |
| `edu_family_read_receipts` | `business_type varchar(40) not null`, `business_id bigint unsigned not null`, `student_id bigint unsigned not null`, `reader_type varchar(20) not null`, `reader_user_id bigint unsigned not null`, `read_at timestamp not null` | `unique uk_edu_family_read_receipts_reader (tenant_id, business_type, business_id, reader_type, reader_user_id)`, `index idx_edu_family_read_receipts_student (tenant_id, student_id, read_at)` |
| `edu_service_quality_metrics` | `metric_date date not null`, `teacher_id bigint unsigned null`, `student_id bigint unsigned null`, `comment_count int unsigned not null default 0`, `homework_review_count int unsigned not null default 0`, `report_count int unsigned not null default 0`, `message_response_minutes int unsigned null` | `unique uk_edu_service_quality_metrics_scope_date (tenant_id, campus_id, metric_date, teacher_id, student_id)`, `index idx_edu_service_quality_metrics_teacher (tenant_id, teacher_id, metric_date)` |
| `edu_family_service_attachments` | `business_type varchar(40) not null`, `business_id bigint unsigned not null`, `student_id bigint unsigned null`, `file_name varchar(160) not null`, `file_url varchar(255) not null`, `file_size bigint unsigned not null default 0`, `uploaded_by bigint unsigned not null` | `index idx_edu_family_service_attachments_business (tenant_id, business_type, business_id)`, `index idx_edu_family_service_attachments_student (tenant_id, student_id)` |

## MineAdmin Backend Module Design

Enums:

```text
PublishStatus: draft, published, withdrawn
HomeworkStatus: draft, published, assigned, submitted, reviewed, overdue, withdrawn
MessageStatus: sent, read, withdrawn
```

Layer tasks:

| Layer | Required implementation |
| --- | --- |
| Model | table names, fillable, enum casts, timestamp casts, attachment metadata casts, soft deletes for content/message records |
| Repository | page filters by tenant/campus/student/teacher/status/date, guardian-visible published filters, thread queries, metric aggregation |
| Service | teacher access check, publish/withdraw state machine, homework assignment target generation, submission/review, read receipt creation, attachment permission, metrics |
| Request | validate published status, due dates, teacher/guardian content, attachment metadata, message payload, report items |
| Controller | admin controllers for governance pages; teacher/guardian API controllers with F06 context; audit writes for create/publish/withdraw/review/message |
| Schema | document comment, homework, report, growth, message, attachment, and quality API payloads |

Visibility rules:

```text
Draft comments/reports/growth records are not visible to guardians.
Published content creates read receipts when guardians view it.
Withdrawn reports and comments disappear from guardian lists.
Attachment download requires the same permission as the parent business record.
```

Audit rules:

```text
Write audit actions: education.family.comment.created, comment.published, comment.withdrawn, homework.published, homework.submitted, homework.reviewed, report.published, report.withdrawn, message.sent, attachment.uploaded.
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
| `POST /mobile/education/family/teacher/lesson-comments` | mobile teacher | teacher | assigned lesson/student | yes |
| `POST /mobile/education/family/teacher/homework-reviews` | mobile teacher | teacher | assigned class/student | yes |
| `POST /mobile/education/family/teacher/growth-records` | mobile teacher | teacher | assigned student | yes |
| `GET /mobile/education/family/guardian/students/{studentId}/lesson-comments` | mobile guardian | guardian | bound student + published | read receipt |
| `POST /mobile/education/family/guardian/homework-submissions` | mobile guardian | guardian | bound student | yes |
| `GET /mobile/education/family/guardian/students/{studentId}/learning-reports` | mobile guardian | guardian | bound student + published | read receipt |
| `POST /mobile/education/family/messages` | mobile teacher/guardian | teacher/guardian | assigned or bound student | yes |
| `POST /admin/education/family/homework-assignments` | `education:family:homework:create` | admin | tenant + campus | yes |
| `POST /admin/education/family/learning-reports/{id}/publish` | `education:family:report:publish` | admin | tenant + campus | yes |
| `GET /admin/education/family/service-quality` | `education:family:quality:page` | admin | tenant + campus | no |

Endpoint-level request/response/failure catalog:

```json
[
  {
    "api": "POST /mobile/education/family/teacher/lesson-comments",
    "request": {"lesson_id": 8801, "student_id": 1201, "content": "participated actively", "tag_ids": [1, 2], "publish": true},
    "success": {"code": 200, "message": "success", "data": {"lesson_comment_id": 101, "status": "published"}},
    "validation_failure": {"code": 422, "message": "content is required", "data": {"field": "content"}},
    "business_failure": {"code": 403, "message": "teacher is not assigned to this lesson student", "data": {"lesson_id": 8801, "student_id": 1201}}
  },
  {
    "api": "POST /admin/education/family/homework-assignments",
    "request": {"title": "Unit 1 practice", "content": "finish worksheet", "class_id": 501, "due_at": "2026-06-15 20:00:00", "student_ids": [1201]},
    "success": {"code": 200, "message": "success", "data": {"homework_assignment_id": 201, "target_count": 1, "status": "published"}},
    "validation_failure": {"code": 422, "message": "title is required", "data": {"field": "title"}},
    "business_failure": {"code": 403, "message": "class is outside current campus scope", "data": {"class_id": 501}}
  },
  {
    "api": "POST /mobile/education/family/guardian/homework-submissions",
    "request": {"homework_target_id": 301, "student_id": 1201, "content": "submitted", "attachment_ids": [9001]},
    "success": {"code": 200, "message": "success", "data": {"homework_submission_id": 401, "status": "submitted"}},
    "validation_failure": {"code": 422, "message": "homework_target_id is required", "data": {"field": "homework_target_id"}},
    "business_failure": {"code": 403, "message": "student is not bound to current guardian", "data": {"student_id": 1201}}
  },
  {
    "api": "POST /mobile/education/family/teacher/homework-reviews",
    "request": {"homework_submission_id": 401, "score": 90, "content": "good work"},
    "success": {"code": 200, "message": "success", "data": {"homework_review_id": 501, "target_status": "reviewed"}},
    "validation_failure": {"code": 422, "message": "content is required", "data": {"field": "content"}},
    "business_failure": {"code": 403, "message": "teacher cannot review this submission", "data": {"homework_submission_id": 401}}
  },
  {
    "api": "POST /admin/education/family/learning-reports/{id}/publish",
    "request": {"publish": true},
    "success": {"code": 200, "message": "success", "data": {"learning_report_id": 601, "status": "published"}},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 409, "message": "learning report has no report items", "data": {"learning_report_id": 601}}
  },
  {
    "api": "GET /mobile/education/family/guardian/students/{studentId}/learning-reports",
    "request": {"page": 1, "pageSize": 20},
    "success": {"code": 200, "message": "success", "data": {"list": [{"learning_report_id": 601, "status": "published"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "studentId must be a positive integer", "data": {"field": "studentId"}},
    "business_failure": {"code": 403, "message": "student is not bound to current guardian", "data": {"student_id": 1201}}
  },
  {
    "api": "POST /mobile/education/family/messages",
    "request": {"student_id": 1201, "thread_id": "student-1201", "content": "Please check homework"},
    "success": {"code": 200, "message": "success", "data": {"message_id": 701, "status": "sent"}},
    "validation_failure": {"code": 422, "message": "content is required", "data": {"field": "content"}},
    "business_failure": {"code": 403, "message": "message thread is outside current user scope", "data": {"student_id": 1201}}
  }
]
```

## PC Admin Page Tasks

API clients:

```text
comment.ts: pageCommentTemplates, saveCommentTemplate, pagePerformanceTags, savePerformanceTag
homework.ts: pageHomeworkAssignments, saveHomeworkAssignment, publishHomeworkAssignment, pageHomeworkSubmissions
report.ts: pageLearningReports, saveLearningReport, publishLearningReport, withdrawLearningReport, pageGrowthRecords
message.ts: pageFamilyMessages, getFamilyMessageThread, sendFamilyMessage
quality.ts: getServiceQualityMetrics, getServiceQualityDashboard
```

Routes and pages:

| Route | Route name | Page | Permission | Key UI work |
| --- | --- | --- | --- | --- |
| `/education/family/comment-templates` | `EducationFamilyCommentTemplateList` | `CommentTemplateList.vue` | `education:family:comment-template:page` | template CRUD |
| `/education/family/performance-tags` | `EducationFamilyPerformanceTagList` | `PerformanceTagList.vue` | `education:family:performance-tag:page` | tag CRUD and status |
| `/education/family/homework` | `EducationFamilyHomeworkAssignmentList` | `HomeworkAssignmentList.vue` | `education:family:homework:page` | assignment form, target count, submission status |
| `/education/family/reports` | `EducationFamilyLearningReportList` | `LearningReportList.vue` | `education:family:report:page` | report editor, publish/withdraw |
| `/education/family/growth-records` | `EducationFamilyGrowthRecordList` | `GrowthRecordList.vue` | `education:family:growth:page` | student record table |
| `/education/family/messages` | `EducationFamilyMessageMonitor` | `FamilyMessageMonitor.vue` | `education:family:message:page` | thread monitor and reply |
| `/education/family/quality` | `EducationFamilyServiceQualityDashboard` | `ServiceQualityDashboard.vue` | `education:family:quality:page` | metrics and response time charts |

Required states:

```text
All V7 PC pages implement loading, empty, 403 permission, 422 validation, 409 publish-state conflict, attachment permission error, success refresh, and campus/student filters.
```

## Teacher / Guardian Mobile Page Tasks

Teacher:

```text
API: mobile-uniapp/src/api/family/teacher.ts
Pages: lesson-comments.vue, comment-form.vue, homework-reviews.vue, growth-record-form.vue, messages.vue
State: assigned lesson selector, template/tag selector, draft/publish toggle, review form, message thread, loading/empty/error/submitted states
Isolation: teacher can operate only assigned lessons/classes/students.
```

Guardian:

```text
API: mobile-uniapp/src/api/family/guardian.ts
Pages: lesson-comments.vue, homework.vue, homework-submit.vue, learning-reports.vue, growth-records.vue, messages.vue
State: selected student, published content lists, read receipts, submission form, attachment upload, message thread, withdrawn content hidden
Isolation: guardian can read and write only for bound students.
```

`pages.json`:

```text
Register V7 teacher pages under teacher role and guardian pages under guardian role with selected student requirement.
```

## Test Plan

Backend tests:

| Test file | Case | Assert |
| --- | --- | --- |
| `FamilyServiceMigrationTest.php` | `test_family_tables_indexes_and_attachment_columns_exist` | all V7 tables and indexes exist |
| `LessonCommentServiceTest.php` | `test_teacher_can_comment_assigned_lesson_student_only` | assigned succeeds; unassigned returns 403 |
| `LearningReportServiceTest.php` | `test_draft_and_withdrawn_reports_are_hidden_from_guardian` | guardian list contains only published report |
| `HomeworkServiceTest.php` | `test_homework_assignment_submission_review_flow` | target status moves assigned -> submitted -> reviewed |
| `FamilyMessageServiceTest.php` | `test_family_message_thread_is_student_scoped` | cross-student sender gets 403 |
| `GuardianFamilyMobileApiTest.php` | `test_guardian_read_creates_read_receipt_once` | one read receipt after repeated reads |
| `FamilyAdminApiTest.php` | `test_publish_validation_and_business_failures_match_catalog` | 422 and 409 match API catalog |
| `FamilyPermissionIsolationAuditTest.php` | `test_family_writes_require_permission_and_write_audit` | audit rows exist for publish/review/message |

PC tests:

```text
HomeworkAssignmentList.spec.ts asserts target count and publish state update after API success.
LearningReportList.spec.ts asserts withdrawn report status hides guardian-visible marker.
FamilyMessageMonitor.spec.ts asserts reply drawer sends thread_id and student_id.
ServiceQualityDashboard.spec.ts asserts date/campus/teacher filters are sent to quality APIs.
```

Mobile tests:

```text
teacher-family.spec.ts asserts unassigned lesson comment returns 403 state and template selection fills content.
guardian-family.spec.ts asserts withdrawn reports do not render, homework submit handles attachment failure, and read receipt API is called once.
```

## Execution Commands

Backend:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter Education\\\\Family
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
V7 migrations run successfully.
All Education\\Family tests pass.
Code style and static analysis pass.
```

PC:

```bash
cd mineadmin-education-saas/admin-web
pnpm install
pnpm lint
pnpm test -- family
pnpm build
```

Expected:

```text
Family PC lint, tests, and build pass.
```

Mobile:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm install
pnpm lint
pnpm test -- family
pnpm build:h5
```

Expected:

```text
Family mobile lint, tests, and H5 build pass.
```

## Acceptance Gate

V7 is accepted only when:

```text
- Teachers can create comments, review homework, create growth records, and reply to family messages only for assigned students.
- Guardians can view only published bound-student comments, reports, growth records, homework, and messages.
- Draft and withdrawn content is hidden from guardian mobile pages.
- Homework target, submission, and review state transitions are enforced.
- Read receipts are created once per reader/content.
- Attachment download checks parent record permissions.
- PC pages enforce permissions and state transitions.
- Service quality metrics aggregate comment, homework, report, and message data.
- All family-service write operations are audited.
```

## Task Breakdown

### Task 1: Migration, Enums, and Models

- [x] Create migration with table catalog, indexes, attachment fields, and reverse rollback.
- [x] Create enums and models listed in `File Structure`.
- [x] Write `FamilyServiceMigrationTest.php`.
- [x] Run `php bin/hyperf.php migrate`; expected output is successful creation of all V7 family tables.

### Task 2: Repositories and Services

- [x] Create repositories with tenant/campus/student/teacher/status/date filters.
- [x] Create services for comments, templates, homework, reports, growth records, messages, attachments, receipts, and metrics.
- [x] Write unit tests listed in `Test Plan`.
- [x] Run `composer test -- --filter Education\\\\Family.*ServiceTest`; expected output is all family service tests passing.

### Task 3: Requests, Schemas, Controllers, and API Tests

- [x] Create admin and mobile request classes.
- [x] Create schemas matching the API catalog.
- [x] Create admin, teacher, and guardian controllers with permissions, Result envelope, role isolation, and audit logging.
- [x] Write API, permission, isolation, read receipt, and audit tests.
- [x] Run `composer test -- --filter Education\\\\Family`; expected output is all V7 backend tests passing.

### Task 4: PC Admin

- [x] Create typed API clients.
- [x] Register family routes and menus in `education.ts`.
- [x] Create template, tag, homework, report, growth, message monitor, and quality pages.
- [x] Write PC tests listed in `Test Plan`.
- [x] Run `pnpm lint && pnpm test -- family && pnpm build`; expected output is all PC gates passing.

### Task 5: Teacher and Guardian Mobile

- [x] Create teacher and guardian family API clients.
- [x] Register V7 mobile pages in `pages.json`.
- [x] Implement teacher comment, homework review, growth record, and message pages.
- [x] Implement guardian comment, homework, report, growth, and message pages.
- [x] Write mobile tests listed in `Test Plan`.
- [x] Run `pnpm lint && pnpm test -- family && pnpm build:h5`; expected output is all mobile gates passing.

### Task 6: V7 Final Gate

- [x] Run all backend, PC, and mobile commands in `Execution Commands`.
- [x] Confirm acceptance gate behavior with seeded teacher, guardian, student, lesson, homework, report, and message data.
- [x] Update status index only after all gates pass during implementation.

## Self-Review

- Spec coverage: Covers comments, templates, tags, homework, submissions, reviews, growth records, reports, messages, receipts, attachments, quality metrics, PC, teacher mobile, and guardian mobile.
- MineAdmin fit: Uses MineAdmin 3.x backend paths, PC route/API/page conventions, and uni-app teacher/guardian pages.
- Code-level readiness: Migration fields, indexes, rollback, API failures, backend layer tasks, PC/mobile states, tests, commands, and acceptance gates are specified.
- Implementation status: V7 family service has been implemented and accepted through backend, PC, and mobile final gates.
