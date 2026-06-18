# MineAdmin Education SaaS V1-06 Teacher Mobile Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement teacher mobile academic workflows for assigned lesson schedule, lesson detail, attendance submission, attendance result, and teacher-side leave review.

**Architecture:** V1-06 depends on Foundation F06 mobile context, V1-01 teacher records, V1-03 lessons and lesson-student snapshots, V1-04 attendance/consumption services, and V1-05 leave requests. Mobile APIs live under `backend/app/Http/Api`, enforce teacher role plus assigned-lesson isolation, and keep business logic in `backend/app/Service`, `backend/app/Repository`, and `backend/app/Schema`. No V1-06 database table is created; attendance submission delegates to V1-04 `AttendanceService`.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, MineAdmin current user context, uni-app Vue3, TypeScript, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. V1 core academic gates have passed.

---

## Scope Check

Included:

- Create teacher-scoped mobile academic API controllers, requests, repositories, services, and schemas.
- Resolve current teacher through F06 mobile context plus V1-01 `edu_teachers.user_profile_id`.
- Return today's assigned lessons and paged assigned lesson history for the current teacher.
- Return lesson detail with lesson-student snapshots, existing attendance rows, approved leave defaults, and submit permissions.
- Return attendance sheet rows with default attendance values derived from V1-05 leave requests.
- Submit lesson attendance through V1-04 `AttendanceService` with teacher-scoped access checks.
- Return attendance result for a submitted lesson without exposing other teachers' data.
- Return teacher-side leave request page and detail for leave requests attached to the teacher's assigned lessons.
- Approve or reject pending leave requests from teacher mobile when the lesson belongs to the current teacher.
- Add uni-app teacher API client, page routes, schedule page, lesson detail page, attendance page, leave list page, leave detail page, loading/empty/error/forbidden/retry/submitting states, and tests.
- Add backend API, service, repository, role isolation, campus isolation, idempotency, and regression tests.

Excluded:

- New database migrations. V1-06 consumes V1-01, V1-03, V1-04, and V1-05 tables.
- PC attendance review pages. V1-04 owns PC attendance.
- PC leave management pages. V1-05 owns PC leave and lesson change pages.
- Guardian mobile pages. V1-07 owns guardian schedule, accounts, consumption, notifications, and leave creation.
- Schedule creation, make-up scheduling, reschedule, and conflict center. V1-03 and V1-05 own these operations.
- Teacher payroll, workload settlement, and class hour compensation. V5 owns payroll.
- WeChat OAuth, openid binding, and mobile token issuing. Foundation/F06 owns mobile context.

Business rules:

```text
All V1-06 APIs require F06 MobileEducationContextMiddleware.
The current education profile role_code must be teacher.
The current profile must map to one enabled V1-01 edu_teachers row through user_profile_id.
Teacher lesson visibility requires edu_lessons.tenant_id, campus_id, and teacher_id to match current context and teacher id.
Teacher can read only non-deleted lessons assigned to the current teacher.
Teacher can submit attendance only for assigned scheduled lessons in an allowed campus scope.
Teacher cannot submit attendance for cancelled lessons.
Teacher cannot submit attendance for completed lessons unless V1-04 idempotency returns the same existing submission summary.
Teacher mobile attendance submission does not implement a different deduction rule; it calls V1-04 AttendanceService.
Teacher leave list shows only leave requests whose lesson_id belongs to the current teacher.
Teacher leave approval or rejection is allowed only for pending leave requests attached to current teacher lessons.
Teacher leave approval or rejection does not create make-up lessons and does not mutate course account balance.
```

Attendance defaults:

```text
Attendance sheet rows are based on V1-03 edu_lesson_students where status is planned.
If V1-05 has an approved or makeup_scheduled leave request for a lesson_student_id, default attendance_status is leave.
Leave default consume_policy is no_consume and consumed_units is 0.00.
Rows without approved leave default to present, consume, and lesson_students.lesson_units.
Existing V1-04 attendance rows override all defaults and make the sheet submitted.
The submitted flag is true when any attendance row exists for the lesson.
```

State machines reused:

```text
Lesson status: scheduled, cancelled, completed.
Attendance status: present, late, absent, leave.
Consume policy: consume, no_consume.
Leave status for mobile review: pending -> approved, pending -> rejected.
Teacher mobile never changes leave status from approved to makeup_scheduled or closed.
```

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherLessonTodayRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherLessonPageRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherLessonDetailRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherAttendanceSheetRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherAttendanceSaveRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherAttendanceResultRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherLeavePageRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherLeaveDetailRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherLeaveReviewRequest.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/TeacherLessonController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/TeacherAttendanceController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/TeacherLeaveController.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/TeacherMobileLessonRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/TeacherMobileLeaveRepository.php
mineadmin-education-saas/backend/app/Service/Education/Academic/TeacherMobileContextResolver.php
mineadmin-education-saas/backend/app/Service/Education/Academic/TeacherMobileLessonService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/TeacherMobileAttendanceService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/TeacherMobileLeaveService.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/TeacherMobileLessonSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/TeacherMobileAttendanceSheetSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/TeacherMobileAttendanceResultSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/TeacherMobileLeaveSchema.php
```

Read backend dependencies:

```text
mineadmin-education-saas/backend/app/Http/Api/Middleware/Education/Foundation/MobileEducationContextMiddleware.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/EducationUserContext.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusScopeService.php
mineadmin-education-saas/backend/app/Event/Education/Foundation/EducationAuditEvent.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationTeacher.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLesson.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonStudent.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonAttendance.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLeaveRequest.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/TeacherRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonStudentRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/AttendanceRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/ConsumptionRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/LeaveRequestRepository.php
mineadmin-education-saas/backend/app/Service/Education/Academic/AttendanceService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/LeaveRequestService.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherMobileContextResolverTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherMobileLessonRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherMobileLeaveRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherMobileLessonServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherMobileAttendanceServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherMobileLeaveServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/TeacherMobileLessonApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/TeacherMobileAttendanceApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/TeacherMobileLeaveApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/TeacherMobileRoleIsolationTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/TeacherMobileCampusIsolationTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/TeacherMobileRegressionTest.php
```

Create mobile:

```text
mineadmin-education-saas/mobile-uniapp/src/api/academic/teacher.ts
mineadmin-education-saas/mobile-uniapp/src/api/academic/__tests__/teacher.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/teacher/schedule/index.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/schedule/__tests__/index.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/teacher/lesson/detail.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/lesson/attendance.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/lesson/__tests__/detail.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/teacher/lesson/__tests__/attendance.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/teacher/leave/index.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/leave/detail.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/leave/__tests__/index.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/teacher/leave/__tests__/detail.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/teacher/components/TeacherStateBlock.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/components/LessonStatusBadge.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/components/AttendanceStudentRow.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/components/LeaveStatusBadge.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/components/__tests__/TeacherStateBlock.spec.ts
mineadmin-education-saas/mobile-uniapp/tests/academic/teacherPagesJson.spec.ts
```

Modify mobile:

```text
mineadmin-education-saas/mobile-uniapp/pages.json
mineadmin-education-saas/mobile-uniapp/pages/teacher/index.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/__tests__/index.spec.ts
```

Verify no PC file is created:

```text
mineadmin-education-saas/admin-web/package.json
```

## Database Migration Design

V1-06 creates no database migration.

Existing tables consumed:

```text
edu_user_profiles from F02
edu_user_campus_scopes from F02
edu_teachers from V1-01
edu_classes from V1-03
edu_lessons from V1-03
edu_lesson_students from V1-03
edu_lesson_attendances from V1-04
edu_lesson_consumptions from V1-04
edu_leave_requests from V1-05
```

Required read indexes already planned:

```text
edu_teachers.uk_edu_teachers_user_profile
edu_teachers.idx_edu_teachers_tenant_campus_status
edu_lessons.idx_edu_lessons_tenant_teacher_time
edu_lessons.idx_edu_lessons_tenant_status_time
edu_lesson_students.idx_edu_lesson_students_tenant_lesson_status
edu_lesson_attendances.uk_edu_lesson_attendances_tenant_lesson_student
edu_lesson_attendances.idx_edu_lesson_attendances_tenant_lesson
edu_leave_requests.idx_edu_leave_requests_tenant_campus_status
edu_leave_requests.idx_edu_leave_requests_tenant_lesson
```

Write behavior:

```text
Teacher mobile attendance writes only through V1-04 AttendanceService.
Teacher mobile leave review writes only edu_leave_requests.status, reviewed_at, reviewed_by, and review_remark through V1-05-compatible state rules.
No V1-06 service writes edu_lessons, edu_lesson_students, edu_lesson_consumptions, edu_student_course_accounts, or edu_lesson_change_records directly.
```

Rollback behavior:

```text
No database rollback exists for V1-06.
Reverting V1-06 means reverting API controllers, requests, services, repositories, schemas, uni-app API client, pages, routes, and tests.
```

Verification command:

```bash
cd mineadmin-education-saas
find backend/databases/migrations -maxdepth 1 -name '*010600*' -o -name '*teacher_mobile*'
```

Expected:

```text
No output.
```

## MineAdmin Backend Module Design

### HTTP Layer

Route prefix:

```text
/mobile/education/academic/teacher
```

Middleware:

```text
F06 MobileEducationContextMiddleware is required for every route.
MineAdmin auth is required before MobileEducationContextMiddleware resolves the education context.
OperationMiddleware is used only on mutating endpoints: attendance submit, leave approve, leave reject.
```

Controllers:

```text
TeacherLessonController
- today(TeacherLessonTodayRequest $request): Result
- page(TeacherLessonPageRequest $request): Result
- detail(int $lessonId, TeacherLessonDetailRequest $request): Result

TeacherAttendanceController
- sheet(int $lessonId, TeacherAttendanceSheetRequest $request): Result
- submit(int $lessonId, TeacherAttendanceSaveRequest $request): Result
- result(int $lessonId, TeacherAttendanceResultRequest $request): Result

TeacherLeaveController
- page(TeacherLeavePageRequest $request): Result
- detail(int $id, TeacherLeaveDetailRequest $request): Result
- approve(int $id, TeacherLeaveReviewRequest $request): Result
- reject(int $id, TeacherLeaveReviewRequest $request): Result
```

Permission model:

```text
Mobile endpoints use education role isolation instead of PC menu button permissions.
Allowed role_code is teacher only.
Guardian, front_desk, academic_staff, finance, tenant_admin, and platform roles receive code 403 on V1-06 teacher routes.
```

Audit events:

```text
education.academic.teacher_mobile.attendance_submitted
education.academic.teacher_mobile.leave_approved
education.academic.teacher_mobile.leave_rejected
```

### Request Classes

`TeacherLessonTodayRequest` rules:

```php
[
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'date' => ['nullable', 'date_format:Y-m-d'],
]
```

Messages:

```text
campus_id.integer: campus_id must be an integer
campus_id.min: campus_id must be at least 1
date.date_format: date must use Y-m-d
```

`TeacherLessonPageRequest` rules:

```php
[
    'page' => ['nullable', 'integer', 'min:1'],
    'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_at'],
    'status' => ['nullable', 'in:scheduled,cancelled,completed'],
    'keyword' => ['nullable', 'string', 'max:120'],
]
```

Messages:

```text
start_at.required: start_at is required
start_at.date_format: start_at must use Y-m-d H:i:s
end_at.required: end_at is required
end_at.after: end_at must be after start_at
status.in: status has an invalid value
keyword.max: keyword must not exceed 120 characters
```

`TeacherLessonDetailRequest`, `TeacherAttendanceSheetRequest`, `TeacherAttendanceResultRequest`, and `TeacherLeaveDetailRequest` rules:

```php
[
    'campus_id' => ['nullable', 'integer', 'min:1'],
]
```

`TeacherAttendanceSaveRequest` rules:

```php
[
    'submitted_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
    'records' => ['required', 'array', 'min:1'],
    'records.*.lesson_student_id' => ['required', 'integer', 'min:1'],
    'records.*.attendance_status' => ['required', 'in:present,late,absent,leave'],
    'records.*.consume_policy' => ['required', 'in:consume,no_consume'],
    'records.*.consumed_units' => ['required', 'numeric', 'min:0', 'max:999.99'],
    'records.*.remark' => ['nullable', 'string', 'max:300'],
]
```

Messages:

```text
records.required: records is required
records.array: records must be an array
records.min: at least one attendance row is required
records.*.lesson_student_id.required: lesson_student_id is required
records.*.attendance_status.in: attendance_status has an invalid value
records.*.consume_policy.in: consume_policy has an invalid value
records.*.consumed_units.numeric: consumed_units must be numeric
records.*.consumed_units.min: consumed_units must be at least 0
records.*.remark.max: remark must not exceed 300 characters
```

`TeacherLeavePageRequest` rules:

```php
[
    'page' => ['nullable', 'integer', 'min:1'],
    'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'status' => ['nullable', 'in:pending,approved,rejected,cancelled,makeup_scheduled,closed'],
    'start_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['nullable', 'date_format:Y-m-d H:i:s', 'after:start_at'],
    'keyword' => ['nullable', 'string', 'max:120'],
]
```

`TeacherLeaveReviewRequest` rules:

```php
[
    'review_remark' => ['required', 'string', 'max:500'],
]
```

Messages:

```text
review_remark.required: review_remark is required
review_remark.max: review_remark must not exceed 500 characters
```

### Context Resolver

Create `TeacherMobileContextResolver`.

Methods:

```php
public function resolveTeacher(EducationUserContext $context): EducationTeacher
public function assertTeacherRole(EducationUserContext $context): void
public function assertCampusAllowed(EducationUserContext $context, ?int $campusId): ?int
public function currentOperatorId(EducationUserContext $context): ?int
```

Behavior:

```text
Reject non-teacher role with code 403 and message teacher mobile role required.
Find edu_teachers by tenant_id, user_profile_id, status enabled, and deleted_at null.
Reject missing or disabled teacher record with code 403 and message teacher profile is not enabled.
If campus_id is passed, verify it is in F02 campus scope.
If campus_id is omitted, use context current_campus_id when present; otherwise allow all campus scopes for list APIs.
```

### Repositories

`TeacherMobileLessonRepository` methods:

```php
public function listToday(int $tenantId, array $campusIds, int $teacherId, string $date): array
public function pageAssigned(array $params, int $page, int $pageSize, EducationUserContext $context, int $teacherId): array
public function findAssignedLesson(int $lessonId, EducationUserContext $context, int $teacherId, ?int $campusId): ?EducationLesson
public function listLessonStudents(int $lessonId, int $tenantId): array
public function listAttendanceRows(int $lessonId, int $tenantId): array
public function listApprovedLeaveRows(int $lessonId, int $tenantId): array
```

Query requirements:

```text
Every query filters tenant_id.
Campus filtering uses either a single requested campus_id or all campus ids from F02 scope.
Lesson queries require teacher_id = current teacher id.
Today list uses start_at >= date 00:00:00 and start_at <= date 23:59:59.
Paged list orders by start_at desc and id desc.
Lesson student rows exclude status cancelled.
Attendance rows are keyed by lesson_student_id.
Approved leave rows include status approved and makeup_scheduled.
```

`TeacherMobileLeaveRepository` methods:

```php
public function pageAssignedLeave(array $params, int $page, int $pageSize, EducationUserContext $context, int $teacherId): array
public function findAssignedLeave(int $id, EducationUserContext $context, int $teacherId, ?int $campusId): ?EducationLeaveRequest
public function updateReview(int $id, string $status, string $reviewRemark, ?int $operatorId): EducationLeaveRequest
```

Query requirements:

```text
Leave queries join or subquery edu_lessons by leave_requests.lesson_id.
Leave visibility requires edu_lessons.teacher_id = current teacher id.
Leave visibility requires same tenant_id and campus scope.
Keyword matches leave_no, student_name_snapshot if available through lesson student, and reason.
Review update must recheck status pending inside a transaction.
```

### Services

`TeacherMobileLessonService` methods:

```php
public function today(array $params, EducationUserContext $context): array
public function page(array $params, EducationUserContext $context): array
public function detail(int $lessonId, array $params, EducationUserContext $context): array
```

Detail response composition:

```text
Load assigned lesson or return code 404.
Load lesson students, attendance rows, and approved leave rows.
Return can_submit_attendance true only when lesson status is scheduled and no differing submitted attendance exists.
Return attendance_submitted true when V1-04 attendance rows exist.
Return leave_badge_count as count of pending leave requests for lesson_id.
```

`TeacherMobileAttendanceService` methods:

```php
public function sheet(int $lessonId, array $params, EducationUserContext $context): array
public function submit(int $lessonId, array $payload, EducationUserContext $context): array
public function result(int $lessonId, array $params, EducationUserContext $context): array
```

Submit transaction boundary:

```text
Resolve current teacher.
Load assigned lesson with campus scope.
Reject cancelled lesson with code 409.
Reject lesson with no active lesson students with code 409.
Normalize records and require the submitted lesson_student_id set to equal the planned lesson student set.
Call V1-04 AttendanceService::submit with current EducationUserContext and current operator id.
Write audit event education.academic.teacher_mobile.attendance_submitted only after AttendanceService returns success.
Return V1-04 attendance summary plus mobile display fields.
```

Idempotency:

```text
Same payload for an already submitted lesson returns V1-04 existing summary.
Different payload for an already submitted lesson returns code 409 with message attendance already submitted with different payload.
Teacher mobile does not update or delete existing attendance rows.
```

`TeacherMobileLeaveService` methods:

```php
public function page(array $params, EducationUserContext $context): array
public function detail(int $id, array $params, EducationUserContext $context): array
public function approve(int $id, array $payload, EducationUserContext $context): array
public function reject(int $id, array $payload, EducationUserContext $context): array
```

Review transaction boundary:

```text
Resolve current teacher.
Load assigned leave request or return code 404.
Lock leave request row for update.
Require status pending.
Set status approved or rejected.
Set reviewed_at to current time.
Set reviewed_by to current operator id.
Set review_remark from request.
Write F04 audit event after commit.
Return leave review result schema.
```

### Schemas

`TeacherMobileLessonSchema`:

```text
id, tenant_id, campus_id, lesson_no, class_id, class_name_snapshot, course_id, course_name_snapshot, teacher_id, teacher_name_snapshot, classroom_id, classroom_name_snapshot, title, start_at, end_at, duration_minutes, lesson_units, student_count, status, attendance_submitted, can_submit_attendance, pending_leave_count, created_at
```

`TeacherMobileAttendanceSheetSchema`:

```text
lesson, submitted, records, summary
lesson.id, lesson.title, lesson.start_at, lesson.end_at, lesson.status, lesson.lesson_units
records.lesson_student_id, records.student_id, records.student_name_snapshot, records.student_no_snapshot, records.account_id, records.default_attendance_status, records.default_consume_policy, records.default_consumed_units, records.existing_attendance_status, records.existing_consumed_units, records.leave_request_id, records.leave_status, records.remark
summary.total_students, summary.default_leave_count, summary.submitted_count, summary.total_consumed_units
```

`TeacherMobileAttendanceResultSchema`:

```text
lesson_id, lesson_status, attendance_batch_no, attendance_count, consumed_count, no_consume_count, total_consumed_units, records, account_changes
records.lesson_student_id, records.student_id, records.student_name_snapshot, records.attendance_status, records.consume_policy, records.consumed_units, records.consumption_status
```

`TeacherMobileLeaveSchema`:

```text
id, leave_no, source, leave_type, lesson_id, lesson_student_id, class_id, course_id, student_id, student_name_snapshot, teacher_id, reason, status, requested_at, reviewed_at, reviewed_by, review_remark, makeup_required, makeup_lesson_id, lesson_title, lesson_start_at, lesson_end_at, created_at
```

## API Contract

Common headers:

```text
Authorization: Bearer mineadmin-mobile-token
X-Tenant-Id: 1001
X-Client-Type: wechat_miniprogram
```

Common success envelope:

```json
{
  "code": 200,
  "message": "success",
  "data": {}
}
```

Endpoint catalog:

```text
GET /mobile/education/academic/teacher/lessons/today
GET /mobile/education/academic/teacher/lessons/page
GET /mobile/education/academic/teacher/lessons/{lessonId}
GET /mobile/education/academic/teacher/lessons/{lessonId}/attendance-sheet
POST /mobile/education/academic/teacher/lessons/{lessonId}/attendance
GET /mobile/education/academic/teacher/lessons/{lessonId}/attendance-result
GET /mobile/education/academic/teacher/leave-requests/page
GET /mobile/education/academic/teacher/leave-requests/{id}
PUT /mobile/education/academic/teacher/leave-requests/{id}/approve
PUT /mobile/education/academic/teacher/leave-requests/{id}/reject
```

Endpoint examples:

```json
[
  {
    "api": "GET /mobile/education/academic/teacher/lessons/today",
    "request": {"query": {"campus_id": 2001, "date": "2026-06-12"}},
    "success": {"code": 200, "message": "success", "data": {"date": "2026-06-12", "list": [{"id": 9001, "title": "Art Basics Lesson 1", "start_at": "2026-06-12 10:00:00", "end_at": "2026-06-12 11:00:00", "status": "scheduled", "attendance_submitted": false, "pending_leave_count": 1}]}},
    "validation_failure": {"code": 422, "message": "date must use Y-m-d", "data": {"field": "date"}},
    "business_failure": {"code": 403, "message": "teacher mobile role required", "data": {"role_code": "guardian"}}
  },
  {
    "api": "GET /mobile/education/academic/teacher/lessons/page",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "start_at": "2026-06-01 00:00:00", "end_at": "2026-06-30 23:59:59", "status": "scheduled", "keyword": "Art"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 9001, "lesson_no": "LES2026061210000010014821", "title": "Art Basics Lesson 1", "class_name_snapshot": "Art A", "course_name_snapshot": "Art", "start_at": "2026-06-12 10:00:00", "status": "scheduled"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "end_at must be after start_at", "data": {"field": "end_at"}},
    "business_failure": {"code": 403, "message": "campus is outside teacher scope", "data": {"campus_id": 2999}}
  },
  {
    "api": "GET /mobile/education/academic/teacher/lessons/{lessonId}",
    "request": {"path": {"lessonId": 9001}, "query": {"campus_id": 2001}},
    "success": {"code": 200, "message": "success", "data": {"id": 9001, "title": "Art Basics Lesson 1", "status": "scheduled", "attendance_submitted": false, "can_submit_attendance": true, "lesson_students": [{"lesson_student_id": 10001, "student_id": 101, "student_name_snapshot": "Student Zhang", "status": "planned"}]}},
    "validation_failure": {"code": 422, "message": "campus_id must be an integer", "data": {"field": "campus_id"}},
    "business_failure": {"code": 404, "message": "lesson not found in current teacher context", "data": {"lesson_id": 9001}}
  },
  {
    "api": "GET /mobile/education/academic/teacher/lessons/{lessonId}/attendance-sheet",
    "request": {"path": {"lessonId": 9001}, "query": {"campus_id": 2001}},
    "success": {"code": 200, "message": "success", "data": {"lesson": {"id": 9001, "status": "scheduled"}, "submitted": false, "records": [{"lesson_student_id": 10001, "student_id": 101, "student_name_snapshot": "Student Zhang", "default_attendance_status": "leave", "default_consume_policy": "no_consume", "default_consumed_units": "0.00", "leave_request_id": 801}], "summary": {"total_students": 1, "default_leave_count": 1}}},
    "validation_failure": {"code": 422, "message": "campus_id must be at least 1", "data": {"field": "campus_id"}},
    "business_failure": {"code": 409, "message": "cancelled lesson cannot build attendance sheet", "data": {"lesson_id": 9001, "status": "cancelled"}}
  },
  {
    "api": "POST /mobile/education/academic/teacher/lessons/{lessonId}/attendance",
    "request": {"path": {"lessonId": 9001}, "body": {"submitted_at": "2026-06-12 11:05:00", "records": [{"lesson_student_id": 10001, "attendance_status": "present", "consume_policy": "consume", "consumed_units": 1, "remark": "on time"}, {"lesson_student_id": 10002, "attendance_status": "leave", "consume_policy": "no_consume", "consumed_units": 0, "remark": "approved leave"}]}},
    "success": {"code": 200, "message": "success", "data": {"lesson_id": 9001, "attendance_batch_no": "ATT2026061211050010014821", "attendance_count": 2, "consumed_count": 1, "no_consume_count": 1, "total_consumed_units": "1.00"}},
    "validation_failure": {"code": 422, "message": "records is required", "data": {"field": "records"}},
    "business_failure": {"code": 409, "message": "attendance already submitted with different payload", "data": {"lesson_id": 9001}}
  },
  {
    "api": "GET /mobile/education/academic/teacher/lessons/{lessonId}/attendance-result",
    "request": {"path": {"lessonId": 9001}, "query": {"campus_id": 2001}},
    "success": {"code": 200, "message": "success", "data": {"lesson_id": 9001, "lesson_status": "completed", "attendance_batch_no": "ATT2026061211050010014821", "attendance_count": 2, "records": [{"lesson_student_id": 10001, "attendance_status": "present", "consumed_units": "1.00"}]}},
    "validation_failure": {"code": 422, "message": "campus_id must be an integer", "data": {"field": "campus_id"}},
    "business_failure": {"code": 404, "message": "attendance result not found", "data": {"lesson_id": 9001}}
  },
  {
    "api": "GET /mobile/education/academic/teacher/leave-requests/page",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "status": "pending", "start_at": "2026-06-01 00:00:00", "end_at": "2026-06-30 23:59:59"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 801, "leave_no": "LEA2026061210000010014821", "student_name_snapshot": "Student Zhang", "lesson_title": "Art Basics Lesson 1", "status": "pending"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "status has an invalid value", "data": {"field": "status"}},
    "business_failure": {"code": 403, "message": "teacher profile is not enabled", "data": {"user_profile_id": 501}}
  },
  {
    "api": "GET /mobile/education/academic/teacher/leave-requests/{id}",
    "request": {"path": {"id": 801}, "query": {"campus_id": 2001}},
    "success": {"code": 200, "message": "success", "data": {"id": 801, "leave_no": "LEA2026061210000010014821", "leave_type": "sick", "student_name_snapshot": "Student Zhang", "status": "pending", "reason": "Sick leave", "lesson_title": "Art Basics Lesson 1"}},
    "validation_failure": {"code": 422, "message": "campus_id must be at least 1", "data": {"field": "campus_id"}},
    "business_failure": {"code": 404, "message": "leave request not found in current teacher context", "data": {"id": 801}}
  },
  {
    "api": "PUT /mobile/education/academic/teacher/leave-requests/{id}/approve",
    "request": {"path": {"id": 801}, "body": {"review_remark": "Approved before class"}},
    "success": {"code": 200, "message": "success", "data": {"id": 801, "status": "approved", "review_remark": "Approved before class", "reviewed_at": "2026-06-12 09:20:00"}},
    "validation_failure": {"code": 422, "message": "review_remark is required", "data": {"field": "review_remark"}},
    "business_failure": {"code": 409, "message": "only pending leave can be approved", "data": {"id": 801, "status": "rejected"}}
  },
  {
    "api": "PUT /mobile/education/academic/teacher/leave-requests/{id}/reject",
    "request": {"path": {"id": 801}, "body": {"review_remark": "Leave reason is invalid"}},
    "success": {"code": 200, "message": "success", "data": {"id": 801, "status": "rejected", "review_remark": "Leave reason is invalid", "reviewed_at": "2026-06-12 09:20:00"}},
    "validation_failure": {"code": 422, "message": "review_remark must not exceed 500 characters", "data": {"field": "review_remark"}},
    "business_failure": {"code": 409, "message": "only pending leave can be rejected", "data": {"id": 801, "status": "approved"}}
  }
]
```

## PC Admin Page Tasks

V1-06 creates no PC admin page and no PC API client.

Regression expectations:

```text
admin-web/src/router/modules/education.ts is not modified by V1-06.
admin-web/src/api/education/academic is not modified by V1-06.
Existing V1-04 attendance PC pages still call /admin/education/academic/attendance/*.
Existing V1-05 leave PC pages still call /admin/education/academic/leave-requests/*.
```

Verification command:

```bash
cd mineadmin-education-saas/admin-web
pnpm test -- AttendanceReview LeaveRequestList
pnpm build
```

Expected:

```text
AttendanceReview and LeaveRequestList tests pass.
Build exits 0.
```

## Teacher / Guardian Mobile Page Tasks

### API Client

Create `mobile-uniapp/src/api/academic/teacher.ts`.

Types:

```ts
export type TeacherLessonStatus = 'scheduled' | 'cancelled' | 'completed'
export type TeacherAttendanceStatus = 'present' | 'late' | 'absent' | 'leave'
export type TeacherConsumePolicy = 'consume' | 'no_consume'
export type TeacherLeaveStatus = 'pending' | 'approved' | 'rejected' | 'cancelled' | 'makeup_scheduled' | 'closed'
```

Functions:

```ts
export function getTeacherTodayLessons(params: TeacherTodayLessonParams): Promise<TeacherTodayLessonResult>
export function pageTeacherLessons(params: TeacherLessonPageParams): Promise<PageResult<TeacherLessonCard>>
export function getTeacherLessonDetail(lessonId: number, params?: TeacherCampusQuery): Promise<TeacherLessonDetail>
export function getTeacherAttendanceSheet(lessonId: number, params?: TeacherCampusQuery): Promise<TeacherAttendanceSheet>
export function submitTeacherAttendance(lessonId: number, payload: TeacherAttendanceSubmitPayload): Promise<TeacherAttendanceResult>
export function getTeacherAttendanceResult(lessonId: number, params?: TeacherCampusQuery): Promise<TeacherAttendanceResult>
export function pageTeacherLeaveRequests(params: TeacherLeavePageParams): Promise<PageResult<TeacherLeaveCard>>
export function getTeacherLeaveRequest(id: number, params?: TeacherCampusQuery): Promise<TeacherLeaveDetail>
export function approveTeacherLeaveRequest(id: number, payload: TeacherLeaveReviewPayload): Promise<TeacherLeaveReviewResult>
export function rejectTeacherLeaveRequest(id: number, payload: TeacherLeaveReviewPayload): Promise<TeacherLeaveReviewResult>
```

Envelope handling:

```text
Resolve data from { code: 200, message: success, data }.
Throw MobileApiError for non-200 code with code, message, and data.
Preserve validation field in error.data.field for page form display.
```

### pages.json

Add routes:

```json
{
  "path": "pages/teacher/schedule/index",
  "style": {"navigationBarTitleText": "今日课表"}
}
```

```json
{
  "path": "pages/teacher/lesson/detail",
  "style": {"navigationBarTitleText": "课节详情"}
}
```

```json
{
  "path": "pages/teacher/lesson/attendance",
  "style": {"navigationBarTitleText": "点名"}
}
```

```json
{
  "path": "pages/teacher/leave/index",
  "style": {"navigationBarTitleText": "请假审核"}
}
```

```json
{
  "path": "pages/teacher/leave/detail",
  "style": {"navigationBarTitleText": "请假详情"}
}
```

### Teacher Entry Page

Modify `mobile-uniapp/pages/teacher/index.vue`.

Tasks:

```text
Load F06 teacher context on mount.
Load today's lesson count and pending leave count.
Show entry buttons for schedule and leave review.
Navigate to pages/teacher/schedule/index when schedule entry is tapped.
Navigate to pages/teacher/leave/index?status=pending when leave entry is tapped.
Show forbidden state when context role is not teacher.
Show retry state when context or summary request fails.
```

### Schedule Page

Create `mobile-uniapp/pages/teacher/schedule/index.vue`.

Tasks:

```text
Use getTeacherTodayLessons on first load with selected date.
Use pageTeacherLessons when date range or status filter is selected.
Provide date picker and status segmented control.
Render lesson cards with title, time, class, classroom, status, attendance_submitted, and pending_leave_count.
Tap lesson card navigates to pages/teacher/lesson/detail?lessonId={id}.
Pull down refresh reloads current query.
Empty state appears when list length is 0.
Forbidden state appears on code 403.
Retry button reruns the current query.
```

### Lesson Detail Page

Create `mobile-uniapp/pages/teacher/lesson/detail.vue`.

Tasks:

```text
Read lessonId from route query.
Call getTeacherLessonDetail.
Render lesson title, time, class, course, classroom, status, student count, and pending leave count.
Render lesson student rows with student name and status.
Show 点名 button only when can_submit_attendance is true.
Tap 点名 navigates to pages/teacher/lesson/attendance?lessonId={lessonId}.
Show 查看点名结果 button when attendance_submitted is true.
Tap 查看点名结果 opens same attendance page in result mode.
Show forbidden, not found, loading, error, retry, and empty student states.
```

### Attendance Page

Create `mobile-uniapp/pages/teacher/lesson/attendance.vue`.

Tasks:

```text
Read lessonId and optional mode from route query.
Call getTeacherAttendanceSheet on load.
Render one AttendanceStudentRow for each sheet record.
Default selected attendance_status, consume_policy, and consumed_units from API defaults.
When default leave exists, show leave badge and keep no_consume unless teacher changes status manually.
Validate every row has attendance_status, consume_policy, and numeric consumed_units before submit.
Disable submit button while submitting.
Call submitTeacherAttendance on submit.
After success, show result summary and disable further edits.
If API returns idempotent existing success, show submitted summary without duplicate warning.
If API returns code 409, show conflict state and a button to reload result.
```

### Leave List Page

Create `mobile-uniapp/pages/teacher/leave/index.vue`.

Tasks:

```text
Load pageTeacherLeaveRequests with default status pending.
Provide status tabs pending, approved, rejected, all.
Render leave cards with student, leave type, lesson time, reason preview, and status.
Tap card navigates to pages/teacher/leave/detail?id={id}.
Pull down refresh resets to page 1.
Reach bottom loads next page until total is reached.
Show empty, loading, error, forbidden, and retry states.
```

### Leave Detail Page

Create `mobile-uniapp/pages/teacher/leave/detail.vue`.

Tasks:

```text
Read id from route query.
Call getTeacherLeaveRequest.
Render leave_no, student, leave_type, reason, lesson time, requested_at, and current status.
Show approve and reject buttons only when status is pending.
Open review remark input before approve or reject.
Call approveTeacherLeaveRequest or rejectTeacherLeaveRequest.
Disable buttons while submitting.
After success, update local status and hide review buttons.
Show validation message when review_remark is empty or too long.
Show conflict state when API returns code 409 because status changed.
```

### Shared Components

`TeacherStateBlock.vue`:

```text
Props: state loading | empty | error | forbidden | not_found, message, retryText.
Emits: retry.
Used by schedule, detail, attendance, leave list, and leave detail pages.
```

`LessonStatusBadge.vue`:

```text
Props: status scheduled | cancelled | completed.
Maps scheduled to normal, cancelled to disabled, completed to success.
```

`AttendanceStudentRow.vue`:

```text
Props: row, readonly.
Emits: update.
Uses segmented attendance status control, consume/no_consume toggle, numeric consumed units input, and remark textarea.
Keeps stable row height by reserving validation message area.
```

`LeaveStatusBadge.vue`:

```text
Props: status pending | approved | rejected | cancelled | makeup_scheduled | closed.
Maps pending to warning, approved and makeup_scheduled to success, rejected and cancelled to danger, closed to neutral.
```

Guardian mobile tasks:

```text
No guardian page is created in V1-06.
V1-06 tests must verify a guardian context receives code 403 from teacher mobile APIs.
V1-07 will create guardian-facing pages and APIs.
```

## Test Plan

Backend tests:

| File | Case | Assertions |
| --- | --- | --- |
| `TeacherMobileContextResolverTest.php` | `test_resolves_enabled_teacher_from_mobile_context` | teacher profile maps to enabled `edu_teachers` row |
| `TeacherMobileContextResolverTest.php` | `test_guardian_role_is_rejected` | code 403 with message `teacher mobile role required` |
| `TeacherMobileContextResolverTest.php` | `test_disabled_teacher_record_is_rejected` | code 403 with message `teacher profile is not enabled` |
| `TeacherMobileLessonRepositoryTest.php` | `test_today_filters_current_teacher_and_date` | returns only lessons with current teacher id and selected date |
| `TeacherMobileLessonRepositoryTest.php` | `test_page_excludes_other_teacher_lessons` | other teacher lessons are absent |
| `TeacherMobileLeaveRepositoryTest.php` | `test_leave_page_filters_by_assigned_lesson_teacher` | leave from another teacher lesson is absent |
| `TeacherMobileLessonServiceTest.php` | `test_detail_returns_students_attendance_and_leave_defaults` | response includes lesson students, attendance_submitted, can_submit_attendance, default leave count |
| `TeacherMobileAttendanceServiceTest.php` | `test_sheet_defaults_approved_leave_to_no_consume` | leave row defaults to leave/no_consume/0.00 |
| `TeacherMobileAttendanceServiceTest.php` | `test_submit_delegates_to_v1_04_attendance_service` | mocked AttendanceService receives lesson id, records, context, operator id |
| `TeacherMobileAttendanceServiceTest.php` | `test_submit_rejects_missing_student_row` | code 422 or 409 when submitted set differs from lesson student set |
| `TeacherMobileLeaveServiceTest.php` | `test_approve_pending_leave_updates_review_fields` | status approved, reviewed_by and reviewed_at set |
| `TeacherMobileLeaveServiceTest.php` | `test_reject_non_pending_leave_returns_conflict` | code 409 and original status preserved |
| `TeacherMobileLessonApiTest.php` | `test_today_lessons_contract` | response envelope and lesson schema match API contract |
| `TeacherMobileLessonApiTest.php` | `test_lesson_detail_wrong_teacher_returns_404` | code 404 |
| `TeacherMobileAttendanceApiTest.php` | `test_attendance_submit_success_contract` | code 200 and batch summary returned |
| `TeacherMobileAttendanceApiTest.php` | `test_attendance_submit_different_payload_conflict` | code 409 |
| `TeacherMobileLeaveApiTest.php` | `test_leave_approve_success_contract` | code 200 and approved status returned |
| `TeacherMobileLeaveApiTest.php` | `test_leave_reject_validation_failure` | code 422 on missing review_remark |
| `TeacherMobileRoleIsolationTest.php` | `test_guardian_cannot_access_teacher_routes` | every teacher route returns 403 |
| `TeacherMobileCampusIsolationTest.php` | `test_teacher_cannot_access_out_of_scope_campus` | code 403 |
| `TeacherMobileRegressionTest.php` | `test_v1_04_admin_attendance_api_still_passes` | V1-04 admin attendance endpoint behavior unchanged |

Mobile tests:

| File | Case | Assertions |
| --- | --- | --- |
| `src/api/academic/__tests__/teacher.spec.ts` | `getTeacherTodayLessons unwraps envelope` | returns `data.list` |
| `src/api/academic/__tests__/teacher.spec.ts` | `submitTeacherAttendance preserves validation error field` | thrown error contains `data.field` |
| `tests/academic/teacherPagesJson.spec.ts` | `teacher academic pages are registered` | pages.json has five V1-06 routes |
| `pages/teacher/__tests__/index.spec.ts` | `teacher entry shows schedule and leave actions` | buttons navigate to V1-06 pages |
| `pages/teacher/schedule/__tests__/index.spec.ts` | `schedule renders loading_empty_error_success` | state blocks render correctly |
| `pages/teacher/schedule/__tests__/index.spec.ts` | `lesson tap navigates to detail` | route contains lessonId |
| `pages/teacher/lesson/__tests__/detail.spec.ts` | `detail hides attendance button when can_submit_attendance false` | button absent |
| `pages/teacher/lesson/__tests__/attendance.spec.ts` | `attendance applies leave defaults` | leave row has no_consume and 0 |
| `pages/teacher/lesson/__tests__/attendance.spec.ts` | `attendance disables submit while submitting` | duplicate tap does not call API twice |
| `pages/teacher/leave/__tests__/index.spec.ts` | `leave list filters pending by default` | API called with status pending |
| `pages/teacher/leave/__tests__/detail.spec.ts` | `approve requires review remark` | validation text displayed before API call |
| `pages/teacher/leave/__tests__/detail.spec.ts` | `reject conflict shows reload state` | 409 state rendered |
| `pages/teacher/components/__tests__/TeacherStateBlock.spec.ts` | `retry emits retry event` | click emits retry |

## Execution Commands

Backend focused tests:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter TeacherMobileContextResolverTest
composer test -- --filter TeacherMobileLessonRepositoryTest
composer test -- --filter TeacherMobileLeaveRepositoryTest
composer test -- --filter TeacherMobileLessonServiceTest
composer test -- --filter TeacherMobileAttendanceServiceTest
composer test -- --filter TeacherMobileLeaveServiceTest
composer test -- --filter TeacherMobileLessonApiTest
composer test -- --filter TeacherMobileAttendanceApiTest
composer test -- --filter TeacherMobileLeaveApiTest
composer test -- --filter TeacherMobileRoleIsolationTest
composer test -- --filter TeacherMobileCampusIsolationTest
composer test -- --filter TeacherMobileRegressionTest
```

Expected:

```text
Every listed backend command exits 0.
Each PHPUnit/co-phpunit run reports OK with zero failures and zero errors.
```

Backend quality gate:

```bash
cd mineadmin-education-saas/backend
composer analyse
composer cs-fix -- --dry-run
```

Expected:

```text
Static analysis exits 0.
Dry-run formatting exits 0 with no changed files required.
```

Mobile focused tests:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm test -- teacher.spec.ts
pnpm test -- teacherPagesJson
pnpm test -- pages/teacher
pnpm test -- TeacherStateBlock
```

Expected:

```text
Every listed mobile test command exits 0.
The test output reports all teacher mobile suites passing.
```

Mobile quality gate:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm lint
pnpm typecheck
pnpm build:h5
```

Expected:

```text
Lint exits 0.
TypeScript exits 0.
H5 build exits 0 and mobile-uniapp/dist/build/h5/index.html exists.
```

Cross-module regression gate:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter Attendance
composer test -- --filter LeaveMakeupReschedule
cd ../admin-web
pnpm test -- AttendanceReview LeaveRequestList
pnpm build
```

Expected:

```text
V1-04 attendance tests pass.
V1-05 leave/change tests pass.
Existing PC attendance and leave tests pass.
PC build exits 0.
```

No-migration verification:

```bash
cd mineadmin-education-saas
find backend/databases/migrations -maxdepth 1 -name '*010600*' -o -name '*teacher_mobile*'
```

Expected:

```text
No output.
```

## Acceptance Gate

V1-06 can be accepted only when all items below are true:

```text
Teacher mobile APIs live under backend/app/Http/Api and use /mobile/education/academic/teacher/*.
No backend/app/Http/Admin controller is created for V1-06.
No backend/databases/migrations file is created for V1-06.
Current teacher is resolved from F06 context and V1-01 edu_teachers.user_profile_id.
Guardian and non-teacher profiles receive code 403 on every V1-06 teacher route.
Teacher cannot read another teacher's lesson, attendance sheet, attendance result, or leave request.
Teacher cannot access a campus outside F02 campus scope.
Attendance sheet returns approved leave defaults as leave/no_consume/0.00.
Attendance submission calls V1-04 AttendanceService and preserves V1-04 idempotency.
Different attendance payload after submission returns code 409.
Teacher leave approve/reject updates only pending leave requests for assigned lessons.
Teacher leave review does not create make-up lessons and does not mutate account balance.
uni-app pages implement loading, empty, error, forbidden, retry, submitting, success, and conflict states.
pages.json registers schedule, lesson detail, attendance, leave list, and leave detail routes.
Backend focused tests, backend quality gate, mobile tests, mobile quality gate, and cross-module regression gate all exit 0.
```

## Task Breakdown

### Task 1: Backend Context And Repositories

**Files:**
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/TeacherMobileContextResolver.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/TeacherMobileLessonRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/TeacherMobileLeaveRepository.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherMobileContextResolverTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherMobileLessonRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherMobileLeaveRepositoryTest.php`

- [x] **Step 1: Write context resolver tests**

Expected cases:

```text
test_resolves_enabled_teacher_from_mobile_context
test_guardian_role_is_rejected
test_disabled_teacher_record_is_rejected
test_out_of_scope_campus_is_rejected
```

- [x] **Step 2: Implement context resolver**

Required methods:

```php
resolveTeacher(EducationUserContext $context): EducationTeacher
assertTeacherRole(EducationUserContext $context): void
assertCampusAllowed(EducationUserContext $context, ?int $campusId): ?int
currentOperatorId(EducationUserContext $context): ?int
```

- [x] **Step 3: Write repository tests**

Expected cases:

```text
today filters by teacher_id, campus scope, and selected date.
paged lessons exclude another teacher's lessons.
assigned leave page excludes leave from another teacher's lesson.
```

- [x] **Step 4: Implement repositories**

Required behavior:

```text
Filter tenant_id on every query.
Apply campus scope before returning rows.
Require lesson teacher_id to equal current teacher id.
Key attendance and leave rows by lesson_student_id for sheet composition.
```

- [x] **Step 5: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter TeacherMobileContextResolverTest
composer test -- --filter TeacherMobileLessonRepositoryTest
composer test -- --filter TeacherMobileLeaveRepositoryTest
```

Expected:

```text
All three commands exit 0.
```

### Task 2: Backend Lesson APIs

**Files:**
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherLessonTodayRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherLessonPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherLessonDetailRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/TeacherLessonController.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/TeacherMobileLessonService.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/TeacherMobileLessonSchema.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherMobileLessonServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/TeacherMobileLessonApiTest.php`

- [x] **Step 1: Write service and API tests**

Expected cases:

```text
today returns assigned lessons.
page returns MineAdmin page envelope.
detail returns lesson students, attendance_submitted, can_submit_attendance, and pending_leave_count.
wrong teacher lesson returns 404.
invalid date returns 422.
```

- [x] **Step 2: Implement request rules**

Use the exact rules documented in `Request Classes`.

- [x] **Step 3: Implement lesson service and schema**

Required output fields:

```text
lesson card fields, attendance_submitted, can_submit_attendance, pending_leave_count, lesson_students.
```

- [x] **Step 4: Implement lesson controller**

Endpoints:

```text
GET /mobile/education/academic/teacher/lessons/today
GET /mobile/education/academic/teacher/lessons/page
GET /mobile/education/academic/teacher/lessons/{lessonId}
```

- [x] **Step 5: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter TeacherMobileLessonServiceTest
composer test -- --filter TeacherMobileLessonApiTest
```

Expected:

```text
Both commands exit 0.
```

### Task 3: Backend Attendance APIs

**Files:**
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherAttendanceSheetRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherAttendanceSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherAttendanceResultRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/TeacherAttendanceController.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/TeacherMobileAttendanceService.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/TeacherMobileAttendanceSheetSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/TeacherMobileAttendanceResultSchema.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherMobileAttendanceServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/TeacherMobileAttendanceApiTest.php`

- [x] **Step 1: Write attendance tests**

Expected cases:

```text
sheet defaults approved leave to leave/no_consume/0.00.
submit passes normalized records to V1-04 AttendanceService.
submit rejects records that do not match lesson student set.
same submitted payload returns idempotent success.
different submitted payload returns 409.
result returns 404 when no attendance exists.
```

- [x] **Step 2: Implement attendance request rules**

Use the exact `TeacherAttendanceSaveRequest` rules documented above.

- [x] **Step 3: Implement sheet and result schemas**

Required output:

```text
sheet lesson, submitted flag, records, summary.
result batch number, counts, rows, and account_changes from V1-04 result.
```

- [x] **Step 4: Implement attendance service**

Required integration:

```text
Call AttendanceService::submit for writes.
Do not write account balances directly.
Preserve V1-04 conflict and idempotency behavior.
```

- [x] **Step 5: Implement attendance controller**

Endpoints:

```text
GET /mobile/education/academic/teacher/lessons/{lessonId}/attendance-sheet
POST /mobile/education/academic/teacher/lessons/{lessonId}/attendance
GET /mobile/education/academic/teacher/lessons/{lessonId}/attendance-result
```

- [x] **Step 6: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter TeacherMobileAttendanceServiceTest
composer test -- --filter TeacherMobileAttendanceApiTest
```

Expected:

```text
Both commands exit 0.
```

### Task 4: Backend Leave Review APIs

**Files:**
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherLeavePageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherLeaveDetailRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/TeacherLeaveReviewRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/TeacherLeaveController.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/TeacherMobileLeaveService.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/TeacherMobileLeaveSchema.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherMobileLeaveServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/TeacherMobileLeaveApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/TeacherMobileRoleIsolationTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/TeacherMobileCampusIsolationTest.php`

- [x] **Step 1: Write leave service and API tests**

Expected cases:

```text
page returns only leave requests for assigned teacher lessons.
detail wrong teacher returns 404.
approve pending leave returns approved.
reject pending leave returns rejected.
approve or reject non-pending leave returns 409.
missing review_remark returns 422.
guardian role returns 403 on all leave endpoints.
out-of-scope campus returns 403.
```

- [x] **Step 2: Implement leave request rules**

Use the exact `TeacherLeavePageRequest` and `TeacherLeaveReviewRequest` rules documented above.

- [x] **Step 3: Implement leave service**

Required write fields:

```text
status, reviewed_at, reviewed_by, review_remark.
```

- [x] **Step 4: Implement leave controller**

Endpoints:

```text
GET /mobile/education/academic/teacher/leave-requests/page
GET /mobile/education/academic/teacher/leave-requests/{id}
PUT /mobile/education/academic/teacher/leave-requests/{id}/approve
PUT /mobile/education/academic/teacher/leave-requests/{id}/reject
```

- [x] **Step 5: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter TeacherMobileLeaveServiceTest
composer test -- --filter TeacherMobileLeaveApiTest
composer test -- --filter TeacherMobileRoleIsolationTest
composer test -- --filter TeacherMobileCampusIsolationTest
```

Expected:

```text
All four commands exit 0.
```

### Task 5: Mobile API Client And Routes

**Files:**
- Create: `mineadmin-education-saas/mobile-uniapp/src/api/academic/teacher.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/src/api/academic/__tests__/teacher.spec.ts`
- Modify: `mineadmin-education-saas/mobile-uniapp/pages.json`
- Create: `mineadmin-education-saas/mobile-uniapp/tests/academic/teacherPagesJson.spec.ts`

- [x] **Step 1: Write API client tests**

Expected cases:

```text
successful envelope unwraps to data.
validation failure throws MobileApiError with field.
business failure throws MobileApiError with code and message.
```

- [x] **Step 2: Implement teacher API client**

Functions must match the `API Client` section exactly.

- [x] **Step 3: Write pages.json test**

Expected routes:

```text
pages/teacher/schedule/index
pages/teacher/lesson/detail
pages/teacher/lesson/attendance
pages/teacher/leave/index
pages/teacher/leave/detail
```

- [x] **Step 4: Modify pages.json**

Add the five V1-06 routes with the documented navigation titles.

- [x] **Step 5: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm test -- teacher.spec.ts
pnpm test -- teacherPagesJson
```

Expected:

```text
Both commands exit 0.
```

### Task 6: Mobile Teacher Schedule And Detail Pages

**Files:**
- Modify: `mineadmin-education-saas/mobile-uniapp/pages/teacher/index.vue`
- Modify: `mineadmin-education-saas/mobile-uniapp/pages/teacher/__tests__/index.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/schedule/index.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/schedule/__tests__/index.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/lesson/detail.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/lesson/__tests__/detail.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/components/TeacherStateBlock.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/components/LessonStatusBadge.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/components/__tests__/TeacherStateBlock.spec.ts`

- [x] **Step 1: Write page state tests**

Expected states:

```text
loading, empty, error, forbidden, retry, success.
```

- [x] **Step 2: Implement shared state and status components**

Components must use stable dimensions for state blocks and badges.

- [x] **Step 3: Update teacher entry page**

Add schedule and leave review entries wired to V1-06 routes.

- [x] **Step 4: Implement schedule page**

Use today list by default and paged list for filtered range.

- [x] **Step 5: Implement lesson detail page**

Show detail and route to attendance page only when `can_submit_attendance` is true.

- [x] **Step 6: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm test -- pages/teacher/__tests__/index.spec.ts
pnpm test -- pages/teacher/schedule
pnpm test -- pages/teacher/lesson/__tests__/detail.spec.ts
pnpm test -- TeacherStateBlock
```

Expected:

```text
All commands exit 0.
```

### Task 7: Mobile Attendance And Leave Pages

**Files:**
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/lesson/attendance.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/lesson/__tests__/attendance.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/leave/index.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/leave/detail.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/leave/__tests__/index.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/leave/__tests__/detail.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/components/AttendanceStudentRow.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/components/LeaveStatusBadge.vue`

- [x] **Step 1: Write attendance and leave page tests**

Expected cases:

```text
attendance applies leave defaults.
attendance prevents duplicate submit while submitting.
attendance 409 conflict shows reload result action.
leave list defaults to pending.
leave detail approve requires review_remark.
leave detail reject conflict shows conflict state.
```

- [x] **Step 2: Implement attendance page**

Use sheet API, row validation, submit API, result rendering, and conflict handling.

- [x] **Step 3: Implement leave list page**

Use paged API, status tabs, pull refresh, and bottom loading.

- [x] **Step 4: Implement leave detail page**

Use detail API, approve/reject APIs, remark validation, and status refresh after success.

- [x] **Step 5: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm test -- pages/teacher/lesson/__tests__/attendance.spec.ts
pnpm test -- pages/teacher/leave
```

Expected:

```text
Both commands exit 0.
```

### Task 8: Final Verification

**Files:**
- Verify: `mineadmin-education-saas/backend`
- Verify: `mineadmin-education-saas/mobile-uniapp`
- Verify: `mineadmin-education-saas/admin-web`

- [x] **Step 1: Run backend focused and quality gates**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter TeacherMobile
composer analyse
composer cs-fix -- --dry-run
```

Expected:

```text
TeacherMobile tests pass.
Static analysis exits 0.
Formatting dry run exits 0.
```

- [x] **Step 2: Run mobile focused and build gates**

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm test -- teacher
pnpm lint
pnpm typecheck
pnpm build:h5
```

Expected:

```text
Teacher mobile tests pass.
Lint exits 0.
Typecheck exits 0.
H5 build exits 0.
```

- [x] **Step 3: Run cross-module regression**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter Attendance
composer test -- --filter LeaveMakeupReschedule
cd ../admin-web
pnpm test -- AttendanceReview LeaveRequestList
pnpm build
```

Expected:

```text
V1-04, V1-05, and PC regression gates pass.
```

- [x] **Step 4: Confirm no migration**

Run:

```bash
cd mineadmin-education-saas
find backend/databases/migrations -maxdepth 1 -name '*010600*' -o -name '*teacher_mobile*'
```

Expected:

```text
No output.
```

## Self-Review

- Scope coverage: V1-06 covers teacher mobile schedule, lesson detail, attendance sheet, attendance submit, attendance result, leave list, leave detail, leave approve, and leave reject.
- MineAdmin fit: Backend mobile APIs use `backend/app/Http/Api`; shared logic uses `backend/app/Service`, `backend/app/Repository`, and `backend/app/Schema`; no V1-06 admin controller or migration is introduced.
- Dependency fit: Attendance submission reuses V1-04 `AttendanceService`; leave review reuses V1-05 leave state rules; teacher identity comes from F06 context and V1-01 teacher profile mapping.
- Permission fit: Role, teacher assignment, tenant, and campus isolation are explicit in services, repositories, API examples, and tests.
- Mobile fit: uni-app API client, routes, pages, shared components, page states, and tests are defined with exact file paths.
- Readiness: This plan has exact paths, no-migration design, Controller/Request/Service/Repository/Schema tasks, complete API request/success/validation/business failure examples, mobile page tasks, tests, commands, expected outputs, and acceptance gates, so V1-06 can be marked `ready`.
