# MineAdmin Education SaaS V3 Admissions CRM Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V3 招生 CRM for lead sources, leads, lead assignment, follow-up, trial lessons, trial feedback, lead conversion to V1 enrollment, admission tasks, guardian consultation, and admissions dashboards.

**Architecture:** V3 creates a MineAdmin Admissions bounded module that integrates with V1 profiles, course packages, enrollments, and course accounts only through services. Lead conversion is a single backend transaction: lead -> guardian -> student -> enrollment -> account -> conversion record -> lead status.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, Redis queue, MineAdmin-Vue, Vue3, TypeScript, uni-app, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. Final backend, PC, and mobile gates passed.

---

## Scope Check

Included:

- Admission channels and lead sources.
- Lead base data, lead guardian data, and lead student data.
- Lead assignment, re-assignment, owner visibility, and campus scope.
- Follow-up records, next follow-up date, and admission tasks.
- Trial lesson scheduling, attendance, teacher feedback, and consultant feedback.
- Lead conversion to V1 official student, guardian, enrollment, and course account.
- Guardian mobile consultation entry and teacher mobile trial feedback page.
- PC lead pool, lead detail, trial calendar, conversion workbench, task list, and dashboard.

Excluded:

- V10 lead scoring, AI talk scripts, ROI optimization, and advanced growth intelligence.
- V4 payment collection after conversion.
- Public marketing automation and external message campaigns.

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_030000_create_v3_admissions_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Admissions/LeadStage.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Admissions/LeadStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Admissions/LeadAssignmentStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Admissions/TrialLessonStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Admissions/AdmissionTaskStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Admissions/LeadConversionStatus.php
mineadmin-education-saas/backend/app/Model/Education/Admissions/EducationLeadSource.php
mineadmin-education-saas/backend/app/Model/Education/Admissions/EducationLead.php
mineadmin-education-saas/backend/app/Model/Education/Admissions/EducationLeadGuardian.php
mineadmin-education-saas/backend/app/Model/Education/Admissions/EducationLeadStudent.php
mineadmin-education-saas/backend/app/Model/Education/Admissions/EducationLeadAssignment.php
mineadmin-education-saas/backend/app/Model/Education/Admissions/EducationLeadFollowRecord.php
mineadmin-education-saas/backend/app/Model/Education/Admissions/EducationTrialLesson.php
mineadmin-education-saas/backend/app/Model/Education/Admissions/EducationTrialAttendance.php
mineadmin-education-saas/backend/app/Model/Education/Admissions/EducationTrialFeedback.php
mineadmin-education-saas/backend/app/Model/Education/Admissions/EducationLeadConversionRecord.php
mineadmin-education-saas/backend/app/Model/Education/Admissions/EducationAdmissionTask.php
mineadmin-education-saas/backend/app/Model/Education/Admissions/EducationAdmissionMetricDaily.php
mineadmin-education-saas/backend/app/Repository/Education/Admissions/LeadSourceRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Admissions/LeadRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Admissions/LeadAssignmentRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Admissions/FollowRecordRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Admissions/TrialLessonRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Admissions/TrialFeedbackRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Admissions/LeadConversionRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Admissions/AdmissionTaskRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Admissions/AdmissionMetricRepository.php
mineadmin-education-saas/backend/app/Service/Education/Admissions/LeadSourceService.php
mineadmin-education-saas/backend/app/Service/Education/Admissions/LeadService.php
mineadmin-education-saas/backend/app/Service/Education/Admissions/LeadAssignmentService.php
mineadmin-education-saas/backend/app/Service/Education/Admissions/FollowRecordService.php
mineadmin-education-saas/backend/app/Service/Education/Admissions/TrialLessonService.php
mineadmin-education-saas/backend/app/Service/Education/Admissions/TrialFeedbackService.php
mineadmin-education-saas/backend/app/Service/Education/Admissions/LeadConversionService.php
mineadmin-education-saas/backend/app/Service/Education/Admissions/AdmissionTaskService.php
mineadmin-education-saas/backend/app/Service/Education/Admissions/AdmissionDashboardService.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Admissions/LeadSourceSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Admissions/LeadPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Admissions/LeadSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Admissions/LeadAssignRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Admissions/LeadFollowRecordSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Admissions/TrialLessonSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Admissions/TrialAttendanceSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Admissions/TrialFeedbackSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Admissions/LeadConvertRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Admissions/AdmissionTaskPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Admissions/AdmissionDashboardRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Admissions/GuardianConsultationRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Admissions/TeacherTrialFeedbackRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Admissions/LeadSourceController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Admissions/LeadController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Admissions/LeadAssignmentController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Admissions/FollowRecordController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Admissions/TrialLessonController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Admissions/TrialFeedbackController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Admissions/LeadConversionController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Admissions/AdmissionTaskController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Admissions/AdmissionDashboardController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Admissions/GuardianConsultationController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Admissions/TeacherTrialController.php
mineadmin-education-saas/backend/app/Schema/Education/Admissions/LeadSourceSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Admissions/LeadSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Admissions/LeadAssignmentSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Admissions/FollowRecordSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Admissions/TrialLessonSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Admissions/TrialFeedbackSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Admissions/LeadConversionSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Admissions/AdmissionTaskSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Admissions/AdmissionDashboardSchema.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Admissions/AdmissionsMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Admissions/LeadServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Admissions/LeadAssignmentServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Admissions/TrialLessonServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Admissions/LeadConversionServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Admissions/LeadAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Admissions/TrialLessonAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Admissions/LeadConversionApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Admissions/GuardianConsultationApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Admissions/TeacherTrialMobileApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Admissions/AdmissionsPermissionIsolationAuditTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/admissions/lead-source.ts
mineadmin-education-saas/admin-web/src/api/education/admissions/lead.ts
mineadmin-education-saas/admin-web/src/api/education/admissions/trial.ts
mineadmin-education-saas/admin-web/src/api/education/admissions/task.ts
mineadmin-education-saas/admin-web/src/api/education/admissions/dashboard.ts
mineadmin-education-saas/admin-web/src/views/education/admissions/LeadSourceList.vue
mineadmin-education-saas/admin-web/src/views/education/admissions/LeadPool.vue
mineadmin-education-saas/admin-web/src/views/education/admissions/LeadDetail.vue
mineadmin-education-saas/admin-web/src/views/education/admissions/TrialLessonCalendar.vue
mineadmin-education-saas/admin-web/src/views/education/admissions/TrialFeedbackList.vue
mineadmin-education-saas/admin-web/src/views/education/admissions/LeadConversionWorkbench.vue
mineadmin-education-saas/admin-web/src/views/education/admissions/AdmissionTaskList.vue
mineadmin-education-saas/admin-web/src/views/education/admissions/AdmissionDashboard.vue
mineadmin-education-saas/admin-web/src/views/education/admissions/components/LeadForm.vue
mineadmin-education-saas/admin-web/src/views/education/admissions/components/LeadAssignDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/admissions/components/FollowRecordTimeline.vue
mineadmin-education-saas/admin-web/src/views/education/admissions/components/TrialLessonForm.vue
mineadmin-education-saas/admin-web/src/views/education/admissions/components/LeadConvertForm.vue
mineadmin-education-saas/admin-web/src/views/education/admissions/__tests__/LeadPool.spec.ts
mineadmin-education-saas/admin-web/src/views/education/admissions/__tests__/LeadDetail.spec.ts
mineadmin-education-saas/admin-web/src/views/education/admissions/__tests__/TrialLessonCalendar.spec.ts
mineadmin-education-saas/admin-web/src/views/education/admissions/__tests__/LeadConversionWorkbench.spec.ts
mineadmin-education-saas/admin-web/src/views/education/admissions/__tests__/AdmissionDashboard.spec.ts
```

Modify PC:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Create mobile:

```text
mineadmin-education-saas/mobile-uniapp/src/api/admissions/guardian.ts
mineadmin-education-saas/mobile-uniapp/src/api/admissions/teacher.ts
mineadmin-education-saas/mobile-uniapp/pages/guardian/admissions/consult.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/admissions/trial-lessons.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/admissions/trial-feedback.vue
mineadmin-education-saas/mobile-uniapp/tests/admissions/guardian-consult.spec.ts
mineadmin-education-saas/mobile-uniapp/tests/admissions/teacher-trial.spec.ts
```

Modify mobile:

```text
mineadmin-education-saas/mobile-uniapp/pages.json
```

## Database Migration Design

Migration file:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_030000_create_v3_admissions_tables.php
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
deleted_at timestamp null for mutable business records
```

Foreign-key policy:

```text
Use service-level validation for V1 student, guardian, teacher, classroom, course, package, enrollment, and account references. Do not create physical foreign keys.
```

Rollback behavior:

```text
Drop tables in reverse order: edu_admission_metrics_daily, edu_admission_tasks, edu_lead_conversion_records, edu_trial_feedbacks, edu_trial_attendances, edu_trial_lessons, edu_lead_follow_records, edu_lead_assignments, edu_lead_students, edu_lead_guardians, edu_leads, edu_lead_sources.
```

Table catalog:

| Table | Business columns | Indexes |
| --- | --- | --- |
| `edu_lead_sources` | `code varchar(64) not null`, `name varchar(120) not null`, `channel_type varchar(40) not null`, `default_consultant_id bigint unsigned null`, `status varchar(20) not null default enabled`, `sort_order int not null default 0`, `remark varchar(500) null` | `unique uk_edu_lead_sources_tenant_code (tenant_id, code)`, `index idx_edu_lead_sources_status (tenant_id, campus_id, status)` |
| `edu_leads` | `lead_no varchar(64) not null`, `source_id bigint unsigned null`, `contact_name varchar(120) not null`, `contact_mobile varchar(30) not null`, `contact_wechat varchar(80) null`, `stage varchar(30) not null default new`, `status varchar(20) not null default active`, `owner_user_id bigint unsigned null`, `intention_course_id bigint unsigned null`, `intention_level varchar(20) not null default medium`, `next_follow_at timestamp null`, `last_follow_at timestamp null`, `remark varchar(500) null` | `unique uk_edu_leads_tenant_lead_no (tenant_id, lead_no)`, `index idx_edu_leads_tenant_mobile (tenant_id, contact_mobile)`, `index idx_edu_leads_stage_owner (tenant_id, campus_id, stage, owner_user_id)`, `index idx_edu_leads_next_follow (tenant_id, next_follow_at, status)` |
| `edu_lead_guardians` | `lead_id bigint unsigned not null`, `name varchar(120) not null`, `mobile varchar(30) not null`, `relation varchar(30) not null`, `wechat varchar(80) null`, `is_primary tinyint(1) not null default 0` | `index idx_edu_lead_guardians_lead (tenant_id, lead_id)`, `index idx_edu_lead_guardians_mobile (tenant_id, mobile)` |
| `edu_lead_students` | `lead_id bigint unsigned not null`, `name varchar(120) not null`, `gender varchar(20) not null default unknown`, `birthday date null`, `grade varchar(60) null`, `school varchar(120) null`, `intention_course_id bigint unsigned null` | `index idx_edu_lead_students_lead (tenant_id, lead_id)`, `index idx_edu_lead_students_name (tenant_id, name)` |
| `edu_lead_assignments` | `lead_id bigint unsigned not null`, `from_user_id bigint unsigned null`, `to_user_id bigint unsigned not null`, `status varchar(20) not null default active`, `assigned_at timestamp not null`, `reason varchar(500) null` | `index idx_edu_lead_assignments_lead (tenant_id, lead_id, status)`, `index idx_edu_lead_assignments_owner (tenant_id, to_user_id, status)` |
| `edu_lead_follow_records` | `lead_id bigint unsigned not null`, `follow_type varchar(30) not null`, `content text not null`, `next_follow_at timestamp null`, `result varchar(40) not null default continued`, `operator_user_id bigint unsigned not null` | `index idx_edu_lead_follow_records_lead_time (tenant_id, lead_id, created_at)`, `index idx_edu_lead_follow_records_operator (tenant_id, operator_user_id, created_at)` |
| `edu_trial_lessons` | `lead_id bigint unsigned not null`, `lead_student_id bigint unsigned not null`, `course_id bigint unsigned not null`, `teacher_id bigint unsigned not null`, `classroom_id bigint unsigned null`, `start_time timestamp not null`, `end_time timestamp not null`, `status varchar(20) not null default scheduled`, `consultant_user_id bigint unsigned null`, `remark varchar(500) null` | `index idx_edu_trial_lessons_time (tenant_id, campus_id, start_time, status)`, `index idx_edu_trial_lessons_teacher_time (tenant_id, teacher_id, start_time, end_time)`, `index idx_edu_trial_lessons_lead (tenant_id, lead_id)` |
| `edu_trial_attendances` | `trial_lesson_id bigint unsigned not null`, `lead_student_id bigint unsigned not null`, `attendance_status varchar(20) not null`, `checked_by bigint unsigned not null`, `checked_at timestamp not null`, `remark varchar(500) null` | `unique uk_edu_trial_attendances_lesson_student (tenant_id, trial_lesson_id, lead_student_id)`, `index idx_edu_trial_attendances_status (tenant_id, attendance_status)` |
| `edu_trial_feedbacks` | `trial_lesson_id bigint unsigned not null`, `lead_id bigint unsigned not null`, `feedback_type varchar(30) not null`, `teacher_id bigint unsigned null`, `consultant_user_id bigint unsigned null`, `score int unsigned null`, `content text not null`, `recommend_course_id bigint unsigned null` | `index idx_edu_trial_feedbacks_lesson (tenant_id, trial_lesson_id, feedback_type)`, `index idx_edu_trial_feedbacks_lead (tenant_id, lead_id)` |
| `edu_lead_conversion_records` | `lead_id bigint unsigned not null`, `student_id bigint unsigned not null`, `guardian_id bigint unsigned not null`, `enrollment_id bigint unsigned not null`, `student_course_account_id bigint unsigned not null`, `status varchar(20) not null default success`, `converted_by bigint unsigned not null`, `converted_at timestamp not null`, `payload_json json not null` | `unique uk_edu_lead_conversion_records_lead (tenant_id, lead_id)`, `index idx_edu_lead_conversion_records_student (tenant_id, student_id)` |
| `edu_admission_tasks` | `lead_id bigint unsigned null`, `task_type varchar(40) not null`, `title varchar(160) not null`, `assignee_user_id bigint unsigned not null`, `status varchar(20) not null default pending`, `due_at timestamp null`, `completed_at timestamp null`, `result varchar(500) null` | `index idx_edu_admission_tasks_owner_due (tenant_id, assignee_user_id, due_at, status)`, `index idx_edu_admission_tasks_lead (tenant_id, lead_id)` |
| `edu_admission_metrics_daily` | `metric_date date not null`, `source_id bigint unsigned null`, `consultant_user_id bigint unsigned null`, `new_leads_count int unsigned not null default 0`, `follow_count int unsigned not null default 0`, `trial_count int unsigned not null default 0`, `trial_attended_count int unsigned not null default 0`, `converted_count int unsigned not null default 0` | `unique uk_edu_admission_metrics_daily_scope (tenant_id, campus_id, metric_date, source_id, consultant_user_id)`, `index idx_edu_admission_metrics_daily_date (tenant_id, metric_date)` |

`edu_admission_tasks` is a domain source record holding admission follow-up context only. Operational lifecycle (SLA timing, escalation, overdue marking, alert conversion) is owned by V9 `edu_workflow_tasks`, which links to this record by `source_type = admission` and `source_id`. This module does not run SLA or escalation; it exposes admission task status that V9 reads and updates through this module's own service.

## MineAdmin Backend Module Design

Enums:

```text
LeadStage: new, assigned, followed, trial_scheduled, trial_done, converted, lost
LeadStatus: active, converted, lost, invalid
LeadAssignmentStatus: active, replaced, cancelled
TrialLessonStatus: scheduled, attended, absent, cancelled, converted
AdmissionTaskStatus: pending, processing, done, cancelled, overdue
LeadConversionStatus: success, failed
```

Layer tasks:

| Layer | Required implementation |
| --- | --- |
| Model | define table names, fillable, casts, enum casts, JSON casts for conversion payload, soft deletes for lead/source/task/trial tables |
| Repository | implement page filters by tenant, campus, owner, source, stage, status, date range, mobile keyword, and due date |
| Service | implement duplicate mobile check, assignment state update, follow-up creation, trial conflict check, conversion transaction, task creation, metrics aggregation |
| Request | implement validation rules for source, lead, assignment, follow record, trial lesson, attendance, feedback, conversion, task, dashboard |
| Controller | use MineAdmin annotations, auth, permission codes, request objects, Result envelope, audit writes for create/update/assign/convert |
| Schema | document request/response/failure payloads for all API groups |

Critical transaction:

```text
LeadConversionService::convert starts a transaction, locks lead row, verifies stage and duplicate conversion, creates or links V1 guardian, creates V1 student, creates V1 enrollment, creates or increments V1 course account, writes edu_lead_conversion_records, updates lead status/stage, completes related admission tasks, writes audit log, and commits. Any failure rolls back every write.
```

Risk controls:

```text
Duplicate lead checks are tenant-scoped by contact_mobile.
Trial scheduling reuses V1 teacher/classroom conflict services.
Consultant visibility is owner-based unless the user has campus manager permission.
Guardian consultation can create a lead but cannot assign owner outside campus default rules.
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
| `GET /admin/education/admissions/lead-sources/page` | `education:admissions:lead-source:page` | admin | tenant + campus | no |
| `POST /admin/education/admissions/lead-sources` | `education:admissions:lead-source:create` | admin | tenant + campus | yes |
| `GET /admin/education/admissions/leads/page` | `education:admissions:lead:page` | consultant/admin | owner or campus | no |
| `POST /admin/education/admissions/leads` | `education:admissions:lead:create` | consultant/admin | tenant + campus | yes |
| `POST /admin/education/admissions/leads/{id}/assign` | `education:admissions:lead:assign` | admissions manager | tenant + campus | yes |
| `POST /admin/education/admissions/leads/{id}/follow-records` | `education:admissions:lead:follow` | lead owner | owner or campus | yes |
| `POST /admin/education/admissions/trial-lessons` | `education:admissions:trial:create` | consultant/admin | tenant + campus | yes |
| `POST /admin/education/admissions/trial-lessons/{id}/attendance` | `education:admissions:trial:attendance` | teacher/admin | assigned teacher or campus | yes |
| `POST /admin/education/admissions/trial-feedbacks` | `education:admissions:trial-feedback:create` | teacher/consultant | assigned teacher or owner | yes |
| `POST /admin/education/admissions/leads/{id}/convert` | `education:admissions:lead:convert` | admissions manager | tenant + campus | yes |
| `GET /admin/education/admissions/dashboard/overview` | `education:admissions:dashboard:overview` | manager | tenant + campus | no |
| `POST /mobile/education/admissions/guardian/consultations` | mobile guardian/public bind | guardian | tenant + campus source | yes |
| `GET /mobile/education/admissions/teacher/trial-lessons` | mobile teacher | teacher | assigned teacher | no |
| `POST /mobile/education/admissions/teacher/trial-feedbacks` | mobile teacher | teacher | assigned teacher | yes |

Endpoint-level request/response/failure catalog:

```json
[
  {
    "api": "POST /admin/education/admissions/leads",
    "request": {"contact_name": "王女士", "contact_mobile": "13800000000", "source_id": 10, "lead_students": [{"name": "小王", "grade": "三年级"}]},
    "success": {"code": 200, "message": "success", "data": {"id": 101, "stage": "new", "status": "active"}},
    "validation_failure": {"code": 422, "message": "contact_mobile is required", "data": {"field": "contact_mobile"}},
    "business_failure": {"code": 409, "message": "lead mobile already exists", "data": {"lead_id": 99}}
  },
  {
    "api": "POST /admin/education/admissions/leads/{id}/assign",
    "request": {"to_user_id": 66, "reason": "new campus consultant"},
    "success": {"code": 200, "message": "success", "data": {"lead_id": 101, "owner_user_id": 66}},
    "validation_failure": {"code": 422, "message": "to_user_id is required", "data": {"field": "to_user_id"}},
    "business_failure": {"code": 403, "message": "consultant is outside current campus scope", "data": {"to_user_id": 66}}
  },
  {
    "api": "POST /admin/education/admissions/leads/{id}/follow-records",
    "request": {"follow_type": "phone", "content": "interested in weekend class", "next_follow_at": "2026-06-12 10:00:00"},
    "success": {"code": 200, "message": "success", "data": {"follow_record_id": 201, "lead_stage": "followed"}},
    "validation_failure": {"code": 422, "message": "content is required", "data": {"field": "content"}},
    "business_failure": {"code": 403, "message": "lead is assigned to another consultant", "data": {"lead_id": 101}}
  },
  {
    "api": "POST /admin/education/admissions/trial-lessons",
    "request": {"lead_id": 101, "lead_student_id": 301, "course_id": 501, "teacher_id": 701, "start_time": "2026-06-13 09:00:00", "end_time": "2026-06-13 10:00:00"},
    "success": {"code": 200, "message": "success", "data": {"id": 401, "status": "scheduled"}},
    "validation_failure": {"code": 422, "message": "start_time must be before end_time", "data": {"field": "start_time"}},
    "business_failure": {"code": 409, "message": "teacher time conflict", "data": {"teacher_id": 701, "conflict_lesson_id": 8801}}
  },
  {
    "api": "POST /admin/education/admissions/trial-feedbacks",
    "request": {"trial_lesson_id": 401, "feedback_type": "teacher", "score": 4, "content": "student follows instructions well"},
    "success": {"code": 200, "message": "success", "data": {"id": 501}},
    "validation_failure": {"code": 422, "message": "feedback_type has an invalid value", "data": {"field": "feedback_type"}},
    "business_failure": {"code": 403, "message": "trial lesson is not assigned to current teacher", "data": {"trial_lesson_id": 401}}
  },
  {
    "api": "POST /admin/education/admissions/leads/{id}/convert",
    "request": {"student_name": "小王", "guardian_name": "王女士", "lesson_package_id": 9001, "paid_amount": "0.00", "enrolled_at": "2026-06-13"},
    "success": {"code": 200, "message": "success", "data": {"lead_id": 101, "student_id": 1201, "guardian_id": 1301, "enrollment_id": 1401, "student_course_account_id": 1501}},
    "validation_failure": {"code": 422, "message": "lesson_package_id is required", "data": {"field": "lesson_package_id"}},
    "business_failure": {"code": 409, "message": "lead has already been converted", "data": {"lead_id": 101}}
  },
  {
    "api": "POST /mobile/education/admissions/guardian/consultations",
    "request": {"contact_name": "李女士", "contact_mobile": "13900000000", "student_name": "小李", "student_age": 8, "interested_course": "美术"},
    "success": {"code": 200, "message": "success", "data": {"lead_id": 102, "stage": "new"}},
    "validation_failure": {"code": 422, "message": "contact_mobile is required", "data": {"field": "contact_mobile"}},
    "business_failure": {"code": 409, "message": "consultation mobile already exists", "data": {"lead_id": 102}}
  },
  {
    "api": "GET /mobile/education/admissions/teacher/trial-lessons",
    "request": {"date": "2026-06-13"},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 401, "student_name": "小王", "start_time": "2026-06-13 09:00:00"}]}},
    "validation_failure": {"code": 422, "message": "date must be a valid date", "data": {"field": "date"}},
    "business_failure": {"code": 403, "message": "current user has no teacher profile", "data": {}}
  }
]
```

## PC Admin Page Tasks

API clients:

```text
lead-source.ts: pageLeadSources, createLeadSource, updateLeadSource, updateLeadSourceStatus
lead.ts: pageLeads, createLead, updateLead, assignLead, addFollowRecord, getLeadDetail, convertLead
trial.ts: pageTrialLessons, createTrialLesson, updateTrialLesson, cancelTrialLesson, saveTrialAttendance, saveTrialFeedback
task.ts: pageAdmissionTasks, completeAdmissionTask, cancelAdmissionTask
dashboard.ts: getAdmissionOverview, getAdmissionFunnel, getSourceSummary, getConsultantSummary
```

Routes and pages:

| Route | Route name | Page | Permission | Key UI work |
| --- | --- | --- | --- | --- |
| `/education/admissions/lead-sources` | `EducationAdmissionLeadSourceList` | `LeadSourceList.vue` | `education:admissions:lead-source:page` | source CRUD, status switch, default consultant |
| `/education/admissions/leads` | `EducationAdmissionLeadPool` | `LeadPool.vue` | `education:admissions:lead:page` | search by mobile/name/stage/owner/source, assign button, follow button |
| `/education/admissions/leads/:id` | `EducationAdmissionLeadDetail` | `LeadDetail.vue` | `education:admissions:lead:detail` | guardian/student tabs, timeline, trial history, conversion entry |
| `/education/admissions/trials` | `EducationAdmissionTrialCalendar` | `TrialLessonCalendar.vue` | `education:admissions:trial:page` | calendar/list switch, conflict warning, attendance action |
| `/education/admissions/conversion` | `EducationAdmissionConversionWorkbench` | `LeadConversionWorkbench.vue` | `education:admissions:lead:convert` | V1 package selector, transaction result, rollback error display |
| `/education/admissions/tasks` | `EducationAdmissionTaskList` | `AdmissionTaskList.vue` | `education:admissions:task:page` | due task table, complete/cancel buttons |
| `/education/admissions/dashboard` | `EducationAdmissionDashboard` | `AdmissionDashboard.vue` | `education:admissions:dashboard:overview` | funnel, source summary, consultant summary, date filter |

Required states:

```text
All pages implement loading, empty, 403 permission, 422 validation, 409 duplicate/conflict, success refresh, and campus filter lock states.
```

## Teacher / Guardian Mobile Page Tasks

Teacher:

```text
API: mobile-uniapp/src/api/admissions/teacher.ts
Pages: pages/teacher/admissions/trial-lessons.vue, pages/teacher/admissions/trial-feedback.vue
State: date selector, assigned trial list, detail, feedback form, submitted state, 403 current user has no teacher profile
Isolation: teacher can read and submit feedback only for trial lessons assigned to current teacher profile.
```

Guardian:

```text
API: mobile-uniapp/src/api/admissions/guardian.ts
Page: pages/guardian/admissions/consult.vue
State: form, submitting, duplicate mobile message, success result
Isolation: guardian consultation writes into current tenant/campus source context and does not expose other leads.
```

`pages.json`:

```text
Register teacher trial pages under teacher role and guardian consultation page under guardian role.
```

## Test Plan

Backend tests:

| Test file | Case | Assert |
| --- | --- | --- |
| `AdmissionsMigrationTest.php` | `test_tables_and_indexes_exist` | all V3 tables, unique keys, tenant/campus fields exist |
| `LeadServiceTest.php` | `test_duplicate_mobile_is_tenant_scoped` | same mobile conflicts in same tenant and is allowed in another tenant |
| `LeadAssignmentServiceTest.php` | `test_assignment_replaces_previous_active_assignment` | old assignment status becomes replaced and lead owner changes |
| `TrialLessonServiceTest.php` | `test_trial_rejects_teacher_and_classroom_conflict` | 409 conflict details include teacher/classroom id |
| `LeadConversionServiceTest.php` | `test_convert_lead_creates_v1_records_in_transaction` | student, guardian, enrollment, account, conversion record, lead status exist |
| `LeadConversionServiceTest.php` | `test_conversion_rolls_back_when_package_disabled` | no V1 records or conversion record remain |
| `LeadAdminApiTest.php` | `test_lead_api_validation_and_business_failures_match_catalog` | 422 and 409 payloads match catalog |
| `GuardianConsultationApiTest.php` | `test_guardian_consultation_creates_lead` | lead source and student rows created |
| `TeacherTrialMobileApiTest.php` | `test_teacher_can_submit_only_assigned_trial_feedback` | assigned returns 200; unassigned returns 403 |
| `AdmissionsPermissionIsolationAuditTest.php` | `test_assignment_and_conversion_require_permissions_and_write_audit` | denied without permission; audit exists with allowed user |

PC tests:

```text
LeadPool.spec.ts asserts assignment button hides without permission and duplicate mobile error is rendered.
LeadDetail.spec.ts asserts follow timeline refreshes after save.
TrialLessonCalendar.spec.ts asserts conflict response displays conflict object and keeps form open.
LeadConversionWorkbench.spec.ts asserts conversion success links to V1 student and enrollment ids.
AdmissionDashboard.spec.ts asserts campus/date filters are passed to dashboard APIs.
```

Mobile tests:

```text
guardian-consult.spec.ts asserts required mobile validation and duplicate lead display.
teacher-trial.spec.ts asserts assigned trial lessons render and unassigned feedback returns 403 state.
```

## Execution Commands

Backend:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter Education\\\\Admissions
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
V3 migrations run successfully.
All Education\\Admissions tests pass.
Code style and static analysis pass.
```

PC:

```bash
cd mineadmin-education-saas/admin-web
pnpm install
pnpm lint
pnpm test -- admissions
pnpm build
```

Expected:

```text
Admissions PC lint, tests, and build pass.
```

Mobile:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm install
pnpm lint
pnpm test -- admissions
pnpm build:h5
```

Expected:

```text
Admissions mobile lint, tests, and H5 build pass.
```

## Acceptance Gate

V3 is accepted only when:

```text
- Leads can be created, deduplicated, assigned, followed, scheduled for trial, and converted.
- Trial lessons reject teacher and classroom conflicts.
- Teacher can submit feedback only for assigned trial lessons.
- Guardian consultation creates a lead without exposing lead pool data.
- Conversion is atomic and rolls back on any V1 enrollment/account failure.
- PC pages enforce permissions and preserve form state on validation/business failures.
- Admissions dashboard respects tenant, campus, owner, source, and date filters.
- All create, assign, follow, trial, feedback, and convert writes are audited.
```

## Task Breakdown

### Task 1: Migration, Enums, and Models

- [x] Create migration with the exact table catalog, indexes, and rollback behavior.
- [x] Create enums and models listed in `File Structure`.
- [x] Write `AdmissionsMigrationTest.php`.
- [x] Run `php bin/hyperf.php migrate`; expected output is successful creation of all V3 tables.

### Task 2: Repositories and Services

- [x] Create repositories with tenant/campus/owner filters and lock methods for lead conversion.
- [x] Create services for lead CRUD, assignment, follow-up, trial, conversion, tasks, and metrics.
- [x] Write unit tests for duplicate, assignment, conflict, conversion, and rollback cases.
- [x] Run `composer test -- --filter Education\\\\Admissions.*ServiceTest`; expected output is all service tests passing.

### Task 3: Requests, Schemas, Controllers, and API Tests

- [x] Create request classes with the validation rules implied by the API catalog.
- [x] Create schemas for page, save, action, conversion, dashboard, mobile consultation, and trial feedback payloads.
- [x] Create admin and mobile controllers with permissions, Result envelope, and audit logging.
- [x] Write feature tests for API catalog, permission, isolation, mobile access, and audit.
- [x] Run `composer test -- --filter Education\\\\Admissions`; expected output is all V3 backend tests passing.

### Task 4: PC Admin

- [x] Create typed API clients.
- [x] Register routes and menus in `education.ts`.
- [x] Create lead source, lead pool, detail, trial, conversion, task, and dashboard pages with required states.
- [x] Write PC tests listed in `Test Plan`.
- [x] Run `pnpm lint && pnpm test -- admissions && pnpm build`; expected output is all PC gates passing.

### Task 5: Mobile

- [x] Create guardian and teacher admissions API clients.
- [x] Register mobile pages in `pages.json`.
- [x] Implement guardian consultation and teacher trial feedback pages with role isolation.
- [x] Write mobile tests listed in `Test Plan`.
- [x] Run `pnpm lint && pnpm test -- admissions && pnpm build:h5`; expected output is all mobile gates passing.

### Task 6: V3 Final Gate

- [x] Run all backend, PC, and mobile commands in `Execution Commands`.
- [x] Confirm acceptance gate behavior against seeded automated fixtures and demo-style test data.
- [x] Update status index only after all gates pass during implementation.

## Self-Review

- Spec coverage: Covers lead sources, leads, guardians/students, assignment, follow records, trial lessons, attendance, feedback, conversion, tasks, metrics, mobile consultation, and dashboards.
- MineAdmin fit: Uses MineAdmin 3.x backend paths, PC route/API/page conventions, and uni-app mobile context.
- Code-level readiness: Database fields, indexes, rollback, backend layers, API failures, PC/mobile states, tests, commands, and acceptance gates are specified.
- Implementation status: Implemented and accepted after backend, PC, and mobile gates passed.
