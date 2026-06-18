# MineAdmin Education SaaS V1-05 Leave Makeup Reschedule Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V1 lightweight leave requests, make-up lesson creation, and lesson reschedule records for daily教学变更 workflows without bypassing scheduling conflicts or课消账本.

**Architecture:** V1-05 depends on Foundation F01 tenant/campus, F02 education user context and campus scope, F04 audit logging, F05 PC conventions, V1-01 student/guardian/teacher/classroom records, V1-02 student course accounts, V1-03 lessons and lesson-student snapshots, and V1-04 attendance/consumption status. Backend follows MineAdmin 3.x native paths under `app/Http/Admin`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, and `databases/migrations`; make-up and reschedule operations run inside transactions and call V1-03 conflict services before writing lesson changes. No teacher or guardian mobile page is added in V1-05.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, MineAdmin-Vue, Vue3, TypeScript, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** incomplete / not implemented. `ready` means this plan is detailed enough to start coding.

---

## Scope Check

Included:

- Create leave request and lesson change record tables.
- Create V1-05 enums for leave source, leave type, leave status, lesson change type, and lesson change status.
- Create models, repositories, services, request classes, schemas, and admin controllers for leave requests and lesson changes.
- Create staff-side leave requests for a single V1-03 lesson-student snapshot.
- Review, approve, reject, and cancel leave requests with strict state transitions.
- Create make-up lessons linked to an approved leave request and original lesson-student snapshot.
- Create make-up lesson-student snapshot for the leave student only.
- Reschedule a scheduled lesson by updating the original V1-03 lesson time, teacher, classroom, and title while preserving change history.
- Validate teacher, classroom, class, and student conflicts through V1-03 `SchedulingConflictService`.
- Prevent make-up or reschedule operations when V1-04 attendance/consumption already makes the change unsafe.
- Add admin APIs for leave page/detail/create/approve/reject/cancel and lesson change page/detail/make-up/reschedule.
- Add PC API client, route/menu entries, leave request page, lesson change page, forms, dialogs, drawers, permission-controlled buttons, loading/empty/error states, and tests.
- Add migration, repository, service, API, permission, tenant/campus isolation, audit, PC, and mobile regression tests.

Excluded:

- Advanced bulk lesson change center, capacity optimization, and automatic make-up matching; V2 owns them.
- Teacher mobile leave review UI; V1-06 owns visible teacher-side flows.
- Guardian mobile leave creation UI; V1-07 owns visible guardian-side flows.
- Attendance submission and account consumption; V1-04 owns them.
- Money refund, payment adjustment, and finance reconciliation; V4 owns them.
- Payroll impact from rescheduled or make-up lessons; V5 owns it.
- Final cross-module reports; V1-08 owns them.

Business rules:

```text
Leave requests and lesson change records are tenant-scoped and campus-scoped.
A leave request targets exactly one non-cancelled V1-03 lesson-student snapshot.
Leave request creation does not change account balance and does not create V1-04 attendance rows.
Approved leave is the source of truth for later attendance defaulting to leave + no_consume.
Leave can be approved only when the source lesson is scheduled or completed without consumption for that lesson student.
Leave approval after V1-04 consumption is rejected with code 409; consumption rollback must happen first in V1-04.
One lesson-student snapshot can have only one leave request record in V1-05.
Rejected or cancelled leave requests cannot be approved again; create/update behavior should reuse the existing row only through explicit admin action.
Make-up creation requires an approved leave request.
Make-up creation creates a new scheduled lesson and one planned lesson-student snapshot for the leave student only.
Make-up creation does not consume lesson units; V1-04 attendance on the make-up lesson consumes later.
Make-up creation updates the leave request status to makeup_scheduled and stores makeup_lesson_id.
Reschedule updates the original scheduled lesson; it does not create a second lesson.
Reschedule is rejected for completed or cancelled lessons.
Reschedule is rejected when V1-04 attendance or consumption exists for the lesson.
Every make-up and reschedule operation writes an immutable lesson change record.
```

Conflict and account rules:

```text
Make-up and reschedule use V1-03 time overlap rule: existing.start_at < target.end_at and existing.end_at > target.start_at.
Cancelled lessons are ignored by conflict checks.
Reschedule excludes the source lesson from its own conflict check.
Make-up validates teacher, classroom, class, and student conflicts.
Reschedule validates teacher, classroom, class, and all planned lesson-student conflicts.
Make-up validates the leave student's V1-02 student course account is active.
Make-up and reschedule do not mutate V1-02 student course account balances.
```

Status machines:

```text
Leave status: pending -> approved, pending -> rejected, pending -> cancelled, approved -> makeup_scheduled, approved -> closed, makeup_scheduled -> closed.
Lesson change status: confirmed -> cancelled only for a make-up change whose target lesson has no attendance or consumption; reschedule changes are historical and cannot be cancelled.
Lesson change type: makeup or reschedule.
Leave source: staff, guardian, teacher.
Leave type: sick, personal, school, other.
```

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_010500_create_v1_leave_change_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LeaveRequestSource.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LeaveType.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LeaveRequestStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LessonChangeType.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LessonChangeStatus.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLeaveRequest.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonChangeRecord.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/LeaveRequestRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonChangeRepository.php
mineadmin-education-saas/backend/app/Service/Education/Academic/LeaveRequestService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/MakeupLessonService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/RescheduleService.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LeaveRequestPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LeaveRequestCreateRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LeaveRequestReviewRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LeaveRequestCancelRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LessonChangePageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/MakeupLessonCreateRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/RescheduleLessonRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/LeaveRequestController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/LessonChangeController.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/LeaveRequestSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/LessonChangeSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/MakeupLessonResultSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/RescheduleResultSchema.php
```

Read backend dependencies:

```text
mineadmin-education-saas/backend/app/Service/Education/Foundation/EducationUserContext.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusScopeService.php
mineadmin-education-saas/backend/app/Event/Education/Foundation/EducationAuditEvent.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LessonStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LessonStudentStatus.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLesson.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonStudent.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonAttendance.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonConsumption.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudentCourseAccount.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonStudentRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/AttendanceRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/ConsumptionRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/StudentCourseAccountRepository.php
mineadmin-education-saas/backend/app/Service/Education/Academic/SchedulingConflictService.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Academic/LeaveMakeupRescheduleMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/LeaveRequestRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/LessonChangeRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/LeaveRequestServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/MakeupLessonServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/RescheduleServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/LeaveMakeupRescheduleAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/LeaveMakeupReschedulePermissionTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/LeaveMakeupRescheduleIsolationTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/LeaveMakeupRescheduleAuditTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/academic/lessonChange.ts
mineadmin-education-saas/admin-web/src/views/education/academic/LeaveRequestList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/LessonChangeList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/LeaveRequestForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/LeaveReviewDialog.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/MakeupLessonForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/RescheduleLessonForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/LessonChangeDetailDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/LeaveRequestList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/LeaveReviewDialog.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/LessonChangeList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/MakeupLessonForm.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/RescheduleLessonForm.spec.ts
```

Modify PC:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Verify mobile:

```text
mineadmin-education-saas/mobile-uniapp/package.json
```

## Database Migration Design

Migration file:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_010500_create_v1_leave_change_tables.php
```

Tables:

```text
edu_leave_requests
edu_lesson_change_records
```

Foreign-key policy:

```text
Use no physical foreign keys in V1-05.
Use service-level validation against V1-01 guardians/students/teachers/classrooms, V1-02 accounts, V1-03 lessons/lesson-students, and V1-04 attendance/consumption rows.
Reason: MineAdmin business tables use soft deletes, tenant isolation, and staged module migrations; service validation keeps rollback and soft-delete behavior predictable.
```

Rollback order:

```text
Schema::dropIfExists('edu_lesson_change_records');
Schema::dropIfExists('edu_leave_requests');
```

### `edu_leave_requests`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Leave request id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Campus id |
| `leave_no` | varchar(64) | no | none | Leave request number |
| `source` | varchar(20) | no | `staff` | staff, guardian, or teacher |
| `leave_type` | varchar(20) | no | `other` | sick, personal, school, or other |
| `lesson_id` | bigint unsigned | no | none | Source lesson id |
| `lesson_student_id` | bigint unsigned | no | none | Source lesson student id |
| `class_id` | bigint unsigned | no | none | Class id |
| `course_id` | bigint unsigned | no | none | Course id |
| `student_id` | bigint unsigned | no | none | Student id |
| `account_id` | bigint unsigned | no | none | Student course account id |
| `guardian_id` | bigint unsigned | yes | null | Guardian id when source is guardian |
| `teacher_id` | bigint unsigned | yes | null | Teacher id of source lesson |
| `reason` | varchar(500) | no | none | Leave reason |
| `status` | varchar(30) | no | `pending` | pending, approved, rejected, cancelled, makeup_scheduled, or closed |
| `requested_at` | timestamp | yes | null | Requested time |
| `reviewed_at` | timestamp | yes | null | Review time |
| `reviewed_by` | bigint unsigned | yes | null | Reviewer user id |
| `review_remark` | varchar(500) | yes | null | Review remark |
| `cancelled_at` | timestamp | yes | null | Cancellation time |
| `cancelled_by` | bigint unsigned | yes | null | Cancellation operator user id |
| `cancel_reason` | varchar(500) | yes | null | Cancellation reason |
| `makeup_required` | tinyint(1) | no | 1 | Whether make-up is required |
| `makeup_lesson_id` | bigint unsigned | yes | null | Created make-up lesson id |
| `remark` | varchar(500) | yes | null | Internal remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_leave_requests_tenant_no (tenant_id, leave_no)
unique uk_edu_leave_requests_tenant_lesson_student (tenant_id, lesson_student_id)
index idx_edu_leave_requests_tenant_campus_status (tenant_id, campus_id, status)
index idx_edu_leave_requests_tenant_student_status (tenant_id, student_id, status)
index idx_edu_leave_requests_tenant_lesson (tenant_id, lesson_id)
index idx_edu_leave_requests_tenant_makeup_lesson (tenant_id, makeup_lesson_id)
index idx_edu_leave_requests_deleted_at (deleted_at)
```

### `edu_lesson_change_records`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Lesson change record id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Campus id |
| `change_no` | varchar(64) | no | none | Lesson change number |
| `change_type` | varchar(20) | no | none | makeup or reschedule |
| `status` | varchar(20) | no | `confirmed` | confirmed or cancelled |
| `leave_request_id` | bigint unsigned | yes | null | Leave request id for make-up |
| `source_lesson_id` | bigint unsigned | no | none | Original lesson id |
| `source_lesson_student_id` | bigint unsigned | yes | null | Original lesson student id for make-up |
| `target_lesson_id` | bigint unsigned | yes | null | Target lesson id for make-up or rescheduled source lesson id |
| `class_id` | bigint unsigned | no | none | Class id |
| `course_id` | bigint unsigned | no | none | Course id |
| `student_id` | bigint unsigned | yes | null | Student id for make-up |
| `account_id` | bigint unsigned | yes | null | Student course account id for make-up |
| `source_teacher_id` | bigint unsigned | yes | null | Source teacher id |
| `target_teacher_id` | bigint unsigned | yes | null | Target teacher id |
| `source_classroom_id` | bigint unsigned | yes | null | Source classroom id |
| `target_classroom_id` | bigint unsigned | yes | null | Target classroom id |
| `source_start_at` | timestamp | yes | null | Source lesson start time |
| `source_end_at` | timestamp | yes | null | Source lesson end time |
| `target_start_at` | timestamp | yes | null | Target lesson start time |
| `target_end_at` | timestamp | yes | null | Target lesson end time |
| `lesson_units` | decimal(10,2) | no | 1.00 | Lesson units |
| `reason` | varchar(500) | no | none | Change reason |
| `cancelled_at` | timestamp | yes | null | Cancellation time |
| `cancelled_by` | bigint unsigned | yes | null | Cancellation operator user id |
| `cancel_reason` | varchar(500) | yes | null | Cancellation reason |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_lesson_change_records_tenant_no (tenant_id, change_no)
index idx_edu_lesson_change_records_tenant_campus_status (tenant_id, campus_id, status)
index idx_edu_lesson_change_records_tenant_type_status (tenant_id, change_type, status)
index idx_edu_lesson_change_records_tenant_source_lesson (tenant_id, source_lesson_id)
index idx_edu_lesson_change_records_tenant_target_lesson (tenant_id, target_lesson_id)
index idx_edu_lesson_change_records_tenant_leave (tenant_id, leave_request_id)
index idx_edu_lesson_change_records_deleted_at (deleted_at)
```

## MineAdmin Backend Module Design

### Enums

Create `LeaveRequestSource`:

```php
enum LeaveRequestSource: string
{
    case Staff = 'staff';
    case Guardian = 'guardian';
    case Teacher = 'teacher';
}
```

Create `LeaveType`:

```php
enum LeaveType: string
{
    case Sick = 'sick';
    case Personal = 'personal';
    case School = 'school';
    case Other = 'other';
}
```

Create `LeaveRequestStatus`:

```php
enum LeaveRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case MakeupScheduled = 'makeup_scheduled';
    case Closed = 'closed';
}
```

Create `LessonChangeType`:

```php
enum LessonChangeType: string
{
    case Makeup = 'makeup';
    case Reschedule = 'reschedule';
}
```

Create `LessonChangeStatus`:

```php
enum LessonChangeStatus: string
{
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
}
```

### Models

All models use MineAdmin/Hyperf model conventions, timestamps, soft deletes, and guarded tenant fields only through service methods.

`EducationLeaveRequest`:

```text
table: edu_leave_requests
fillable: tenant_id, campus_id, leave_no, source, leave_type, lesson_id, lesson_student_id, class_id, course_id, student_id, account_id, guardian_id, teacher_id, reason, status, requested_at, reviewed_at, reviewed_by, review_remark, cancelled_at, cancelled_by, cancel_reason, makeup_required, makeup_lesson_id, remark, created_by, updated_by
casts: tenant_id integer, campus_id integer, lesson_id integer, lesson_student_id integer, class_id integer, course_id integer, student_id integer, account_id integer, guardian_id integer, teacher_id integer, requested_at datetime, reviewed_at datetime, reviewed_by integer, cancelled_at datetime, cancelled_by integer, makeup_required boolean, makeup_lesson_id integer
soft delete: yes
relationships: lesson belongsTo EducationLesson, lessonStudent belongsTo EducationLessonStudent, makeupLesson belongsTo EducationLesson, account belongsTo EducationStudentCourseAccount
```

`EducationLessonChangeRecord`:

```text
table: edu_lesson_change_records
fillable: tenant_id, campus_id, change_no, change_type, status, leave_request_id, source_lesson_id, source_lesson_student_id, target_lesson_id, class_id, course_id, student_id, account_id, source_teacher_id, target_teacher_id, source_classroom_id, target_classroom_id, source_start_at, source_end_at, target_start_at, target_end_at, lesson_units, reason, cancelled_at, cancelled_by, cancel_reason, created_by, updated_by
casts: tenant_id integer, campus_id integer, leave_request_id integer, source_lesson_id integer, source_lesson_student_id integer, target_lesson_id integer, class_id integer, course_id integer, student_id integer, account_id integer, source_teacher_id integer, target_teacher_id integer, source_classroom_id integer, target_classroom_id integer, source_start_at datetime, source_end_at datetime, target_start_at datetime, target_end_at datetime, lesson_units decimal:2, cancelled_at datetime, cancelled_by integer
soft delete: yes
relationships: leaveRequest belongsTo EducationLeaveRequest, sourceLesson belongsTo EducationLesson, targetLesson belongsTo EducationLesson
```

### Repositories

All repositories extend:

```text
App\Repository\IRepository
```

Common query rules:

```text
tenant_id is always resolved from EducationUserContext for tenant users.
platform users may pass tenant_id when their role permits platform access.
campus_id is filtered by F02 campus scope.
keyword matches leave_no, change_no, lesson_no, student snapshot fields, and reason fields where present.
status exact match.
page and pageSize use MineAdmin pagination defaults and cap pageSize at 100.
```

`LeaveRequestRepository` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function findScoped(int $id, EducationUserContext $context): ?EducationLeaveRequest
public function findByLessonStudent(int $lessonStudentId, int $tenantId): ?EducationLeaveRequest
public function existsForLessonStudent(int $lessonStudentId, int $tenantId, ?int $excludeId = null): bool
public function createRequest(array $data): EducationLeaveRequest
public function updateStatus(int $id, string $status, array $data, ?int $operatorId): EducationLeaveRequest
public function nextLeaveNo(int $tenantId, int $campusId): string
```

`LessonChangeRepository` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function findScoped(int $id, EducationUserContext $context): ?EducationLessonChangeRecord
public function createRecord(array $data): EducationLessonChangeRecord
public function hasMakeupForLeave(int $leaveRequestId, int $tenantId): bool
public function hasRescheduleForLessonAtTarget(int $sourceLessonId, string $targetStartAt, string $targetEndAt, int $tenantId): bool
public function nextChangeNo(int $tenantId, int $campusId): string
```

### Services

All services extend:

```text
App\Service\IService
```

Common service rules:

```text
Resolve EducationUserContext from F02 before every operation.
Validate tenant id and campus id through Foundation services.
Reject writes outside tenant or campus scope with code 403.
Reject cancelled/completed lessons or unsafe attendance/consumption state with documented 409 codes.
Write F04 audit events after successful leave create/review/cancel, make-up creation, and reschedule operations.
Use MineAdmin OperationMiddleware on write controllers.
Use database transactions for make-up and reschedule operations.
```

`LeaveRequestService` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function detail(int $id, EducationUserContext $context): EducationLeaveRequest
public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationLeaveRequest
public function approve(int $id, string $reviewRemark, EducationUserContext $context, ?int $operatorId): EducationLeaveRequest
public function reject(int $id, string $reviewRemark, EducationUserContext $context, ?int $operatorId): EducationLeaveRequest
public function cancel(int $id, string $cancelReason, EducationUserContext $context, ?int $operatorId): EducationLeaveRequest
```

Leave creation rules:

```text
lesson_student_id must point to a non-cancelled V1-03 lesson-student snapshot visible in tenant and campus scope.
The source lesson must not be cancelled.
The lesson-student snapshot account_id must point to an active V1-02 student course account.
Duplicate leave request for the same lesson_student_id is rejected with code 409.
source guardian requires guardian_id and same-tenant student binding from V1-01.
source teacher requires teacher_id and teacher must be assigned to the lesson.
leave_no is generated as LEA + yyyyMMddHHmmss + tenant short id + random 4 digits and retried up to 3 times on unique-key collision.
```

Leave review rules:

```text
Only pending leave can be approved or rejected.
Approval rejects completed lessons that already have active V1-04 consumption for this lesson student.
Approval does not write V1-04 attendance rows.
Approval sets reviewed_at, reviewed_by, review_remark, and status approved.
Rejection sets reviewed_at, reviewed_by, review_remark, and status rejected.
Cancellation is allowed only while status is pending or approved and before makeup_scheduled.
```

`MakeupLessonService` methods:

```php
public function create(array $data, EducationUserContext $context, ?int $operatorId): array
protected function createMakeupLesson(EducationLeaveRequest $leaveRequest, array $data, EducationUserContext $context, ?int $operatorId): array
```

Make-up creation transaction:

```text
1. Validate leave request exists in current tenant and campus scope.
2. Require leave status approved.
3. Reject if makeup_lesson_id is already present or a make-up change record exists.
4. Load source lesson and source lesson-student snapshot.
5. Validate target teacher is enabled and authorized for source course through V1-02 teacher-course authorization.
6. Validate target classroom is enabled and same tenant/campus when classroom_id is present.
7. Validate target start_at < target end_at and compute duration_minutes.
8. Validate leave student's account is active and same account as source lesson-student account_id.
9. Run V1-03 conflict checks for teacher, classroom, class, and the leave student.
10. Generate makeup lesson_no as LES + yyyyMMddHHmmss + tenant short id + random 4 digits and retry up to 3 times on unique-key collision.
11. Create a scheduled V1-03 lesson with one student_count and source_type manual.
12. Create one V1-03 lesson-student snapshot for the leave student.
13. Create lesson change record with change_type makeup and status confirmed.
14. Update leave request status to makeup_scheduled and set makeup_lesson_id.
15. Dispatch audit event `education.academic.lesson_change.makeup_created`.
16. Commit the transaction and return leave request, target lesson, target lesson student, and change record summary.
```

`RescheduleService` methods:

```php
public function reschedule(array $data, EducationUserContext $context, ?int $operatorId): array
```

Reschedule transaction:

```text
1. Validate source lesson exists in current tenant and campus scope.
2. Require source lesson status scheduled.
3. Reject if any V1-04 attendance row exists for source lesson.
4. Reject if any V1-04 consumption row exists for source lesson.
5. Validate target teacher is enabled and authorized for the source course.
6. Validate target classroom is enabled and same tenant/campus when classroom_id is present.
7. Validate target start_at < target end_at and compute duration_minutes.
8. Run V1-03 conflict checks for teacher, classroom, class, and all planned lesson students, excluding source_lesson_id.
9. Update the source lesson with target teacher, classroom, title, start_at, end_at, duration_minutes, lesson_units, and snapshots.
10. Create lesson change record with change_type reschedule, source and target fields, and status confirmed.
11. Dispatch audit event `education.academic.lesson_change.rescheduled`.
12. Commit the transaction and return updated lesson plus change record summary.
```

### Request Classes

`LeaveRequestPageRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'tenant_id' => ['nullable', 'integer', 'min:1'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'student_id' => ['nullable', 'integer', 'min:1'],
    'class_id' => ['nullable', 'integer', 'min:1'],
    'lesson_id' => ['nullable', 'integer', 'min:1'],
    'source' => ['nullable', 'in:staff,guardian,teacher'],
    'status' => ['nullable', 'in:pending,approved,rejected,cancelled,makeup_scheduled,closed'],
    'keyword' => ['nullable', 'string', 'max:120'],
]
```

`LeaveRequestCreateRequest` rules:

```php
[
    'lesson_student_id' => ['required', 'integer', 'min:1'],
    'source' => ['required', 'in:staff,guardian,teacher'],
    'leave_type' => ['required', 'in:sick,personal,school,other'],
    'guardian_id' => ['nullable', 'integer', 'min:1'],
    'teacher_id' => ['nullable', 'integer', 'min:1'],
    'reason' => ['required', 'string', 'max:500'],
    'makeup_required' => ['nullable', 'boolean'],
    'remark' => ['nullable', 'string', 'max:500'],
]
```

`LeaveRequestReviewRequest` rules:

```php
[
    'review_remark' => ['required', 'string', 'max:500'],
]
```

`LeaveRequestCancelRequest` rules:

```php
[
    'cancel_reason' => ['required', 'string', 'max:500'],
]
```

`LessonChangePageRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'tenant_id' => ['nullable', 'integer', 'min:1'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'change_type' => ['nullable', 'in:makeup,reschedule'],
    'status' => ['nullable', 'in:confirmed,cancelled'],
    'source_lesson_id' => ['nullable', 'integer', 'min:1'],
    'target_lesson_id' => ['nullable', 'integer', 'min:1'],
    'student_id' => ['nullable', 'integer', 'min:1'],
    'keyword' => ['nullable', 'string', 'max:120'],
]
```

`MakeupLessonCreateRequest` rules:

```php
[
    'leave_request_id' => ['required', 'integer', 'min:1'],
    'teacher_id' => ['required', 'integer', 'min:1'],
    'classroom_id' => ['nullable', 'integer', 'min:1'],
    'title' => ['required', 'string', 'max:160'],
    'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'lesson_units' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
    'reason' => ['required', 'string', 'max:500'],
]
```

`RescheduleLessonRequest` rules:

```php
[
    'source_lesson_id' => ['required', 'integer', 'min:1'],
    'teacher_id' => ['required', 'integer', 'min:1'],
    'classroom_id' => ['nullable', 'integer', 'min:1'],
    'title' => ['required', 'string', 'max:160'],
    'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'lesson_units' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
    'reason' => ['required', 'string', 'max:500'],
]
```

Validation messages:

```text
page.required: page is required
pageSize.between: pageSize must be between 1 and 100
lesson_student_id.required: lesson_student_id is required
leave_request_id.required: leave_request_id is required
source_lesson_id.required: source_lesson_id is required
teacher_id.required: teacher_id is required
title.required: title is required
start_at.required: start_at is required
end_at.required: end_at is required
lesson_units.min: lesson_units must be greater than 0
reason.required: reason is required
review_remark.required: review_remark is required
cancel_reason.required: cancel_reason is required
status.in: status has an invalid value
```

### Controllers

Controller base:

```text
Use `#[Controller(prefix: 'admin/education/academic/<resource>')]`.
Use MineAdmin auth middleware.
Use Permission attributes on every endpoint.
Use OperationMiddleware on leave create/approve/reject/cancel, make-up create, and reschedule endpoints.
Return MineAdmin Result shape through `$this->success(...)`.
Resolve current EducationUserContext through F02 context resolver.
```

`LeaveRequestController`:

```php
public function page(LeaveRequestPageRequest $request): Result
public function detail(int $id): Result
public function create(LeaveRequestCreateRequest $request): Result
public function approve(int $id, LeaveRequestReviewRequest $request): Result
public function reject(int $id, LeaveRequestReviewRequest $request): Result
public function cancel(int $id, LeaveRequestCancelRequest $request): Result
```

`LessonChangeController`:

```php
public function page(LessonChangePageRequest $request): Result
public function detail(int $id): Result
public function makeup(MakeupLessonCreateRequest $request): Result
public function reschedule(RescheduleLessonRequest $request): Result
```

### Schemas

Schema fields:

```text
LeaveRequestSchema: id, tenant_id, campus_id, leave_no, source, leave_type, lesson_id, lesson_student_id, class_id, course_id, student_id, student_name, account_id, guardian_id, teacher_id, reason, status, requested_at, reviewed_at, reviewed_by, review_remark, cancelled_at, cancelled_by, cancel_reason, makeup_required, makeup_lesson_id, remark, created_at, updated_at
LessonChangeSchema: id, tenant_id, campus_id, change_no, change_type, status, leave_request_id, source_lesson_id, source_lesson_student_id, target_lesson_id, class_id, course_id, student_id, account_id, source_teacher_id, target_teacher_id, source_classroom_id, target_classroom_id, source_start_at, source_end_at, target_start_at, target_end_at, lesson_units, reason, cancelled_at, cancelled_by, cancel_reason, created_at
MakeupLessonResultSchema: leave_request, target_lesson, target_lesson_student, change_record
RescheduleResultSchema: lesson, change_record
```

## API Contract

### Endpoint Matrix

| API | Permission | Caller | Isolation | Audit |
| --- | --- | --- | --- | --- |
| `GET /admin/education/academic/leave-requests/page` | `education:academic:leave-request:page` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `GET /admin/education/academic/leave-requests/{id}` | `education:academic:leave-request:detail` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `POST /admin/education/academic/leave-requests` | `education:academic:leave-request:create` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | `education.academic.leave_request.created` |
| `PUT /admin/education/academic/leave-requests/{id}/approve` | `education:academic:leave-request:approve` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.leave_request.approved` |
| `PUT /admin/education/academic/leave-requests/{id}/reject` | `education:academic:leave-request:reject` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.leave_request.rejected` |
| `PUT /admin/education/academic/leave-requests/{id}/cancel` | `education:academic:leave-request:cancel` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | `education.academic.leave_request.cancelled` |
| `GET /admin/education/academic/lesson-changes/page` | `education:academic:lesson-change:page` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `GET /admin/education/academic/lesson-changes/{id}` | `education:academic:lesson-change:detail` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `POST /admin/education/academic/lesson-changes/makeup` | `education:academic:lesson-change:makeup` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.lesson_change.makeup_created` |
| `POST /admin/education/academic/lesson-changes/reschedule` | `education:academic:lesson-change:reschedule` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.lesson_change.rescheduled` |

Headers for tenant-scoped callers:

```text
Authorization: Bearer test-admin-token
X-Tenant-Id: 1001
X-Campus-Id: 2001
X-Request-Id: req-v1-leave-change-001
```

### Resource Examples

Leave approve success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 801,
    "leave_no": "LEA2026061210000010014821",
    "status": "approved",
    "reviewed_at": "2026-06-12 09:20:00",
    "review_remark": "Approved before class"
  }
}
```

Make-up creation success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "leave_request": {
      "id": 801,
      "status": "makeup_scheduled",
      "makeup_lesson_id": 9101
    },
    "target_lesson": {
      "id": 9101,
      "lesson_no": "LES2026061510000010019231",
      "title": "Art A Make-up",
      "student_count": 1,
      "status": "scheduled"
    },
    "change_record": {
      "id": 901,
      "change_no": "CHG2026061210300010013312",
      "change_type": "makeup",
      "status": "confirmed"
    }
  }
}
```

Reschedule conflict failure:

```json
{
  "code": 409,
  "message": "teacher time conflict",
  "data": {
    "conflict_type": "teacher",
    "lesson_ids": [9009],
    "teacher_id": 201
  }
}
```

### Endpoint-Level Request/Response/Failure Catalog

Use this catalog as the controller test fixture set. Each API has a concrete request, success response, validation failure response, and business failure response.

```json
[
  {
    "api": "GET /admin/education/academic/leave-requests/page",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "student_id": 101, "status": "pending", "keyword": "LEA"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 801, "leave_no": "LEA2026061210000010014821", "student_id": 101, "status": "pending"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "page is required", "data": {"field": "page"}},
    "business_failure": {"code": 403, "message": "campus is outside current context", "data": {"campus_id": 9999}}
  },
  {
    "api": "GET /admin/education/academic/leave-requests/{id}",
    "request": {"path": {"id": 801}},
    "success": {"code": 200, "message": "success", "data": {"id": 801, "leave_no": "LEA2026061210000010014821", "lesson_id": 9001, "lesson_student_id": 10001, "status": "pending", "reason": "Sick leave"}},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 404, "message": "leave request not found in current context", "data": {"id": 801}}
  },
  {
    "api": "POST /admin/education/academic/leave-requests",
    "request": {"body": {"lesson_student_id": 10001, "source": "staff", "leave_type": "sick", "reason": "Sick leave", "makeup_required": true, "remark": "Called front desk"}},
    "success": {"code": 200, "message": "success", "data": {"id": 801, "leave_no": "LEA2026061210000010014821", "lesson_student_id": 10001, "status": "pending"}},
    "validation_failure": {"code": 422, "message": "lesson_student_id is required", "data": {"field": "lesson_student_id"}},
    "business_failure": {"code": 409, "message": "leave request already exists for lesson student", "data": {"lesson_student_id": 10001}}
  },
  {
    "api": "PUT /admin/education/academic/leave-requests/{id}/approve",
    "request": {"path": {"id": 801}, "body": {"review_remark": "Approved before class"}},
    "success": {"code": 200, "message": "success", "data": {"id": 801, "status": "approved", "review_remark": "Approved before class"}},
    "validation_failure": {"code": 422, "message": "review_remark is required", "data": {"field": "review_remark"}},
    "business_failure": {"code": 409, "message": "lesson student already has active consumption", "data": {"lesson_student_id": 10001}}
  },
  {
    "api": "PUT /admin/education/academic/leave-requests/{id}/reject",
    "request": {"path": {"id": 801}, "body": {"review_remark": "Leave reason is invalid"}},
    "success": {"code": 200, "message": "success", "data": {"id": 801, "status": "rejected", "review_remark": "Leave reason is invalid"}},
    "validation_failure": {"code": 422, "message": "review_remark is required", "data": {"field": "review_remark"}},
    "business_failure": {"code": 409, "message": "only pending leave can be rejected", "data": {"id": 801, "status": "approved"}}
  },
  {
    "api": "PUT /admin/education/academic/leave-requests/{id}/cancel",
    "request": {"path": {"id": 801}, "body": {"cancel_reason": "Student will attend normally"}},
    "success": {"code": 200, "message": "success", "data": {"id": 801, "status": "cancelled", "cancel_reason": "Student will attend normally"}},
    "validation_failure": {"code": 422, "message": "cancel_reason is required", "data": {"field": "cancel_reason"}},
    "business_failure": {"code": 409, "message": "leave request with make-up lesson cannot be cancelled", "data": {"id": 801, "status": "makeup_scheduled"}}
  },
  {
    "api": "GET /admin/education/academic/lesson-changes/page",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "change_type": "makeup", "status": "confirmed"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 901, "change_no": "CHG2026061210300010013312", "change_type": "makeup", "source_lesson_id": 9001, "target_lesson_id": 9101, "status": "confirmed"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "pageSize must be between 1 and 100", "data": {"field": "pageSize"}},
    "business_failure": {"code": 403, "message": "campus is outside current context", "data": {"campus_id": 9999}}
  },
  {
    "api": "GET /admin/education/academic/lesson-changes/{id}",
    "request": {"path": {"id": 901}},
    "success": {"code": 200, "message": "success", "data": {"id": 901, "change_no": "CHG2026061210300010013312", "change_type": "makeup", "source_lesson_id": 9001, "target_lesson_id": 9101, "status": "confirmed"}},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 404, "message": "lesson change record not found in current context", "data": {"id": 901}}
  },
  {
    "api": "POST /admin/education/academic/lesson-changes/makeup",
    "request": {"body": {"leave_request_id": 801, "teacher_id": 201, "classroom_id": 1, "title": "Art A Make-up", "start_at": "2026-06-15 10:00:00", "end_at": "2026-06-15 11:00:00", "lesson_units": 1, "reason": "Approved leave make-up"}},
    "success": {"code": 200, "message": "success", "data": {"leave_request": {"id": 801, "status": "makeup_scheduled", "makeup_lesson_id": 9101}, "target_lesson": {"id": 9101, "student_count": 1, "status": "scheduled"}, "change_record": {"id": 901, "change_type": "makeup", "status": "confirmed"}}},
    "validation_failure": {"code": 422, "message": "leave_request_id is required", "data": {"field": "leave_request_id"}},
    "business_failure": {"code": 409, "message": "student time conflict", "data": {"conflict_type": "student", "lesson_ids": [9008], "student_ids": [101]}}
  },
  {
    "api": "POST /admin/education/academic/lesson-changes/reschedule",
    "request": {"body": {"source_lesson_id": 9001, "teacher_id": 201, "classroom_id": 2, "title": "Art Basics Lesson 1 Rescheduled", "start_at": "2026-06-16 10:00:00", "end_at": "2026-06-16 11:00:00", "lesson_units": 1, "reason": "Teacher meeting conflict"}},
    "success": {"code": 200, "message": "success", "data": {"lesson": {"id": 9001, "title": "Art Basics Lesson 1 Rescheduled", "start_at": "2026-06-16 10:00:00", "end_at": "2026-06-16 11:00:00"}, "change_record": {"id": 902, "change_type": "reschedule", "status": "confirmed"}}},
    "validation_failure": {"code": 422, "message": "source_lesson_id is required", "data": {"field": "source_lesson_id"}},
    "business_failure": {"code": 409, "message": "teacher time conflict", "data": {"conflict_type": "teacher", "lesson_ids": [9009], "teacher_id": 201}}
  }
]
```

## PC Admin Page Tasks

### API Client

Create:

```text
mineadmin-education-saas/admin-web/src/api/education/academic/lessonChange.ts
```

Types:

```ts
export type LeaveRequestSource = 'staff' | 'guardian' | 'teacher'
export type LeaveType = 'sick' | 'personal' | 'school' | 'other'
export type LeaveRequestStatus = 'pending' | 'approved' | 'rejected' | 'cancelled' | 'makeup_scheduled' | 'closed'
export type LessonChangeType = 'makeup' | 'reschedule'
export type LessonChangeStatus = 'confirmed' | 'cancelled'

export interface PageResult<T> {
  list: T[]
  total: number
}

export interface LeaveRequestRecord {
  id: number
  leave_no: string
  source: LeaveRequestSource
  leave_type: LeaveType
  lesson_id: number
  lesson_student_id: number
  student_id: number
  student_name?: string
  reason: string
  status: LeaveRequestStatus
  makeup_required: boolean
  makeup_lesson_id?: number | null
}

export interface LessonChangeRecord {
  id: number
  change_no: string
  change_type: LessonChangeType
  status: LessonChangeStatus
  leave_request_id?: number | null
  source_lesson_id: number
  source_lesson_student_id?: number | null
  target_lesson_id?: number | null
  source_start_at?: string | null
  target_start_at?: string | null
  lesson_units: string
  reason: string
}
```

Methods:

```ts
export function pageLeaveRequests(params: Record<string, unknown>): Promise<PageResult<LeaveRequestRecord>>
export function getLeaveRequest(id: number): Promise<LeaveRequestRecord>
export function createLeaveRequest(payload: Record<string, unknown>): Promise<LeaveRequestRecord>
export function approveLeaveRequest(id: number, review_remark: string): Promise<LeaveRequestRecord>
export function rejectLeaveRequest(id: number, review_remark: string): Promise<LeaveRequestRecord>
export function cancelLeaveRequest(id: number, cancel_reason: string): Promise<LeaveRequestRecord>
export function pageLessonChanges(params: Record<string, unknown>): Promise<PageResult<LessonChangeRecord>>
export function getLessonChange(id: number): Promise<LessonChangeRecord>
export function createMakeupLesson(payload: Record<string, unknown>): Promise<Record<string, unknown>>
export function rescheduleLesson(payload: Record<string, unknown>): Promise<Record<string, unknown>>
```

### Routes and Menus

Modify:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Route entries:

```text
Route: /education/academic/leave-requests
Route name: EducationAcademicLeaveRequestList
Menu: 教务 SaaS / 请假调课 / 请假申请
Permission: education:academic:leave-request:page
Page file: admin-web/src/views/education/academic/LeaveRequestList.vue

Route: /education/academic/lesson-changes
Route name: EducationAcademicLessonChangeList
Menu: 教务 SaaS / 请假调课 / 补课调课
Permission: education:academic:lesson-change:page
Page file: admin-web/src/views/education/academic/LessonChangeList.vue
```

### LeaveRequestList

File:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/LeaveRequestList.vue
```

Component files:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/components/LeaveRequestForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/LeaveReviewDialog.vue
```

Search fields:

```text
campus_id, student_id, class_id, lesson_id, source, status, keyword
```

Table columns:

```text
leave_no, source, leave_type, student_name, lesson_id, reason, status, makeup_required, makeup_lesson_id, requested_at, reviewed_at
```

Actions and states:

```text
create button: education:academic:leave-request:create
detail button: education:academic:leave-request:detail
approve button: education:academic:leave-request:approve
reject button: education:academic:leave-request:reject
cancel button: education:academic:leave-request:cancel
loading: table skeleton while pageLeaveRequests is pending
empty: show MineAdmin empty state when total is 0
error: show API message and keep filters
permission: hide action buttons without matching permission code
create success: close form drawer and reload first page
review validation failure: keep dialog open and show field message
review business failure: keep dialog open and show consumption or state transition message
cancel success: reload row status as cancelled
```

### LessonChangeList

File:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/LessonChangeList.vue
```

Component files:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/components/MakeupLessonForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/RescheduleLessonForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/LessonChangeDetailDrawer.vue
```

Search fields:

```text
campus_id, change_type, status, source_lesson_id, target_lesson_id, student_id, keyword
```

Table columns:

```text
change_no, change_type, status, source_lesson_id, target_lesson_id, student_name, source_start_at, target_start_at, lesson_units, reason, created_at
```

Make-up form fields:

```text
leave_request_id selector filtered by approved leave requests
teacher_id selector filtered by course authorization
classroom_id selector
title input
start_at and end_at datetime pickers
lesson_units decimal input
reason textarea
```

Reschedule form fields:

```text
source_lesson_id selector filtered by scheduled lessons
teacher_id selector filtered by course authorization
classroom_id selector
title input
start_at and end_at datetime pickers
lesson_units decimal input
reason textarea
```

Actions and states:

```text
make-up button: education:academic:lesson-change:makeup
reschedule button: education:academic:lesson-change:reschedule
detail button: education:academic:lesson-change:detail
loading/empty/error/permission states match LeaveRequestList
make-up success: close form and show target lesson id plus change_no
make-up conflict: keep form open and show conflict_type plus lesson_ids
reschedule success: close form and reload changed lesson row
reschedule conflict: keep form open and show teacher/classroom/class/student conflict details
detail drawer: shows source and target fields for audit trail
```

## Teacher / Guardian Mobile Page Tasks

This module has no teacher or guardian page because V1-05 creates admin-side leave, make-up, and reschedule services; visible teacher leave review is implemented in V1-06 and guardian leave creation is implemented in V1-07.

Mobile dependency notes:

```text
V1-06 teacher mobile can call teacher-scoped leave review APIs created in V1-06 and reuse V1-05 services.
V1-07 guardian mobile can call guardian-scoped leave creation APIs created in V1-07 and reuse V1-05 services.
No mobile-uniapp API client or pages are created in V1-05.
Run the mobile H5 build to prove V1-05 did not break the existing mobile shell.
```

Role isolation tests deferred to mobile modules:

```text
Teacher can access only lessons/classes/students assigned to the current teacher profile in V1-06.
Guardian can access only students bound to the current guardian profile in V1-07.
```

## Test Plan

### Backend Migration Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `LeaveMakeupRescheduleMigrationTest.php` | `test_leave_change_tables_exist` | both V1-05 tables exist |
| `LeaveMakeupRescheduleMigrationTest.php` | `test_leave_request_columns_and_indexes_exist` | leave columns and tenant + lesson_student unique key exist |
| `LeaveMakeupRescheduleMigrationTest.php` | `test_lesson_change_columns_and_indexes_exist` | source/target lesson fields and change indexes exist |
| `LeaveMakeupRescheduleMigrationTest.php` | `test_rollback_drops_tables_in_dependency_order` | rollback drops lesson change records before leave requests |

### Repository Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `LeaveRequestRepositoryTest.php` | `test_page_filters_by_tenant_campus_student_status_and_keyword` | cross-tenant and cross-campus rows are absent |
| `LeaveRequestRepositoryTest.php` | `test_exists_for_lesson_student_detects_duplicate` | duplicate lesson_student_id returns true |
| `LessonChangeRepositoryTest.php` | `test_page_filters_by_type_status_source_and_target_lesson` | only matching change records are returned |
| `LessonChangeRepositoryTest.php` | `test_has_makeup_for_leave_detects_existing_makeup` | approved leave with make-up record returns true |

### Service Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `LeaveRequestServiceTest.php` | `test_create_leave_request_from_lesson_student_snapshot` | leave row snapshots lesson, student, course, and account ids |
| `LeaveRequestServiceTest.php` | `test_create_rejects_duplicate_lesson_student` | service throws code 409 |
| `LeaveRequestServiceTest.php` | `test_approve_rejects_when_consumption_exists` | service throws code 409 |
| `LeaveRequestServiceTest.php` | `test_approve_pending_leave_sets_review_fields` | status becomes approved and reviewed_by is set |
| `LeaveRequestServiceTest.php` | `test_reject_pending_leave_sets_review_fields` | status becomes rejected and review_remark is set |
| `LeaveRequestServiceTest.php` | `test_cancel_makeup_scheduled_leave_is_rejected` | service throws code 409 |
| `MakeupLessonServiceTest.php` | `test_makeup_creates_single_student_lesson_and_change_record` | target lesson student_count is 1 and change_type is makeup |
| `MakeupLessonServiceTest.php` | `test_makeup_rejects_unapproved_leave` | service throws code 409 |
| `MakeupLessonServiceTest.php` | `test_makeup_rejects_student_conflict` | conflict_type student is returned with code 409 |
| `MakeupLessonServiceTest.php` | `test_makeup_does_not_change_account_balance` | account available_units remains unchanged |
| `RescheduleServiceTest.php` | `test_reschedule_updates_lesson_and_creates_change_record` | lesson target fields are updated and change_type is reschedule |
| `RescheduleServiceTest.php` | `test_reschedule_rejects_completed_lesson` | service throws code 409 |
| `RescheduleServiceTest.php` | `test_reschedule_rejects_lesson_with_attendance` | service throws code 409 |
| `RescheduleServiceTest.php` | `test_reschedule_rejects_teacher_conflict` | conflict_type teacher is returned with code 409 |

### Controller/API Feature Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `LeaveMakeupRescheduleAdminApiTest.php` | `test_leave_request_crud_review_returns_mineadmin_shape` | page/detail/create/approve/reject/cancel responses use `{code,message,data}` |
| `LeaveMakeupRescheduleAdminApiTest.php` | `test_makeup_returns_leave_lesson_and_change_summary` | response contains leave_request, target_lesson, and change_record blocks |
| `LeaveMakeupRescheduleAdminApiTest.php` | `test_reschedule_returns_updated_lesson_and_change_record` | response contains lesson and change_record blocks |
| `LeaveMakeupRescheduleAdminApiTest.php` | `test_validation_failures_match_catalog` | missing required fields return documented 422 messages |
| `LeaveMakeupRescheduleAdminApiTest.php` | `test_business_failures_match_catalog` | duplicate leave, consumption exists, and conflict failures return documented codes |

### Permission, Isolation, and Audit Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `LeaveMakeupReschedulePermissionTest.php` | `test_missing_leave_approve_permission_returns_403` | API returns code 403 |
| `LeaveMakeupReschedulePermissionTest.php` | `test_front_desk_cannot_reschedule_lesson` | reschedule API returns code 403 |
| `LeaveMakeupRescheduleIsolationTest.php` | `test_tenant_user_cannot_read_other_tenant_leave` | page and detail responses exclude other tenant rows |
| `LeaveMakeupRescheduleIsolationTest.php` | `test_campus_scoped_user_cannot_makeup_other_campus_leave` | make-up API returns code 403 |
| `LeaveMakeupRescheduleIsolationTest.php` | `test_reschedule_respects_campus_scope` | cross-campus source lesson returns code 403 |
| `LeaveMakeupRescheduleAuditTest.php` | `test_leave_create_review_cancel_create_audit_logs` | leave audit actions exist |
| `LeaveMakeupRescheduleAuditTest.php` | `test_makeup_and_reschedule_create_audit_logs` | change audit actions exist with change_no |

### PC Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `LeaveRequestList.spec.ts` | `renders_leave_table_and_filters` | table shows leave_no, student, reason, and status |
| `LeaveRequestList.spec.ts` | `permission_buttons_are_hidden_without_permission` | approve/reject/cancel buttons are hidden |
| `LeaveReviewDialog.spec.ts` | `approve_success_updates_row_status` | row status changes to approved |
| `LeaveReviewDialog.spec.ts` | `consumption_failure_keeps_dialog_open` | 409 message is displayed and dialog remains open |
| `LessonChangeList.spec.ts` | `renders_lesson_change_rows` | change_no, change_type, source and target lesson ids display |
| `MakeupLessonForm.spec.ts` | `makeup_success_shows_target_lesson` | result displays target_lesson id and change_no |
| `MakeupLessonForm.spec.ts` | `student_conflict_keeps_form_open` | conflict_type and lesson_ids are displayed |
| `RescheduleLessonForm.spec.ts` | `reschedule_success_updates_lesson_summary` | updated start/end time is displayed |
| `RescheduleLessonForm.spec.ts` | `teacher_conflict_keeps_form_open` | conflict details are displayed |

### Mobile Regression Test

| Verification | Assert |
| --- | --- |
| `pnpm build:h5` in `mobile-uniapp` | existing teacher/guardian shell still builds because V1-05 adds no mobile files |

## Execution Commands

### Backend Migration Gate

Run:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter LeaveMakeupRescheduleMigrationTest
php bin/hyperf.php migrate:rollback --step=1
php bin/hyperf.php migrate
```

Expected:

```text
V1-05 migration runs successfully.
LeaveMakeupRescheduleMigrationTest passes.
Rollback drops V1-05 tables in dependency-safe order.
Re-running migration succeeds.
```

### Backend Unit Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter LeaveRequestRepositoryTest
composer test -- --filter LessonChangeRepositoryTest
composer test -- --filter LeaveRequestServiceTest
composer test -- --filter MakeupLessonServiceTest
composer test -- --filter RescheduleServiceTest
```

Expected:

```text
V1-05 repository and service tests pass.
Leave status transition, make-up linkage, reschedule conflict, and account no-mutation tests pass.
```

### Backend Feature Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter LeaveMakeupRescheduleAdminApiTest
composer test -- --filter LeaveMakeupReschedulePermissionTest
composer test -- --filter LeaveMakeupRescheduleIsolationTest
composer test -- --filter LeaveMakeupRescheduleAuditTest
```

Expected:

```text
V1-05 admin API, permission, isolation, and audit tests pass.
Every API returns the documented MineAdmin result shape.
```

### PC Gate

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- LeaveRequestList
pnpm test -- LeaveReviewDialog
pnpm test -- LessonChangeList
pnpm test -- MakeupLessonForm
pnpm test -- RescheduleLessonForm
pnpm build
```

Expected:

```text
PC lint passes.
V1-05 page tests pass.
Production build succeeds.
```

### Mobile Regression Gate

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm build:h5
```

Expected:

```text
Mobile H5 build passes.
```

### V1-05 Final Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter LeaveMakeupReschedule
composer cs-fix -- --dry-run
composer analyse
cd ../admin-web
pnpm lint
pnpm test -- LeaveRequestList
pnpm test -- LeaveReviewDialog
pnpm test -- LessonChangeList
pnpm test -- MakeupLessonForm
pnpm test -- RescheduleLessonForm
pnpm build
cd ../mobile-uniapp
pnpm build:h5
```

Expected:

```text
All V1-05 backend tests pass.
Backend code style dry run passes.
Backend static analysis passes.
PC lint, page tests, and build pass.
Mobile H5 build passes.
```

## Acceptance Gate

V1-05 is accepted only when all conditions are true:

```text
- `edu_leave_requests` and `edu_lesson_change_records` exist with documented columns and indexes.
- Migration rollback drops all V1-05 tables in dependency-safe order.
- Leave request and lesson change models cast date, status, decimal, and id fields correctly.
- Repositories apply tenant and campus scope filters consistently.
- Leave creation rejects duplicate lesson-student leave records.
- Leave approval rejects lesson students with active V1-04 consumption.
- Leave status transitions follow the documented state machine.
- Make-up creation requires approved leave and creates one scheduled lesson plus one lesson-student snapshot.
- Make-up creation preserves original leave, source lesson, source lesson-student, target lesson, student, and account linkage.
- Make-up creation and reschedule use V1-03 conflict checks and return documented 409 conflict payloads.
- Make-up and reschedule operations do not change V1-02 account balances.
- Reschedule rejects completed, cancelled, attended, or consumed lessons.
- Admin APIs return MineAdmin result shape and documented validation/business failures.
- Permission tests prove missing MineAdmin permission codes return 403.
- Isolation tests prove tenant and campus scoped users cannot read or mutate unauthorized rows.
- F04 audit logs are created for all V1-05 write operations.
- PC API client, routes, leave request page, review dialog, lesson change page, make-up form, reschedule form, detail drawer, permission buttons, loading, empty, error, success, and submit states pass tests.
- V1-05 adds no mobile pages and mobile H5 build still passes.
```

## Task Breakdown

### Task 1: Create Migration, Enums, and Models

**Files:**

- Create: `mineadmin-education-saas/backend/databases/migrations/2026_06_10_010500_create_v1_leave_change_tables.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LeaveRequestSource.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LeaveType.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LeaveRequestStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LessonChangeType.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LessonChangeStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLeaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonChangeRecord.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/LeaveMakeupRescheduleMigrationTest.php`

- [x] **Step 1: Create migration**

Use the full table, column, index, foreign-key policy, and rollback order from `Database Migration Design`.

- [x] **Step 2: Create enums**

Create all five V1-05 enums exactly as defined in `MineAdmin Backend Module Design`.

- [x] **Step 3: Create models**

Create both models with table names, fillable fields, casts, relationships, timestamps, and soft delete behavior defined in `MineAdmin Backend Module Design`.

- [x] **Step 4: Write migration test**

Create `LeaveMakeupRescheduleMigrationTest` with cases listed in `Test Plan`.

- [x] **Step 5: Run migration gate**

Run commands from `Backend Migration Gate`.

Expected:

```text
Migration, rollback, re-migration, and migration tests pass.
```

### Task 2: Create Repositories and Services

**Files:**

- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/LeaveRequestRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonChangeRepository.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/LeaveRequestService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/MakeupLessonService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/RescheduleService.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/LeaveRequestRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/LessonChangeRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/LeaveRequestServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/MakeupLessonServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/RescheduleServiceTest.php`

- [x] **Step 1: Create repositories**

Implement repository methods, filters, tenant scope, campus scope, duplicate checks, make-up detection, change record queries, and pagination rules from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create services**

Implement leave create/review/cancel, make-up lesson transaction, reschedule transaction, conflict checks, attendance/consumption safety checks, audit dispatch, and no-account-mutation guarantees from `MineAdmin Backend Module Design`.

- [x] **Step 3: Write repository tests**

Create repository tests listed in `Test Plan`.

- [x] **Step 4: Write service tests**

Create service tests listed in `Test Plan`.

- [x] **Step 5: Run backend unit gate**

Run commands from `Backend Unit Gate`.

Expected:

```text
V1-05 repository, service, state transition, make-up, reschedule, and conflict tests pass.
```

### Task 3: Create Requests, Schemas, and Controllers

**Files:**

- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LeaveRequestPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LeaveRequestCreateRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LeaveRequestReviewRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LeaveRequestCancelRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LessonChangePageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/MakeupLessonCreateRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/RescheduleLessonRequest.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/LeaveRequestSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/LessonChangeSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/MakeupLessonResultSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/RescheduleResultSchema.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/LeaveRequestController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/LessonChangeController.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/LeaveMakeupRescheduleAdminApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/LeaveMakeupReschedulePermissionTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/LeaveMakeupRescheduleIsolationTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/LeaveMakeupRescheduleAuditTest.php`

- [x] **Step 1: Create request classes**

Use the request rules and validation messages from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create schema classes**

Use schema fields from `MineAdmin Backend Module Design`.

- [x] **Step 3: Create admin controllers**

Use endpoint matrix, permissions, middleware, context resolver, response envelope, and endpoint catalog from `API Contract`.

- [x] **Step 4: Write feature tests**

Create API, permission, isolation, and audit tests listed in `Test Plan`.

- [x] **Step 5: Run backend feature gate**

Run commands from `Backend Feature Gate`.

Expected:

```text
V1-05 admin API, permission, isolation, and audit tests pass.
```

### Task 4: Create PC API Client, Routes, Pages, Forms, and Dialogs

**Files:**

- Create: `mineadmin-education-saas/admin-web/src/api/education/academic/lessonChange.ts`
- Modify: `mineadmin-education-saas/admin-web/src/router/modules/education.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/LeaveRequestList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/LessonChangeList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/LeaveRequestForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/LeaveReviewDialog.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/MakeupLessonForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/RescheduleLessonForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/LessonChangeDetailDrawer.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/LeaveRequestList.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/LeaveReviewDialog.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/LessonChangeList.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/MakeupLessonForm.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/RescheduleLessonForm.spec.ts`

- [x] **Step 1: Create typed API client**

Implement all types and methods listed in `PC Admin Page Tasks`.

- [x] **Step 2: Add routes and menus**

Add V1-05 route entries and auth meta to `admin-web/src/router/modules/education.ts`.

- [x] **Step 3: Create leave request page and review dialogs**

Implement LeaveRequestList, LeaveRequestForm, and LeaveReviewDialog using page tasks from `PC Admin Page Tasks`.

- [x] **Step 4: Create lesson change page and forms**

Implement LessonChangeList, MakeupLessonForm, RescheduleLessonForm, and LessonChangeDetailDrawer with conflict display and audit trail behavior.

- [x] **Step 5: Write PC tests**

Create PC tests listed in `Test Plan`.

- [x] **Step 6: Run PC gate**

Run commands from `PC Gate`.

Expected:

```text
PC lint, V1-05 page tests, and production build pass.
```

### Task 5: Run V1-05 Final Gate

**Files:**

- Verify: all backend, PC, and mobile paths listed in `File Structure`.

- [x] **Step 1: Run backend final gate**

Run backend commands from `V1-05 Final Gate`.

- [x] **Step 2: Run PC final gate**

Run PC commands from `V1-05 Final Gate`.

- [x] **Step 3: Run mobile regression gate**

Run mobile command from `V1-05 Final Gate`.

- [x] **Step 4: Commit V1-05**

Run:

```bash
cd mineadmin-education-saas
git add backend admin-web mobile-uniapp
git commit -m "feat: add v1 leave makeup reschedule"
```

Expected:

```text
Commit succeeds with V1-05 backend, PC, tests, and verification changes.
```

## Self-Review

- Spec coverage: V1-05 covers staff-side leave requests, approval/rejection/cancellation, make-up lesson creation, reschedule records, and conflict-safe lesson changes from the V1 leave-makeup-reschedule scope.
- MineAdmin fit: The plan uses MineAdmin 3.x `app/Http/Admin`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, `databases/migrations`, permission attributes, OperationMiddleware for writes, and MineAdmin result shape.
- Tenant isolation: All records are tenant-scoped and campus-scoped; services validate campus scope before every write.
- V1 dependency fit: V1-05 consumes V1-03 lessons/lesson-student snapshots and V1-04 attendance/consumption safety checks; V1-06 and V1-07 can expose teacher/guardian mobile flows through scoped APIs.
- Conflict fit: Make-up and reschedule operations reuse V1-03 teacher, classroom, class, and student conflict rules.
- Ledger boundary: V1-05 does not mutate V1-02 account balances and does not write V1-04 consumption rows.
- PC fit: Pages include typed API client, route/menu entries, leave request page, review dialog, lesson change page, make-up form, reschedule form, detail drawer, permission buttons, loading, empty, error, success, and submit states.
- Mobile fit: V1-05 adds no visible mobile page and keeps mobile build verification.
- Readiness: This plan has exact paths, full migration design, backend layer tasks, API request/response/failure examples, PC tasks, mobile rationale, tests, commands, expected outputs, and acceptance gates, so V1-05 can be marked `ready`.
