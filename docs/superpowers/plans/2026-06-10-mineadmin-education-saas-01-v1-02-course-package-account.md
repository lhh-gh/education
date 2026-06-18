# MineAdmin Education SaaS V1-02 Course Package Account Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V1 courses, teacher-course authorization, lesson packages, enrollments, and student course accounts so operators can sell a课包 and initialize or increment the student's remaining lesson balance.

**Architecture:** V1-02 depends on Foundation F01 tenant/campus, F02 education user context and campus scope, F04 audit logging, F05 PC conventions, and V1-01 student/teacher records. Backend follows MineAdmin 3.x native paths under `app/Http/Admin`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, and `databases/migrations`; enrollment create/cancel operations update `edu_student_course_accounts` inside database transactions. No teacher or guardian mobile page is added in V1-02.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8 decimal columns, Redis queue where project defaults require it, MineAdmin-Vue, Vue3, TypeScript, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** incomplete / not implemented. `ready` means this plan is detailed enough to start coding.

---

## Scope Check

Included:

- Create course, teacher-course authorization, lesson package, enrollment, and student course account tables.
- Create V1-02 enums for enrollment status, account status, and account ledger source type.
- Create models, repositories, services, request classes, schemas, and admin controllers for courses, lesson packages, enrollments, and student course accounts.
- Manage teacher authorization from the course page by linking V1-01 enabled teachers to enabled courses in the same tenant and campus.
- Manage lesson packages with decimal lesson units, bonus units, price snapshots, validity days, and enabled/disabled state.
- Create enrollment records as pending, then materialize or increment a student course account on confirmation, governed by the Enrollment Activation Timing Contract.
- Cancel a confirmed enrollment by reversing available lesson units when no later consumption/freeze prevents reversal.
- Add admin APIs for page/create/update/status/delete where applicable, teacher authorization, enrollment detail/create/cancel, account page/status, and account ledger.
- Add PC API client, route/menu entries, list pages, forms, drawers, permission-controlled buttons, loading/empty/error states, and tests.
- Add migration, repository, service, API, permission, tenant/campus isolation, audit, PC, and mobile regression tests.

Excluded:

- Online payment, payment reconciliation, invoices, refunds, and finance account posting; V4 owns those workflows.
- Class creation, class membership, lesson scheduling, and scheduling conflict checks; V1-03 owns them.
- Attendance, lesson consumption, manual adjustment, and consumption rollback ledger; V1-04 owns them.
- Leave, make-up, and reschedule workflows; V1-05 owns them.
- Teacher mobile lesson pages; V1-06 owns them.
- Guardian mobile account and consumption views; V1-07 owns them.
- Advanced course standards, syllabus, class-level teaching content, and service package standards; V11 owns them.

Business rules:

```text
Courses, packages, teacher authorization, enrollments, and accounts are campus-scoped in V1.
Course code is unique inside tenant + campus.
Lesson package code is unique inside tenant + campus.
Teacher-course authorization accepts only V1-01 enabled teachers from the same tenant and campus.
Enrollment accepts only enabled students, enabled courses, and enabled packages from the same tenant and campus.
Enrollment creation uses package snapshots and does not mutate historical package data after creation.
Enrollment creation persists a pending enrollment; account units are materialized only on confirmation, governed by the Enrollment Activation Timing Contract section.
Account materialization, consumption, and adjustment are the only operations that change account unit columns, and all of them go through StudentCourseAccountService.
Enrollment cancellation is not a finance refund; it only reverses lesson units in the course account.
Enrollment cancellation is allowed only when the target account has enough available units to reverse the enrollment total units.
Course account balance changes must go through StudentCourseAccountService methods only.
Account available_units is maintained by service transactions, not calculated by PC frontend code.
```

Decimal rules:

```text
Lesson units use decimal(10,2), allowing values such as 1.00, 1.50, and 0.50.
Money uses decimal(12,2).
Every service calculation rounds to two decimal places before persistence.
The account invariant is:
available_units = purchased_units + bonus_units + adjusted_units - consumed_units - refunded_units - frozen_units
V1-02 writes purchased_units, bonus_units, refunded_units, frozen_units, and available_units, but only through StudentCourseAccountService.materializeEnrollment at confirmation time.
V1-04 will write consumed_units and adjusted_units through the same account service boundary.
```

Status machines:

```text
Course status: enabled -> disabled, disabled -> enabled.
Teacher-course status: enabled -> disabled, disabled -> enabled; saveTeachers replaces the enabled authorization set.
Lesson package status: enabled -> disabled, disabled -> enabled.
Enrollment status: pending -> confirmed, pending -> cancelled, confirmed -> cancelled.
Account status: active -> frozen, frozen -> active, active/frozen -> closed only when available_units is 0.00.
```

## Enrollment Activation Timing Contract

This section is the single source of truth for when an enrollment becomes active and when its course account units are materialized. V4 Finance Payment consumes this contract and must not redefine it.

Concepts:

```text
Enrollment creation: persist a pending enrollment row with all snapshots; no account units are materialized at this step.
Enrollment confirmation: transition pending -> confirmed and materialize account units exactly once.
Account materialization: create-or-increment one student course account for a single enrollment, keyed by enrollment_id, through StudentCourseAccountService.materializeEnrollment.
```

Two activation modes:

```text
Direct mode (finance gate off): EnrollmentService.create persists a pending enrollment and confirms it inline in the same transaction, so create == confirm == materialize. This is the V1 offline-registration behavior.
Gated mode (finance gate on): EnrollmentService.create persists a pending enrollment and returns. Confirmation is triggered later by V4 on payment success. Units stay unmaterialized until then.
```

Gate switch:

```text
The activation mode is selected by F03 feature flag finance_payment_enabled, resolved per tenant.
The flag defaults to off and is only turned on when V4 is deployed for the tenant, so without a payment module enrollments always confirm inline and never strand in pending.
Flag resolution happens inside EnrollmentService.create, never in controllers or frontend.
Flipping the flag does not migrate or recompute existing rows. Already-confirmed enrollments and their accounts are untouched. Only enrollments created after the flip follow the new mode.
```

Materialization idempotency:

```text
StudentCourseAccountService.materializeEnrollment is idempotent on enrollment_id.
A confirmed enrollment carries materialized_at; re-confirming a confirmed enrollment is a no-op that returns the existing account summary.
The materialization guard is the enrollment row itself: confirm locks the enrollment row and only materializes when status is pending, then sets status confirmed and materialized_at, so a retried confirmation finds a non-pending row and returns the existing account without re-crediting units.
```

Single entry point for account unit columns:

```text
materializeEnrollment, consumeUnits (V1-04), and adjustUnits (V1-04) are the only methods that change account unit columns.
EnrollmentService never writes account unit columns directly; it calls materializeEnrollment.
V4 never writes account unit columns directly; it calls EnrollmentService.confirm, which calls materializeEnrollment.
```

Enrollment creators:

```text
Three creators produce enrollments and all funnel through the same create -> confirm seam:
- V1-02 admin direct enrollment.
- V3 lead conversion.
- V4 order-driven enrollment.
In gated mode, only V4 payment success may confirm. In direct mode, the creating service confirms inline.
```

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_010200_create_v1_course_account_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/EnrollmentStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/StudentCourseAccountStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/AccountLedgerSourceType.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationCourse.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationTeacherCourse.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonPackage.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationEnrollment.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudentCourseAccount.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/CourseRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/TeacherCourseRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonPackageRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/EnrollmentRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/StudentCourseAccountRepository.php
mineadmin-education-saas/backend/app/Service/Education/Academic/CourseService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/TeacherCourseService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/LessonPackageService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/EnrollmentService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/StudentCourseAccountService.php
mineadmin-education-saas/backend/app/Event/Education/Academic/EducationEnrollmentConfirmed.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/CoursePageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/CourseSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/CourseStatusRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/CourseTeacherSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LessonPackagePageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LessonPackageSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LessonPackageStatusRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/EnrollmentPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/EnrollmentCreateRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/EnrollmentCancelRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/StudentCourseAccountPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/StudentCourseAccountStatusRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/AccountLedgerPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/CourseController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/LessonPackageController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/EnrollmentController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/StudentCourseAccountController.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/CourseSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/TeacherCourseSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/LessonPackageSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/EnrollmentSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/StudentCourseAccountSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/AccountLedgerSchema.php
```

Read backend dependencies:

```text
mineadmin-education-saas/backend/app/Service/Education/Foundation/EducationUserContext.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusScopeService.php
mineadmin-education-saas/backend/app/Event/Education/Foundation/EducationAuditEvent.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/AcademicRecordStatus.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudent.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationTeacher.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/StudentRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/TeacherRepository.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Academic/CourseAccountMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/CourseRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherCourseRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/LessonPackageRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/EnrollmentRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/StudentCourseAccountRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/CourseServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherCourseServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/LessonPackageServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/EnrollmentServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/StudentCourseAccountServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/CourseAccountAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/CourseAccountPermissionTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/CourseAccountIsolationTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/CourseAccountAuditTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/academic/courseAccount.ts
mineadmin-education-saas/admin-web/src/views/education/academic/CourseList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/LessonPackageList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/EnrollmentWorkbench.vue
mineadmin-education-saas/admin-web/src/views/education/academic/AccountLedgerList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/CourseForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/CourseTeacherDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/LessonPackageForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/EnrollmentCreateDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/AccountLedgerDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/CourseList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/CourseTeacherDrawer.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/LessonPackageList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/EnrollmentWorkbench.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/AccountLedgerList.spec.ts
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
mineadmin-education-saas/backend/databases/migrations/2026_06_10_010200_create_v1_course_account_tables.php
```

Tables:

```text
edu_courses
edu_teacher_courses
edu_lesson_packages
edu_student_course_accounts
edu_enrollments
```

Foreign-key policy:

```text
Use no physical foreign keys in V1-02.
Use service-level validation against V1-01 students and teachers and V1-02 courses/packages/accounts.
Reason: MineAdmin business tables use soft deletes, tenant isolation, and staged module migrations; service validation keeps rollback and soft-delete behavior predictable.
```

Rollback order:

```text
Schema::dropIfExists('edu_enrollments');
Schema::dropIfExists('edu_student_course_accounts');
Schema::dropIfExists('edu_lesson_packages');
Schema::dropIfExists('edu_teacher_courses');
Schema::dropIfExists('edu_courses');
```

### `edu_courses`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Course id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Campus id |
| `code` | varchar(64) | no | none | Course code inside campus |
| `name` | varchar(120) | no | none | Course name |
| `category` | varchar(80) | yes | null | Course category |
| `subject` | varchar(80) | yes | null | Course subject |
| `unit_minutes` | int unsigned | no | 60 | Minutes represented by one lesson unit |
| `cover_url` | varchar(255) | yes | null | Course cover URL |
| `description` | text | yes | null | Course description |
| `status` | varchar(20) | no | `enabled` | enabled or disabled |
| `sort_order` | int | no | 0 | Sort order |
| `remark` | varchar(500) | yes | null | Internal remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_courses_tenant_campus_code (tenant_id, campus_id, code)
index idx_edu_courses_tenant_campus_status (tenant_id, campus_id, status)
index idx_edu_courses_tenant_campus_name (tenant_id, campus_id, name)
index idx_edu_courses_deleted_at (deleted_at)
```

### `edu_teacher_courses`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Teacher course authorization id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Campus id |
| `course_id` | bigint unsigned | no | none | Course id |
| `teacher_id` | bigint unsigned | no | none | V1-01 teacher id |
| `status` | varchar(20) | no | `enabled` | enabled or disabled |
| `authorized_at` | timestamp | yes | null | Authorization time |
| `remark` | varchar(500) | yes | null | Internal remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_teacher_courses_tenant_course_teacher (tenant_id, course_id, teacher_id)
index idx_edu_teacher_courses_tenant_teacher_status (tenant_id, teacher_id, status)
index idx_edu_teacher_courses_tenant_course_status (tenant_id, course_id, status)
index idx_edu_teacher_courses_tenant_campus_status (tenant_id, campus_id, status)
index idx_edu_teacher_courses_deleted_at (deleted_at)
```

### `edu_lesson_packages`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Lesson package id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Campus id |
| `course_id` | bigint unsigned | no | none | Course id |
| `code` | varchar(64) | no | none | Package code inside campus |
| `name` | varchar(120) | no | none | Package name |
| `lesson_units` | decimal(10,2) | no | 0.00 | Purchased lesson units |
| `bonus_units` | decimal(10,2) | no | 0.00 | Bonus lesson units |
| `total_units` | decimal(10,2) | no | 0.00 | lesson_units + bonus_units |
| `list_price` | decimal(12,2) | no | 0.00 | Original list price |
| `sale_price` | decimal(12,2) | no | 0.00 | Default deal price |
| `validity_days` | int unsigned | yes | null | Account validity days after enrollment |
| `status` | varchar(20) | no | `enabled` | enabled or disabled |
| `sort_order` | int | no | 0 | Sort order |
| `remark` | varchar(500) | yes | null | Internal remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_lesson_packages_tenant_campus_code (tenant_id, campus_id, code)
index idx_edu_lesson_packages_tenant_course_status (tenant_id, course_id, status)
index idx_edu_lesson_packages_tenant_campus_status (tenant_id, campus_id, status)
index idx_edu_lesson_packages_deleted_at (deleted_at)
```

### `edu_student_course_accounts`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Student course account id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Campus id |
| `student_id` | bigint unsigned | no | none | V1-01 student id |
| `course_id` | bigint unsigned | no | none | Course id |
| `purchased_units` | decimal(10,2) | no | 0.00 | Purchased lesson units from enrollments |
| `bonus_units` | decimal(10,2) | no | 0.00 | Bonus lesson units from enrollments |
| `consumed_units` | decimal(10,2) | no | 0.00 | Consumed lesson units, owned by V1-04 |
| `adjusted_units` | decimal(10,2) | no | 0.00 | Manual adjustment units, owned by V1-04 |
| `refunded_units` | decimal(10,2) | no | 0.00 | Units reversed by cancelled/refunded enrollments |
| `frozen_units` | decimal(10,2) | no | 0.00 | Frozen lesson units |
| `available_units` | decimal(10,2) | no | 0.00 | Remaining usable lesson units |
| `status` | varchar(20) | no | `active` | active, frozen, or closed |
| `first_enrollment_id` | bigint unsigned | yes | null | First enrollment id |
| `last_enrollment_id` | bigint unsigned | yes | null | Last enrollment id |
| `opened_at` | timestamp | yes | null | Account opened time |
| `expires_at` | timestamp | yes | null | Account expiry time |
| `remark` | varchar(500) | yes | null | Internal remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_student_course_accounts_tenant_student_course (tenant_id, student_id, course_id)
index idx_edu_student_course_accounts_tenant_campus_status (tenant_id, campus_id, status)
index idx_edu_student_course_accounts_tenant_student (tenant_id, student_id)
index idx_edu_student_course_accounts_tenant_course (tenant_id, course_id)
index idx_edu_student_course_accounts_expires_at (expires_at)
index idx_edu_student_course_accounts_deleted_at (deleted_at)
```

### `edu_enrollments`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Enrollment id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Campus id |
| `enrollment_no` | varchar(64) | no | none | Enrollment number |
| `student_id` | bigint unsigned | no | none | V1-01 student id |
| `course_id` | bigint unsigned | no | none | Course id |
| `lesson_package_id` | bigint unsigned | no | none | Lesson package id |
| `account_id` | bigint unsigned | yes | null | Student course account id, set on confirmation |
| `student_name_snapshot` | varchar(120) | no | none | Student name at enrollment time |
| `course_name_snapshot` | varchar(120) | no | none | Course name at enrollment time |
| `package_name_snapshot` | varchar(120) | no | none | Package name at enrollment time |
| `package_lesson_units` | decimal(10,2) | no | 0.00 | Purchased units snapshot |
| `package_bonus_units` | decimal(10,2) | no | 0.00 | Bonus units snapshot |
| `total_units` | decimal(10,2) | no | 0.00 | Total units snapshot |
| `list_price` | decimal(12,2) | no | 0.00 | List price snapshot |
| `deal_amount` | decimal(12,2) | no | 0.00 | Deal amount snapshot |
| `status` | varchar(20) | no | `pending` | pending, confirmed, or cancelled |
| `enrolled_at` | timestamp | yes | null | Enrollment business time |
| `confirmed_at` | timestamp | yes | null | Confirmed time |
| `materialized_at` | timestamp | yes | null | Account materialization time |
| `cancelled_at` | timestamp | yes | null | Cancelled time |
| `cancel_reason` | varchar(500) | yes | null | Cancellation reason |
| `remark` | varchar(500) | yes | null | Internal remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_enrollments_tenant_enrollment_no (tenant_id, enrollment_no)
index idx_edu_enrollments_tenant_student_status (tenant_id, student_id, status)
index idx_edu_enrollments_tenant_course_status (tenant_id, course_id, status)
index idx_edu_enrollments_tenant_package (tenant_id, lesson_package_id)
index idx_edu_enrollments_tenant_account (tenant_id, account_id)
index idx_edu_enrollments_tenant_campus_enrolled (tenant_id, campus_id, enrolled_at)
index idx_edu_enrollments_deleted_at (deleted_at)
```

## MineAdmin Backend Module Design

### Enums

Create `EnrollmentStatus`:

```php
enum EnrollmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
}
```

Create `StudentCourseAccountStatus`:

```php
enum StudentCourseAccountStatus: string
{
    case Active = 'active';
    case Frozen = 'frozen';
    case Closed = 'closed';
}
```

Create `AccountLedgerSourceType`:

```php
enum AccountLedgerSourceType: string
{
    case Enrollment = 'enrollment';
    case EnrollmentCancel = 'enrollment_cancel';
    case Consumption = 'consumption';
    case Adjustment = 'adjustment';
}
```

Reuse V1-01 `AcademicRecordStatus` for course, teacher-course, and lesson package status values:

```text
enabled
disabled
```

### Models

All models use MineAdmin/Hyperf model conventions, timestamps, soft deletes, and guarded tenant fields only through service methods.

`EducationCourse`:

```text
table: edu_courses
fillable: tenant_id, campus_id, code, name, category, subject, unit_minutes, cover_url, description, status, sort_order, remark, created_by, updated_by
casts: tenant_id integer, campus_id integer, unit_minutes integer, sort_order integer
soft delete: yes
relationships: lessonPackages hasMany EducationLessonPackage, teacherCourses hasMany EducationTeacherCourse
```

`EducationTeacherCourse`:

```text
table: edu_teacher_courses
fillable: tenant_id, campus_id, course_id, teacher_id, status, authorized_at, remark, created_by, updated_by
casts: tenant_id integer, campus_id integer, course_id integer, teacher_id integer, authorized_at datetime
soft delete: yes
relationships: course belongsTo EducationCourse, teacher belongsTo EducationTeacher
```

`EducationLessonPackage`:

```text
table: edu_lesson_packages
fillable: tenant_id, campus_id, course_id, code, name, lesson_units, bonus_units, total_units, list_price, sale_price, validity_days, status, sort_order, remark, created_by, updated_by
casts: tenant_id integer, campus_id integer, course_id integer, lesson_units decimal:2, bonus_units decimal:2, total_units decimal:2, list_price decimal:2, sale_price decimal:2, validity_days integer, sort_order integer
soft delete: yes
relationships: course belongsTo EducationCourse
```

`EducationStudentCourseAccount`:

```text
table: edu_student_course_accounts
fillable: tenant_id, campus_id, student_id, course_id, purchased_units, bonus_units, consumed_units, adjusted_units, refunded_units, frozen_units, available_units, status, first_enrollment_id, last_enrollment_id, opened_at, expires_at, remark, created_by, updated_by
casts: tenant_id integer, campus_id integer, student_id integer, course_id integer, purchased_units decimal:2, bonus_units decimal:2, consumed_units decimal:2, adjusted_units decimal:2, refunded_units decimal:2, frozen_units decimal:2, available_units decimal:2, first_enrollment_id integer, last_enrollment_id integer, opened_at datetime, expires_at datetime
soft delete: yes
relationships: student belongsTo EducationStudent, course belongsTo EducationCourse, enrollments hasMany EducationEnrollment
```

`EducationEnrollment`:

```text
table: edu_enrollments
fillable: tenant_id, campus_id, enrollment_no, student_id, course_id, lesson_package_id, account_id, student_name_snapshot, course_name_snapshot, package_name_snapshot, package_lesson_units, package_bonus_units, total_units, list_price, deal_amount, status, enrolled_at, confirmed_at, materialized_at, cancelled_at, cancel_reason, remark, created_by, updated_by
casts: tenant_id integer, campus_id integer, student_id integer, course_id integer, lesson_package_id integer, account_id integer, package_lesson_units decimal:2, package_bonus_units decimal:2, total_units decimal:2, list_price decimal:2, deal_amount decimal:2, enrolled_at datetime, confirmed_at datetime, materialized_at datetime, cancelled_at datetime
soft delete: yes
relationships: student belongsTo EducationStudent, course belongsTo EducationCourse, lessonPackage belongsTo EducationLessonPackage, account belongsTo EducationStudentCourseAccount
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
campus_id is required for creates and is filtered by F02 campus scope.
keyword matches code/name/no/mobile fields where present.
status exact match.
page and pageSize use MineAdmin pagination defaults and cap pageSize at 100.
```

`CourseRepository` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function findScoped(int $id, EducationUserContext $context): ?EducationCourse
public function findEnabledForEnrollment(int $id, int $tenantId, int $campusId): ?EducationCourse
public function existsCode(int $tenantId, int $campusId, string $code, ?int $excludeId = null): bool
public function hasBusinessReferences(int $id, int $tenantId): bool
public function options(array $filters, EducationUserContext $context): array
```

`TeacherCourseRepository` methods:

```php
public function listByCourse(int $courseId, EducationUserContext $context): array
public function enabledTeacherIdsByCourse(int $courseId, int $tenantId, int $campusId): array
public function replaceEnabledTeachers(int $courseId, array $teacherIds, int $tenantId, int $campusId, ?int $operatorId): array
public function teacherCanTeachCourse(int $teacherId, int $courseId, int $tenantId, int $campusId): bool
```

`LessonPackageRepository` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function findScoped(int $id, EducationUserContext $context): ?EducationLessonPackage
public function findEnabledForEnrollment(int $id, int $tenantId, int $campusId, int $courseId): ?EducationLessonPackage
public function existsCode(int $tenantId, int $campusId, string $code, ?int $excludeId = null): bool
public function hasEnrollmentReferences(int $id, int $tenantId): bool
public function optionsByCourse(int $courseId, EducationUserContext $context): array
```

`StudentCourseAccountRepository` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function findScoped(int $id, EducationUserContext $context): ?EducationStudentCourseAccount
public function findByStudentCourseForUpdate(int $tenantId, int $campusId, int $studentId, int $courseId): ?EducationStudentCourseAccount
public function createForEnrollment(array $data): EducationStudentCourseAccount
public function updateBalances(int $accountId, array $delta, ?int $operatorId): EducationStudentCourseAccount
public function ledger(int $accountId, array $filters, EducationUserContext $context): array
```

`EnrollmentRepository` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function findScoped(int $id, EducationUserContext $context): ?EducationEnrollment
public function lockScoped(int $id, EducationUserContext $context): ?EducationEnrollment
public function createPending(array $data): EducationEnrollment
public function markConfirmed(int $id, int $accountId, ?int $operatorId): EducationEnrollment
public function cancel(int $id, string $reason, ?int $operatorId): EducationEnrollment
public function nextEnrollmentNo(int $tenantId, int $campusId): string
public function accountLedgerRows(int $accountId, array $filters, EducationUserContext $context): array
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
Reject duplicate course/package codes with code 409.
Reject disabled student, teacher, course, or package inputs with code 422.
Write F04 audit events after successful create/update/status/delete/teacher-save/enroll/cancel/account-status operations.
Use MineAdmin OperationMiddleware on write controllers.
Soft delete only when the row is not referenced by later V1 data.
```

`CourseService` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationCourse
public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationCourse
public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationCourse
public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
public function options(array $filters, EducationUserContext $context): array
```

`TeacherCourseService` methods:

```php
public function listTeachers(int $courseId, EducationUserContext $context): array
public function saveTeachers(int $courseId, array $teacherIds, EducationUserContext $context, ?int $operatorId): array
public function assertTeacherCanTeachCourse(int $teacherId, int $courseId, int $tenantId, int $campusId): void
```

Teacher authorization rules:

```text
Course must exist in the current tenant and campus scope.
Every teacher_id must point to an enabled V1-01 teacher in the same tenant and campus.
Duplicate teacher ids in the request are normalized before persistence.
saveTeachers replaces the enabled authorization set for that course.
Removed authorizations are soft deleted so audit history remains readable.
```

`LessonPackageService` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationLessonPackage
public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationLessonPackage
public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationLessonPackage
public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
public function optionsByCourse(int $courseId, EducationUserContext $context): array
```

Lesson package rules:

```text
course_id must point to an enabled course in the same tenant and campus.
lesson_units and bonus_units are rounded to two decimals.
lesson_units + bonus_units must be greater than 0.00.
total_units is computed by the service and cannot be trusted from request input.
sale_price must be less than or equal to list_price unless list_price is 0.00.
Package cannot be deleted when any enrollment references it.
```

`EnrollmentService` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function detail(int $id, EducationUserContext $context): EducationEnrollment
public function create(array $data, EducationUserContext $context, ?int $operatorId): array
public function confirm(int $id, EducationUserContext $context, ?int $operatorId): array
public function cancel(int $id, string $reason, EducationUserContext $context, ?int $operatorId): array
```

Enrollment creation transaction:

```text
1. Validate campus scope from EducationUserContext.
2. Validate student is enabled and belongs to the same tenant and campus.
3. Validate course is enabled and belongs to the same tenant and campus.
4. Validate lesson package is enabled, belongs to the same course, tenant, and campus.
5. Normalize deal_amount to package sale_price when request deal_amount is null.
6. Generate enrollment_no as ENR + yyyyMMddHHmmss + tenant short id + random 4 digits and retry up to 3 times on unique-key collision.
7. Create a pending enrollment with course/package/student snapshots and null account_id.
8. Dispatch audit event `education.academic.enrollment.created`.
9. Resolve finance_payment_enabled for the current tenant from F03.
10. In direct mode (flag off), call confirm in the same transaction.
11. In gated mode (flag on), leave the enrollment pending; V4 confirms it on payment success.
12. Commit and return enrollment plus account summary; account summary is null while the enrollment is pending.
```

Enrollment confirmation transaction:

```text
confirm(id, context, operatorId) is called inline by direct-mode create and by V4 on payment success.
1. Lock the enrollment row in current tenant and campus scope.
2. When status is already confirmed, return the existing account summary as an idempotent no-op.
3. When status is cancelled, reject with code 409.
4. Lock the student-course account row by tenant_id + student_id + course_id, creating it when absent.
5. Call StudentCourseAccountService.materializeEnrollment to create-or-increment units idempotently on enrollment_id.
6. Set enrollment status to confirmed, confirmed_at and materialized_at to now, and account_id to the materialized account.
7. Dispatch EducationEnrollmentConfirmed event and audit event `education.academic.enrollment.confirmed`.
8. Commit the transaction and return enrollment plus account summary.
```

Enrollment cancellation transaction:

```text
1. Validate enrollment exists in current tenant and campus scope.
2. Reject if status is already cancelled with code 409.
3. When status is pending, set status cancelled with no account change, dispatch audit event, and return; no units were ever materialized.
4. Lock the linked student course account.
5. Reject if account status is closed with code 409.
6. Reject if account available_units is less than enrollment total_units with code 409.
7. Set enrollment status to cancelled, cancelled_at to now, and cancel_reason from request.
8. Increase account refunded_units by enrollment total_units.
9. Decrease account available_units by enrollment total_units.
10. Keep purchased_units and bonus_units as historical purchase totals.
11. Dispatch audit event `education.academic.enrollment.cancelled`.
12. Commit the transaction and return enrollment plus account summary.
```

`StudentCourseAccountService` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function ledger(int $id, array $filters, EducationUserContext $context): array
public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationStudentCourseAccount
public function materializeEnrollment(int $enrollmentId, EducationUserContext $context, ?int $operatorId): EducationStudentCourseAccount
public function assertAccountCanConsume(int $accountId, string $units, EducationUserContext $context): void
public function consumeUnits(int $accountId, string $units, string $sourceNo, EducationUserContext $context, ?int $operatorId): EducationStudentCourseAccount
public function adjustUnits(int $accountId, string $units, string $reason, EducationUserContext $context, ?int $operatorId): EducationStudentCourseAccount
```

Account service rules:

```text
V1-02 implements page, ledger, status changes, and materializeEnrollment.
materializeEnrollment is the only V1-02 method that writes account unit columns; it locks or creates the account by tenant_id + student_id + course_id, increases purchased_units, bonus_units, and available_units from the enrollment snapshot, and extends expires_at when the package has validity_days. Its idempotency comes from the caller (confirm) gating on enrollment status pending under a row lock, so it is never invoked twice for the same enrollment.
materializeEnrollment is idempotent: a second call for the same enrollment_id returns the existing account without re-crediting units.
consumeUnits and adjustUnits are declared for V1-04 to use and must enforce the same balance invariant when implemented there.
changeStatus to closed requires available_units = 0.00.
changeStatus from closed to active or frozen is rejected with code 409.
ledger combines V1-02 materialization and cancellation rows; V1-04 will append consumption and adjustment rows after its tables exist.
```

### Request Classes

Page request base rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'tenant_id' => ['nullable', 'integer', 'min:1'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'keyword' => ['nullable', 'string', 'max:120'],
    'status' => ['nullable', 'string', 'max:30'],
]
```

`CourseSaveRequest` rules:

```php
[
    'campus_id' => ['required', 'integer', 'min:1'],
    'code' => ['required', 'string', 'max:64'],
    'name' => ['required', 'string', 'max:120'],
    'category' => ['nullable', 'string', 'max:80'],
    'subject' => ['nullable', 'string', 'max:80'],
    'unit_minutes' => ['required', 'integer', 'between:1,1440'],
    'cover_url' => ['nullable', 'string', 'max:255'],
    'description' => ['nullable', 'string', 'max:5000'],
    'status' => ['required', 'in:enabled,disabled'],
    'sort_order' => ['nullable', 'integer', 'between:-9999,9999'],
    'remark' => ['nullable', 'string', 'max:500'],
]
```

`CourseTeacherSaveRequest` rules:

```php
[
    'teacher_ids' => ['required', 'array'],
    'teacher_ids.*' => ['required', 'integer', 'min:1'],
]
```

`CourseStatusRequest` rules:

```php
[
    'status' => ['required', 'in:enabled,disabled'],
]
```

`LessonPackageSaveRequest` rules:

```php
[
    'campus_id' => ['required', 'integer', 'min:1'],
    'course_id' => ['required', 'integer', 'min:1'],
    'code' => ['required', 'string', 'max:64'],
    'name' => ['required', 'string', 'max:120'],
    'lesson_units' => ['required', 'numeric', 'min:0', 'max:999999.99'],
    'bonus_units' => ['required', 'numeric', 'min:0', 'max:999999.99'],
    'list_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
    'sale_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
    'validity_days' => ['nullable', 'integer', 'between:1,3650'],
    'status' => ['required', 'in:enabled,disabled'],
    'sort_order' => ['nullable', 'integer', 'between:-9999,9999'],
    'remark' => ['nullable', 'string', 'max:500'],
]
```

`LessonPackageStatusRequest` rules:

```php
[
    'status' => ['required', 'in:enabled,disabled'],
]
```

`EnrollmentPageRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'tenant_id' => ['nullable', 'integer', 'min:1'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'student_id' => ['nullable', 'integer', 'min:1'],
    'course_id' => ['nullable', 'integer', 'min:1'],
    'status' => ['nullable', 'in:pending,confirmed,cancelled'],
    'keyword' => ['nullable', 'string', 'max:120'],
    'enrolled_at_start' => ['nullable', 'date_format:Y-m-d'],
    'enrolled_at_end' => ['nullable', 'date_format:Y-m-d'],
]
```

`EnrollmentCreateRequest` rules:

```php
[
    'campus_id' => ['required', 'integer', 'min:1'],
    'student_id' => ['required', 'integer', 'min:1'],
    'course_id' => ['required', 'integer', 'min:1'],
    'lesson_package_id' => ['required', 'integer', 'min:1'],
    'deal_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
    'enrolled_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
    'remark' => ['nullable', 'string', 'max:500'],
]
```

`EnrollmentCancelRequest` rules:

```php
[
    'cancel_reason' => ['required', 'string', 'max:500'],
]
```

`StudentCourseAccountPageRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'tenant_id' => ['nullable', 'integer', 'min:1'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'student_id' => ['nullable', 'integer', 'min:1'],
    'course_id' => ['nullable', 'integer', 'min:1'],
    'status' => ['nullable', 'in:active,frozen,closed'],
    'keyword' => ['nullable', 'string', 'max:120'],
]
```

`StudentCourseAccountStatusRequest` rules:

```php
[
    'status' => ['required', 'in:active,frozen,closed'],
]
```

`AccountLedgerPageRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'source_type' => ['nullable', 'in:enrollment,enrollment_cancel,consumption,adjustment'],
]
```

Validation messages:

```text
page.required: page is required
pageSize.between: pageSize must be between 1 and 100
campus_id.required: campus_id is required
code.required: code is required
name.required: name is required
course_id.required: course_id is required
lesson_units.required: lesson_units is required
bonus_units.required: bonus_units is required
student_id.required: student_id is required
lesson_package_id.required: lesson_package_id is required
cancel_reason.required: cancel_reason is required
status.in: status has an invalid value
teacher_ids.required: teacher_ids is required
teacher_ids.*.integer: teacher id must be an integer
```

### Controllers

Controller base:

```text
Use `#[Controller(prefix: 'admin/education/academic/<resource>')]`.
Use MineAdmin auth middleware.
Use Permission attributes on every endpoint.
Use OperationMiddleware on create/update/status/delete/save-teachers/enroll/cancel/account-status endpoints.
Return MineAdmin Result shape through `$this->success(...)`.
Resolve current EducationUserContext through F02 context resolver.
```

`CourseController`:

```php
public function page(CoursePageRequest $request): Result
public function create(CourseSaveRequest $request): Result
public function update(int $id, CourseSaveRequest $request): Result
public function status(int $id, CourseStatusRequest $request): Result
public function delete(int $id): Result
public function teachers(int $id): Result
public function saveTeachers(int $id, CourseTeacherSaveRequest $request): Result
```

`LessonPackageController`:

```php
public function page(LessonPackagePageRequest $request): Result
public function create(LessonPackageSaveRequest $request): Result
public function update(int $id, LessonPackageSaveRequest $request): Result
public function status(int $id, LessonPackageStatusRequest $request): Result
public function delete(int $id): Result
```

`EnrollmentController`:

```php
public function page(EnrollmentPageRequest $request): Result
public function detail(int $id): Result
public function create(EnrollmentCreateRequest $request): Result
public function cancel(int $id, EnrollmentCancelRequest $request): Result
```

`StudentCourseAccountController`:

```php
public function page(StudentCourseAccountPageRequest $request): Result
public function ledger(int $id, AccountLedgerPageRequest $request): Result
public function status(int $id, StudentCourseAccountStatusRequest $request): Result
```

### Schemas

Schema fields:

```text
CourseSchema: id, tenant_id, campus_id, code, name, category, subject, unit_minutes, cover_url, description, status, sort_order, remark, teacher_count, package_count, created_at, updated_at
TeacherCourseSchema: id, tenant_id, campus_id, course_id, teacher_id, teacher_name, teacher_no, teacher_mobile, status, authorized_at, remark
LessonPackageSchema: id, tenant_id, campus_id, course_id, course_name, code, name, lesson_units, bonus_units, total_units, list_price, sale_price, validity_days, status, sort_order, remark, created_at, updated_at
EnrollmentSchema: id, tenant_id, campus_id, enrollment_no, student_id, student_name_snapshot, course_id, course_name_snapshot, lesson_package_id, package_name_snapshot, account_id, package_lesson_units, package_bonus_units, total_units, list_price, deal_amount, status, enrolled_at, confirmed_at, materialized_at, cancelled_at, cancel_reason, remark, created_at
StudentCourseAccountSchema: id, tenant_id, campus_id, student_id, student_name, student_no, course_id, course_name, purchased_units, bonus_units, consumed_units, adjusted_units, refunded_units, frozen_units, available_units, status, first_enrollment_id, last_enrollment_id, opened_at, expires_at, remark, updated_at
AccountLedgerSchema: source_type, source_id, source_no, occurred_at, direction, units, before_available_units, after_available_units, operator_id, operator_name, remark
```

## API Contract

### Endpoint Matrix

| API | Permission | Caller | Isolation | Audit |
| --- | --- | --- | --- | --- |
| `GET /admin/education/academic/courses/page` | `education:academic:course:page` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `POST /admin/education/academic/courses` | `education:academic:course:create` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.course.created` |
| `PUT /admin/education/academic/courses/{id}` | `education:academic:course:update` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.course.updated` |
| `PUT /admin/education/academic/courses/{id}/status` | `education:academic:course:status` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.course.status_changed` |
| `DELETE /admin/education/academic/courses/{id}` | `education:academic:course:delete` | tenant admin, principal | tenant and campus scope | `education.academic.course.deleted` |
| `GET /admin/education/academic/courses/{id}/teachers` | `education:academic:course-teacher:page` | tenant admin, principal, academic_staff | tenant and campus scope | none |
| `PUT /admin/education/academic/courses/{id}/teachers` | `education:academic:course-teacher:save` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.course_teacher.saved` |
| `GET /admin/education/academic/lesson-packages/page` | `education:academic:lesson-package:page` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `POST /admin/education/academic/lesson-packages` | `education:academic:lesson-package:create` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.lesson_package.created` |
| `PUT /admin/education/academic/lesson-packages/{id}` | `education:academic:lesson-package:update` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.lesson_package.updated` |
| `PUT /admin/education/academic/lesson-packages/{id}/status` | `education:academic:lesson-package:status` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.lesson_package.status_changed` |
| `DELETE /admin/education/academic/lesson-packages/{id}` | `education:academic:lesson-package:delete` | tenant admin, principal | tenant and campus scope | `education.academic.lesson_package.deleted` |
| `GET /admin/education/academic/enrollments/page` | `education:academic:enrollment:page` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `GET /admin/education/academic/enrollments/{id}` | `education:academic:enrollment:detail` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `POST /admin/education/academic/enrollments` | `education:academic:enrollment:create` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | `education.academic.enrollment.created` |
| `PUT /admin/education/academic/enrollments/{id}/cancel` | `education:academic:enrollment:cancel` | tenant admin, principal | tenant and campus scope | `education.academic.enrollment.cancelled` |
| `GET /admin/education/academic/student-course-accounts/page` | `education:academic:student-course-account:page` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `GET /admin/education/academic/student-course-accounts/{id}/ledger` | `education:academic:student-course-account:ledger` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `PUT /admin/education/academic/student-course-accounts/{id}/status` | `education:academic:student-course-account:status` | tenant admin, principal | tenant and campus scope | `education.academic.student_course_account.status_changed` |

Headers for tenant-scoped callers:

```text
Authorization: Bearer test-admin-token
X-Tenant-Id: 1001
X-Campus-Id: 2001
X-Request-Id: req-v1-course-account-001
```

### Resource Examples

Enrollment create request:

```json
{
  "campus_id": 2001,
  "student_id": 101,
  "course_id": 301,
  "lesson_package_id": 401,
  "deal_amount": 3000.00,
  "enrolled_at": "2026-06-10 10:00:00",
  "remark": "Spring package"
}
```

Enrollment create success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "enrollment": {
      "id": 501,
      "enrollment_no": "ENR2026061010000010014821",
      "student_id": 101,
      "course_id": 301,
      "lesson_package_id": 401,
      "account_id": 601,
      "total_units": "24.00",
      "deal_amount": "3000.00",
      "status": "confirmed"
    },
    "account": {
      "id": 601,
      "student_id": 101,
      "course_id": 301,
      "purchased_units": "20.00",
      "bonus_units": "4.00",
      "available_units": "24.00",
      "status": "active"
    }
  }
}
```

Enrollment validation failure:

```json
{
  "code": 422,
  "message": "lesson_package_id is required",
  "data": {
    "field": "lesson_package_id"
  }
}
```

Enrollment business failure:

```json
{
  "code": 422,
  "message": "lesson package is disabled",
  "data": {
    "lesson_package_id": 401
  }
}
```

Account increment success after a second enrollment:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "enrollment": {
      "id": 502,
      "enrollment_no": "ENR2026061110000010017309",
      "total_units": "12.00",
      "status": "confirmed"
    },
    "account": {
      "id": 601,
      "purchased_units": "30.00",
      "bonus_units": "6.00",
      "available_units": "36.00",
      "last_enrollment_id": 502
    }
  }
}
```

### Endpoint-Level Request/Response/Failure Catalog

Use this catalog as the controller test fixture set. Each API has a concrete request, success response, validation failure response, and business failure response.

```json
[
  {
    "api": "GET /admin/education/academic/courses/page",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "keyword": "Art", "status": "enabled"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 301, "tenant_id": 1001, "campus_id": 2001, "code": "ART-001", "name": "Art Basics", "unit_minutes": 60, "status": "enabled"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "page is required", "data": {"field": "page"}},
    "business_failure": {"code": 403, "message": "campus is outside current context", "data": {"campus_id": 9999}}
  },
  {
    "api": "POST /admin/education/academic/courses",
    "request": {"body": {"campus_id": 2001, "code": "ART-001", "name": "Art Basics", "category": "Fine Art", "subject": "Painting", "unit_minutes": 60, "cover_url": null, "description": "Basic art course", "status": "enabled", "sort_order": 10, "remark": "Main course"}},
    "success": {"code": 200, "message": "success", "data": {"id": 301, "tenant_id": 1001, "campus_id": 2001, "code": "ART-001", "name": "Art Basics", "status": "enabled"}},
    "validation_failure": {"code": 422, "message": "code is required", "data": {"field": "code"}},
    "business_failure": {"code": 409, "message": "course code already exists", "data": {"campus_id": 2001, "code": "ART-001"}}
  },
  {
    "api": "PUT /admin/education/academic/courses/{id}",
    "request": {"path": {"id": 301}, "body": {"campus_id": 2001, "code": "ART-002", "name": "Art Basics Updated", "category": "Fine Art", "subject": "Painting", "unit_minutes": 60, "cover_url": null, "description": "Updated basic art course", "status": "enabled", "sort_order": 20, "remark": "Updated course"}},
    "success": {"code": 200, "message": "success", "data": {"id": 301, "code": "ART-002", "name": "Art Basics Updated", "status": "enabled"}},
    "validation_failure": {"code": 422, "message": "name is required", "data": {"field": "name"}},
    "business_failure": {"code": 404, "message": "course not found in current context", "data": {"id": 301}}
  },
  {
    "api": "PUT /admin/education/academic/courses/{id}/status",
    "request": {"path": {"id": 301}, "body": {"status": "disabled"}},
    "success": {"code": 200, "message": "success", "data": {"id": 301, "status": "disabled"}},
    "validation_failure": {"code": 422, "message": "status has an invalid value", "data": {"field": "status"}},
    "business_failure": {"code": 403, "message": "course is outside current campus scope", "data": {"id": 301, "campus_id": 9999}}
  },
  {
    "api": "DELETE /admin/education/academic/courses/{id}",
    "request": {"path": {"id": 301}},
    "success": {"code": 200, "message": "success", "data": true},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 409, "message": "course is referenced by packages or enrollments", "data": {"id": 301}}
  },
  {
    "api": "GET /admin/education/academic/courses/{id}/teachers",
    "request": {"path": {"id": 301}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"teacher_id": 201, "teacher_no": "T20260610001", "teacher_name": "Teacher Wang", "status": "enabled"}]}},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 404, "message": "course not found in current context", "data": {"id": 301}}
  },
  {
    "api": "PUT /admin/education/academic/courses/{id}/teachers",
    "request": {"path": {"id": 301}, "body": {"teacher_ids": [201, 202]}},
    "success": {"code": 200, "message": "success", "data": {"course_id": 301, "teacher_ids": [201, 202]}},
    "validation_failure": {"code": 422, "message": "teacher_ids is required", "data": {"field": "teacher_ids"}},
    "business_failure": {"code": 403, "message": "teacher is outside current campus scope", "data": {"teacher_id": 202}}
  },
  {
    "api": "GET /admin/education/academic/lesson-packages/page",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "course_id": 301, "keyword": "24", "status": "enabled"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 401, "course_id": 301, "code": "ART-24", "name": "24 Lesson Package", "lesson_units": "20.00", "bonus_units": "4.00", "total_units": "24.00", "sale_price": "3000.00", "status": "enabled"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "page is required", "data": {"field": "page"}},
    "business_failure": {"code": 403, "message": "campus is outside current context", "data": {"campus_id": 9999}}
  },
  {
    "api": "POST /admin/education/academic/lesson-packages",
    "request": {"body": {"campus_id": 2001, "course_id": 301, "code": "ART-24", "name": "24 Lesson Package", "lesson_units": 20, "bonus_units": 4, "list_price": 3600, "sale_price": 3000, "validity_days": 365, "status": "enabled", "sort_order": 10, "remark": "Popular package"}},
    "success": {"code": 200, "message": "success", "data": {"id": 401, "course_id": 301, "code": "ART-24", "name": "24 Lesson Package", "total_units": "24.00", "status": "enabled"}},
    "validation_failure": {"code": 422, "message": "lesson_units is required", "data": {"field": "lesson_units"}},
    "business_failure": {"code": 409, "message": "lesson package code already exists", "data": {"campus_id": 2001, "code": "ART-24"}}
  },
  {
    "api": "PUT /admin/education/academic/lesson-packages/{id}",
    "request": {"path": {"id": 401}, "body": {"campus_id": 2001, "course_id": 301, "code": "ART-24A", "name": "24 Lesson Package Updated", "lesson_units": 20, "bonus_units": 4, "list_price": 3600, "sale_price": 3100, "validity_days": 365, "status": "enabled", "sort_order": 20, "remark": "Updated package"}},
    "success": {"code": 200, "message": "success", "data": {"id": 401, "code": "ART-24A", "name": "24 Lesson Package Updated", "total_units": "24.00", "status": "enabled"}},
    "validation_failure": {"code": 422, "message": "course_id is required", "data": {"field": "course_id"}},
    "business_failure": {"code": 404, "message": "lesson package not found in current context", "data": {"id": 401}}
  },
  {
    "api": "PUT /admin/education/academic/lesson-packages/{id}/status",
    "request": {"path": {"id": 401}, "body": {"status": "disabled"}},
    "success": {"code": 200, "message": "success", "data": {"id": 401, "status": "disabled"}},
    "validation_failure": {"code": 422, "message": "status has an invalid value", "data": {"field": "status"}},
    "business_failure": {"code": 403, "message": "lesson package is outside current campus scope", "data": {"id": 401, "campus_id": 9999}}
  },
  {
    "api": "DELETE /admin/education/academic/lesson-packages/{id}",
    "request": {"path": {"id": 401}},
    "success": {"code": 200, "message": "success", "data": true},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 409, "message": "lesson package is referenced by enrollments", "data": {"id": 401}}
  },
  {
    "api": "GET /admin/education/academic/enrollments/page",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "student_id": 101, "course_id": 301, "status": "confirmed"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 501, "enrollment_no": "ENR2026061010000010014821", "student_name_snapshot": "Student Zhang", "course_name_snapshot": "Art Basics", "total_units": "24.00", "status": "confirmed"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "pageSize must be between 1 and 100", "data": {"field": "pageSize"}},
    "business_failure": {"code": 403, "message": "campus is outside current context", "data": {"campus_id": 9999}}
  },
  {
    "api": "GET /admin/education/academic/enrollments/{id}",
    "request": {"path": {"id": 501}},
    "success": {"code": 200, "message": "success", "data": {"id": 501, "enrollment_no": "ENR2026061010000010014821", "account_id": 601, "total_units": "24.00", "deal_amount": "3000.00", "status": "confirmed"}},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 404, "message": "enrollment not found in current context", "data": {"id": 501}}
  },
  {
    "api": "POST /admin/education/academic/enrollments",
    "request": {"body": {"campus_id": 2001, "student_id": 101, "course_id": 301, "lesson_package_id": 401, "deal_amount": 3000, "enrolled_at": "2026-06-10 10:00:00", "remark": "Spring package"}},
    "success": {"code": 200, "message": "success", "data": {"enrollment": {"id": 501, "enrollment_no": "ENR2026061010000010014821", "account_id": 601, "total_units": "24.00", "status": "confirmed"}, "account": {"id": 601, "available_units": "24.00", "status": "active"}}},
    "validation_failure": {"code": 422, "message": "lesson_package_id is required", "data": {"field": "lesson_package_id"}},
    "business_failure": {"code": 422, "message": "lesson package is disabled", "data": {"lesson_package_id": 401}}
  },
  {
    "api": "PUT /admin/education/academic/enrollments/{id}/cancel",
    "request": {"path": {"id": 501}, "body": {"cancel_reason": "Wrong package selected"}},
    "success": {"code": 200, "message": "success", "data": {"enrollment": {"id": 501, "status": "cancelled", "cancel_reason": "Wrong package selected"}, "account": {"id": 601, "available_units": "0.00", "refunded_units": "24.00"}}},
    "validation_failure": {"code": 422, "message": "cancel_reason is required", "data": {"field": "cancel_reason"}},
    "business_failure": {"code": 409, "message": "enrollment units are already consumed or frozen", "data": {"id": 501, "available_units": "10.00", "required_units": "24.00"}}
  },
  {
    "api": "GET /admin/education/academic/student-course-accounts/page",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "student_id": 101, "course_id": 301, "status": "active"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 601, "student_id": 101, "student_name": "Student Zhang", "course_id": 301, "course_name": "Art Basics", "available_units": "24.00", "status": "active"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "page is required", "data": {"field": "page"}},
    "business_failure": {"code": 403, "message": "campus is outside current context", "data": {"campus_id": 9999}}
  },
  {
    "api": "GET /admin/education/academic/student-course-accounts/{id}/ledger",
    "request": {"path": {"id": 601}, "query": {"page": 1, "pageSize": 20, "source_type": "enrollment"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"source_type": "enrollment", "source_id": 501, "source_no": "ENR2026061010000010014821", "direction": "increase", "units": "24.00", "after_available_units": "24.00"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "source_type has an invalid value", "data": {"field": "source_type"}},
    "business_failure": {"code": 404, "message": "student course account not found in current context", "data": {"id": 601}}
  },
  {
    "api": "PUT /admin/education/academic/student-course-accounts/{id}/status",
    "request": {"path": {"id": 601}, "body": {"status": "frozen"}},
    "success": {"code": 200, "message": "success", "data": {"id": 601, "status": "frozen"}},
    "validation_failure": {"code": 422, "message": "status has an invalid value", "data": {"field": "status"}},
    "business_failure": {"code": 409, "message": "account can be closed only when available units are zero", "data": {"id": 601, "available_units": "24.00"}}
  }
]
```

## PC Admin Page Tasks

### API Client

Create:

```text
mineadmin-education-saas/admin-web/src/api/education/academic/courseAccount.ts
```

Types:

```ts
export type AcademicRecordStatus = 'enabled' | 'disabled'
export type EnrollmentStatus = 'pending' | 'confirmed' | 'cancelled'
export type StudentCourseAccountStatus = 'active' | 'frozen' | 'closed'
export type AccountLedgerSourceType = 'enrollment' | 'enrollment_cancel' | 'consumption' | 'adjustment'

export interface PageResult<T> {
  list: T[]
  total: number
}

export interface CourseRecord {
  id: number
  tenant_id: number
  campus_id: number
  code: string
  name: string
  category?: string | null
  subject?: string | null
  unit_minutes: number
  status: AcademicRecordStatus
  teacher_count?: number
  package_count?: number
}

export interface CourseSavePayload {
  campus_id: number
  code: string
  name: string
  category?: string | null
  subject?: string | null
  unit_minutes: number
  cover_url?: string | null
  description?: string | null
  status: AcademicRecordStatus
  sort_order?: number
  remark?: string | null
}

export interface CourseTeacherSavePayload {
  teacher_ids: number[]
}

export interface LessonPackageRecord {
  id: number
  tenant_id: number
  campus_id: number
  course_id: number
  course_name?: string
  code: string
  name: string
  lesson_units: string
  bonus_units: string
  total_units: string
  list_price: string
  sale_price: string
  validity_days?: number | null
  status: AcademicRecordStatus
}

export interface EnrollmentCreatePayload {
  campus_id: number
  student_id: number
  course_id: number
  lesson_package_id: number
  deal_amount?: number | null
  enrolled_at?: string | null
  remark?: string | null
}

export interface StudentCourseAccountRecord {
  id: number
  tenant_id: number
  campus_id: number
  student_id: number
  student_name: string
  course_id: number
  course_name: string
  purchased_units: string
  bonus_units: string
  consumed_units: string
  adjusted_units: string
  refunded_units: string
  frozen_units: string
  available_units: string
  status: StudentCourseAccountStatus
}
```

Methods:

```ts
export function pageCourses(params: Record<string, unknown>): Promise<PageResult<CourseRecord>>
export function createCourse(payload: CourseSavePayload): Promise<CourseRecord>
export function updateCourse(id: number, payload: CourseSavePayload): Promise<CourseRecord>
export function changeCourseStatus(id: number, status: AcademicRecordStatus): Promise<CourseRecord>
export function deleteCourse(id: number): Promise<boolean>
export function getCourseTeachers(id: number): Promise<{ list: Array<Record<string, unknown>> }>
export function saveCourseTeachers(id: number, payload: CourseTeacherSavePayload): Promise<{ course_id: number; teacher_ids: number[] }>
export function pageLessonPackages(params: Record<string, unknown>): Promise<PageResult<LessonPackageRecord>>
export function createLessonPackage(payload: Record<string, unknown>): Promise<LessonPackageRecord>
export function updateLessonPackage(id: number, payload: Record<string, unknown>): Promise<LessonPackageRecord>
export function changeLessonPackageStatus(id: number, status: AcademicRecordStatus): Promise<LessonPackageRecord>
export function deleteLessonPackage(id: number): Promise<boolean>
export function pageEnrollments(params: Record<string, unknown>): Promise<PageResult<Record<string, unknown>>>
export function getEnrollment(id: number): Promise<Record<string, unknown>>
export function createEnrollment(payload: EnrollmentCreatePayload): Promise<Record<string, unknown>>
export function cancelEnrollment(id: number, cancel_reason: string): Promise<Record<string, unknown>>
export function pageStudentCourseAccounts(params: Record<string, unknown>): Promise<PageResult<StudentCourseAccountRecord>>
export function getAccountLedger(id: number, params: Record<string, unknown>): Promise<PageResult<Record<string, unknown>>>
export function changeStudentCourseAccountStatus(id: number, status: StudentCourseAccountStatus): Promise<StudentCourseAccountRecord>
```

### Routes and Menus

Modify:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Route entries:

```text
Route: /education/academic/courses
Route name: EducationAcademicCourseList
Menu: 教务 SaaS / 教务基础 / 课程
Permission: education:academic:course:page
Page file: admin-web/src/views/education/academic/CourseList.vue

Route: /education/academic/lesson-packages
Route name: EducationAcademicLessonPackageList
Menu: 教务 SaaS / 教务基础 / 课包
Permission: education:academic:lesson-package:page
Page file: admin-web/src/views/education/academic/LessonPackageList.vue

Route: /education/academic/enrollments
Route name: EducationAcademicEnrollmentWorkbench
Menu: 教务 SaaS / 报名账户 / 报名办理
Permission: education:academic:enrollment:page
Page file: admin-web/src/views/education/academic/EnrollmentWorkbench.vue

Route: /education/academic/course-accounts
Route name: EducationAcademicAccountLedgerList
Menu: 教务 SaaS / 报名账户 / 课时账户
Permission: education:academic:student-course-account:page
Page file: admin-web/src/views/education/academic/AccountLedgerList.vue
```

### CourseList

File:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/CourseList.vue
```

Component files:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/components/CourseForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/CourseTeacherDrawer.vue
```

Search fields:

```text
campus_id, keyword, status
```

Table columns:

```text
code, name, category, subject, unit_minutes, status, teacher_count, package_count, sort_order, updated_at
```

Actions:

```text
create button: education:academic:course:create
edit button: education:academic:course:update
enable/disable button: education:academic:course:status
teacher authorization button: education:academic:course-teacher:page
save teacher authorization button: education:academic:course-teacher:save
delete button: education:academic:course:delete
```

States:

```text
loading: table skeleton while pageCourses is pending
empty: show MineAdmin empty state when total is 0
error: show API message and keep previous filters
permission: hide action buttons without matching permission code
submit success: close form drawer and reload first page
validation failure: keep drawer open and show field message
business failure: keep drawer open and show API message
teacher drawer success: close drawer, reload row teacher_count
```

### LessonPackageList

File:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/LessonPackageList.vue
```

Component file:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/components/LessonPackageForm.vue
```

Search fields:

```text
campus_id, course_id, keyword, status
```

Table columns:

```text
course_name, code, name, lesson_units, bonus_units, total_units, list_price, sale_price, validity_days, status, updated_at
```

Form fields:

```text
campus_id selector
course_id selector filtered by campus
code input
name input
lesson_units decimal input with two decimals
bonus_units decimal input with two decimals
list_price money input
sale_price money input
validity_days number input
status segmented control
sort_order number input
remark textarea
computed display total_units = lesson_units + bonus_units
```

Actions and states:

```text
create button: education:academic:lesson-package:create
edit button: education:academic:lesson-package:update
enable/disable button: education:academic:lesson-package:status
delete button: education:academic:lesson-package:delete
loading/empty/error/permission states match CourseList
validation failure keeps form open
business failure for duplicate code or disabled course keeps form open
```

### EnrollmentWorkbench

File:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/EnrollmentWorkbench.vue
```

Component file:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/components/EnrollmentCreateDrawer.vue
```

Search fields:

```text
campus_id, student_id, course_id, status, enrolled_at_start, enrolled_at_end, keyword
```

Table columns:

```text
enrollment_no, student_name_snapshot, course_name_snapshot, package_name_snapshot, total_units, deal_amount, status, enrolled_at, created_at
```

Create drawer fields:

```text
campus_id selector
student_id remote selector filtered by campus and enabled status
course_id selector filtered by campus and enabled status
lesson_package_id selector filtered by course and enabled status
package summary: lesson_units, bonus_units, total_units, sale_price, validity_days
deal_amount input defaulting to package sale_price
enrolled_at datetime picker
remark textarea
```

Actions and states:

```text
create enrollment button: education:academic:enrollment:create
detail button: education:academic:enrollment:detail
cancel button: education:academic:enrollment:cancel
loading: table skeleton while pageEnrollments is pending
empty: show empty state and visible create button when permitted
error: show API message and keep filters
create success: show enrollment_no, account_id, added units, new available_units, then reload enrollment and account lists
validation failure: keep drawer open and mark field
business failure: keep drawer open and show disabled student/course/package or campus scope message
cancel success: reload row and account balance
cancel business failure: keep confirmation dialog open and show account available units message
```

### AccountLedgerList

File:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/AccountLedgerList.vue
```

Component file:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/components/AccountLedgerDrawer.vue
```

Search fields:

```text
campus_id, student_id, course_id, status, keyword
```

Table columns:

```text
student_name, student_no, course_name, purchased_units, bonus_units, consumed_units, adjusted_units, refunded_units, frozen_units, available_units, status, expires_at, updated_at
```

Ledger drawer columns:

```text
source_type, source_no, occurred_at, direction, units, before_available_units, after_available_units, operator_name, remark
```

Actions and states:

```text
ledger button: education:academic:student-course-account:ledger
freeze/unfreeze/close button: education:academic:student-course-account:status
loading/empty/error/permission states match CourseList
ledger loading state is scoped to the drawer
ledger empty state explains no enrollment or cancellation ledger exists yet
status business failure keeps row state unchanged and shows API message
```

## Teacher / Guardian Mobile Page Tasks

This module has no teacher or guardian page because V1-02 is PC-side tenant administration for course products, packages, enrollment, and account initialization.

Mobile dependency notes:

```text
V1-06 teacher mobile does not directly read V1-02 pages.
V1-07 guardian mobile will read student course accounts and ledger through guardian-scoped APIs created in V1-07.
No mobile-uniapp API client or pages are created in V1-02.
Run the mobile H5 build to prove V1-02 did not break the existing mobile shell.
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
| `CourseAccountMigrationTest.php` | `test_course_account_tables_exist` | all five V1-02 tables exist |
| `CourseAccountMigrationTest.php` | `test_course_columns_and_indexes_exist` | `edu_courses` has documented columns and `uk_edu_courses_tenant_campus_code` |
| `CourseAccountMigrationTest.php` | `test_package_decimal_columns_exist` | package decimal columns are `decimal(10,2)` and money columns are `decimal(12,2)` |
| `CourseAccountMigrationTest.php` | `test_account_balance_columns_exist` | account balance columns and unique student-course index exist |
| `CourseAccountMigrationTest.php` | `test_enrollment_snapshot_columns_exist` | enrollment snapshot and status columns exist |
| `CourseAccountMigrationTest.php` | `test_rollback_drops_tables_in_dependency_order` | rollback drops enrollments before accounts and courses last |

### Repository Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `CourseRepositoryTest.php` | `test_page_filters_by_tenant_campus_keyword_status` | cross-tenant and cross-campus courses are absent |
| `CourseRepositoryTest.php` | `test_exists_code_ignores_deleted_rows_and_current_id` | duplicate active code returns true and current id update returns false |
| `TeacherCourseRepositoryTest.php` | `test_replace_enabled_teachers_soft_deletes_removed_authorizations` | removed teacher authorization has deleted_at and new teacher is enabled |
| `LessonPackageRepositoryTest.php` | `test_page_filters_by_course_and_status` | only matching course packages are returned |
| `EnrollmentRepositoryTest.php` | `test_next_enrollment_no_is_unique_per_call` | two generated numbers differ and start with `ENR` |
| `StudentCourseAccountRepositoryTest.php` | `test_find_by_student_course_for_update_returns_account` | account row is returned for tenant + student + course |
| `StudentCourseAccountRepositoryTest.php` | `test_ledger_returns_enrollment_and_cancel_rows` | ledger includes `enrollment` increase and `enrollment_cancel` decrease rows |

### Service Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `CourseServiceTest.php` | `test_create_rejects_duplicate_course_code` | service throws code 409 with code |
| `CourseServiceTest.php` | `test_delete_rejects_course_with_package_reference` | service throws code 409 |
| `TeacherCourseServiceTest.php` | `test_save_teachers_rejects_disabled_teacher` | service throws code 422 with teacher_id |
| `TeacherCourseServiceTest.php` | `test_save_teachers_rejects_teacher_outside_campus_scope` | service throws code 403 |
| `LessonPackageServiceTest.php` | `test_create_computes_total_units` | total_units equals lesson_units + bonus_units |
| `LessonPackageServiceTest.php` | `test_create_rejects_zero_total_units` | service throws code 422 |
| `LessonPackageServiceTest.php` | `test_create_rejects_disabled_course` | service throws code 422 |
| `EnrollmentServiceTest.php` | `test_create_enrollment_creates_new_account_transactionally` | with finance gate off, enrollment is confirmed, account available_units equals package total_units, audit event exists |
| `EnrollmentServiceTest.php` | `test_create_enrollment_increments_existing_account` | existing account purchased_units, bonus_units, available_units, and last_enrollment_id are incremented |
| `EnrollmentServiceTest.php` | `test_create_with_finance_gate_on_leaves_enrollment_pending` | enrollment status is pending, account_id is null, and no account unit columns changed |
| `EnrollmentServiceTest.php` | `test_confirm_materializes_account_once` | confirm sets status confirmed and available_units equals total_units |
| `EnrollmentServiceTest.php` | `test_confirm_is_idempotent_on_repeated_calls` | second confirm returns same account without re-crediting units |
| `EnrollmentServiceTest.php` | `test_cancel_pending_enrollment_changes_no_account_units` | pending enrollment becomes cancelled and account unit columns are unchanged |
| `EnrollmentServiceTest.php` | `test_create_rejects_disabled_package` | service throws code 422 |
| `EnrollmentServiceTest.php` | `test_create_rolls_back_account_when_enrollment_insert_fails` | no account balance is changed after simulated failure |
| `EnrollmentServiceTest.php` | `test_cancel_enrollment_reverses_available_units` | enrollment status is cancelled and account refunded_units increases |
| `EnrollmentServiceTest.php` | `test_cancel_rejects_when_units_already_consumed_or_frozen` | service throws code 409 and account balances stay unchanged |
| `StudentCourseAccountServiceTest.php` | `test_change_status_to_closed_requires_zero_available_units` | account with available units returns code 409 |
| `StudentCourseAccountServiceTest.php` | `test_account_balance_invariant_is_preserved_after_enrollment_and_cancel` | available_units matches documented formula |

### Controller/API Feature Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `CourseAccountAdminApiTest.php` | `test_course_crud_returns_mineadmin_shape` | create/update/status/page responses use `{code,message,data}` |
| `CourseAccountAdminApiTest.php` | `test_course_teacher_save_returns_teacher_ids` | saved teacher ids are returned in response data |
| `CourseAccountAdminApiTest.php` | `test_lesson_package_crud_returns_total_units` | response contains computed total_units |
| `CourseAccountAdminApiTest.php` | `test_enrollment_create_returns_enrollment_and_account_summary` | response contains enrollment and account blocks |
| `CourseAccountAdminApiTest.php` | `test_enrollment_cancel_returns_cancelled_status` | response enrollment status is cancelled |
| `CourseAccountAdminApiTest.php` | `test_account_page_and_ledger_return_expected_rows` | page total is 1 and ledger contains enrollment row |
| `CourseAccountAdminApiTest.php` | `test_validation_failures_match_catalog` | missing required fields return documented 422 messages |
| `CourseAccountAdminApiTest.php` | `test_business_failures_match_catalog` | duplicate code, disabled package, and insufficient available units return documented codes |

### Permission, Isolation, and Audit Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `CourseAccountPermissionTest.php` | `test_missing_course_create_permission_returns_403` | API returns code 403 |
| `CourseAccountPermissionTest.php` | `test_front_desk_cannot_cancel_enrollment` | cancel API returns code 403 |
| `CourseAccountIsolationTest.php` | `test_tenant_user_cannot_read_other_tenant_course` | page and detail responses exclude other tenant rows |
| `CourseAccountIsolationTest.php` | `test_campus_scoped_user_cannot_enroll_other_campus_student` | enrollment API returns code 403 |
| `CourseAccountIsolationTest.php` | `test_account_page_respects_campus_scope` | cross-campus account is absent |
| `CourseAccountAuditTest.php` | `test_course_write_creates_audit_log` | audit action `education.academic.course.created` exists |
| `CourseAccountAuditTest.php` | `test_teacher_authorization_creates_audit_log` | audit action `education.academic.course_teacher.saved` exists |
| `CourseAccountAuditTest.php` | `test_enrollment_create_and_cancel_create_audit_logs` | both enrollment audit actions exist with enrollment id |
| `CourseAccountAuditTest.php` | `test_account_status_change_creates_audit_log` | audit action `education.academic.student_course_account.status_changed` exists |

### PC Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `CourseList.spec.ts` | `renders_course_table_and_filters` | table shows code/name/status after pageCourses resolves |
| `CourseList.spec.ts` | `permission_buttons_are_hidden_without_permission` | create/edit/delete buttons are hidden |
| `CourseList.spec.ts` | `duplicate_course_error_keeps_form_open` | form remains visible after 409 |
| `CourseTeacherDrawer.spec.ts` | `loads_and_saves_teacher_authorizations` | drawer sends selected teacher_ids and reloads row |
| `LessonPackageList.spec.ts` | `computes_total_units_in_form` | lesson_units 20 and bonus_units 4 displays 24.00 |
| `LessonPackageList.spec.ts` | `validation_failure_keeps_package_form_open` | form remains visible after 422 |
| `EnrollmentWorkbench.spec.ts` | `create_enrollment_shows_transaction_summary` | success summary displays enrollment_no and available_units |
| `EnrollmentWorkbench.spec.ts` | `cancel_failure_keeps_confirm_dialog_open` | 409 message is displayed and row status remains confirmed |
| `AccountLedgerList.spec.ts` | `renders_account_balances` | available_units and status are displayed |
| `AccountLedgerList.spec.ts` | `ledger_drawer_renders_enrollment_rows` | ledger drawer displays source_no and units |
| `AccountLedgerList.spec.ts` | `close_account_failure_keeps_status_unchanged` | 409 message is displayed and status remains active |

### Mobile Regression Test

| Verification | Assert |
| --- | --- |
| `pnpm build:h5` in `mobile-uniapp` | existing teacher/guardian shell still builds because V1-02 adds no mobile files |

## Execution Commands

### Backend Migration Gate

Run:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter CourseAccountMigrationTest
php bin/hyperf.php migrate:rollback --step=1
php bin/hyperf.php migrate
```

Expected:

```text
V1-02 migration runs successfully.
CourseAccountMigrationTest passes.
Rollback drops V1-02 tables in dependency-safe order.
Re-running migration succeeds.
```

### Backend Unit Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter CourseRepositoryTest
composer test -- --filter TeacherCourseRepositoryTest
composer test -- --filter LessonPackageRepositoryTest
composer test -- --filter EnrollmentRepositoryTest
composer test -- --filter StudentCourseAccountRepositoryTest
composer test -- --filter CourseServiceTest
composer test -- --filter TeacherCourseServiceTest
composer test -- --filter LessonPackageServiceTest
composer test -- --filter EnrollmentServiceTest
composer test -- --filter StudentCourseAccountServiceTest
```

Expected:

```text
V1-02 repository and service tests pass.
Enrollment create and cancel transaction tests pass.
Account balance invariant tests pass.
```

### Backend Feature Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter CourseAccountAdminApiTest
composer test -- --filter CourseAccountPermissionTest
composer test -- --filter CourseAccountIsolationTest
composer test -- --filter CourseAccountAuditTest
```

Expected:

```text
V1-02 admin API, permission, isolation, and audit tests pass.
Every API returns the documented MineAdmin result shape.
```

### PC Gate

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- CourseList
pnpm test -- CourseTeacherDrawer
pnpm test -- LessonPackageList
pnpm test -- EnrollmentWorkbench
pnpm test -- AccountLedgerList
pnpm build
```

Expected:

```text
PC lint passes.
V1-02 page tests pass.
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

### V1-02 Final Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter CourseAccount
composer cs-fix -- --dry-run
composer analyse
cd ../admin-web
pnpm lint
pnpm test -- CourseList
pnpm test -- CourseTeacherDrawer
pnpm test -- LessonPackageList
pnpm test -- EnrollmentWorkbench
pnpm test -- AccountLedgerList
pnpm build
cd ../mobile-uniapp
pnpm build:h5
```

Expected:

```text
All V1-02 backend tests pass.
Backend code style dry run passes.
Backend static analysis passes.
PC lint, page tests, and build pass.
Mobile H5 build passes.
```

## Acceptance Gate

V1-02 is accepted only when all conditions are true:

```text
- `edu_courses`, `edu_teacher_courses`, `edu_lesson_packages`, `edu_student_course_accounts`, and `edu_enrollments` exist with documented columns and indexes.
- Migration rollback drops all V1-02 tables in dependency-safe order.
- Course, teacher-course, lesson package, enrollment, and student course account models cast decimal, date, status, and id fields correctly.
- Repositories apply tenant and campus scope filters consistently.
- Services reject duplicate course/package codes and invalid disabled student/course/package/teacher inputs with documented codes.
- Teacher authorization validates enabled V1-01 teachers in the same tenant and campus.
- Lesson package service computes total_units and rejects zero-unit packages.
- Enrollment creation persists a pending enrollment; confirmation materializes or increments exactly one student course account inside one transaction. In direct mode (finance gate off) creation confirms inline; in gated mode confirmation is driven by V4 payment success.
- Enrollment creation snapshots student, course, package, units, and prices.
- Enrollment cancellation reverses available units only when enough available units exist.
- Account balance invariant is preserved after enrollment create and cancellation.
- Admin APIs return MineAdmin result shape and documented validation/business failures.
- Permission tests prove missing MineAdmin permission codes return 403.
- Isolation tests prove tenant and campus scoped users cannot read or mutate unauthorized rows.
- F04 audit logs are created for all V1-02 write operations.
- PC API client, routes, list pages, forms, teacher authorization drawer, enrollment drawer, ledger drawer, permission buttons, loading, empty, error, success, and submit states pass tests.
- V1-02 adds no mobile pages and mobile H5 build still passes.
```

## Task Breakdown

### Task 1: Create Migration, Enums, and Models

**Files:**

- Create: `mineadmin-education-saas/backend/databases/migrations/2026_06_10_010200_create_v1_course_account_tables.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/EnrollmentStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/StudentCourseAccountStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/AccountLedgerSourceType.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationCourse.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationTeacherCourse.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonPackage.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationEnrollment.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudentCourseAccount.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/CourseAccountMigrationTest.php`

- [x] **Step 1: Create migration**

Use the full table, column, index, foreign-key policy, and rollback order from `Database Migration Design`.

- [x] **Step 2: Create enums**

Create `EnrollmentStatus`, `StudentCourseAccountStatus`, and `AccountLedgerSourceType` exactly as defined in `MineAdmin Backend Module Design`.

- [x] **Step 3: Create models**

Create all five models with table names, fillable fields, casts, relationships, timestamps, and soft delete behavior defined in `MineAdmin Backend Module Design`.

- [x] **Step 4: Write migration test**

Create `CourseAccountMigrationTest` with cases listed in `Test Plan`.

- [x] **Step 5: Run migration gate**

Run commands from `Backend Migration Gate`.

Expected:

```text
Migration, rollback, re-migration, and migration tests pass.
```

### Task 2: Create Repositories and Services

**Files:**

- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/CourseRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/TeacherCourseRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonPackageRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/EnrollmentRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/StudentCourseAccountRepository.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/CourseService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/TeacherCourseService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/LessonPackageService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/EnrollmentService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/StudentCourseAccountService.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/CourseRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherCourseRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/LessonPackageRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/EnrollmentRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/StudentCourseAccountRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/CourseServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherCourseServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/LessonPackageServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/EnrollmentServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/StudentCourseAccountServiceTest.php`

- [x] **Step 1: Create repositories**

Implement repository methods, filters, tenant scope, campus scope, duplicate checks, lock queries, and ledger query rules from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create services**

Implement service methods, decimal normalization, teacher authorization validation, package total unit computation, enrollment create/cancel transactions, account balance invariant, audit dispatch, and delete guards from `MineAdmin Backend Module Design`.

- [x] **Step 3: Write repository tests**

Create repository tests listed in `Test Plan`.

- [x] **Step 4: Write service tests**

Create service tests listed in `Test Plan`.

- [x] **Step 5: Run backend unit gate**

Run commands from `Backend Unit Gate`.

Expected:

```text
V1-02 repository, service, transaction, and balance invariant tests pass.
```

### Task 3: Create Requests, Schemas, and Controllers

**Files:**

- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/CoursePageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/CourseSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/CourseStatusRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/CourseTeacherSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LessonPackagePageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LessonPackageSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LessonPackageStatusRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/EnrollmentPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/EnrollmentCreateRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/EnrollmentCancelRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/StudentCourseAccountPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/StudentCourseAccountStatusRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/AccountLedgerPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/CourseSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/TeacherCourseSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/LessonPackageSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/EnrollmentSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/StudentCourseAccountSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/AccountLedgerSchema.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/CourseController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/LessonPackageController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/EnrollmentController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/StudentCourseAccountController.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/CourseAccountAdminApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/CourseAccountPermissionTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/CourseAccountIsolationTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/CourseAccountAuditTest.php`

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
V1-02 admin API, permission, isolation, and audit tests pass.
```

### Task 4: Create PC API Client, Routes, Pages, Forms, and Drawers

**Files:**

- Create: `mineadmin-education-saas/admin-web/src/api/education/academic/courseAccount.ts`
- Modify: `mineadmin-education-saas/admin-web/src/router/modules/education.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/CourseList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/LessonPackageList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/EnrollmentWorkbench.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/AccountLedgerList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/CourseForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/CourseTeacherDrawer.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/LessonPackageForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/EnrollmentCreateDrawer.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/AccountLedgerDrawer.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/CourseList.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/CourseTeacherDrawer.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/LessonPackageList.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/EnrollmentWorkbench.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/AccountLedgerList.spec.ts`

- [x] **Step 1: Create typed API client**

Implement all types and methods listed in `PC Admin Page Tasks`.

- [x] **Step 2: Add routes and menus**

Add V1-02 route entries and auth meta to `admin-web/src/router/modules/education.ts`.

- [x] **Step 3: Create course page and teacher authorization drawer**

Implement CourseList, CourseForm, and CourseTeacherDrawer using page tasks from `PC Admin Page Tasks`.

- [x] **Step 4: Create lesson package page**

Implement LessonPackageList and LessonPackageForm with computed total_units display and package validation states.

- [x] **Step 5: Create enrollment workbench**

Implement EnrollmentWorkbench and EnrollmentCreateDrawer with package summary, transaction success summary, cancel flow, and account refresh behavior.

- [x] **Step 6: Create account ledger page**

Implement AccountLedgerList and AccountLedgerDrawer with balance columns, ledger rows, and account status actions.

- [x] **Step 7: Write PC tests**

Create PC tests listed in `Test Plan`.

- [x] **Step 8: Run PC gate**

Run commands from `PC Gate`.

Expected:

```text
PC lint, V1-02 page tests, and production build pass.
```

### Task 5: Run V1-02 Final Gate

**Files:**

- Verify: all backend, PC, and mobile paths listed in `File Structure`.

- [x] **Step 1: Run backend final gate**

Run backend commands from `V1-02 Final Gate`.

- [x] **Step 2: Run PC final gate**

Run PC commands from `V1-02 Final Gate`.

- [x] **Step 3: Run mobile regression gate**

Run mobile command from `V1-02 Final Gate`.

- [x] **Step 4: Commit V1-02**

Run:

```bash
cd mineadmin-education-saas
git add backend admin-web mobile-uniapp
git commit -m "feat: add v1 course package accounts"
```

Expected:

```text
Commit succeeds with V1-02 backend, PC, tests, and verification changes.
```

## Self-Review

- Spec coverage: V1-02 covers courses, teacher-course authorization, lesson packages, enrollment, and student course accounts from the V1 purchase-to-account scope.
- MineAdmin fit: The plan uses MineAdmin 3.x `app/Http/Admin`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, `databases/migrations`, permission attributes, OperationMiddleware for writes, and MineAdmin result shape.
- Tenant isolation: All records are tenant-scoped and campus-scoped; services validate campus scope before every write.
- V1 dependency fit: V1-02 consumes V1-01 students and teachers; V1-03 can use courses and teacher authorizations; V1-04 can use student course accounts for consumption; V1-07 can expose guardian account views through guardian-scoped APIs.
- Transaction fit: Enrollment create and cancel are explicit database transactions and preserve the account balance invariant.
- Finance boundary: V1-02 records deal amount snapshots but does not implement payment, reconciliation, invoice, or refund posting.
- PC fit: Pages include typed API client, route/menu entries, list/search/form/action behavior, permission buttons, teacher authorization drawer, enrollment drawer, account ledger drawer, loading, empty, error, success, and submit states.
- Mobile fit: V1-02 adds no visible mobile page and keeps mobile build verification.
- Readiness: This plan has exact paths, full migration design, backend layer tasks, API request/response/failure examples, PC tasks, mobile rationale, tests, commands, expected outputs, and acceptance gates, so V1-02 can be marked `ready`.
