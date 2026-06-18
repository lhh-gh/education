# MineAdmin Education SaaS V2 Academic Operations Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V2 教务运营增强 for lesson changes, leave/make-up closure, consumption review and reversal, renewal alerts, teacher workload records, and operation dashboards.

**Architecture:** V2 extends V1 Academic records and reuses Foundation tenant, campus, permission, audit, PC, and mobile context services. Backend follows MineAdmin 3.x paths under `app/Http/Admin`, `app/Http/Api`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, and `databases/migrations`; PC pages live in `admin-web`; teacher and guardian pages live in `mobile-uniapp`.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, Redis queue, MineAdmin-Vue, Vue3, TypeScript, uni-app, pnpm, PHPUnit/co-phpunit.

**Status:** ready

**Completion:** implemented / accepted. Final backend, PC, and mobile gates passed.

---

## Scope Check

Included:

- Lesson change center for reschedule, suspend, cancel, teacher replacement, classroom replacement, substitute teacher, and batch change.
- Leave and make-up closure on top of V1 leave requests and V1 lessons.
- Consumption review mode and consumption adjustment/reversal records.
- Renewal alerts, renewal tasks, and student follow-up records.
- Teacher workload records for main teacher and substitute teacher workloads.
- Daily operation metrics and PC operation dashboards.
- Teacher mobile changed lesson list, make-up attendance, and leave/make-up notifications.
- Guardian mobile make-up entitlement, make-up record, changed lesson, and renewal reminder views.

Excluded:

- Payroll settlement and salary calculation; V5 owns them.
- Online payment, refund money movement, and receipts; V4 owns them.
- Admissions lead conversion; V3 owns it.
- Workflow automation rule engine; V9 owns it.

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_020000_create_v2_academic_operation_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Operations/LessonChangeStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Operations/LessonChangeType.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Operations/MakeupEntitlementStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Operations/ConsumptionReviewStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Operations/RenewalAlertStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Operations/RenewalTaskStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Operations/TeacherWorkloadType.php
mineadmin-education-saas/backend/app/Model/Education/Operations/EducationLessonChangeRequest.php
mineadmin-education-saas/backend/app/Model/Education/Operations/EducationLessonChangeLog.php
mineadmin-education-saas/backend/app/Model/Education/Operations/EducationMakeupEntitlement.php
mineadmin-education-saas/backend/app/Model/Education/Operations/EducationMakeupRecord.php
mineadmin-education-saas/backend/app/Model/Education/Operations/EducationLessonConsumptionReview.php
mineadmin-education-saas/backend/app/Model/Education/Operations/EducationLessonConsumptionAdjustment.php
mineadmin-education-saas/backend/app/Model/Education/Operations/EducationRenewalAlert.php
mineadmin-education-saas/backend/app/Model/Education/Operations/EducationRenewalTask.php
mineadmin-education-saas/backend/app/Model/Education/Operations/EducationStudentFollowRecord.php
mineadmin-education-saas/backend/app/Model/Education/Operations/EducationTeacherWorkloadRecord.php
mineadmin-education-saas/backend/app/Model/Education/Operations/EducationDailyOperationMetric.php
mineadmin-education-saas/backend/app/Repository/Education/Operations/LessonChangeRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Operations/MakeupEntitlementRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Operations/MakeupRecordRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Operations/ConsumptionReviewRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Operations/ConsumptionAdjustmentRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Operations/RenewalAlertRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Operations/RenewalTaskRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Operations/StudentFollowRecordRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Operations/TeacherWorkloadRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Operations/DailyOperationMetricRepository.php
mineadmin-education-saas/backend/app/Service/Education/Operations/LessonChangeService.php
mineadmin-education-saas/backend/app/Service/Education/Operations/MakeupService.php
mineadmin-education-saas/backend/app/Service/Education/Operations/ConsumptionReviewService.php
mineadmin-education-saas/backend/app/Service/Education/Operations/RenewalAlertService.php
mineadmin-education-saas/backend/app/Service/Education/Operations/TeacherWorkloadService.php
mineadmin-education-saas/backend/app/Service/Education/Operations/OperationDashboardService.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Operations/LessonChangePageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Operations/LessonChangeCreateRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Operations/LessonChangeReviewRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Operations/LessonBatchChangeRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Operations/MakeupEntitlementPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Operations/MakeupArrangeRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Operations/ConsumptionReviewPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Operations/ConsumptionReviewActionRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Operations/ConsumptionAdjustmentRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Operations/RenewalAlertPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Operations/RenewalTaskAssignRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Operations/StudentFollowRecordSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Operations/TeacherWorkloadPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Operations/OperationDashboardRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Operations/GuardianMakeupPageRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Operations/TeacherMakeupAttendanceRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Operations/LessonChangeController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Operations/MakeupController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Operations/ConsumptionReviewController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Operations/RenewalAlertController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Operations/TeacherWorkloadController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Operations/OperationDashboardController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Operations/TeacherOperationController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Operations/GuardianOperationController.php
mineadmin-education-saas/backend/app/Schema/Education/Operations/LessonChangeSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Operations/MakeupSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Operations/ConsumptionReviewSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Operations/RenewalSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Operations/TeacherWorkloadSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Operations/OperationDashboardSchema.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Operations/AcademicOperationMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Operations/LessonChangeServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Operations/MakeupServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Operations/ConsumptionReviewServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Operations/RenewalAlertServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Operations/TeacherWorkloadServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Operations/LessonChangeAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Operations/MakeupAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Operations/ConsumptionReviewAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Operations/RenewalAlertAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Operations/TeacherOperationMobileApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Operations/GuardianOperationMobileApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Operations/OperationPermissionIsolationAuditTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/operations/lesson-change.ts
mineadmin-education-saas/admin-web/src/api/education/operations/makeup.ts
mineadmin-education-saas/admin-web/src/api/education/operations/consumption-review.ts
mineadmin-education-saas/admin-web/src/api/education/operations/renewal.ts
mineadmin-education-saas/admin-web/src/api/education/operations/workload.ts
mineadmin-education-saas/admin-web/src/api/education/operations/dashboard.ts
mineadmin-education-saas/admin-web/src/views/education/operations/LessonChangeCenter.vue
mineadmin-education-saas/admin-web/src/views/education/operations/LeaveMakeupList.vue
mineadmin-education-saas/admin-web/src/views/education/operations/ConsumptionReviewList.vue
mineadmin-education-saas/admin-web/src/views/education/operations/RenewalAlertList.vue
mineadmin-education-saas/admin-web/src/views/education/operations/TeacherWorkloadReport.vue
mineadmin-education-saas/admin-web/src/views/education/operations/OperationDashboard.vue
mineadmin-education-saas/admin-web/src/views/education/operations/components/LessonChangeForm.vue
mineadmin-education-saas/admin-web/src/views/education/operations/components/LessonChangeReviewDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/operations/components/MakeupArrangeForm.vue
mineadmin-education-saas/admin-web/src/views/education/operations/components/ConsumptionReviewDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/operations/components/RenewalFollowDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/operations/__tests__/LessonChangeCenter.spec.ts
mineadmin-education-saas/admin-web/src/views/education/operations/__tests__/LeaveMakeupList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/operations/__tests__/ConsumptionReviewList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/operations/__tests__/RenewalAlertList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/operations/__tests__/TeacherWorkloadReport.spec.ts
mineadmin-education-saas/admin-web/src/views/education/operations/__tests__/OperationDashboard.spec.ts
```

Modify PC:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Create mobile:

```text
mineadmin-education-saas/mobile-uniapp/src/api/operations/teacher.ts
mineadmin-education-saas/mobile-uniapp/src/api/operations/guardian.ts
mineadmin-education-saas/mobile-uniapp/pages/teacher/operations/changed-lessons.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/operations/makeup-attendance.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/operations/workload-summary.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/operations/changed-lessons.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/operations/makeup-entitlements.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/operations/makeup-records.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/operations/renewal-alerts.vue
mineadmin-education-saas/mobile-uniapp/tests/operations/teacher-operations.spec.ts
mineadmin-education-saas/mobile-uniapp/tests/operations/guardian-operations.spec.ts
```

Modify mobile:

```text
mineadmin-education-saas/mobile-uniapp/pages.json
```

## Database Migration Design

Migration file:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_020000_create_v2_academic_operation_tables.php
```

Foreign-key policy:

```text
Use no physical foreign keys. Validate referenced V1 records through services so MineAdmin migrations remain deployable across managed MySQL and tenant-sharded deployments.
```

Rollback behavior:

```text
Drop tables in reverse order: edu_daily_operation_metrics, edu_teacher_workload_records, edu_student_follow_records, edu_renewal_tasks, edu_renewal_alerts, edu_lesson_consumption_adjustments, edu_lesson_consumption_reviews, edu_makeup_records, edu_makeup_entitlements, edu_lesson_change_logs, edu_lesson_change_requests.
```

Shared columns:

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Record id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | yes | null | Campus id, null only for tenant-wide settings or logs derived through source records |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time where the table is mutable |

### `edu_lesson_change_requests`

Business columns:

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `lesson_id` | bigint unsigned | no | none | V1 lesson id |
| `change_type` | varchar(30) | no | none | reschedule, suspend, cancel, replace_teacher, replace_classroom, substitute_teacher |
| `status` | varchar(20) | no | `pending` | pending, approved, rejected, applied, cancelled |
| `old_values_json` | json | no | none | Snapshot before change |
| `new_values_json` | json | no | none | Proposed values |
| `reason` | varchar(500) | no | none | Change reason |
| `requested_by` | bigint unsigned | no | none | Request user id |
| `approved_by` | bigint unsigned | yes | null | Reviewer user id |
| `approved_at` | timestamp | yes | null | Review time |
| `applied_at` | timestamp | yes | null | Apply time |

Indexes:

```text
index idx_edu_lesson_change_requests_tenant_lesson_status (tenant_id, lesson_id, status)
index idx_edu_lesson_change_requests_campus_status (tenant_id, campus_id, status)
index idx_edu_lesson_change_requests_requested_by (tenant_id, requested_by, created_at)
index idx_edu_lesson_change_requests_deleted_at (deleted_at)
```

### `edu_lesson_change_logs`

Business columns:

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `lesson_id` | bigint unsigned | no | none | V1 lesson id |
| `change_request_id` | bigint unsigned | yes | null | Change request id |
| `change_type` | varchar(30) | no | none | Change type |
| `before_json` | json | no | none | Before snapshot |
| `after_json` | json | no | none | After snapshot |
| `operator_id` | bigint unsigned | no | none | Operator user id |

Indexes:

```text
index idx_edu_lesson_change_logs_tenant_lesson (tenant_id, lesson_id, created_at)
index idx_edu_lesson_change_logs_request (tenant_id, change_request_id)
```

### `edu_makeup_entitlements`

Business columns:

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `student_id` | bigint unsigned | no | none | V1 student id |
| `course_id` | bigint unsigned | no | none | V1 course id |
| `source_lesson_id` | bigint unsigned | no | none | Leave source lesson id |
| `source_leave_request_id` | bigint unsigned | no | none | V1 leave request id |
| `status` | varchar(20) | no | `available` | available, used, expired, cancelled |
| `expires_at` | timestamp | yes | null | Expire time |
| `used_lesson_id` | bigint unsigned | yes | null | Make-up lesson id |
| `used_at` | timestamp | yes | null | Used time |

Indexes:

```text
unique uk_edu_makeup_entitlements_leave_student (tenant_id, source_leave_request_id, student_id)
index idx_edu_makeup_entitlements_student_status (tenant_id, student_id, status)
index idx_edu_makeup_entitlements_expire (tenant_id, expires_at, status)
index idx_edu_makeup_entitlements_deleted_at (deleted_at)
```

### `edu_makeup_records`

Business columns:

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `makeup_entitlement_id` | bigint unsigned | no | none | Entitlement id |
| `student_id` | bigint unsigned | no | none | V1 student id |
| `makeup_lesson_id` | bigint unsigned | no | none | V1 lesson id used for make-up |
| `status` | varchar(20) | no | `arranged` | arranged, completed, cancelled |
| `arranged_by` | bigint unsigned | no | none | Arranger user id |
| `arranged_at` | timestamp | no | none | Arrange time |
| `completed_at` | timestamp | yes | null | Completion time |

Indexes:

```text
unique uk_edu_makeup_records_entitlement_active (tenant_id, makeup_entitlement_id, status)
index idx_edu_makeup_records_student_status (tenant_id, student_id, status)
index idx_edu_makeup_records_lesson (tenant_id, makeup_lesson_id)
index idx_edu_makeup_records_deleted_at (deleted_at)
```

### `edu_lesson_consumption_reviews`

Business columns:

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `lesson_id` | bigint unsigned | no | none | V1 lesson id |
| `status` | varchar(20) | no | `pending` | pending, approved, rejected, cancelled |
| `submitted_by` | bigint unsigned | no | none | Submitter user id |
| `submitted_at` | timestamp | no | none | Submit time |
| `reviewed_by` | bigint unsigned | yes | null | Reviewer user id |
| `reviewed_at` | timestamp | yes | null | Review time |
| `review_note` | varchar(500) | yes | null | Review note |

Indexes:

```text
unique uk_edu_lesson_consumption_reviews_lesson (tenant_id, lesson_id)
index idx_edu_lesson_consumption_reviews_tenant_status (tenant_id, campus_id, status)
index idx_edu_lesson_consumption_reviews_submitter (tenant_id, submitted_by, submitted_at)
index idx_edu_lesson_consumption_reviews_deleted_at (deleted_at)
```

### `edu_lesson_consumption_adjustments`

Business columns:

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `original_consumption_id` | bigint unsigned | no | none | Original V1 consumption id |
| `adjustment_consumption_id` | bigint unsigned | yes | null | Reverse or supplement consumption id |
| `student_id` | bigint unsigned | no | none | V1 student id |
| `student_course_account_id` | bigint unsigned | no | none | V1 course account id |
| `credits` | decimal(10,2) | no | `0.00` | Positive or negative adjustment credits |
| `reason` | varchar(500) | no | none | Adjustment reason |

Indexes:

```text
index idx_edu_lesson_consumption_adjustments_original (tenant_id, original_consumption_id)
index idx_edu_lesson_consumption_adjustments_student (tenant_id, student_id, created_at)
index idx_edu_lesson_consumption_adjustments_account (tenant_id, student_course_account_id, created_at)
```

### `edu_renewal_alerts`

Business columns:

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `student_id` | bigint unsigned | no | none | V1 student id |
| `course_id` | bigint unsigned | no | none | V1 course id |
| `student_course_account_id` | bigint unsigned | no | none | V1 course account id |
| `alert_type` | varchar(30) | no | none | low_balance, expire_soon, expired |
| `alert_level` | varchar(20) | no | `normal` | normal, warning, urgent |
| `status` | varchar(20) | no | `open` | open, converted, ignored, closed |
| `trigger_value` | varchar(60) | no | none | Actual value causing alert |
| `threshold_value` | varchar(60) | no | none | Configured threshold |
| `due_date` | date | yes | null | Suggested follow date |

Indexes:

```text
unique uk_edu_renewal_alerts_open_account_type (tenant_id, student_course_account_id, alert_type, status)
index idx_edu_renewal_alerts_tenant_due (tenant_id, campus_id, due_date, status)
index idx_edu_renewal_alerts_student_status (tenant_id, student_id, status)
index idx_edu_renewal_alerts_deleted_at (deleted_at)
```

### `edu_renewal_tasks`

Business columns:

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `student_id` | bigint unsigned | no | none | V1 student id |
| `course_id` | bigint unsigned | no | none | V1 course id |
| `renewal_alert_id` | bigint unsigned | no | none | Renewal alert id |
| `assignee_id` | bigint unsigned | yes | null | Follow-up owner user id |
| `status` | varchar(20) | no | `pending` | pending, following, done, closed |
| `next_follow_at` | timestamp | yes | null | Next follow-up time |
| `result` | varchar(500) | yes | null | Latest result |

Indexes:

```text
index idx_edu_renewal_tasks_assignee_status (tenant_id, assignee_id, status, next_follow_at)
index idx_edu_renewal_tasks_alert (tenant_id, renewal_alert_id)
index idx_edu_renewal_tasks_student (tenant_id, student_id, status)
index idx_edu_renewal_tasks_deleted_at (deleted_at)
```

`edu_renewal_tasks` is a domain source record holding renewal follow-up context only. Operational lifecycle (SLA timing, escalation, overdue marking, alert conversion) is owned by V9 `edu_workflow_tasks`, which links to this record by `source_type = renewal` and `source_id`. This module does not run SLA or escalation; it exposes renewal status that V9 reads and updates through this module's own service.

### `edu_student_follow_records`

Business columns:

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `student_id` | bigint unsigned | no | none | V1 student id |
| `renewal_task_id` | bigint unsigned | yes | null | Renewal task id |
| `follow_type` | varchar(30) | no | none | phone, wechat, offline, system |
| `content` | text | no | none | Follow-up content |
| `next_follow_at` | timestamp | yes | null | Next follow time |

Indexes:

```text
index idx_edu_student_follow_records_student_time (tenant_id, student_id, created_at)
index idx_edu_student_follow_records_task_time (tenant_id, renewal_task_id, created_at)
```

### `edu_teacher_workload_records`

Business columns:

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `teacher_id` | bigint unsigned | no | none | V1 teacher id |
| `lesson_id` | bigint unsigned | no | none | V1 lesson id |
| `workload_type` | varchar(30) | no | none | main, substitute, makeup, trial_support |
| `lesson_type` | varchar(30) | no | `normal` | normal, makeup, trial |
| `credits` | decimal(10,2) | no | `0.00` | Workload credits |
| `student_count` | int unsigned | no | 0 | Lesson student count |
| `present_count` | int unsigned | no | 0 | Present count |
| `leave_count` | int unsigned | no | 0 | Leave count |
| `absent_count` | int unsigned | no | 0 | Absent count |
| `recorded_at` | timestamp | no | none | Workload record time |

Indexes:

```text
unique uk_edu_teacher_workload_records_lesson_teacher_type (tenant_id, lesson_id, teacher_id, workload_type)
index idx_edu_teacher_workload_records_teacher_date (tenant_id, campus_id, teacher_id, recorded_at)
index idx_edu_teacher_workload_records_lesson (tenant_id, lesson_id)
```

### `edu_daily_operation_metrics`

Business columns:

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `metric_date` | date | no | none | Metric date |
| `lessons_count` | int unsigned | no | 0 | Lessons count |
| `pending_attendance_count` | int unsigned | no | 0 | Pending attendance lessons |
| `consumed_credits` | decimal(12,2) | no | `0.00` | Consumed credits |
| `present_count` | int unsigned | no | 0 | Present count |
| `leave_count` | int unsigned | no | 0 | Leave count |
| `absent_count` | int unsigned | no | 0 | Absent count |
| `renewal_alert_count` | int unsigned | no | 0 | Open renewal alert count |
| `pending_review_count` | int unsigned | no | 0 | Pending consumption review count |

Indexes:

```text
unique uk_edu_daily_operation_metrics_campus_date (tenant_id, campus_id, metric_date)
index idx_edu_daily_operation_metrics_tenant_date (tenant_id, metric_date)
```

## MineAdmin Backend Module Design

Enums:

```text
LessonChangeType: reschedule, suspend, cancel, replace_teacher, replace_classroom, substitute_teacher
LessonChangeStatus: pending, approved, rejected, applied, cancelled
MakeupEntitlementStatus: available, used, expired, cancelled
ConsumptionReviewStatus: pending, approved, rejected, cancelled
RenewalAlertStatus: open, converted, ignored, closed
RenewalTaskStatus: pending, following, done, closed
TeacherWorkloadType: main, substitute, makeup, trial_support
```

Model rules:

```text
All models define table name, fillable fields, casts for JSON/timestamps/decimal, SoftDeletes only for mutable business records, and tenant/campus scoped query helpers.
```

Repository tasks:

| Repository | Required methods |
| --- | --- |
| `LessonChangeRepository` | `pageByCampusScope`, `findPendingByLesson`, `lockById`, `createRequest`, `writeLog` |
| `MakeupEntitlementRepository` | `pageAvailable`, `lockEntitlement`, `createFromLeave`, `markUsed`, `restoreAvailable`, `markExpired` |
| `ConsumptionReviewRepository` | `pagePending`, `lockReview`, `createFromAttendance`, `markApproved`, `markRejected` |
| `ConsumptionAdjustmentRepository` | `pageByConsumption`, `createReverseRecord`, `sumAdjustedCredits` |
| `RenewalAlertRepository` | `pageOpen`, `findOpenAlert`, `createAlert`, `markIgnored`, `markConverted`, `markClosed` |
| `RenewalTaskRepository` | `pageMineOrCampus`, `assign`, `markFollowing`, `markDone` |
| `StudentFollowRecordRepository` | `pageByStudent`, `createRecord` |
| `TeacherWorkloadRepository` | `createOrUpdateLessonTeacherRecord`, `summaryByTeacher`, `summaryByCampus` |
| `DailyOperationMetricRepository` | `upsertDailyMetric`, `trend`, `dashboardSummary` |

Service tasks:

| Service | Code-level responsibility |
| --- | --- |
| `LessonChangeService` | Validate V1 lesson status, teacher/classroom/student conflicts, create request, approve/reject, apply changed lesson snapshot, write change logs, dispatch notifications |
| `MakeupService` | Create entitlement once after approved leave, arrange make-up lesson, cancel arrangement, complete entitlement after make-up attendance, restore entitlement when make-up lesson is cancelled |
| `ConsumptionReviewService` | Create pending review from attendance in review mode, approve to call V1 consumption service, reject to unlock attendance resubmission, create reversal through adjustment ledger |
| `RenewalAlertService` | Scan V1 course accounts by low balance and expiry thresholds, create one open alert per account/type, assign tasks, save follow-up, close alerts after renewal |
| `TeacherWorkloadService` | Write main/substitute/make-up workload after attendance or lesson change, keep historical main teacher snapshot, expose summaries |
| `OperationDashboardService` | Aggregate daily metrics from lessons, attendance, reviews, alerts, consumption, and workload records |

Request validation:

| Request | Rules |
| --- | --- |
| `LessonChangeCreateRequest` | `lesson_id` required integer, `change_type` in enum, `new_values_json` required array, `reason` required max 500 |
| `LessonChangeReviewRequest` | `action` required in approve/reject, `review_note` max 500 |
| `LessonBatchChangeRequest` | `lesson_ids` required array min 1 max 100, `change_type` in suspend/cancel/replace_teacher/replace_classroom, `reason` required |
| `MakeupArrangeRequest` | `makeup_entitlement_id` required integer, `makeup_lesson_id` required integer, `arranged_at` required date |
| `ConsumptionReviewActionRequest` | `action` required in approve/reject, `review_note` max 500 |
| `ConsumptionAdjustmentRequest` | `original_consumption_id` required integer, `credits` required decimal, `reason` required max 500 |
| `RenewalTaskAssignRequest` | `renewal_alert_id` required integer, `assignee_id` required integer, `next_follow_at` nullable date |
| `StudentFollowRecordSaveRequest` | `student_id` required integer, `follow_type` in phone/wechat/offline/system, `content` required, `next_follow_at` nullable date |

Controller tasks:

```text
Admin controllers use #[Controller], #[Auth], #[Permission], request objects, Result envelope, TenantContext, CampusScopeService, and AuditLogger for writes.
Api controllers use mobile auth context from F06 and enforce teacher/guardian identity before calling services.
```

Schema tasks:

```text
Create schemas for page query, save payload, action payload, and response resources for LessonChange, Makeup, ConsumptionReview, Renewal, TeacherWorkload, and OperationDashboard. Schemas must match the API contract examples below.
```

Audit rules:

```text
Write audit actions: education.operations.lesson_change.created, approved, rejected, applied, batch_changed; education.operations.makeup.arranged, cancelled, completed; education.operations.consumption_review.approved, rejected, adjusted; education.operations.renewal_task.assigned, followed, closed.
```

## API Contract

Common headers:

```text
Authorization: Bearer <token>
X-Tenant-Id: <tenant id>
X-Campus-Id: <campus id, required for campus-scoped admin pages>
```

Endpoint matrix:

| API | Permission | Caller | Isolation | Audit |
| --- | --- | --- | --- | --- |
| `GET /admin/education/operations/lesson-change-requests/page` | `education:operations:lesson-change:page` | tenant admin | tenant + campus scope | no |
| `POST /admin/education/operations/lesson-change-requests` | `education:operations:lesson-change:create` | academic admin | tenant + campus scope | yes |
| `POST /admin/education/operations/lesson-change-requests/{id}/approve` | `education:operations:lesson-change:approve` | academic supervisor | tenant + campus scope | yes |
| `POST /admin/education/operations/lesson-change-requests/{id}/reject` | `education:operations:lesson-change:reject` | academic supervisor | tenant + campus scope | yes |
| `POST /admin/education/operations/lesson-change-requests/{id}/apply` | `education:operations:lesson-change:apply` | academic supervisor | tenant + campus scope | yes |
| `POST /admin/education/operations/lessons/batch-change` | `education:operations:lesson-change:batch` | academic supervisor | tenant + campus scope | yes |
| `GET /admin/education/operations/makeup-entitlements/page` | `education:operations:makeup:page` | academic admin | tenant + campus scope | no |
| `POST /admin/education/operations/makeup-entitlements/{id}/arrange` | `education:operations:makeup:arrange` | academic admin | tenant + campus scope | yes |
| `POST /admin/education/operations/makeup-records/{id}/cancel` | `education:operations:makeup:cancel` | academic admin | tenant + campus scope | yes |
| `GET /admin/education/operations/consumption-reviews/page` | `education:operations:consumption-review:page` | academic admin | tenant + campus scope | no |
| `POST /admin/education/operations/consumption-reviews/{id}/approve` | `education:operations:consumption-review:approve` | academic supervisor | tenant + campus scope | yes |
| `POST /admin/education/operations/lesson-consumptions/{id}/adjust` | `education:operations:consumption-adjustment:create` | academic supervisor | tenant + campus scope | yes |
| `GET /admin/education/operations/renewal-alerts/page` | `education:operations:renewal-alert:page` | academic/consultant | tenant + campus scope | no |
| `POST /admin/education/operations/renewal-tasks/{id}/follow` | `education:operations:renewal-task:follow` | consultant | tenant + assignee/campus scope | yes |
| `GET /admin/education/operations/reports/teacher-workloads` | `education:operations:teacher-workload:report` | tenant admin | tenant + campus scope | no |
| `GET /admin/education/operations/dashboard/overview` | `education:operations:dashboard:overview` | tenant admin | tenant + campus scope | no |
| `GET /mobile/education/operations/teacher/changed-lessons` | mobile teacher | teacher | assigned teacher only | no |
| `POST /mobile/education/operations/teacher/makeup-attendance` | mobile teacher | teacher | assigned teacher only | yes |
| `GET /mobile/education/operations/guardian/makeup-entitlements` | mobile guardian | guardian | bound students only | no |
| `GET /mobile/education/operations/guardian/renewal-alerts` | mobile guardian | guardian | bound students only | no |

Endpoint-level request/response/failure catalog:

```json
[
  {
    "api": "POST /admin/education/operations/lesson-change-requests",
    "request": {"lesson_id": 8801, "change_type": "reschedule", "new_values_json": {"start_time": "2026-06-11 19:00:00", "end_time": "2026-06-11 20:30:00"}, "reason": "teacher training"},
    "success": {"code": 200, "message": "success", "data": {"id": 3001, "status": "pending"}},
    "validation_failure": {"code": 422, "message": "change_type has an invalid value", "data": {"field": "change_type"}},
    "business_failure": {"code": 409, "message": "lesson has been consumed and cannot be changed", "data": {"lesson_id": 8801}}
  },
  {
    "api": "POST /admin/education/operations/lesson-change-requests/{id}/approve",
    "request": {"review_note": "approved"},
    "success": {"code": 200, "message": "success", "data": {"id": 3001, "status": "approved"}},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 409, "message": "classroom time conflict", "data": {"classroom_id": 11, "conflict_lesson_id": 8809}}
  },
  {
    "api": "POST /admin/education/operations/lessons/batch-change",
    "request": {"lesson_ids": [8801, 8802], "change_type": "cancel", "reason": "campus holiday"},
    "success": {"code": 200, "message": "success", "data": {"success_ids": [8801], "failed": [{"lesson_id": 8802, "message": "lesson already consumed"}]}},
    "validation_failure": {"code": 422, "message": "lesson_ids must contain at least one item", "data": {"field": "lesson_ids"}},
    "business_failure": {"code": 403, "message": "lesson is outside current campus scope", "data": {"lesson_id": 8802}}
  },
  {
    "api": "POST /admin/education/operations/makeup-entitlements/{id}/arrange",
    "request": {"makeup_lesson_id": 9901, "arranged_at": "2026-06-12 10:00:00"},
    "success": {"code": 200, "message": "success", "data": {"makeup_record_id": 501, "status": "arranged"}},
    "validation_failure": {"code": 422, "message": "makeup_lesson_id is required", "data": {"field": "makeup_lesson_id"}},
    "business_failure": {"code": 409, "message": "makeup entitlement is expired", "data": {"id": 401}}
  },
  {
    "api": "POST /admin/education/operations/consumption-reviews/{id}/approve",
    "request": {"review_note": "attendance checked"},
    "success": {"code": 200, "message": "success", "data": {"id": 601, "status": "approved", "consumption_ids": [7101, 7102]}},
    "validation_failure": {"code": 422, "message": "review_note must not exceed 500 characters", "data": {"field": "review_note"}},
    "business_failure": {"code": 409, "message": "student course account has insufficient balance", "data": {"student_id": 1201, "available_units": "0.00"}}
  },
  {
    "api": "POST /admin/education/operations/lesson-consumptions/{id}/adjust",
    "request": {"credits": "-1.00", "reason": "wrong attendance status"},
    "success": {"code": 200, "message": "success", "data": {"adjustment_id": 801, "adjustment_consumption_id": 7109}},
    "validation_failure": {"code": 422, "message": "reason is required", "data": {"field": "reason"}},
    "business_failure": {"code": 409, "message": "consumption has already been fully reversed", "data": {"original_consumption_id": 7101}}
  },
  {
    "api": "GET /admin/education/operations/renewal-alerts/page",
    "request": {"page": 1, "pageSize": 20, "status": "open", "alert_level": "urgent"},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 901, "student_id": 1201, "alert_type": "low_balance", "status": "open"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "status has an invalid value", "data": {"field": "status"}},
    "business_failure": {"code": 403, "message": "campus is outside current context", "data": {"campus_id": 99}}
  },
  {
    "api": "POST /admin/education/operations/renewal-tasks/{id}/follow",
    "request": {"follow_type": "phone", "content": "guardian will renew next week", "next_follow_at": "2026-06-15 10:00:00"},
    "success": {"code": 200, "message": "success", "data": {"follow_record_id": 1001, "task_status": "following"}},
    "validation_failure": {"code": 422, "message": "content is required", "data": {"field": "content"}},
    "business_failure": {"code": 403, "message": "renewal task is assigned to another user", "data": {"task_id": 100}}
  },
  {
    "api": "GET /mobile/education/operations/guardian/makeup-entitlements",
    "request": {"student_id": 1201, "status": "available"},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 401, "course_name": "Art", "status": "available"}]}},
    "validation_failure": {"code": 422, "message": "student_id is required", "data": {"field": "student_id"}},
    "business_failure": {"code": 403, "message": "student is not bound to current guardian", "data": {"student_id": 1201}}
  },
  {
    "api": "POST /mobile/education/operations/teacher/makeup-attendance",
    "request": {"makeup_record_id": 501, "attendance_status": "present"},
    "success": {"code": 200, "message": "success", "data": {"makeup_record_id": 501, "entitlement_status": "used"}},
    "validation_failure": {"code": 422, "message": "attendance_status has an invalid value", "data": {"field": "attendance_status"}},
    "business_failure": {"code": 403, "message": "makeup lesson is not assigned to current teacher", "data": {"makeup_record_id": 501}}
  }
]
```

## PC Admin Page Tasks

API clients:

```text
lesson-change.ts: pageLessonChangeRequests, createLessonChangeRequest, approveLessonChangeRequest, rejectLessonChangeRequest, applyLessonChangeRequest, batchChangeLessons, listLessonChangeLogs
makeup.ts: pageMakeupEntitlements, arrangeMakeup, cancelMakeupRecord, pageMakeupRecords
consumption-review.ts: pageConsumptionReviews, approveConsumptionReview, rejectConsumptionReview, createConsumptionAdjustment, pageConsumptionAdjustments
renewal.ts: pageRenewalAlerts, ignoreRenewalAlert, closeRenewalAlert, assignRenewalTask, followRenewalTask, pageStudentFollowRecords
workload.ts: pageTeacherWorkloadRecords, getTeacherWorkloadSummary
dashboard.ts: getOperationOverview, getConsumptionTrend, getRenewalAlertSummary, getDailyOperationMetrics
```

Routes and menus:

| Route | Route name | Menu | Permission |
| --- | --- | --- | --- |
| `/education/operations/lesson-changes` | `EducationOperationLessonChangeCenter` | 教务 SaaS / 教务运营 / 课次变更中心 | `education:operations:lesson-change:page` |
| `/education/operations/makeups` | `EducationOperationMakeupList` | 教务 SaaS / 教务运营 / 请假补课闭环 | `education:operations:makeup:page` |
| `/education/operations/consumption-reviews` | `EducationOperationConsumptionReviewList` | 教务 SaaS / 教务运营 / 课消审核 | `education:operations:consumption-review:page` |
| `/education/operations/renewal-alerts` | `EducationOperationRenewalAlertList` | 教务 SaaS / 教务运营 / 续费预警 | `education:operations:renewal-alert:page` |
| `/education/operations/teacher-workloads` | `EducationOperationTeacherWorkloadReport` | 教务 SaaS / 教务运营 / 教师课时统计 | `education:operations:teacher-workload:report` |
| `/education/operations/dashboard` | `EducationOperationDashboard` | 教务 SaaS / 教务运营 / 经营看板 | `education:operations:dashboard:overview` |

Page tasks:

| Page | List columns | Search fields | Forms and actions | Required states |
| --- | --- | --- | --- | --- |
| `LessonChangeCenter.vue` | lesson, class, teacher, change_type, status, requested_by, approved_at | campus, date range, teacher, status, change_type | `LessonChangeForm`, approve, reject, apply, batch cancel/suspend | loading skeleton, empty list, conflict error drawer, success refresh |
| `LeaveMakeupList.vue` | student, course, source lesson, entitlement status, expires_at, makeup lesson | campus, student keyword, course, status, expire range | `MakeupArrangeForm`, cancel arrangement, view source leave | expired badge, used lock, 409 inline error, refresh after arrange |
| `ConsumptionReviewList.vue` | lesson, teacher, submitted_at, status, reviewed_by, review_note | campus, date range, teacher, status | `ConsumptionReviewDrawer`, approve, reject, adjustment form | pending badge, insufficient balance alert, form kept open on 422 |
| `RenewalAlertList.vue` | student, course, alert_type, alert_level, trigger_value, status, due_date | campus, alert type, level, status, assignee | `RenewalFollowDrawer`, assign, follow, ignore, close | urgent color, overdue marker, assignee permission buttons |
| `TeacherWorkloadReport.vue` | teacher, workload_type, lesson_type, credits, student_count, present_count | campus, teacher, date range, workload type | export current page, open lesson detail | zero data state, totals footer, campus filter lock |
| `OperationDashboard.vue` | metric cards and charts | campus, date range | refresh metrics, link to renewal/review pages | loading cards, empty metric, API error message |

Button permissions:

```text
approve/reject consumption: education:operations:consumption-review:approve
adjust consumption: education:operations:consumption-adjustment:create
batch lesson change: education:operations:lesson-change:batch
arrange make-up: education:operations:makeup:arrange
renewal follow: education:operations:renewal-task:follow
```

PC test assertions:

```text
LessonChangeCenter.spec.ts asserts conflict response opens review drawer error block.
ConsumptionReviewList.spec.ts asserts approve button is hidden without permission.
RenewalAlertList.spec.ts asserts urgent alerts sort before normal alerts when same due date.
OperationDashboard.spec.ts asserts chart requests include X-Campus-Id and date range.
```

## Teacher / Guardian Mobile Page Tasks

Teacher API client:

```text
mobile-uniapp/src/api/operations/teacher.ts
methods: getChangedLessons, getMakeupAttendanceDetail, submitMakeupAttendance, getWorkloadSummary
headers: mobile token, X-Tenant-Id, X-Campus-Id from F06 context
```

Teacher pages:

| Page | State | Navigation | Isolation | Test |
| --- | --- | --- | --- | --- |
| `pages/teacher/operations/changed-lessons.vue` | tabs: pending_today, upcoming, applied | lesson detail and attendance | current teacher must be main or substitute teacher | `teacher-operations.spec.ts` checks unassigned lessons hidden |
| `pages/teacher/operations/makeup-attendance.vue` | loading, detail, attendance form, submitted, 409 used state | back to changed lessons | make-up lesson must be assigned to current teacher | submit test checks duplicate used response |
| `pages/teacher/operations/workload-summary.vue` | month selector, summary cards, detail list | lesson detail | only current teacher workload | test checks teacher_id cannot be overridden |

Guardian API client:

```text
mobile-uniapp/src/api/operations/guardian.ts
methods: getChangedLessons, getMakeupEntitlements, getMakeupRecords, getRenewalAlerts
headers: mobile token, X-Tenant-Id, selected student id from F06 context
```

Guardian pages:

| Page | State | Navigation | Isolation | Test |
| --- | --- | --- | --- | --- |
| `pages/guardian/operations/changed-lessons.vue` | student selector, changed lesson list, empty state | lesson detail | selected student must be guardian-bound | `guardian-operations.spec.ts` checks cross-student 403 |
| `pages/guardian/operations/makeup-entitlements.vue` | available/used/expired tabs | make-up record detail | bound student only | expired entitlement cannot show arrange action |
| `pages/guardian/operations/makeup-records.vue` | arranged/completed/cancelled list | lesson detail | bound student only | cancelled record restores entitlement badge |
| `pages/guardian/operations/renewal-alerts.vue` | low balance and expiry cards | course account detail | bound student only | guardian sees only alerts configured as visible |

`pages.json` registration:

```text
Add all V2 teacher and guardian pages with role meta: teacher pages require teacher profile; guardian pages require guardian profile and selected student.
```

## Test Plan

Backend migration/schema tests:

| Test file | Case | Assert |
| --- | --- | --- |
| `AcademicOperationMigrationTest.php` | `test_operation_tables_exist_with_indexes` | all V2 tables and documented unique/index keys exist |
| `AcademicOperationMigrationTest.php` | `test_operation_tables_have_tenant_and_campus_columns` | all tenant-owned tables contain `tenant_id`; campus-owned tables contain `campus_id` |

Backend service tests:

| Test file | Case | Assert |
| --- | --- | --- |
| `LessonChangeServiceTest.php` | `test_reschedule_rejects_teacher_conflict` | service throws 409 with conflict teacher and lesson id |
| `LessonChangeServiceTest.php` | `test_apply_change_writes_before_after_log` | lesson row changes and `edu_lesson_change_logs` stores snapshots |
| `MakeupServiceTest.php` | `test_approved_leave_creates_one_entitlement` | repeated call keeps one `available` entitlement by unique key |
| `MakeupServiceTest.php` | `test_makeup_completion_marks_entitlement_used_once` | duplicate attendance returns current used state without second deduction |
| `ConsumptionReviewServiceTest.php` | `test_review_mode_does_not_consume_before_approval` | no V1 consumption row before approve |
| `ConsumptionReviewServiceTest.php` | `test_adjustment_never_deletes_original_consumption` | original consumption remains; reverse row and adjustment row are created |
| `RenewalAlertServiceTest.php` | `test_low_balance_alert_is_deduped` | one open alert per account/type |
| `TeacherWorkloadServiceTest.php` | `test_substitute_workload_does_not_overwrite_main_teacher` | main and substitute workload rows both exist |

Feature/API tests:

| Test file | Case | Assert |
| --- | --- | --- |
| `LessonChangeAdminApiTest.php` | `test_create_approve_apply_lesson_change` | status moves pending -> approved -> applied, audit rows exist |
| `LessonChangeAdminApiTest.php` | `test_batch_change_returns_success_and_failed_lists` | response has `success_ids` and `failed` |
| `MakeupAdminApiTest.php` | `test_arrange_makeup_rejects_expired_entitlement` | documented 409 response |
| `ConsumptionReviewAdminApiTest.php` | `test_validation_and_business_failures_match_catalog` | 422 and 409 examples match API catalog |
| `RenewalAlertAdminApiTest.php` | `test_follow_record_updates_task_status` | follow record exists and task status is following |
| `TeacherOperationMobileApiTest.php` | `test_teacher_can_submit_only_assigned_makeup_attendance` | assigned returns 200; unassigned returns 403 |
| `GuardianOperationMobileApiTest.php` | `test_guardian_reads_only_bound_student_entitlements` | bound student returns data; unbound returns 403 |
| `OperationPermissionIsolationAuditTest.php` | `test_high_risk_buttons_require_permissions` | approve, adjust, batch change are denied without permission |
| `OperationPermissionIsolationAuditTest.php` | `test_write_operations_are_audited` | audit actions listed above are stored |

PC tests:

| Test file | Case | Assert |
| --- | --- | --- |
| `LessonChangeCenter.spec.ts` | `permission_buttons_follow_acl` | batch and approve buttons hide without permissions |
| `LeaveMakeupList.spec.ts` | `arrange_form_handles_409` | 409 message is shown and form remains open |
| `ConsumptionReviewList.spec.ts` | `approval_refreshes_page` | successful approve reloads current page |
| `RenewalAlertList.spec.ts` | `follow_drawer_saves_next_follow` | API request includes `next_follow_at` |
| `TeacherWorkloadReport.spec.ts` | `summary_uses_date_range` | API query has exact selected range |
| `OperationDashboard.spec.ts` | `dashboard_links_to_filtered_pages` | click on pending review opens review page with status filter |

Mobile tests:

| Test file | Case | Assert |
| --- | --- | --- |
| `teacher-operations.spec.ts` | `changed_lessons_hide_unassigned_records` | list renders only API-provided assigned records and handles 403 |
| `teacher-operations.spec.ts` | `makeup_attendance_submit_handles_used_state` | duplicate submit shows used state |
| `guardian-operations.spec.ts` | `guardian_entitlement_requires_bound_student` | 403 routes back to student selector |
| `guardian-operations.spec.ts` | `renewal_alert_visibility_respects_setting` | hidden guardian alerts do not render |

## Execution Commands

Backend migration gate:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
```

Expected:

```text
V2 academic operation tables are created successfully.
```

Backend test gate:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter Education\\\\Operations
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
All Education\\Operations tests pass.
Code style dry run passes.
Static analysis passes.
```

PC gate:

```bash
cd mineadmin-education-saas/admin-web
pnpm install
pnpm lint
pnpm test -- operations
pnpm build
```

Expected:

```text
PC dependencies install, lint passes, operations tests pass, production build succeeds.
```

Mobile gate:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm install
pnpm lint
pnpm test -- operations
pnpm build:h5
```

Expected:

```text
Mobile lint passes, operations tests pass, H5 build succeeds.
```

Final V2 gate:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter Education\\\\Operations
cd ../admin-web
pnpm lint && pnpm test -- operations && pnpm build
cd ../mobile-uniapp
pnpm lint && pnpm test -- operations && pnpm build:h5
```

Expected:

```text
Backend, PC, and mobile V2 gates pass with no failed tests.
```

## Acceptance Gate

V2 is accepted only when:

```text
- Academic staff can create, approve, reject, apply, and batch-handle lesson changes.
- Teacher, classroom, class, and student conflicts are rejected before a change is approved or applied.
- Approved leave creates exactly one make-up entitlement.
- Make-up attendance uses entitlement once and does not double-consume the original package.
- Review mode prevents attendance from directly creating consumption until approval.
- Consumption reversal preserves original consumption and writes adjustment ledger records.
- Renewal scan creates deduped open alerts and follow-up tasks.
- Teacher workload report separates main, substitute, make-up, and trial-support workload.
- PC pages enforce button permissions and preserve form state on 422/409 failures.
- Teacher mobile APIs expose only assigned lessons and workload.
- Guardian mobile APIs expose only bound-student make-up, changed lesson, and renewal data.
- All write operations listed in audit rules are present in audit logs.
```

## Task Breakdown

### Task 1: Create Migration, Enums, and Models

**Files:**

- Create migration, enum, and model files listed in `File Structure`.
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Operations/AcademicOperationMigrationTest.php`

- [x] **Step 1: Write migration from the table design**

Implement every table, column, index, soft delete, and reverse-order rollback exactly as defined in `Database Migration Design`.

- [x] **Step 2: Create enums**

Create enum classes with the values listed in `MineAdmin Backend Module Design`.

- [x] **Step 3: Create models**

Create models with table names, fillable fields, casts, tenant/campus scope helpers, and SoftDeletes for mutable tables.

- [x] **Step 4: Write migration tests**

Assert table existence, tenant/campus columns, unique indexes, decimal columns, JSON columns, and rollback safety.

- [x] **Step 5: Run migration gate**

Run `php bin/hyperf.php migrate`; expected output is successful creation of all V2 operation tables.

### Task 2: Create Repositories and Services

**Files:**

- Create repository and service files listed in `File Structure`.
- Test: unit test files listed in `Test Plan`.

- [x] **Step 1: Create repositories**

Implement every repository method listed in the repository task table with tenant and campus filters.

- [x] **Step 2: Create services**

Implement lesson change, make-up, consumption review, renewal, workload, and dashboard service responsibilities with database transactions around state changes.

- [x] **Step 3: Write service tests**

Implement all service cases listed in `Backend service tests`.

- [x] **Step 4: Run backend unit gate**

Run `composer test -- --filter Education\\\\Operations.*ServiceTest`; expected output is all V2 service tests passing.

### Task 3: Create Requests, Schemas, and Controllers

**Files:**

- Create request, controller, and schema files listed in `File Structure`.
- Test: feature/API tests listed in `Test Plan`.

- [x] **Step 1: Create request classes**

Implement validation rules and messages exactly as listed in `Request validation`.

- [x] **Step 2: Create schemas**

Document request, success, validation failure, and business failure payloads from the API catalog.

- [x] **Step 3: Create admin controllers**

Implement endpoint matrix permissions, request injection, service calls, result envelope, and audit logging.

- [x] **Step 4: Create mobile controllers**

Implement teacher and guardian endpoints with F06 mobile context and role isolation checks.

- [x] **Step 5: Write feature tests**

Implement API, permission, isolation, and audit tests listed in `Feature/API tests`.

- [x] **Step 6: Run backend feature gate**

Run `composer test -- --filter Education\\\\Operations`; expected output is all V2 backend tests passing.

### Task 4: Create PC Admin Pages

**Files:**

- Create PC API, page, component, and test files listed in `File Structure`.
- Modify: `mineadmin-education-saas/admin-web/src/router/modules/education.ts`

- [ ] **Step 1: Create typed API clients**

Implement the methods listed under `API clients` with request and response TypeScript types matching the API catalog.

- [ ] **Step 2: Add routes and menus**

Register every route, route name, menu label, and permission listed in `Routes and menus`.

- [ ] **Step 3: Create page components**

Implement every list column, search field, form action, button permission, loading, empty, error, and success state listed in `Page tasks`.

- [ ] **Step 4: Write PC tests**

Implement all PC test cases listed in `PC tests`.

- [ ] **Step 5: Run PC gate**

Run `pnpm lint && pnpm test -- operations && pnpm build`; expected output is lint, tests, and build passing.

### Task 5: Create Teacher and Guardian Mobile Pages

**Files:**

- Create mobile API, page, and test files listed in `File Structure`.
- Modify: `mineadmin-education-saas/mobile-uniapp/pages.json`

- [ ] **Step 1: Create mobile API clients**

Implement teacher and guardian API methods with F06 headers and selected student context.

- [ ] **Step 2: Register pages**

Add V2 pages to `pages.json` with role meta for teacher and guardian profiles.

- [ ] **Step 3: Implement teacher pages**

Implement changed lessons, make-up attendance, and workload summary states and isolation rules.

- [ ] **Step 4: Implement guardian pages**

Implement changed lessons, make-up entitlements, make-up records, and renewal alert pages with bound-student isolation.

- [ ] **Step 5: Write mobile tests**

Implement all mobile cases listed in `Mobile tests`.

- [ ] **Step 6: Run mobile gate**

Run `pnpm lint && pnpm test -- operations && pnpm build:h5`; expected output is mobile lint, tests, and build passing.

### Task 6: Run V2 Final Gate

**Files:**

- Verify all V2 backend, PC, and mobile files.

- [ ] **Step 1: Run backend final gate**

Run the backend commands in `Execution Commands`; expected output is all migrations, tests, style, and analysis passing.

- [ ] **Step 2: Run PC final gate**

Run the PC commands in `Execution Commands`; expected output is lint, tests, and build passing.

- [ ] **Step 3: Run mobile final gate**

Run the mobile commands in `Execution Commands`; expected output is lint, tests, and H5 build passing.

- [ ] **Step 4: Record implementation completion**

Only after all gates pass, update completion status from `incomplete / not implemented` to implemented in the project status index.

## Self-Review

- Spec coverage: Covers V2 lesson change center, leave/make-up closure, consumption review/reversal, renewal alerts/follow-up, teacher workload, dashboards, notifications, permissions, and tenant/campus isolation.
- MineAdmin fit: Uses MineAdmin 3.x admin/mobile controller paths, request classes, services, repositories, models, schemas, migrations, PC routes/API/pages, and uni-app pages.
- Code-level readiness: Migration fields, indexes, rollback, API request/response/failures, backend layer tasks, PC/mobile page tasks, tests, commands, and acceptance gates are defined.
- Implementation status: No code has been implemented; this is a ready implementation plan only.
