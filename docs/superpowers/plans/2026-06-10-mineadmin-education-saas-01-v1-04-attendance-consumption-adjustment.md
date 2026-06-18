# MineAdmin Education SaaS V1-04 Attendance Consumption Adjustment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V1 attendance submission, automatic lesson consumption, manual supplement deduction, immutable rollback records, and account balance consistency for课消制机构.

**Architecture:** V1-04 depends on Foundation F01 tenant/campus, F02 education user context and campus scope, F04 audit logging, F05 PC conventions, V1-02 student course accounts, and V1-03 lessons plus lesson-student snapshots. Backend follows MineAdmin 3.x native paths under `app/Http/Admin`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, and `databases/migrations`; attendance submission, consumption rollback, supplement deduction, and adjustment rollback all run inside database transactions and update `edu_student_course_accounts` only through account service methods. No teacher or guardian mobile page is added in V1-04.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8 decimal columns, transaction-safe services, MineAdmin-Vue, Vue3, TypeScript, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. V1 core academic gates have passed.

---

## Scope Check

Included:

- Create attendance, lesson consumption, and account adjustment tables.
- Create V1-04 enums for attendance status, consumption policy, consumption source type, ledger direction, consumption status, adjustment type, and adjustment status.
- Create models, repositories, services, request classes, schemas, and admin controllers for attendance, consumption, and account adjustments.
- Submit a full lesson attendance sheet against V1-03 lesson-student snapshots.
- Deduct course account balance exactly once for each consumed lesson-student attendance row.
- Mark a lesson as completed after successful full attendance submission.
- Roll back attendance consumption through a reversing consumption row instead of deleting the original row.
- Create manual supplement deduction adjustments with operator reason and account balance updates.
- Roll back manual supplement deductions through a reversing account adjustment row.
- Add admin APIs for attendance lesson page/detail/submit, consumption page/detail/rollback, account adjustment page/detail/create/rollback.
- Add PC API client, route/menu entries, attendance review page, consumption ledger page, account adjustment page, drawers, permission-controlled buttons, loading/empty/error states, and tests.
- Add migration, repository, service, API, permission, tenant/campus isolation, audit, PC, and mobile regression tests.

Excluded:

- Teacher mobile attendance submission page; V1-06 owns visible teacher attendance workflow and will call scoped APIs built there.
- Guardian mobile consumption view; V1-07 owns guardian-facing consumption and account APIs.
- Leave request, make-up lesson, and reschedule workflow; V1-05 owns them.
- Finance payment ledger, refund posting, and reconciliation; V4 owns them.
- Teacher payroll settlement from attendance and consumption data; V5 owns it.
- V1 reports and final cross-module acceptance; V1-08 owns them.

Business rules:

```text
Attendance, consumption, and adjustments are tenant-scoped and campus-scoped.
Attendance can be submitted only for a V1-03 scheduled lesson visible in current tenant and campus scope.
Attendance submission must include every non-cancelled lesson-student snapshot for the lesson.
Attendance submission is idempotent for an identical payload and must not deduct twice.
Submitting a different payload after attendance exists is rejected with code 409; rollback affected consumptions first.
Attendance status `present` and `late` default to consume.
Attendance status `absent` can consume or no_consume based on operator choice.
Attendance status `leave` must use no_consume; V1-05 handles formal leave/make-up flows.
Consumption requires an active student course account and sufficient available_units.
Consumption updates account consumed_units and available_units in one transaction.
Consumption rollback creates a reversing consumption row and updates account consumed_units and available_units in one transaction.
Manual supplement deduction requires account_id, units greater than 0.00, and a human reason.
Manual supplement deduction updates account adjusted_units with a negative delta and decreases available_units.
Manual supplement rollback creates a reversing adjustment row, marks the original adjustment rolled_back, and restores account adjusted_units and available_units.
No attendance, consumption, or adjustment row is hard-deleted by V1-04 business services.
```

Account balance rules:

```text
Lesson units use decimal(10,2), allowing values such as 1.00, 1.50, and 0.50.
Every service calculation rounds to two decimal places before persistence.
V1-04 preserves the V1-02 account invariant:
available_units = purchased_units + bonus_units + adjusted_units - consumed_units - refunded_units - frozen_units
Attendance consumption increases consumed_units and decreases available_units.
Attendance consumption rollback decreases consumed_units and increases available_units.
Manual supplement deduction decreases adjusted_units and decreases available_units.
Manual supplement rollback increases adjusted_units and increases available_units.
Frozen or closed accounts reject consumption and manual supplement deduction with code 409.
```

Idempotency and rollback rules:

```text
Attendance idempotency key is tenant_id + lesson_student_id.
Consumption idempotency key for attendance is tenant_id + attendance_id + source_type attendance.
Rollback idempotency key for consumption is tenant_id + original_consumption_id + source_type rollback.
Adjustment rollback idempotency key is tenant_id + original_adjustment_id + adjustment_type rollback.
Rollback never deletes original ledger rows.
Original consumption status changes from active to reversed after rollback.
Original account adjustment status changes from confirmed to rolled_back after rollback.
Rollback rows use direction increase and reference the original row id.
```

Status machines:

```text
Attendance status: present, late, absent, leave.
Consumption policy: consume or no_consume.
Consumption source type: attendance or rollback.
Ledger direction: decrease or increase.
Attendance consumption status: none -> active -> reversed.
Consumption row status: active -> reversed.
Account adjustment type: supplement_deduction or rollback.
Account adjustment status: confirmed -> rolled_back.
Lesson status dependency: V1-04 moves V1-03 lesson status scheduled -> completed after successful attendance submission.
Lesson-student dependency: V1-04 maps attendance status to V1-03 lesson-student status only through attendance records; it does not overwrite V1-03 snapshots except optional completed markers supported by the implementation.
```

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_010400_create_v1_attendance_consumption_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/AttendanceStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/ConsumptionPolicy.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/ConsumptionSourceType.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LedgerDirection.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/ConsumptionStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/AccountAdjustmentType.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/AccountAdjustmentStatus.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonAttendance.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonConsumption.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationAccountAdjustment.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/AttendanceRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/ConsumptionRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/AccountAdjustmentRepository.php
mineadmin-education-saas/backend/app/Service/Education/Academic/AttendanceService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/LessonConsumptionService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/AccountAdjustmentService.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/AttendanceLessonPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/AttendanceSubmitRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ConsumptionPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ConsumptionRollbackRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/AccountAdjustmentPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/AccountAdjustmentCreateRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/AccountAdjustmentRollbackRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/AttendanceController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/ConsumptionController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/AccountAdjustmentController.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/AttendanceSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/ConsumptionSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/AccountAdjustmentSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/AttendanceSubmitResultSchema.php
```

Read backend dependencies:

```text
mineadmin-education-saas/backend/app/Service/Education/Foundation/EducationUserContext.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusScopeService.php
mineadmin-education-saas/backend/app/Event/Education/Foundation/EducationAuditEvent.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LessonStatus.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLesson.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonStudent.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudentCourseAccount.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonStudentRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/StudentCourseAccountRepository.php
mineadmin-education-saas/backend/app/Service/Education/Academic/StudentCourseAccountService.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Academic/AttendanceConsumptionMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/AttendanceRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/ConsumptionRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/AccountAdjustmentRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/AttendanceServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/LessonConsumptionServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/AccountAdjustmentServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/AttendanceConsumptionAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/AttendanceConsumptionPermissionTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/AttendanceConsumptionIsolationTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/AttendanceConsumptionAuditTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/academic/attendanceConsumption.ts
mineadmin-education-saas/admin-web/src/views/education/academic/AttendanceReview.vue
mineadmin-education-saas/admin-web/src/views/education/academic/ConsumptionLedgerList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/AccountAdjustmentList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/AttendanceSubmitDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/AttendanceResultDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/ConsumptionRollbackDialog.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/AccountAdjustmentForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/AccountAdjustmentRollbackDialog.vue
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/AttendanceReview.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/AttendanceSubmitDrawer.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/ConsumptionLedgerList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/AccountAdjustmentList.spec.ts
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
mineadmin-education-saas/backend/databases/migrations/2026_06_10_010400_create_v1_attendance_consumption_tables.php
```

Tables:

```text
edu_lesson_attendances
edu_lesson_consumptions
edu_account_adjustments
```

Foreign-key policy:

```text
Use no physical foreign keys in V1-04.
Use service-level validation against V1-02 student course accounts and V1-03 lessons plus lesson students.
Reason: MineAdmin business tables use soft deletes, tenant isolation, and staged module migrations; service validation keeps rollback and soft-delete behavior predictable.
```

Rollback order:

```text
Schema::dropIfExists('edu_account_adjustments');
Schema::dropIfExists('edu_lesson_consumptions');
Schema::dropIfExists('edu_lesson_attendances');
```

### `edu_lesson_attendances`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Lesson attendance id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Campus id |
| `lesson_id` | bigint unsigned | no | none | V1-03 lesson id |
| `lesson_student_id` | bigint unsigned | no | none | V1-03 lesson student id |
| `class_id` | bigint unsigned | no | none | Class id |
| `course_id` | bigint unsigned | no | none | Course id |
| `student_id` | bigint unsigned | no | none | Student id |
| `account_id` | bigint unsigned | no | none | V1-02 student course account id |
| `attendance_status` | varchar(20) | no | none | present, late, absent, or leave |
| `consume_policy` | varchar(20) | no | `no_consume` | consume or no_consume |
| `planned_units` | decimal(10,2) | no | 0.00 | Planned lesson units from lesson student |
| `consumed_units` | decimal(10,2) | no | 0.00 | Actual consumed units |
| `consumption_status` | varchar(20) | no | `none` | none, active, or reversed |
| `submitted_at` | timestamp | yes | null | Attendance submitted time |
| `submitted_by` | bigint unsigned | yes | null | Submitter user id |
| `attendance_batch_no` | varchar(64) | no | none | Attendance submission batch number |
| `remark` | varchar(500) | yes | null | Attendance remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_lesson_attendances_tenant_lesson_student (tenant_id, lesson_student_id)
index idx_edu_lesson_attendances_tenant_lesson (tenant_id, lesson_id)
index idx_edu_lesson_attendances_tenant_student (tenant_id, student_id)
index idx_edu_lesson_attendances_tenant_account (tenant_id, account_id)
index idx_edu_lesson_attendances_batch_no (attendance_batch_no)
index idx_edu_lesson_attendances_deleted_at (deleted_at)
```

### `edu_lesson_consumptions`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Lesson consumption id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Campus id |
| `consumption_no` | varchar(64) | no | none | Consumption number |
| `account_id` | bigint unsigned | no | none | Student course account id |
| `student_id` | bigint unsigned | no | none | Student id |
| `course_id` | bigint unsigned | no | none | Course id |
| `lesson_id` | bigint unsigned | no | none | Lesson id |
| `lesson_student_id` | bigint unsigned | no | none | Lesson student id |
| `attendance_id` | bigint unsigned | no | none | Attendance id |
| `source_type` | varchar(30) | no | `attendance` | attendance or rollback |
| `direction` | varchar(20) | no | `decrease` | decrease or increase |
| `units` | decimal(10,2) | no | 0.00 | Consumption units |
| `before_available_units` | decimal(10,2) | no | 0.00 | Account available units before change |
| `after_available_units` | decimal(10,2) | no | 0.00 | Account available units after change |
| `before_consumed_units` | decimal(10,2) | no | 0.00 | Account consumed units before change |
| `after_consumed_units` | decimal(10,2) | no | 0.00 | Account consumed units after change |
| `status` | varchar(20) | no | `active` | active or reversed |
| `original_consumption_id` | bigint unsigned | yes | null | Original consumption id when this is rollback |
| `reversed_at` | timestamp | yes | null | Original row reversed time |
| `reversed_by` | bigint unsigned | yes | null | Reversing operator user id |
| `reason` | varchar(500) | yes | null | Rollback or consumption reason |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_lesson_consumptions_tenant_no (tenant_id, consumption_no)
unique uk_edu_lesson_consumptions_tenant_attendance_source (tenant_id, attendance_id, source_type)
unique uk_edu_lesson_consumptions_tenant_original_source (tenant_id, original_consumption_id, source_type)
index idx_edu_lesson_consumptions_tenant_account_time (tenant_id, account_id, created_at)
index idx_edu_lesson_consumptions_tenant_lesson (tenant_id, lesson_id)
index idx_edu_lesson_consumptions_tenant_student_course (tenant_id, student_id, course_id)
index idx_edu_lesson_consumptions_tenant_status (tenant_id, status)
index idx_edu_lesson_consumptions_deleted_at (deleted_at)
```

### `edu_account_adjustments`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Account adjustment id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Campus id |
| `adjustment_no` | varchar(64) | no | none | Adjustment number |
| `account_id` | bigint unsigned | no | none | Student course account id |
| `student_id` | bigint unsigned | no | none | Student id |
| `course_id` | bigint unsigned | no | none | Course id |
| `adjustment_type` | varchar(30) | no | `supplement_deduction` | supplement_deduction or rollback |
| `direction` | varchar(20) | no | `decrease` | decrease or increase |
| `units` | decimal(10,2) | no | 0.00 | Adjustment units |
| `before_available_units` | decimal(10,2) | no | 0.00 | Account available units before change |
| `after_available_units` | decimal(10,2) | no | 0.00 | Account available units after change |
| `before_adjusted_units` | decimal(10,2) | no | 0.00 | Account adjusted units before change |
| `after_adjusted_units` | decimal(10,2) | no | 0.00 | Account adjusted units after change |
| `status` | varchar(20) | no | `confirmed` | confirmed or rolled_back |
| `original_adjustment_id` | bigint unsigned | yes | null | Original adjustment id when this is rollback |
| `rolled_back_at` | timestamp | yes | null | Original row rolled back time |
| `rolled_back_by` | bigint unsigned | yes | null | Rollback operator user id |
| `reason` | varchar(500) | no | none | Deduction or rollback reason |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_account_adjustments_tenant_no (tenant_id, adjustment_no)
unique uk_edu_account_adjustments_tenant_original_type (tenant_id, original_adjustment_id, adjustment_type)
index idx_edu_account_adjustments_tenant_account_time (tenant_id, account_id, created_at)
index idx_edu_account_adjustments_tenant_student_course (tenant_id, student_id, course_id)
index idx_edu_account_adjustments_tenant_status (tenant_id, status)
index idx_edu_account_adjustments_deleted_at (deleted_at)
```

## MineAdmin Backend Module Design

### Enums

Create `AttendanceStatus`:

```php
enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';
    case Absent = 'absent';
    case Leave = 'leave';
}
```

Create `ConsumptionPolicy`:

```php
enum ConsumptionPolicy: string
{
    case Consume = 'consume';
    case NoConsume = 'no_consume';
}
```

Create `ConsumptionSourceType`:

```php
enum ConsumptionSourceType: string
{
    case Attendance = 'attendance';
    case Rollback = 'rollback';
}
```

Create `LedgerDirection`:

```php
enum LedgerDirection: string
{
    case Decrease = 'decrease';
    case Increase = 'increase';
}
```

Create `ConsumptionStatus`:

```php
enum ConsumptionStatus: string
{
    case None = 'none';
    case Active = 'active';
    case Reversed = 'reversed';
}
```

Create `AccountAdjustmentType`:

```php
enum AccountAdjustmentType: string
{
    case SupplementDeduction = 'supplement_deduction';
    case Rollback = 'rollback';
}
```

Create `AccountAdjustmentStatus`:

```php
enum AccountAdjustmentStatus: string
{
    case Confirmed = 'confirmed';
    case RolledBack = 'rolled_back';
}
```

### Models

All models use MineAdmin/Hyperf model conventions, timestamps, soft deletes, and guarded tenant fields only through service methods.

`EducationLessonAttendance`:

```text
table: edu_lesson_attendances
fillable: tenant_id, campus_id, lesson_id, lesson_student_id, class_id, course_id, student_id, account_id, attendance_status, consume_policy, planned_units, consumed_units, consumption_status, submitted_at, submitted_by, attendance_batch_no, remark, created_by, updated_by
casts: tenant_id integer, campus_id integer, lesson_id integer, lesson_student_id integer, class_id integer, course_id integer, student_id integer, account_id integer, planned_units decimal:2, consumed_units decimal:2, submitted_at datetime, submitted_by integer
soft delete: yes
relationships: lesson belongsTo EducationLesson, lessonStudent belongsTo EducationLessonStudent, account belongsTo EducationStudentCourseAccount, consumption hasOne EducationLessonConsumption
```

`EducationLessonConsumption`:

```text
table: edu_lesson_consumptions
fillable: tenant_id, campus_id, consumption_no, account_id, student_id, course_id, lesson_id, lesson_student_id, attendance_id, source_type, direction, units, before_available_units, after_available_units, before_consumed_units, after_consumed_units, status, original_consumption_id, reversed_at, reversed_by, reason, created_by, updated_by
casts: tenant_id integer, campus_id integer, account_id integer, student_id integer, course_id integer, lesson_id integer, lesson_student_id integer, attendance_id integer, units decimal:2, before_available_units decimal:2, after_available_units decimal:2, before_consumed_units decimal:2, after_consumed_units decimal:2, original_consumption_id integer, reversed_at datetime, reversed_by integer
soft delete: yes
relationships: attendance belongsTo EducationLessonAttendance, account belongsTo EducationStudentCourseAccount, originalConsumption belongsTo EducationLessonConsumption
```

`EducationAccountAdjustment`:

```text
table: edu_account_adjustments
fillable: tenant_id, campus_id, adjustment_no, account_id, student_id, course_id, adjustment_type, direction, units, before_available_units, after_available_units, before_adjusted_units, after_adjusted_units, status, original_adjustment_id, rolled_back_at, rolled_back_by, reason, created_by, updated_by
casts: tenant_id integer, campus_id integer, account_id integer, student_id integer, course_id integer, units decimal:2, before_available_units decimal:2, after_available_units decimal:2, before_adjusted_units decimal:2, after_adjusted_units decimal:2, original_adjustment_id integer, rolled_back_at datetime, rolled_back_by integer
soft delete: yes
relationships: account belongsTo EducationStudentCourseAccount, originalAdjustment belongsTo EducationAccountAdjustment
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
keyword matches lesson_no, student snapshot, consumption_no, adjustment_no, and reason fields where present.
status exact match.
page and pageSize use MineAdmin pagination defaults and cap pageSize at 100.
```

`AttendanceRepository` methods:

```php
public function lessonPage(array $filters, EducationUserContext $context): array
public function lessonDetail(int $lessonId, EducationUserContext $context): array
public function existingByLesson(int $lessonId, int $tenantId): array
public function existingByLessonStudentIds(array $lessonStudentIds, int $tenantId): array
public function bulkCreate(array $rows): array
public function markConsumptionStatus(int $attendanceId, string $status, ?int $operatorId): EducationLessonAttendance
```

`ConsumptionRepository` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function findScoped(int $id, EducationUserContext $context): ?EducationLessonConsumption
public function findActiveForRollback(int $id, EducationUserContext $context): ?EducationLessonConsumption
public function createConsumption(array $data): EducationLessonConsumption
public function createRollback(array $data): EducationLessonConsumption
public function hasRollback(int $originalConsumptionId, int $tenantId): bool
public function nextConsumptionNo(int $tenantId, int $campusId): string
```

`AccountAdjustmentRepository` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function findScoped(int $id, EducationUserContext $context): ?EducationAccountAdjustment
public function findConfirmedForRollback(int $id, EducationUserContext $context): ?EducationAccountAdjustment
public function createAdjustment(array $data): EducationAccountAdjustment
public function createRollback(array $data): EducationAccountAdjustment
public function hasRollback(int $originalAdjustmentId, int $tenantId): bool
public function nextAdjustmentNo(int $tenantId, int $campusId): string
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
Reject disabled, cancelled, missing, frozen, or closed source rows with documented 409 or 422 codes.
Write F04 audit events after successful attendance submit, consumption rollback, adjustment create, and adjustment rollback operations.
Use MineAdmin OperationMiddleware on write controllers.
Use database transactions for every balance-changing operation.
```

`AttendanceService` methods:

```php
public function lessonPage(array $filters, EducationUserContext $context): array
public function lessonDetail(int $lessonId, EducationUserContext $context): array
public function submit(int $lessonId, array $records, ?string $submittedAt, EducationUserContext $context, ?int $operatorId): array
protected function normalizeRecords(array $records, array $lessonStudents): array
protected function assertIdempotentOrReject(int $lessonId, array $normalizedRecords, EducationUserContext $context): ?array
```

Attendance submit transaction:

```text
1. Validate lesson exists in current tenant and campus scope.
2. Reject lesson status cancelled or completed with code 409.
3. Load all non-cancelled V1-03 lesson-student snapshots for the lesson.
4. Require request records to cover exactly all lesson-student ids, no missing and no extra ids.
5. Normalize attendance_status, consume_policy, and consumed_units to two decimals.
6. Enforce leave + consume_policy consume is rejected with code 422.
7. Enforce consume_policy no_consume uses consumed_units 0.00.
8. Enforce consume_policy consume uses consumed_units greater than 0.00 and not greater than planned_units unless principal role is present.
9. If attendance already exists and normalized payload is identical, return existing summary without new deductions.
10. If attendance already exists and payload differs, reject with code 409.
11. Generate attendance_batch_no as ATT + yyyyMMddHHmmss + tenant short id + random 4 digits.
12. Lock every consumed account row.
13. Reject frozen or closed account with code 409.
14. Reject insufficient available_units with code 409 before writing any row.
15. Create attendance rows for every lesson student.
16. Create consumption rows for consume records.
17. Increase account consumed_units and decrease account available_units for each consume record.
18. Mark attendance consumption_status active for consumed rows and none for no_consume rows.
19. Update lesson status from scheduled to completed.
20. Dispatch audit event `education.academic.attendance.submitted`.
21. Commit the transaction and return attendance summary, consumption summary, and account balance changes.
```

`LessonConsumptionService` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function detail(int $id, EducationUserContext $context): EducationLessonConsumption
public function createForAttendance(EducationLessonAttendance $attendance, EducationStudentCourseAccount $account, string $units, EducationUserContext $context, ?int $operatorId): EducationLessonConsumption
public function rollback(int $id, string $reason, EducationUserContext $context, ?int $operatorId): array
```

Consumption rollback transaction:

```text
1. Validate original consumption exists in current tenant and campus scope.
2. Reject source_type rollback with code 409.
3. Reject status reversed with code 409.
4. Reject if a rollback row already exists for original_consumption_id with code 409.
5. Lock linked student course account.
6. Create rollback consumption row with direction increase and same units.
7. Decrease account consumed_units by original units.
8. Increase account available_units by original units.
9. Mark original consumption status reversed and set reversed_at/reversed_by.
10. Mark linked attendance consumption_status reversed.
11. Dispatch audit event `education.academic.consumption.rollback`.
12. Commit the transaction and return original, rollback, and account summary.
```

`AccountAdjustmentService` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function detail(int $id, EducationUserContext $context): EducationAccountAdjustment
public function createSupplementDeduction(array $data, EducationUserContext $context, ?int $operatorId): array
public function rollback(int $id, string $reason, EducationUserContext $context, ?int $operatorId): array
```

Supplement deduction transaction:

```text
1. Validate account exists in current tenant and campus scope.
2. Reject frozen or closed account with code 409.
3. Normalize units to two decimals and require units greater than 0.00.
4. Require reason string.
5. Reject insufficient available_units with code 409.
6. Generate adjustment_no as ADJ + yyyyMMddHHmmss + tenant short id + random 4 digits and retry up to 3 times on unique-key collision.
7. Lock account row.
8. Create supplement_deduction adjustment with direction decrease.
9. Decrease account adjusted_units by units.
10. Decrease account available_units by units.
11. Dispatch audit event `education.academic.account_adjustment.created`.
12. Commit the transaction and return adjustment plus account summary.
```

Adjustment rollback transaction:

```text
1. Validate original adjustment exists in current tenant and campus scope.
2. Reject adjustment_type rollback with code 409.
3. Reject status rolled_back with code 409.
4. Reject if a rollback row already exists for original_adjustment_id with code 409.
5. Lock linked student course account.
6. Create rollback adjustment with direction increase and same units.
7. Increase account adjusted_units by units.
8. Increase account available_units by units.
9. Mark original adjustment status rolled_back and set rolled_back_at/rolled_back_by.
10. Dispatch audit event `education.academic.account_adjustment.rollback`.
11. Commit the transaction and return original, rollback, and account summary.
```

### Request Classes

`AttendanceLessonPageRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'tenant_id' => ['nullable', 'integer', 'min:1'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'class_id' => ['nullable', 'integer', 'min:1'],
    'teacher_id' => ['nullable', 'integer', 'min:1'],
    'status' => ['nullable', 'in:scheduled,completed,cancelled'],
    'keyword' => ['nullable', 'string', 'max:120'],
    'start_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
]
```

`AttendanceSubmitRequest` rules:

```php
[
    'submitted_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
    'records' => ['required', 'array', 'min:1'],
    'records.*.lesson_student_id' => ['required', 'integer', 'min:1'],
    'records.*.attendance_status' => ['required', 'in:present,late,absent,leave'],
    'records.*.consume_policy' => ['required', 'in:consume,no_consume'],
    'records.*.consumed_units' => ['required', 'numeric', 'min:0', 'max:999999.99'],
    'records.*.remark' => ['nullable', 'string', 'max:500'],
]
```

`ConsumptionPageRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'tenant_id' => ['nullable', 'integer', 'min:1'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'account_id' => ['nullable', 'integer', 'min:1'],
    'student_id' => ['nullable', 'integer', 'min:1'],
    'course_id' => ['nullable', 'integer', 'min:1'],
    'lesson_id' => ['nullable', 'integer', 'min:1'],
    'source_type' => ['nullable', 'in:attendance,rollback'],
    'status' => ['nullable', 'in:active,reversed'],
    'keyword' => ['nullable', 'string', 'max:120'],
]
```

`ConsumptionRollbackRequest` rules:

```php
[
    'reason' => ['required', 'string', 'max:500'],
]
```

`AccountAdjustmentPageRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'tenant_id' => ['nullable', 'integer', 'min:1'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'account_id' => ['nullable', 'integer', 'min:1'],
    'student_id' => ['nullable', 'integer', 'min:1'],
    'course_id' => ['nullable', 'integer', 'min:1'],
    'adjustment_type' => ['nullable', 'in:supplement_deduction,rollback'],
    'status' => ['nullable', 'in:confirmed,rolled_back'],
    'keyword' => ['nullable', 'string', 'max:120'],
]
```

`AccountAdjustmentCreateRequest` rules:

```php
[
    'account_id' => ['required', 'integer', 'min:1'],
    'units' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
    'reason' => ['required', 'string', 'max:500'],
]
```

`AccountAdjustmentRollbackRequest` rules:

```php
[
    'reason' => ['required', 'string', 'max:500'],
]
```

Validation messages:

```text
page.required: page is required
pageSize.between: pageSize must be between 1 and 100
records.required: records is required
records.*.lesson_student_id.required: lesson_student_id is required
records.*.attendance_status.in: attendance_status has an invalid value
records.*.consume_policy.in: consume_policy has an invalid value
records.*.consumed_units.required: consumed_units is required
account_id.required: account_id is required
units.min: units must be greater than 0
reason.required: reason is required
status.in: status has an invalid value
```

### Controllers

Controller base:

```text
Use `#[Controller(prefix: 'admin/education/academic/<resource>')]`.
Use MineAdmin auth middleware.
Use Permission attributes on every endpoint.
Use OperationMiddleware on attendance submit, consumption rollback, adjustment create, and adjustment rollback endpoints.
Return MineAdmin Result shape through `$this->success(...)`.
Resolve current EducationUserContext through F02 context resolver.
```

`AttendanceController`:

```php
public function lessonPage(AttendanceLessonPageRequest $request): Result
public function lessonDetail(int $lessonId): Result
public function submit(int $lessonId, AttendanceSubmitRequest $request): Result
```

`ConsumptionController`:

```php
public function page(ConsumptionPageRequest $request): Result
public function detail(int $id): Result
public function rollback(int $id, ConsumptionRollbackRequest $request): Result
```

`AccountAdjustmentController`:

```php
public function page(AccountAdjustmentPageRequest $request): Result
public function detail(int $id): Result
public function create(AccountAdjustmentCreateRequest $request): Result
public function rollback(int $id, AccountAdjustmentRollbackRequest $request): Result
```

### Schemas

Schema fields:

```text
AttendanceSchema: id, tenant_id, campus_id, lesson_id, lesson_student_id, class_id, course_id, student_id, student_name_snapshot, account_id, attendance_status, consume_policy, planned_units, consumed_units, consumption_status, submitted_at, submitted_by, attendance_batch_no, remark
ConsumptionSchema: id, tenant_id, campus_id, consumption_no, account_id, student_id, student_name, course_id, course_name, lesson_id, lesson_no, lesson_student_id, attendance_id, source_type, direction, units, before_available_units, after_available_units, before_consumed_units, after_consumed_units, status, original_consumption_id, reversed_at, reversed_by, reason, created_at
AccountAdjustmentSchema: id, tenant_id, campus_id, adjustment_no, account_id, student_id, student_name, course_id, course_name, adjustment_type, direction, units, before_available_units, after_available_units, before_adjusted_units, after_adjusted_units, status, original_adjustment_id, rolled_back_at, rolled_back_by, reason, created_at
AttendanceSubmitResultSchema: lesson_id, attendance_batch_no, attendance_count, consumed_count, no_consume_count, total_consumed_units, account_changes.account_id, account_changes.before_available_units, account_changes.after_available_units
```

## API Contract

### Endpoint Matrix

| API | Permission | Caller | Isolation | Audit |
| --- | --- | --- | --- | --- |
| `GET /admin/education/academic/attendance/lessons/page` | `education:academic:attendance:lesson-page` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `GET /admin/education/academic/attendance/lessons/{lessonId}` | `education:academic:attendance:detail` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `POST /admin/education/academic/attendance/lessons/{lessonId}/submit` | `education:academic:attendance:submit` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.attendance.submitted` |
| `GET /admin/education/academic/consumptions/page` | `education:academic:consumption:page` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `GET /admin/education/academic/consumptions/{id}` | `education:academic:consumption:detail` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `POST /admin/education/academic/consumptions/{id}/rollback` | `education:academic:consumption:rollback` | tenant admin, principal | tenant and campus scope | `education.academic.consumption.rollback` |
| `GET /admin/education/academic/account-adjustments/page` | `education:academic:account-adjustment:page` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `GET /admin/education/academic/account-adjustments/{id}` | `education:academic:account-adjustment:detail` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `POST /admin/education/academic/account-adjustments` | `education:academic:account-adjustment:create` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.account_adjustment.created` |
| `POST /admin/education/academic/account-adjustments/{id}/rollback` | `education:academic:account-adjustment:rollback` | tenant admin, principal | tenant and campus scope | `education.academic.account_adjustment.rollback` |

Headers for tenant-scoped callers:

```text
Authorization: Bearer test-admin-token
X-Tenant-Id: 1001
X-Campus-Id: 2001
X-Request-Id: req-v1-attendance-consumption-001
```

### Resource Examples

Attendance submit request:

```json
{
  "submitted_at": "2026-06-12 11:05:00",
  "records": [
    {
      "lesson_student_id": 10001,
      "attendance_status": "present",
      "consume_policy": "consume",
      "consumed_units": 1.00,
      "remark": "on time"
    },
    {
      "lesson_student_id": 10002,
      "attendance_status": "leave",
      "consume_policy": "no_consume",
      "consumed_units": 0.00,
      "remark": "approved leave"
    }
  ]
}
```

Attendance submit success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "lesson_id": 9001,
    "attendance_batch_no": "ATT2026061211050010014821",
    "attendance_count": 2,
    "consumed_count": 1,
    "no_consume_count": 1,
    "total_consumed_units": "1.00",
    "account_changes": [
      {
        "account_id": 601,
        "before_available_units": "24.00",
        "after_available_units": "23.00"
      }
    ]
  }
}
```

Insufficient balance failure:

```json
{
  "code": 409,
  "message": "insufficient available units",
  "data": {
    "account_id": 601,
    "available_units": "0.50",
    "required_units": "1.00"
  }
}
```

Supplement deduction request:

```json
{
  "account_id": 601,
  "units": 1.00,
  "reason": "Manual supplement deduction after missed make-up confirmation"
}
```

Supplement deduction success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "adjustment": {
      "id": 301,
      "adjustment_no": "ADJ2026061211300010017311",
      "adjustment_type": "supplement_deduction",
      "direction": "decrease",
      "units": "1.00",
      "status": "confirmed"
    },
    "account": {
      "id": 601,
      "before_available_units": "23.00",
      "after_available_units": "22.00"
    }
  }
}
```

### Endpoint-Level Request/Response/Failure Catalog

Use this catalog as the controller test fixture set. Each API has a concrete request, success response, validation failure response, and business failure response.

```json
[
  {
    "api": "GET /admin/education/academic/attendance/lessons/page",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "class_id": 701, "teacher_id": 201, "status": "scheduled", "start_at": "2026-06-12 00:00:00", "end_at": "2026-06-12 23:59:59"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 9001, "lesson_no": "LES2026061210000010014821", "title": "Art Basics Lesson 1", "student_count": 2, "status": "scheduled"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "page is required", "data": {"field": "page"}},
    "business_failure": {"code": 403, "message": "campus is outside current context", "data": {"campus_id": 9999}}
  },
  {
    "api": "GET /admin/education/academic/attendance/lessons/{lessonId}",
    "request": {"path": {"lessonId": 9001}},
    "success": {"code": 200, "message": "success", "data": {"lesson": {"id": 9001, "lesson_no": "LES2026061210000010014821", "status": "scheduled"}, "students": [{"lesson_student_id": 10001, "student_id": 101, "student_name_snapshot": "Student Zhang", "planned_units": "1.00", "account_available_units": "24.00"}]}},
    "validation_failure": {"code": 422, "message": "lessonId must be a positive integer", "data": {"field": "lessonId"}},
    "business_failure": {"code": 404, "message": "lesson not found in current context", "data": {"lesson_id": 9001}}
  },
  {
    "api": "POST /admin/education/academic/attendance/lessons/{lessonId}/submit",
    "request": {"path": {"lessonId": 9001}, "body": {"submitted_at": "2026-06-12 11:05:00", "records": [{"lesson_student_id": 10001, "attendance_status": "present", "consume_policy": "consume", "consumed_units": 1, "remark": "on time"}, {"lesson_student_id": 10002, "attendance_status": "leave", "consume_policy": "no_consume", "consumed_units": 0, "remark": "approved leave"}]}},
    "success": {"code": 200, "message": "success", "data": {"lesson_id": 9001, "attendance_batch_no": "ATT2026061211050010014821", "attendance_count": 2, "consumed_count": 1, "no_consume_count": 1, "total_consumed_units": "1.00"}},
    "validation_failure": {"code": 422, "message": "records is required", "data": {"field": "records"}},
    "business_failure": {"code": 409, "message": "insufficient available units", "data": {"account_id": 601, "available_units": "0.50", "required_units": "1.00"}}
  },
  {
    "api": "GET /admin/education/academic/consumptions/page",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "account_id": 601, "source_type": "attendance", "status": "active"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 201, "consumption_no": "CON2026061211050010019912", "account_id": 601, "lesson_id": 9001, "source_type": "attendance", "direction": "decrease", "units": "1.00", "status": "active"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "pageSize must be between 1 and 100", "data": {"field": "pageSize"}},
    "business_failure": {"code": 403, "message": "campus is outside current context", "data": {"campus_id": 9999}}
  },
  {
    "api": "GET /admin/education/academic/consumptions/{id}",
    "request": {"path": {"id": 201}},
    "success": {"code": 200, "message": "success", "data": {"id": 201, "consumption_no": "CON2026061211050010019912", "units": "1.00", "before_available_units": "24.00", "after_available_units": "23.00", "status": "active"}},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 404, "message": "consumption not found in current context", "data": {"id": 201}}
  },
  {
    "api": "POST /admin/education/academic/consumptions/{id}/rollback",
    "request": {"path": {"id": 201}, "body": {"reason": "Attendance correction"}},
    "success": {"code": 200, "message": "success", "data": {"original": {"id": 201, "status": "reversed"}, "rollback": {"id": 202, "source_type": "rollback", "direction": "increase", "units": "1.00"}, "account": {"id": 601, "after_available_units": "24.00"}}},
    "validation_failure": {"code": 422, "message": "reason is required", "data": {"field": "reason"}},
    "business_failure": {"code": 409, "message": "consumption is already reversed", "data": {"id": 201}}
  },
  {
    "api": "GET /admin/education/academic/account-adjustments/page",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "account_id": 601, "adjustment_type": "supplement_deduction", "status": "confirmed"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 301, "adjustment_no": "ADJ2026061211300010017311", "account_id": 601, "adjustment_type": "supplement_deduction", "direction": "decrease", "units": "1.00", "status": "confirmed"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "page is required", "data": {"field": "page"}},
    "business_failure": {"code": 403, "message": "campus is outside current context", "data": {"campus_id": 9999}}
  },
  {
    "api": "GET /admin/education/academic/account-adjustments/{id}",
    "request": {"path": {"id": 301}},
    "success": {"code": 200, "message": "success", "data": {"id": 301, "adjustment_no": "ADJ2026061211300010017311", "units": "1.00", "before_available_units": "23.00", "after_available_units": "22.00", "status": "confirmed", "reason": "Manual supplement deduction after missed make-up confirmation"}},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 404, "message": "account adjustment not found in current context", "data": {"id": 301}}
  },
  {
    "api": "POST /admin/education/academic/account-adjustments",
    "request": {"body": {"account_id": 601, "units": 1, "reason": "Manual supplement deduction after missed make-up confirmation"}},
    "success": {"code": 200, "message": "success", "data": {"adjustment": {"id": 301, "adjustment_no": "ADJ2026061211300010017311", "adjustment_type": "supplement_deduction", "direction": "decrease", "units": "1.00", "status": "confirmed"}, "account": {"id": 601, "after_available_units": "22.00"}}},
    "validation_failure": {"code": 422, "message": "account_id is required", "data": {"field": "account_id"}},
    "business_failure": {"code": 409, "message": "insufficient available units", "data": {"account_id": 601, "available_units": "0.50", "required_units": "1.00"}}
  },
  {
    "api": "POST /admin/education/academic/account-adjustments/{id}/rollback",
    "request": {"path": {"id": 301}, "body": {"reason": "Manual deduction correction"}},
    "success": {"code": 200, "message": "success", "data": {"original": {"id": 301, "status": "rolled_back"}, "rollback": {"id": 302, "adjustment_type": "rollback", "direction": "increase", "units": "1.00"}, "account": {"id": 601, "after_available_units": "23.00"}}},
    "validation_failure": {"code": 422, "message": "reason is required", "data": {"field": "reason"}},
    "business_failure": {"code": 409, "message": "account adjustment is already rolled back", "data": {"id": 301}}
  }
]
```

## PC Admin Page Tasks

### API Client

Create:

```text
mineadmin-education-saas/admin-web/src/api/education/academic/attendanceConsumption.ts
```

Types:

```ts
export type AttendanceStatus = 'present' | 'late' | 'absent' | 'leave'
export type ConsumptionPolicy = 'consume' | 'no_consume'
export type ConsumptionSourceType = 'attendance' | 'rollback'
export type LedgerDirection = 'decrease' | 'increase'
export type ConsumptionStatus = 'active' | 'reversed'
export type AccountAdjustmentType = 'supplement_deduction' | 'rollback'
export type AccountAdjustmentStatus = 'confirmed' | 'rolled_back'

export interface PageResult<T> {
  list: T[]
  total: number
}

export interface AttendanceLessonRecord {
  id: number
  lesson_no: string
  title: string
  class_name_snapshot: string
  teacher_name_snapshot: string
  start_at: string
  end_at: string
  student_count: number
  status: 'scheduled' | 'completed' | 'cancelled'
}

export interface AttendanceSubmitRecord {
  lesson_student_id: number
  attendance_status: AttendanceStatus
  consume_policy: ConsumptionPolicy
  consumed_units: number
  remark?: string | null
}

export interface ConsumptionRecord {
  id: number
  consumption_no: string
  account_id: number
  student_id: number
  student_name?: string
  course_id: number
  course_name?: string
  lesson_id: number
  source_type: ConsumptionSourceType
  direction: LedgerDirection
  units: string
  before_available_units: string
  after_available_units: string
  status: ConsumptionStatus
}

export interface AccountAdjustmentRecord {
  id: number
  adjustment_no: string
  account_id: number
  student_id: number
  student_name?: string
  course_id: number
  course_name?: string
  adjustment_type: AccountAdjustmentType
  direction: LedgerDirection
  units: string
  before_available_units: string
  after_available_units: string
  status: AccountAdjustmentStatus
  reason: string
}
```

Methods:

```ts
export function pageAttendanceLessons(params: Record<string, unknown>): Promise<PageResult<AttendanceLessonRecord>>
export function getAttendanceLesson(lessonId: number): Promise<Record<string, unknown>>
export function submitAttendance(lessonId: number, payload: { submitted_at?: string | null; records: AttendanceSubmitRecord[] }): Promise<Record<string, unknown>>
export function pageConsumptions(params: Record<string, unknown>): Promise<PageResult<ConsumptionRecord>>
export function getConsumption(id: number): Promise<ConsumptionRecord>
export function rollbackConsumption(id: number, reason: string): Promise<Record<string, unknown>>
export function pageAccountAdjustments(params: Record<string, unknown>): Promise<PageResult<AccountAdjustmentRecord>>
export function getAccountAdjustment(id: number): Promise<AccountAdjustmentRecord>
export function createSupplementDeduction(payload: { account_id: number; units: number; reason: string }): Promise<Record<string, unknown>>
export function rollbackAccountAdjustment(id: number, reason: string): Promise<Record<string, unknown>>
```

### Routes and Menus

Modify:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Route entries:

```text
Route: /education/academic/attendance-review
Route name: EducationAcademicAttendanceReview
Menu: 教务 SaaS / 点名课消 / 点名审核
Permission: education:academic:attendance:lesson-page
Page file: admin-web/src/views/education/academic/AttendanceReview.vue

Route: /education/academic/consumptions
Route name: EducationAcademicConsumptionLedgerList
Menu: 教务 SaaS / 点名课消 / 课消流水
Permission: education:academic:consumption:page
Page file: admin-web/src/views/education/academic/ConsumptionLedgerList.vue

Route: /education/academic/account-adjustments
Route name: EducationAcademicAccountAdjustmentList
Menu: 教务 SaaS / 点名课消 / 补扣回滚
Permission: education:academic:account-adjustment:page
Page file: admin-web/src/views/education/academic/AccountAdjustmentList.vue
```

### AttendanceReview

File:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/AttendanceReview.vue
```

Component files:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/components/AttendanceSubmitDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/AttendanceResultDrawer.vue
```

Search fields:

```text
campus_id, class_id, teacher_id, status, start_at, end_at, keyword
```

Table columns:

```text
lesson_no, title, class_name_snapshot, teacher_name_snapshot, start_at, end_at, student_count, status
```

Attendance drawer fields:

```text
lesson base info
student rows with student_name_snapshot, planned_units, account_available_units
attendance_status selector
consume_policy selector
consumed_units decimal input
remark input
batch submitted_at datetime picker
```

Actions and states:

```text
open attendance drawer: education:academic:attendance:detail
submit attendance button: education:academic:attendance:submit
loading: table skeleton while pageAttendanceLessons is pending
empty: show no lessons in selected range
error: show API message and keep filters
permission: hide submit button without permission
validation failure: keep drawer open and mark field
insufficient balance failure: keep drawer open and highlight student account row
idempotent success: show existing attendance summary without duplicate warning
submit success: close submit drawer, open result drawer, reload lesson row as completed
```

### ConsumptionLedgerList

File:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/ConsumptionLedgerList.vue
```

Component file:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/components/ConsumptionRollbackDialog.vue
```

Search fields:

```text
campus_id, account_id, student_id, course_id, lesson_id, source_type, status, keyword
```

Table columns:

```text
consumption_no, student_name, course_name, lesson_no, source_type, direction, units, before_available_units, after_available_units, status, created_at
```

Actions and states:

```text
detail button: education:academic:consumption:detail
rollback button: education:academic:consumption:rollback
loading/empty/error/permission states match AttendanceReview
rollback confirmation requires reason
rollback success reloads row as reversed and refreshes account balance display
rollback business failure keeps dialog open and shows already reversed message
```

### AccountAdjustmentList

File:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/AccountAdjustmentList.vue
```

Component files:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/components/AccountAdjustmentForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/AccountAdjustmentRollbackDialog.vue
```

Search fields:

```text
campus_id, account_id, student_id, course_id, adjustment_type, status, keyword
```

Table columns:

```text
adjustment_no, student_name, course_name, adjustment_type, direction, units, before_available_units, after_available_units, status, reason, created_at
```

Form fields:

```text
account_id remote selector filtered by campus and active account status
units decimal input
reason textarea
```

Actions and states:

```text
create supplement deduction button: education:academic:account-adjustment:create
detail button: education:academic:account-adjustment:detail
rollback button: education:academic:account-adjustment:rollback
loading/empty/error/permission states match AttendanceReview
create validation failure keeps form open
insufficient balance failure keeps form open and shows account available_units
create success closes form and reloads table
rollback success reloads original row as rolled_back and displays rollback row
```

## Teacher / Guardian Mobile Page Tasks

This module has no teacher or guardian page because V1-04 creates admin-side attendance, consumption, and adjustment services; visible teacher attendance submission is implemented in V1-06 and guardian consumption viewing is implemented in V1-07.

Mobile dependency notes:

```text
V1-06 teacher mobile will submit attendance through teacher-scoped APIs created in V1-06, reusing AttendanceService rules from V1-04.
V1-07 guardian mobile will read consumption rows through guardian-scoped APIs created in V1-07.
No mobile-uniapp API client or pages are created in V1-04.
Run the mobile H5 build to prove V1-04 did not break the existing mobile shell.
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
| `AttendanceConsumptionMigrationTest.php` | `test_attendance_consumption_tables_exist` | all three V1-04 tables exist |
| `AttendanceConsumptionMigrationTest.php` | `test_attendance_columns_and_unique_keys_exist` | attendance columns and tenant + lesson_student unique key exist |
| `AttendanceConsumptionMigrationTest.php` | `test_consumption_decimal_and_rollback_indexes_exist` | consumption decimal columns and original rollback unique key exist |
| `AttendanceConsumptionMigrationTest.php` | `test_adjustment_decimal_and_rollback_indexes_exist` | adjustment decimal columns and original rollback unique key exist |
| `AttendanceConsumptionMigrationTest.php` | `test_rollback_drops_tables_in_dependency_order` | rollback drops adjustments, consumptions, and attendances |

### Repository Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `AttendanceRepositoryTest.php` | `test_lesson_page_filters_by_tenant_campus_teacher_status_and_range` | cross-tenant and cross-campus lessons are absent |
| `AttendanceRepositoryTest.php` | `test_existing_by_lesson_student_ids_returns_idempotency_rows` | existing attendance rows are returned by lesson_student_id |
| `ConsumptionRepositoryTest.php` | `test_page_filters_by_account_source_status_and_keyword` | only matching consumption rows are returned |
| `ConsumptionRepositoryTest.php` | `test_has_rollback_detects_existing_reversal` | original consumption with rollback returns true |
| `AccountAdjustmentRepositoryTest.php` | `test_page_filters_by_account_type_status_and_keyword` | only matching adjustment rows are returned |
| `AccountAdjustmentRepositoryTest.php` | `test_has_rollback_detects_existing_adjustment_reversal` | original adjustment with rollback returns true |

### Service Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `AttendanceServiceTest.php` | `test_submit_attendance_creates_attendance_and_consumption_transactionally` | attendance rows, consumption rows, completed lesson, and account balance changes persist |
| `AttendanceServiceTest.php` | `test_submit_attendance_is_idempotent_for_same_payload` | second identical submit returns same summary and creates no extra consumption rows |
| `AttendanceServiceTest.php` | `test_submit_attendance_rejects_changed_payload_after_submit` | service throws code 409 |
| `AttendanceServiceTest.php` | `test_submit_attendance_requires_all_lesson_students` | missing lesson student returns code 422 |
| `AttendanceServiceTest.php` | `test_leave_status_cannot_consume` | service throws code 422 |
| `AttendanceServiceTest.php` | `test_submit_attendance_rejects_insufficient_balance` | service throws code 409 and no attendance rows persist |
| `LessonConsumptionServiceTest.php` | `test_rollback_consumption_creates_reversal_and_restores_account` | original status reversed, rollback row exists, account available_units restored |
| `LessonConsumptionServiceTest.php` | `test_rollback_consumption_is_rejected_when_already_reversed` | service throws code 409 |
| `AccountAdjustmentServiceTest.php` | `test_create_supplement_deduction_decreases_adjusted_and_available_units` | adjustment row exists and account adjusted_units decreases |
| `AccountAdjustmentServiceTest.php` | `test_create_supplement_deduction_requires_reason` | service throws code 422 |
| `AccountAdjustmentServiceTest.php` | `test_create_supplement_deduction_rejects_insufficient_balance` | service throws code 409 |
| `AccountAdjustmentServiceTest.php` | `test_rollback_adjustment_creates_reversal_and_restores_account` | original rolled_back, rollback row exists, account available_units restored |
| `AccountAdjustmentServiceTest.php` | `test_account_balance_invariant_is_preserved_after_consumption_and_adjustment_rollback` | available_units matches documented formula |

### Controller/API Feature Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `AttendanceConsumptionAdminApiTest.php` | `test_attendance_lesson_page_and_detail_return_mineadmin_shape` | responses use `{code,message,data}` |
| `AttendanceConsumptionAdminApiTest.php` | `test_attendance_submit_returns_summary` | response contains attendance_count, consumed_count, and total_consumed_units |
| `AttendanceConsumptionAdminApiTest.php` | `test_attendance_submit_duplicate_payload_is_idempotent` | two submits return 200 and only one consumption row exists |
| `AttendanceConsumptionAdminApiTest.php` | `test_consumption_page_detail_and_rollback_return_expected_shape` | rollback response contains original, rollback, and account blocks |
| `AttendanceConsumptionAdminApiTest.php` | `test_account_adjustment_create_and_rollback_return_expected_shape` | create and rollback responses contain adjustment and account blocks |
| `AttendanceConsumptionAdminApiTest.php` | `test_validation_failures_match_catalog` | missing required fields return documented 422 messages |
| `AttendanceConsumptionAdminApiTest.php` | `test_business_failures_match_catalog` | insufficient balance and already reversed failures return documented codes |

### Permission, Isolation, and Audit Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `AttendanceConsumptionPermissionTest.php` | `test_missing_attendance_submit_permission_returns_403` | API returns code 403 |
| `AttendanceConsumptionPermissionTest.php` | `test_front_desk_cannot_rollback_consumption` | rollback API returns code 403 |
| `AttendanceConsumptionIsolationTest.php` | `test_tenant_user_cannot_read_other_tenant_consumption` | page and detail responses exclude other tenant rows |
| `AttendanceConsumptionIsolationTest.php` | `test_campus_scoped_user_cannot_submit_other_campus_lesson_attendance` | attendance submit returns code 403 |
| `AttendanceConsumptionIsolationTest.php` | `test_account_adjustment_respects_campus_scope` | cross-campus account adjustment returns code 403 |
| `AttendanceConsumptionAuditTest.php` | `test_attendance_submit_creates_audit_log` | audit action `education.academic.attendance.submitted` exists |
| `AttendanceConsumptionAuditTest.php` | `test_consumption_rollback_creates_audit_log` | audit action `education.academic.consumption.rollback` exists |
| `AttendanceConsumptionAuditTest.php` | `test_adjustment_create_and_rollback_create_audit_logs` | both account adjustment audit actions exist |

### PC Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `AttendanceReview.spec.ts` | `renders_attendance_lesson_table_and_filters` | table shows lesson_no/title/status after pageAttendanceLessons resolves |
| `AttendanceReview.spec.ts` | `permission_buttons_are_hidden_without_permission` | submit button is hidden |
| `AttendanceSubmitDrawer.spec.ts` | `loads_students_and_submits_attendance` | drawer sends records payload and displays result summary |
| `AttendanceSubmitDrawer.spec.ts` | `insufficient_balance_highlights_student_row` | 409 message and account row warning are displayed |
| `AttendanceSubmitDrawer.spec.ts` | `idempotent_success_does_not_duplicate_result_rows` | repeated submit shows one summary |
| `ConsumptionLedgerList.spec.ts` | `renders_consumption_rows_and_filters` | consumption_no, units, and status are displayed |
| `ConsumptionLedgerList.spec.ts` | `rollback_success_marks_row_reversed` | row status changes to reversed after API success |
| `ConsumptionLedgerList.spec.ts` | `rollback_failure_keeps_dialog_open` | already reversed message is displayed |
| `AccountAdjustmentList.spec.ts` | `creates_supplement_deduction` | form sends account_id, units, and reason |
| `AccountAdjustmentList.spec.ts` | `adjustment_rollback_success_marks_original_rolled_back` | row status changes to rolled_back |
| `AccountAdjustmentList.spec.ts` | `insufficient_balance_keeps_adjustment_form_open` | 409 message is displayed and form remains visible |

### Mobile Regression Test

| Verification | Assert |
| --- | --- |
| `pnpm build:h5` in `mobile-uniapp` | existing teacher/guardian shell still builds because V1-04 adds no mobile files |

## Execution Commands

### Backend Migration Gate

Run:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter AttendanceConsumptionMigrationTest
php bin/hyperf.php migrate:rollback --step=1
php bin/hyperf.php migrate
```

Expected:

```text
V1-04 migration runs successfully.
AttendanceConsumptionMigrationTest passes.
Rollback drops V1-04 tables in dependency-safe order.
Re-running migration succeeds.
```

### Backend Unit Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter AttendanceRepositoryTest
composer test -- --filter ConsumptionRepositoryTest
composer test -- --filter AccountAdjustmentRepositoryTest
composer test -- --filter AttendanceServiceTest
composer test -- --filter LessonConsumptionServiceTest
composer test -- --filter AccountAdjustmentServiceTest
```

Expected:

```text
V1-04 repository and service tests pass.
Attendance idempotency, consumption rollback, supplement deduction, and account invariant tests pass.
```

### Backend Feature Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter AttendanceConsumptionAdminApiTest
composer test -- --filter AttendanceConsumptionPermissionTest
composer test -- --filter AttendanceConsumptionIsolationTest
composer test -- --filter AttendanceConsumptionAuditTest
```

Expected:

```text
V1-04 admin API, permission, isolation, and audit tests pass.
Every API returns the documented MineAdmin result shape.
```

### PC Gate

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- AttendanceReview
pnpm test -- AttendanceSubmitDrawer
pnpm test -- ConsumptionLedgerList
pnpm test -- AccountAdjustmentList
pnpm build
```

Expected:

```text
PC lint passes.
V1-04 page tests pass.
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

### V1-04 Final Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter AttendanceConsumption
composer cs-fix -- --dry-run
composer analyse
cd ../admin-web
pnpm lint
pnpm test -- AttendanceReview
pnpm test -- AttendanceSubmitDrawer
pnpm test -- ConsumptionLedgerList
pnpm test -- AccountAdjustmentList
pnpm build
cd ../mobile-uniapp
pnpm build:h5
```

Expected:

```text
All V1-04 backend tests pass.
Backend code style dry run passes.
Backend static analysis passes.
PC lint, page tests, and build pass.
Mobile H5 build passes.
```

## Acceptance Gate

V1-04 is accepted only when all conditions are true:

```text
- `edu_lesson_attendances`, `edu_lesson_consumptions`, and `edu_account_adjustments` exist with documented columns and indexes.
- Migration rollback drops all V1-04 tables in dependency-safe order.
- Attendance, consumption, and account adjustment models cast decimal, date, status, and id fields correctly.
- Repositories apply tenant and campus scope filters consistently.
- Attendance submission requires every non-cancelled lesson-student snapshot.
- Attendance submission is idempotent for identical payloads and rejects changed payloads after submit.
- Attendance submission deducts account balance exactly once per consumed lesson student.
- Attendance submission rejects insufficient balance, frozen accounts, closed accounts, cancelled lessons, and completed lessons with documented codes.
- Consumption rollback creates a reversing ledger row and restores account consumed_units and available_units.
- Manual supplement deduction creates an account adjustment row and decreases adjusted_units and available_units.
- Account adjustment rollback creates a reversing adjustment row and restores adjusted_units and available_units.
- Account balance invariant is preserved after submit, consumption rollback, supplement deduction, and adjustment rollback.
- Admin APIs return MineAdmin result shape and documented validation/business failures.
- Permission tests prove missing MineAdmin permission codes return 403.
- Isolation tests prove tenant and campus scoped users cannot read or mutate unauthorized rows.
- F04 audit logs are created for all V1-04 write operations.
- PC API client, routes, attendance review page, attendance submit drawer, result drawer, consumption ledger, rollback dialog, account adjustment form, permission buttons, loading, empty, error, success, and submit states pass tests.
- V1-04 adds no mobile pages and mobile H5 build still passes.
```

## Task Breakdown

### Task 1: Create Migration, Enums, and Models

**Files:**

- Create: `mineadmin-education-saas/backend/databases/migrations/2026_06_10_010400_create_v1_attendance_consumption_tables.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/AttendanceStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/ConsumptionPolicy.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/ConsumptionSourceType.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LedgerDirection.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/ConsumptionStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/AccountAdjustmentType.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/AccountAdjustmentStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonAttendance.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonConsumption.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationAccountAdjustment.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/AttendanceConsumptionMigrationTest.php`

- [x] **Step 1: Create migration**

Use the full table, column, index, foreign-key policy, and rollback order from `Database Migration Design`.

- [x] **Step 2: Create enums**

Create all seven V1-04 enums exactly as defined in `MineAdmin Backend Module Design`.

- [x] **Step 3: Create models**

Create all three models with table names, fillable fields, casts, relationships, timestamps, and soft delete behavior defined in `MineAdmin Backend Module Design`.

- [x] **Step 4: Write migration test**

Create `AttendanceConsumptionMigrationTest` with cases listed in `Test Plan`.

- [x] **Step 5: Run migration gate**

Run commands from `Backend Migration Gate`.

Expected:

```text
Migration, rollback, re-migration, and migration tests pass.
```

### Task 2: Create Repositories and Services

**Files:**

- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/AttendanceRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/ConsumptionRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/AccountAdjustmentRepository.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/AttendanceService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/LessonConsumptionService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/AccountAdjustmentService.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/AttendanceRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/ConsumptionRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/AccountAdjustmentRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/AttendanceServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/LessonConsumptionServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/AccountAdjustmentServiceTest.php`

- [x] **Step 1: Create repositories**

Implement repository methods, filters, tenant scope, campus scope, idempotency queries, rollback queries, and pagination rules from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create services**

Implement attendance submit, consumption creation, consumption rollback, supplement deduction, adjustment rollback, account balance invariant updates, audit dispatch, and transaction boundaries from `MineAdmin Backend Module Design`.

- [x] **Step 3: Write repository tests**

Create repository tests listed in `Test Plan`.

- [x] **Step 4: Write service tests**

Create service tests listed in `Test Plan`.

- [x] **Step 5: Run backend unit gate**

Run commands from `Backend Unit Gate`.

Expected:

```text
V1-04 repository, service, idempotency, rollback, adjustment, and account invariant tests pass.
```

### Task 3: Create Requests, Schemas, and Controllers

**Files:**

- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/AttendanceLessonPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/AttendanceSubmitRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ConsumptionPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ConsumptionRollbackRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/AccountAdjustmentPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/AccountAdjustmentCreateRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/AccountAdjustmentRollbackRequest.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/AttendanceSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/ConsumptionSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/AccountAdjustmentSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/AttendanceSubmitResultSchema.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/AttendanceController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/ConsumptionController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/AccountAdjustmentController.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/AttendanceConsumptionAdminApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/AttendanceConsumptionPermissionTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/AttendanceConsumptionIsolationTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/AttendanceConsumptionAuditTest.php`

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
V1-04 admin API, permission, isolation, and audit tests pass.
```

### Task 4: Create PC API Client, Routes, Pages, Forms, and Dialogs

**Files:**

- Create: `mineadmin-education-saas/admin-web/src/api/education/academic/attendanceConsumption.ts`
- Modify: `mineadmin-education-saas/admin-web/src/router/modules/education.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/AttendanceReview.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/ConsumptionLedgerList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/AccountAdjustmentList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/AttendanceSubmitDrawer.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/AttendanceResultDrawer.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/ConsumptionRollbackDialog.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/AccountAdjustmentForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/AccountAdjustmentRollbackDialog.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/AttendanceReview.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/AttendanceSubmitDrawer.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/ConsumptionLedgerList.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/AccountAdjustmentList.spec.ts`

- [x] **Step 1: Create typed API client**

Implement all types and methods listed in `PC Admin Page Tasks`.

- [x] **Step 2: Add routes and menus**

Add V1-04 route entries and auth meta to `admin-web/src/router/modules/education.ts`.

- [x] **Step 3: Create attendance review page and submit drawer**

Implement AttendanceReview, AttendanceSubmitDrawer, and AttendanceResultDrawer using page tasks from `PC Admin Page Tasks`.

- [x] **Step 4: Create consumption ledger page and rollback dialog**

Implement ConsumptionLedgerList and ConsumptionRollbackDialog with rollback reason and reversed row states.

- [x] **Step 5: Create account adjustment page and rollback dialog**

Implement AccountAdjustmentList, AccountAdjustmentForm, and AccountAdjustmentRollbackDialog with supplement deduction and rollback flows.

- [x] **Step 6: Write PC tests**

Create PC tests listed in `Test Plan`.

- [x] **Step 7: Run PC gate**

Run commands from `PC Gate`.

Expected:

```text
PC lint, V1-04 page tests, and production build pass.
```

### Task 5: Run V1-04 Final Gate

**Files:**

- Verify: all backend, PC, and mobile paths listed in `File Structure`.

- [x] **Step 1: Run backend final gate**

Run backend commands from `V1-04 Final Gate`.

- [x] **Step 2: Run PC final gate**

Run PC commands from `V1-04 Final Gate`.

- [x] **Step 3: Run mobile regression gate**

Run mobile command from `V1-04 Final Gate`.

- [x] **Step 4: Commit V1-04**

Run:

```bash
cd mineadmin-education-saas
git add backend admin-web mobile-uniapp
git commit -m "feat: add v1 attendance consumption adjustments"
```

Expected:

```text
Commit succeeds with V1-04 backend, PC, tests, and verification changes.
```

## Self-Review

- Spec coverage: V1-04 covers attendance submission, automatic lesson consumption, manual supplement deduction, rollback rows, and account balance consistency from the V1 attendance-consumption-adjustment scope.
- MineAdmin fit: The plan uses MineAdmin 3.x `app/Http/Admin`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, `databases/migrations`, permission attributes, OperationMiddleware for writes, and MineAdmin result shape.
- Tenant isolation: All records are tenant-scoped and campus-scoped; services validate campus scope before every write.
- V1 dependency fit: V1-04 consumes V1-02 student course accounts and V1-03 lesson-student snapshots; V1-06 can reuse attendance services for teacher mobile submission; V1-07 can expose guardian consumption rows through scoped mobile APIs.
- Transaction fit: Attendance submit, consumption rollback, supplement deduction, and adjustment rollback are explicit database transactions and preserve the account balance invariant.
- Ledger fit: Rollback creates reversing rows instead of deleting original consumption or adjustment rows.
- Finance boundary: V1-04 adjusts lesson account units only and does not implement money refund, reconciliation, invoice, or finance account posting.
- PC fit: Pages include typed API client, route/menu entries, attendance review, submit drawer, result drawer, consumption ledger, rollback dialog, account adjustment form, permission buttons, loading, empty, error, success, and submit states.
- Mobile fit: V1-04 adds no visible mobile page and keeps mobile build verification.
- Readiness: This plan has exact paths, full migration design, backend layer tasks, API request/response/failure examples, PC tasks, mobile rationale, tests, commands, expected outputs, and acceptance gates, so V1-04 can be marked `ready`.
