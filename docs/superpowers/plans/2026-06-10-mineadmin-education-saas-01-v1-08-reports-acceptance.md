# MineAdmin Education SaaS V1-08 Reports Acceptance Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V1 academic dashboard/report APIs, PC report pages, and the final V1 acceptance gate proving the课消制 MVP works end to end.

**Architecture:** V1-08 is a read-heavy finalization module. It creates no tables; it reads V1-01 through V1-07 data through report repositories, exposes MineAdmin admin report APIs under `backend/app/Http/Admin`, renders PC report pages in `admin-web`, and adds cross-module acceptance tests for enrollment, scheduling, attendance, consumption, leave, guardian, teacher, notice, and ledger consistency. Report numbers are computed from immutable ledger rows and current account balances, not from frontend arithmetic.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, MineAdmin-Vue, Vue3, TypeScript, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** incomplete / not implemented. `ready` means this plan is detailed enough to start coding.

---

## Scope Check

Included:

- Create read-only academic report repositories, services, request classes, schemas, and admin controllers.
- Add academic dashboard metrics for students, classes, lessons, attendance, consumption, leave, account balances, and notices.
- Add attendance report grouped by date, campus, class, teacher, course, and attendance status.
- Add consumption report grouped by date, campus, course, class, teacher, source type, and ledger status.
- Add account balance report by campus, course, student, account status, remaining units, and expiry risk.
- Add leave report by campus, course, class, teacher, leave type, source, and status.
- Add V1 acceptance summary API that reports pass/fail status for required V1 implementation gates.
- Add PC API client, routes, dashboard page, attendance report page, consumption report page, account balance report page, leave report page, acceptance report page, permissions, loading/empty/error states, export-ready table columns, and tests.
- Add backend report API tests, report math tests, ledger consistency tests, tenant/campus isolation tests, and full V1 acceptance tests.
- Add final cross-module verification commands for backend, PC, and mobile suites.

Excluded:

- Finance revenue, payment collection, refunds, invoices, and payment reconciliation. V4 owns finance.
- Teacher payroll, commission, and payable class-hour reports. V5 owns payroll.
- Multi-campus group BI and cross-tenant dashboards. V6 owns group management.
- Family service learning reports, homework, and learning content analytics. Later V7/V12 modules own them.
- New mobile report pages. V1-06 and V1-07 mobile regression tests are part of acceptance, but V1-08 adds no mobile route.
- New database tables or materialized report snapshots. V1-08 computes reports directly from V1 tables.

Report invariants:

```text
All reports are tenant-scoped and campus-scoped.
Report APIs must use F02 EducationUserContext and CampusScopeService.
Platform roles cannot read tenant reports without an active tenant context.
Campus filters must be inside the current user's campus scope.
Dashboard lesson counts read edu_lessons.
Attendance counts read edu_lesson_attendances.
Consumption totals read edu_lesson_consumptions and ignore soft-deleted rows.
Account balance totals read edu_student_course_accounts.
Leave counts read edu_leave_requests.
Notice counts read edu_notices and edu_notice_receipts.
Frontend pages display server-provided metrics; they do not recalculate financial or课时 balance totals.
```

Ledger consistency rules:

```text
For every student course account:
available_units = purchased_units + bonus_units + adjusted_units - consumed_units - refunded_units - frozen_units.
Consumption report consumed_units total uses active decrease rows from edu_lesson_consumptions.
Rollback rows use direction increase and status active or reversed according to V1-04 rules.
Account consumed_units must equal active attendance consumption decrease units minus valid rollback increase units.
No report uses edu_lesson_attendances alone as the source of consumed units.
Attendance report counts attendance rows; consumption report sums consumption ledger rows.
```

Acceptance boundaries:

```text
V1-08 validates that V1-01 through V1-07 can work together; it does not replace their module tests.
V1 acceptance creates a representative tenant, campus, profiles, student, guardian, teacher, course, package, enrollment, class, lesson, attendance, consumption, leave, notice, and mobile access scenario.
Acceptance tests assert both happy path and isolation failures.
V1 remains not implemented until code exists and all acceptance commands pass.
```

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ReportDashboardRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ReportAttendanceRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ReportConsumptionRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ReportAccountBalanceRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ReportLeaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ReportAcceptanceRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/AcademicReportController.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/AcademicReportRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/AcademicAcceptanceRepository.php
mineadmin-education-saas/backend/app/Service/Education/Academic/AcademicReportService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/AcademicAcceptanceService.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/AcademicDashboardSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/AttendanceReportSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/ConsumptionReportSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/AccountBalanceReportSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/LeaveReportSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/V1AcceptanceSummarySchema.php
```

Read backend dependencies:

```text
mineadmin-education-saas/backend/app/Service/Education/Foundation/EducationUserContext.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusScopeService.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudent.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationGuardian.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudentGuardian.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationTeacher.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationCourse.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonPackage.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationEnrollment.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudentCourseAccount.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationClass.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationClassStudent.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLesson.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonStudent.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonAttendance.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonConsumption.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationAccountAdjustment.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLeaveRequest.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonChangeRecord.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationNotice.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationNoticeReceipt.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Unit/Education/Academic/AcademicReportRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/AcademicReportServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/AcademicAcceptanceServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/AcademicDashboardApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/AttendanceReportApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/ConsumptionReportApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/AccountBalanceReportApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/LeaveReportApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/V1AcceptanceSummaryApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/V1AcademicAcceptanceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/V1LedgerConsistencyTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/V1TenantCampusIsolationAcceptanceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/V1MobileAcceptanceTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/academic/report.ts
mineadmin-education-saas/admin-web/src/views/education/academic/AcademicDashboard.vue
mineadmin-education-saas/admin-web/src/views/education/academic/AttendanceReport.vue
mineadmin-education-saas/admin-web/src/views/education/academic/ConsumptionReport.vue
mineadmin-education-saas/admin-web/src/views/education/academic/AccountBalanceReport.vue
mineadmin-education-saas/admin-web/src/views/education/academic/LeaveReport.vue
mineadmin-education-saas/admin-web/src/views/education/academic/V1AcceptanceReport.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/ReportDateRangeFilter.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/ReportMetricCard.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/ReportStateBlock.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/ReportTableToolbar.vue
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/AcademicDashboard.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/AttendanceReport.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/ConsumptionReport.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/AccountBalanceReport.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/LeaveReport.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/V1AcceptanceReport.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/components/__tests__/ReportDateRangeFilter.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/components/__tests__/ReportStateBlock.spec.ts
```

Modify PC:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Verify mobile:

```text
mineadmin-education-saas/mobile-uniapp/src/api/academic/teacher.ts
mineadmin-education-saas/mobile-uniapp/src/api/academic/guardian.ts
mineadmin-education-saas/mobile-uniapp/pages/teacher/
mineadmin-education-saas/mobile-uniapp/pages/guardian/
```

## Database Migration Design

V1-08 creates no database migration.

Existing tables read:

```text
edu_tenants from F01
edu_campuses from F01
edu_user_profiles from F02
edu_user_campus_scopes from F02
edu_students from V1-01
edu_guardians from V1-01
edu_student_guardians from V1-01
edu_teachers from V1-01
edu_courses from V1-02
edu_teacher_courses from V1-02
edu_lesson_packages from V1-02
edu_enrollments from V1-02
edu_student_course_accounts from V1-02
edu_classes from V1-03
edu_class_students from V1-03
edu_lessons from V1-03
edu_lesson_students from V1-03
edu_lesson_attendances from V1-04
edu_lesson_consumptions from V1-04
edu_account_adjustments from V1-04
edu_leave_requests from V1-05
edu_lesson_change_records from V1-05
edu_notices from V1-07
edu_notice_receipts from V1-07
```

Required read indexes already planned:

```text
edu_students.idx_edu_students_tenant_campus_status
edu_enrollments.idx_edu_enrollments_tenant_student_status
edu_student_course_accounts.idx_edu_student_course_accounts_tenant_campus_status
edu_student_course_accounts.idx_edu_student_course_accounts_tenant_student
edu_lessons.idx_edu_lessons_tenant_campus_time
edu_lessons.idx_edu_lessons_tenant_teacher_time
edu_lesson_attendances.idx_edu_lesson_attendances_tenant_lesson
edu_lesson_attendances.idx_edu_lesson_attendances_tenant_student
edu_lesson_consumptions.idx_edu_lesson_consumptions_tenant_account_time
edu_lesson_consumptions.idx_edu_lesson_consumptions_tenant_student_course
edu_lesson_consumptions.idx_edu_lesson_consumptions_tenant_status
edu_account_adjustments.idx_edu_account_adjustments_tenant_account_time
edu_leave_requests.idx_edu_leave_requests_tenant_campus_status
edu_leave_requests.idx_edu_leave_requests_tenant_student_status
edu_notice_receipts.idx_edu_notice_receipts_tenant_guardian_status
edu_notice_receipts.idx_edu_notice_receipts_tenant_student_status
```

Write behavior:

```text
V1-08 report APIs perform no writes.
V1-08 acceptance tests may create fixture data through V1-01 to V1-07 services in the test database only.
No V1-08 production service inserts, updates, or deletes academic business records.
```

Rollback behavior:

```text
No database rollback exists for V1-08.
Reverting V1-08 means reverting report controllers, requests, services, repositories, schemas, PC report pages, API clients, and tests.
```

Verification command:

```bash
cd mineadmin-education-saas
find backend/databases/migrations -maxdepth 1 -name '*010800*' -o -name '*report*acceptance*'
```

Expected:

```text
No output.
```

## MineAdmin Backend Module Design

### HTTP Layer

Admin route prefix:

```text
/admin/education/academic/reports
```

Controller:

```text
AcademicReportController
- dashboard(ReportDashboardRequest $request): Result
- attendance(ReportAttendanceRequest $request): Result
- consumption(ReportConsumptionRequest $request): Result
- accountBalances(ReportAccountBalanceRequest $request): Result
- leaves(ReportLeaveRequest $request): Result
- acceptanceSummary(ReportAcceptanceRequest $request): Result
```

Permissions:

```text
education:academic:report:dashboard
education:academic:report:attendance
education:academic:report:consumption
education:academic:report:account-balance
education:academic:report:leave
education:academic:report:acceptance
```

Allowed roles:

```text
tenant admin, principal, academic_staff, front_desk can read dashboard, attendance, consumption, account balance, and leave reports inside tenant and campus scope.
finance can read account balance and consumption reports inside tenant and campus scope.
teacher, guardian, platform_operator, and platform_super_admin cannot use V1 tenant report APIs unless separate admin permissions and tenant context are explicitly granted.
```

Audit:

```text
Report reads do not write F04 audit logs.
Acceptance summary reads do not write audit logs.
```

### Request Classes

Shared date range behavior:

```text
If start_at/end_at are omitted on dashboard, use the current local day.
For table reports, start_at and end_at are required.
Maximum date range for table reports is 366 days.
campus_id is nullable for tenant-wide users and required for roles with a single campus scope when current_campus_id is absent.
```

`ReportDashboardRequest` rules:

```php
[
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'start_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['nullable', 'date_format:Y-m-d H:i:s', 'after:start_at'],
]
```

`ReportAttendanceRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'class_id' => ['nullable', 'integer', 'min:1'],
    'teacher_id' => ['nullable', 'integer', 'min:1'],
    'course_id' => ['nullable', 'integer', 'min:1'],
    'attendance_status' => ['nullable', 'in:present,late,absent,leave'],
    'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_at'],
    'group_by' => ['nullable', 'in:date,campus,class,teacher,course,status'],
]
```

`ReportConsumptionRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'course_id' => ['nullable', 'integer', 'min:1'],
    'class_id' => ['nullable', 'integer', 'min:1'],
    'student_id' => ['nullable', 'integer', 'min:1'],
    'account_id' => ['nullable', 'integer', 'min:1'],
    'source_type' => ['nullable', 'in:attendance,rollback'],
    'status' => ['nullable', 'in:active,reversed'],
    'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_at'],
    'group_by' => ['nullable', 'in:date,campus,course,class,teacher,source_type,status'],
]
```

`ReportAccountBalanceRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'course_id' => ['nullable', 'integer', 'min:1'],
    'student_id' => ['nullable', 'integer', 'min:1'],
    'status' => ['nullable', 'in:active,frozen,closed'],
    'balance_level' => ['nullable', 'in:zero,low,normal,expired,expiring_soon'],
]
```

`ReportLeaveRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'class_id' => ['nullable', 'integer', 'min:1'],
    'teacher_id' => ['nullable', 'integer', 'min:1'],
    'course_id' => ['nullable', 'integer', 'min:1'],
    'source' => ['nullable', 'in:staff,guardian,teacher'],
    'leave_type' => ['nullable', 'in:sick,personal,school,other'],
    'status' => ['nullable', 'in:pending,approved,rejected,cancelled,makeup_scheduled,closed'],
    'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_at'],
]
```

`ReportAcceptanceRequest` rules:

```php
[
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'include_detail' => ['nullable', 'boolean'],
]
```

Validation messages:

```text
page.required: page is required
pageSize.between: pageSize must be between 1 and 100
campus_id.integer: campus_id must be an integer
start_at.required: start_at is required
start_at.date_format: start_at must use Y-m-d H:i:s
end_at.required: end_at is required
end_at.after: end_at must be after start_at
attendance_status.in: attendance_status has an invalid value
source_type.in: source_type has an invalid value
balance_level.in: balance_level has an invalid value
status.in: status has an invalid value
group_by.in: group_by has an invalid value
```

### Repository

`AcademicReportRepository` methods:

```php
public function dashboard(array $params, EducationUserContext $context): array
public function attendanceRows(array $params, int $page, int $pageSize, EducationUserContext $context): array
public function attendanceSummary(array $params, EducationUserContext $context): array
public function consumptionRows(array $params, int $page, int $pageSize, EducationUserContext $context): array
public function consumptionSummary(array $params, EducationUserContext $context): array
public function accountBalanceRows(array $params, int $page, int $pageSize, EducationUserContext $context): array
public function accountBalanceSummary(array $params, EducationUserContext $context): array
public function leaveRows(array $params, int $page, int $pageSize, EducationUserContext $context): array
public function leaveSummary(array $params, EducationUserContext $context): array
```

Query requirements:

```text
Every query filters tenant_id.
Every campus-scoped query applies CampusScopeService before filtering.
Soft-deleted rows are excluded.
Attendance report joins lessons and lesson_students for class, course, teacher, and student snapshots.
Consumption report uses consumption.created_at for date range and joins lessons/accounts for display only.
Account balance report reads current account columns and computes balance_level in SQL or repository mapping.
Leave report uses requested_at date range and current leave status.
Summary methods return deterministic decimal strings with two decimal places.
Pagination methods return list and total.
```

`AcademicAcceptanceRepository` methods:

```php
public function moduleTableHealth(EducationUserContext $context): array
public function ledgerConsistency(EducationUserContext $context, ?int $campusId): array
public function acceptanceFixtureSummary(EducationUserContext $context, ?int $campusId): array
public function mobileReadinessSummary(EducationUserContext $context, ?int $campusId): array
public function pcReadinessSummary(EducationUserContext $context, ?int $campusId): array
```

### Services

`AcademicReportService` methods:

```php
public function dashboard(array $params, EducationUserContext $context): array
public function attendance(array $params, EducationUserContext $context): array
public function consumption(array $params, EducationUserContext $context): array
public function accountBalances(array $params, EducationUserContext $context): array
public function leaves(array $params, EducationUserContext $context): array
```

Service behavior:

```text
Normalize date range and campus filter.
Reject campus outside scope with code 403.
Reject date ranges over 366 days with code 422.
Return summary plus paginated rows for table reports.
Return empty list and zero summary when no rows match.
Never mutate report source data.
```

`AcademicAcceptanceService` methods:

```php
public function summary(array $params, EducationUserContext $context): array
public function assertLedgerConsistency(EducationUserContext $context, ?int $campusId): array
public function requiredGateCatalog(): array
```

Acceptance gate catalog:

```text
foundation_context_ready
profile_records_ready
course_account_ready
class_schedule_ready
attendance_consumption_ready
leave_change_ready
teacher_mobile_ready
guardian_mobile_ready
reports_ready
ledger_consistent
tenant_isolation_passed
campus_isolation_passed
pc_build_passed
mobile_build_passed
```

### Schemas

`AcademicDashboardSchema`:

```text
range.start_at, range.end_at, campus_id
metrics.student_count, active_student_count, guardian_count, teacher_count, active_class_count
metrics.scheduled_lesson_count, completed_lesson_count, cancelled_lesson_count
metrics.attendance_count, present_count, late_count, absent_count, leave_count
metrics.consumed_units, rollback_units, net_consumed_units
metrics.total_available_units, frozen_units, low_balance_account_count, expiring_account_count
metrics.pending_leave_count, approved_leave_count, makeup_scheduled_count
metrics.published_notice_count, unread_notice_receipt_count
trends.date, scheduled_lesson_count, completed_lesson_count, consumed_units
alerts.type, level, title, count
```

`AttendanceReportSchema`:

```text
summary.total_records, present_count, late_count, absent_count, leave_count, attendance_rate, leave_rate
rows.date, campus_id, campus_name, class_id, class_name, teacher_id, teacher_name, course_id, course_name, lesson_id, lesson_title, student_id, student_name, attendance_status, consume_policy, consumed_units, submitted_at
```

`ConsumptionReportSchema`:

```text
summary.decrease_units, rollback_units, net_units, active_row_count, reversed_row_count
rows.date, campus_id, campus_name, consumption_no, account_id, student_id, student_name, course_id, course_name, class_id, class_name, teacher_id, teacher_name, lesson_id, lesson_title, source_type, direction, units, before_available_units, after_available_units, status, created_at
```

`AccountBalanceReportSchema`:

```text
summary.account_count, active_count, frozen_count, closed_count, total_purchased_units, total_bonus_units, total_consumed_units, total_adjusted_units, total_refunded_units, total_frozen_units, total_available_units, low_balance_count, expiring_count
rows.account_id, campus_id, campus_name, student_id, student_name, student_no, course_id, course_name, purchased_units, bonus_units, consumed_units, adjusted_units, refunded_units, frozen_units, available_units, status, balance_level, opened_at, expires_at
```

`LeaveReportSchema`:

```text
summary.total_count, pending_count, approved_count, rejected_count, cancelled_count, makeup_scheduled_count, guardian_source_count, teacher_source_count, staff_source_count
rows.leave_id, leave_no, source, leave_type, status, campus_id, campus_name, class_id, class_name, teacher_id, teacher_name, course_id, course_name, student_id, student_name, lesson_id, lesson_title, requested_at, reviewed_at, makeup_required
```

`V1AcceptanceSummarySchema`:

```text
overall_status pass | fail
checked_at
gates.key, gates.name, gates.status, gates.message, gates.evidence
ledger.account_count, ledger.mismatch_count, ledger.mismatches
modules.foundation, modules.v1_01, modules.v1_02, modules.v1_03, modules.v1_04, modules.v1_05, modules.v1_06, modules.v1_07, modules.v1_08
next_action
```

## API Contract

Common headers:

```text
Authorization: Bearer mineadmin-admin-token
X-Tenant-Id: 1001
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
GET /admin/education/academic/reports/dashboard
GET /admin/education/academic/reports/attendance
GET /admin/education/academic/reports/consumption
GET /admin/education/academic/reports/account-balances
GET /admin/education/academic/reports/leaves
GET /admin/education/academic/reports/v1-acceptance-summary
```

Endpoint examples:

```json
[
  {
    "api": "GET /admin/education/academic/reports/dashboard",
    "request": {"query": {"campus_id": 2001, "start_at": "2026-06-01 00:00:00", "end_at": "2026-06-30 23:59:59"}},
    "success": {"code": 200, "message": "success", "data": {"range": {"start_at": "2026-06-01 00:00:00", "end_at": "2026-06-30 23:59:59"}, "metrics": {"active_student_count": 42, "scheduled_lesson_count": 80, "completed_lesson_count": 56, "attendance_count": 112, "net_consumed_units": "108.00", "total_available_units": "820.00", "pending_leave_count": 3}, "trends": [{"date": "2026-06-12", "completed_lesson_count": 6, "consumed_units": "12.00"}], "alerts": [{"type": "low_balance", "level": "warning", "title": "Low balance accounts", "count": 5}]}},
    "validation_failure": {"code": 422, "message": "end_at must be after start_at", "data": {"field": "end_at"}},
    "business_failure": {"code": 403, "message": "campus is outside current scope", "data": {"campus_id": 2999}}
  },
  {
    "api": "GET /admin/education/academic/reports/attendance",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "teacher_id": 201, "attendance_status": "present", "start_at": "2026-06-01 00:00:00", "end_at": "2026-06-30 23:59:59", "group_by": "teacher"}},
    "success": {"code": 200, "message": "success", "data": {"summary": {"total_records": 112, "present_count": 96, "late_count": 4, "absent_count": 8, "leave_count": 4, "attendance_rate": "89.29"}, "list": [{"date": "2026-06-12", "lesson_id": 9001, "lesson_title": "Art Basics Lesson 1", "teacher_name": "Teacher Wang", "student_name": "Student Zhang", "attendance_status": "present", "consumed_units": "1.00"}], "total": 112}},
    "validation_failure": {"code": 422, "message": "attendance_status has an invalid value", "data": {"field": "attendance_status"}},
    "business_failure": {"code": 403, "message": "permission education:academic:report:attendance is required", "data": {"permission": "education:academic:report:attendance"}}
  },
  {
    "api": "GET /admin/education/academic/reports/consumption",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "course_id": 301, "source_type": "attendance", "status": "active", "start_at": "2026-06-01 00:00:00", "end_at": "2026-06-30 23:59:59", "group_by": "course"}},
    "success": {"code": 200, "message": "success", "data": {"summary": {"decrease_units": "108.00", "rollback_units": "2.00", "net_units": "106.00", "active_row_count": 108, "reversed_row_count": 2}, "list": [{"date": "2026-06-12", "consumption_no": "CON2026061211050010019912", "student_name": "Student Zhang", "course_name": "Art Basics", "lesson_title": "Art Basics Lesson 1", "direction": "decrease", "units": "1.00", "after_available_units": "23.00", "status": "active"}], "total": 108}},
    "validation_failure": {"code": 422, "message": "source_type has an invalid value", "data": {"field": "source_type"}},
    "business_failure": {"code": 422, "message": "date range cannot exceed 366 days", "data": {"max_days": 366}}
  },
  {
    "api": "GET /admin/education/academic/reports/account-balances",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "course_id": 301, "status": "active", "balance_level": "low"}},
    "success": {"code": 200, "message": "success", "data": {"summary": {"account_count": 20, "active_count": 18, "total_purchased_units": "480.00", "total_consumed_units": "108.00", "total_available_units": "360.00", "low_balance_count": 5}, "list": [{"account_id": 601, "student_name": "Student Zhang", "course_name": "Art Basics", "available_units": "2.00", "status": "active", "balance_level": "low", "expires_at": "2026-12-31 23:59:59"}], "total": 5}},
    "validation_failure": {"code": 422, "message": "balance_level has an invalid value", "data": {"field": "balance_level"}},
    "business_failure": {"code": 403, "message": "permission education:academic:report:account-balance is required", "data": {"permission": "education:academic:report:account-balance"}}
  },
  {
    "api": "GET /admin/education/academic/reports/leaves",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "source": "guardian", "leave_type": "sick", "status": "pending", "start_at": "2026-06-01 00:00:00", "end_at": "2026-06-30 23:59:59"}},
    "success": {"code": 200, "message": "success", "data": {"summary": {"total_count": 9, "pending_count": 3, "approved_count": 4, "rejected_count": 1, "guardian_source_count": 6}, "list": [{"leave_id": 801, "leave_no": "LEA2026061210000010014821", "source": "guardian", "leave_type": "sick", "status": "pending", "student_name": "Student Zhang", "lesson_title": "Art Basics Lesson 1", "requested_at": "2026-06-12 09:00:00"}], "total": 9}},
    "validation_failure": {"code": 422, "message": "status has an invalid value", "data": {"field": "status"}},
    "business_failure": {"code": 403, "message": "campus is outside current scope", "data": {"campus_id": 2999}}
  },
  {
    "api": "GET /admin/education/academic/reports/v1-acceptance-summary",
    "request": {"query": {"campus_id": 2001, "include_detail": true}},
    "success": {"code": 200, "message": "success", "data": {"overall_status": "pass", "checked_at": "2026-06-30 18:00:00", "gates": [{"key": "ledger_consistent", "name": "Ledger consistency", "status": "pass", "message": "All account balances match ledger totals"}], "ledger": {"account_count": 20, "mismatch_count": 0, "mismatches": []}, "next_action": "V1 implementation is ready for product UAT"}},
    "validation_failure": {"code": 422, "message": "include_detail must be boolean", "data": {"field": "include_detail"}},
    "business_failure": {"code": 409, "message": "ledger consistency failed", "data": {"mismatch_count": 1, "account_ids": [601]}}
  }
]
```

## PC Admin Page Tasks

### API Client

Create `admin-web/src/api/education/academic/report.ts`.

Types:

```ts
export type ReportGroupBy = 'date' | 'campus' | 'class' | 'teacher' | 'course' | 'status' | 'source_type'
export type AttendanceStatus = 'present' | 'late' | 'absent' | 'leave'
export type ConsumptionSourceType = 'attendance' | 'rollback'
export type ConsumptionStatus = 'active' | 'reversed'
export type AccountBalanceLevel = 'zero' | 'low' | 'normal' | 'expired' | 'expiring_soon'
export type V1AcceptanceStatus = 'pass' | 'fail'
```

Functions:

```ts
export function getAcademicDashboard(params: DashboardReportParams): Promise<AcademicDashboardResult>
export function pageAttendanceReport(params: AttendanceReportParams): Promise<ReportPageResult<AttendanceReportRow, AttendanceReportSummary>>
export function pageConsumptionReport(params: ConsumptionReportParams): Promise<ReportPageResult<ConsumptionReportRow, ConsumptionReportSummary>>
export function pageAccountBalanceReport(params: AccountBalanceReportParams): Promise<ReportPageResult<AccountBalanceReportRow, AccountBalanceReportSummary>>
export function pageLeaveReport(params: LeaveReportParams): Promise<ReportPageResult<LeaveReportRow, LeaveReportSummary>>
export function getV1AcceptanceSummary(params: V1AcceptanceParams): Promise<V1AcceptanceSummary>
```

### Routes And Menus

Modify `admin-web/src/router/modules/education.ts`.

Routes:

```text
EducationAcademicDashboard -> /education/academic/dashboard -> AcademicDashboard.vue -> education:academic:report:dashboard
EducationAttendanceReport -> /education/academic/reports/attendance -> AttendanceReport.vue -> education:academic:report:attendance
EducationConsumptionReport -> /education/academic/reports/consumption -> ConsumptionReport.vue -> education:academic:report:consumption
EducationAccountBalanceReport -> /education/academic/reports/account-balances -> AccountBalanceReport.vue -> education:academic:report:account-balance
EducationLeaveReport -> /education/academic/reports/leaves -> LeaveReport.vue -> education:academic:report:leave
EducationV1AcceptanceReport -> /education/academic/reports/v1-acceptance -> V1AcceptanceReport.vue -> education:academic:report:acceptance
```

### Shared Components

`ReportDateRangeFilter.vue`:

```text
Props: modelValue, requireRange, maxDays.
Emits: update:modelValue, submit, reset.
Fields: campus selector, start_at, end_at, optional quick range buttons today, this_week, this_month.
Validation: start_at required when requireRange true, end_at must be after start_at, range must not exceed maxDays.
```

`ReportMetricCard.vue`:

```text
Props: title, value, unit, trend, status.
Used for dashboard and report summaries.
Stable dimensions so metric loading and long values do not shift layout.
```

`ReportStateBlock.vue`:

```text
Props: state loading | empty | error | forbidden, message, retryText.
Emits: retry.
Used by every report page.
```

`ReportTableToolbar.vue`:

```text
Props: title, total, loading.
Emits: refresh.
No export implementation is required in V1-08; toolbar reserves an export slot for later modules.
```

### AcademicDashboard

Tasks:

```text
Load getAcademicDashboard on mount with current month range.
Render metric cards for students, lessons, attendance, consumed units, available units, pending leave, and unread notices.
Render trend table by date.
Render alert list for low balance, expiring accounts, pending leave, and unread notices.
Campus and date range filters reload data.
Show loading, empty, error, forbidden, and retry states.
Metric cards use server-provided numbers only.
```

### AttendanceReport

Tasks:

```text
Render filters: campus_id, class_id, teacher_id, course_id, attendance_status, date range, group_by.
Render summary cards: total, present, late, absent, leave, attendance_rate.
Render table columns: date, class, teacher, course, lesson, student, attendance_status, consume_policy, consumed_units, submitted_at.
Provide drill link to V1-04 attendance detail route when permission exists.
Show loading, empty, error, forbidden, pagination, and retry states.
```

### ConsumptionReport

Tasks:

```text
Render filters: campus_id, course_id, class_id, student_id, account_id, source_type, status, date range, group_by.
Render summary cards: decrease_units, rollback_units, net_units, active_row_count, reversed_row_count.
Render table columns: date, consumption_no, student, course, class, teacher, lesson, source_type, direction, units, before_available_units, after_available_units, status.
Provide drill link to V1-04 consumption detail route when permission exists.
Show reversed rows with neutral status style.
```

### AccountBalanceReport

Tasks:

```text
Render filters: campus_id, course_id, student_id, status, balance_level.
Render summary cards: account_count, active_count, total_purchased_units, total_consumed_units, total_available_units, low_balance_count, expiring_count.
Render table columns: student, student_no, course, purchased_units, bonus_units, consumed_units, adjusted_units, refunded_units, frozen_units, available_units, status, balance_level, expires_at.
Provide drill link to V1-02 account ledger route when permission exists.
Show zero, low, normal, expired, and expiring_soon balance badges.
```

### LeaveReport

Tasks:

```text
Render filters: campus_id, class_id, teacher_id, course_id, source, leave_type, status, date range.
Render summary cards: total, pending, approved, rejected, cancelled, makeup_scheduled, guardian_source_count.
Render table columns: leave_no, source, leave_type, status, student, class, teacher, course, lesson, requested_at, reviewed_at, makeup_required.
Provide drill link to V1-05 leave detail route when permission exists.
```

### V1AcceptanceReport

Tasks:

```text
Load getV1AcceptanceSummary on mount.
Render overall status pass/fail.
Render gate table with key, name, status, message, and evidence.
Render ledger mismatch table when mismatch_count > 0.
Render next_action from API.
Show loading, empty, error, forbidden, and retry states.
Do not mark V1 complete in UI; completion is controlled by implementation and acceptance command results.
```

## Teacher / Guardian Mobile Page Tasks

V1-08 creates no new mobile page and no mobile API client.

Teacher mobile regression:

```text
Run V1-06 teacher API client and page tests.
Verify teacher schedule, lesson detail, attendance submit, attendance result, leave list, and leave review pages still pass.
Verify teacher cannot access guardian routes.
```

Guardian mobile regression:

```text
Run V1-07 guardian API client and page tests.
Verify guardian student selector, schedule, account, consumption, notice list/detail/read, and leave create pages still pass.
Verify guardian cannot access teacher routes and cannot access unbound students.
```

## Test Plan

Backend tests:

| File | Case | Assertions |
| --- | --- | --- |
| `AcademicReportRepositoryTest.php` | `test_dashboard_counts_use_tenant_and_campus_scope` | cross-tenant and out-of-scope campus rows excluded |
| `AcademicReportRepositoryTest.php` | `test_consumption_summary_uses_ledger_rows` | summary net units equals consumption decrease minus rollback increase |
| `AcademicReportRepositoryTest.php` | `test_account_balance_level_mapping` | zero, low, normal, expired, and expiring_soon levels are assigned |
| `AcademicReportServiceTest.php` | `test_date_range_over_366_days_is_rejected` | code 422 with max_days 366 |
| `AcademicReportServiceTest.php` | `test_empty_report_returns_zero_summary_and_empty_list` | summary zero values and list empty |
| `AcademicAcceptanceServiceTest.php` | `test_gate_catalog_contains_required_v1_gates` | all required gate keys present |
| `AcademicAcceptanceServiceTest.php` | `test_ledger_mismatch_returns_fail_status` | overall_status fail and mismatch account id present |
| `AcademicDashboardApiTest.php` | `test_dashboard_contract` | response envelope and dashboard schema match API contract |
| `AttendanceReportApiTest.php` | `test_attendance_report_contract` | summary, list, total returned |
| `AttendanceReportApiTest.php` | `test_attendance_report_permission_required` | missing permission returns 403 |
| `ConsumptionReportApiTest.php` | `test_consumption_report_contract` | net units and rows returned |
| `ConsumptionReportApiTest.php` | `test_consumption_date_range_validation` | invalid range returns 422 |
| `AccountBalanceReportApiTest.php` | `test_account_balance_report_contract` | balance summary and levels returned |
| `LeaveReportApiTest.php` | `test_leave_report_contract` | leave summary and rows returned |
| `V1AcceptanceSummaryApiTest.php` | `test_acceptance_summary_pass_contract` | overall_status pass with gate rows |
| `V1AcademicAcceptanceTest.php` | `test_v1_enrollment_to_consumption_happy_path` | enrollment creates account, lesson attendance consumes once |
| `V1AcademicAcceptanceTest.php` | `test_v1_leave_and_makeup_flow_is_visible_in_reports` | leave report and dashboard pending/approved counts update |
| `V1LedgerConsistencyTest.php` | `test_account_balance_formula_matches_ledger` | every account satisfies balance formula |
| `V1LedgerConsistencyTest.php` | `test_consumption_rollback_reconciles_account_balance` | rollback restores available units |
| `V1TenantCampusIsolationAcceptanceTest.php` | `test_reports_exclude_other_tenant_data` | other tenant rows absent |
| `V1TenantCampusIsolationAcceptanceTest.php` | `test_reports_reject_out_of_scope_campus` | code 403 |
| `V1MobileAcceptanceTest.php` | `test_teacher_and_guardian_mobile_regression_contracts` | teacher and guardian critical APIs return expected envelopes |

PC tests:

| File | Case | Assertions |
| --- | --- | --- |
| `AcademicDashboard.spec.ts` | `renders_metric_cards_and_alerts` | dashboard metrics and alerts visible |
| `AcademicDashboard.spec.ts` | `dashboard_forbidden_state` | forbidden state rendered on 403 |
| `AttendanceReport.spec.ts` | `filters_reload_attendance_report` | API called with selected filters |
| `AttendanceReport.spec.ts` | `empty_attendance_report_state` | empty state rendered |
| `ConsumptionReport.spec.ts` | `renders_consumption_summary_and_rows` | net units and rows visible |
| `ConsumptionReport.spec.ts` | `reversed_rows_use_neutral_badge` | reversed badge present |
| `AccountBalanceReport.spec.ts` | `renders_balance_levels` | low and expired badges visible |
| `AccountBalanceReport.spec.ts` | `account_ledger_link_requires_permission` | link hidden without permission |
| `LeaveReport.spec.ts` | `renders_leave_summary_and_rows` | pending and approved counts visible |
| `V1AcceptanceReport.spec.ts` | `renders_pass_gate_table` | pass gates visible |
| `V1AcceptanceReport.spec.ts` | `renders_ledger_mismatch_when_failed` | mismatch table visible |
| `ReportDateRangeFilter.spec.ts` | `rejects_range_over_max_days` | validation message rendered |
| `ReportStateBlock.spec.ts` | `retry_emits_retry_event` | click emits retry |

Mobile regression tests:

| Command | Assertions |
| --- | --- |
| `pnpm test -- teacher` | V1-06 teacher API client and pages pass |
| `pnpm test -- guardian` | V1-07 guardian API client and pages pass |
| `pnpm build:h5` | H5 mobile build succeeds |

## Execution Commands

Backend focused tests:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter AcademicReportRepositoryTest
composer test -- --filter AcademicReportServiceTest
composer test -- --filter AcademicAcceptanceServiceTest
composer test -- --filter AcademicDashboardApiTest
composer test -- --filter AttendanceReportApiTest
composer test -- --filter ConsumptionReportApiTest
composer test -- --filter AccountBalanceReportApiTest
composer test -- --filter LeaveReportApiTest
composer test -- --filter V1AcceptanceSummaryApiTest
composer test -- --filter V1AcademicAcceptanceTest
composer test -- --filter V1LedgerConsistencyTest
composer test -- --filter V1TenantCampusIsolationAcceptanceTest
composer test -- --filter V1MobileAcceptanceTest
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

PC focused tests:

```bash
cd mineadmin-education-saas/admin-web
pnpm test -- AcademicDashboard
pnpm test -- AttendanceReport
pnpm test -- ConsumptionReport
pnpm test -- AccountBalanceReport
pnpm test -- LeaveReport
pnpm test -- V1AcceptanceReport
pnpm test -- ReportDateRangeFilter
pnpm test -- ReportStateBlock
```

Expected:

```text
Every listed PC test command exits 0.
All report page and shared component suites pass.
```

PC quality gate:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm typecheck
pnpm build
```

Expected:

```text
Lint exits 0.
TypeScript exits 0.
Build exits 0.
```

Mobile regression gate:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm test -- teacher
pnpm test -- guardian
pnpm lint
pnpm typecheck
pnpm build:h5
```

Expected:

```text
Teacher mobile tests pass.
Guardian mobile tests pass.
Lint exits 0.
TypeScript exits 0.
H5 build exits 0 and mobile-uniapp/dist/build/h5/index.html exists.
```

Full V1 gate:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter Foundation
composer test -- --filter ProfileRecord
composer test -- --filter CourseAccount
composer test -- --filter ClassSchedule
composer test -- --filter Attendance
composer test -- --filter LeaveMakeupReschedule
composer test -- --filter TeacherMobile
composer test -- --filter GuardianMobile
composer test -- --filter Notice
composer test -- --filter AcademicReport
composer test -- --filter V1
cd ../admin-web
pnpm test -- education
pnpm build
cd ../mobile-uniapp
pnpm test -- teacher
pnpm test -- guardian
pnpm build:h5
```

Expected:

```text
Foundation and V1-01 through V1-08 backend tests pass.
PC education tests and build pass.
Teacher and guardian mobile tests and H5 build pass.
```

No-migration verification:

```bash
cd mineadmin-education-saas
find backend/databases/migrations -maxdepth 1 -name '*010800*' -o -name '*report*acceptance*'
```

Expected:

```text
No output.
```

## Acceptance Gate

V1-08 can be accepted only when all items below are true:

```text
No V1-08 migration file exists.
Report APIs live under backend/app/Http/Admin and use /admin/education/academic/reports/*.
Report services are read-only in production code.
All report queries filter tenant_id and campus scope.
Dashboard metrics match V1-01 through V1-07 source tables.
Attendance report counts attendance rows and does not infer attendance from lessons alone.
Consumption report sums immutable consumption ledger rows.
Account balance report satisfies available_units formula for every account.
Leave report matches edu_leave_requests statuses and sources.
V1 acceptance summary returns pass only when required gates are pass and ledger mismatches are zero.
PC report pages implement filters, metric cards, tables, permissions, loading, empty, error, forbidden, and retry states.
No mobile route is added in V1-08.
Teacher and guardian mobile regression tests pass.
Backend focused tests, backend quality gate, PC tests, PC quality gate, mobile regression gate, full V1 gate, and no-migration verification all exit 0.
```

## Task Breakdown

### Task 1: Backend Report Requests And Schemas

**Files:**
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ReportDashboardRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ReportAttendanceRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ReportConsumptionRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ReportAccountBalanceRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ReportLeaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ReportAcceptanceRequest.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/AcademicDashboardSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/AttendanceReportSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/ConsumptionReportSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/AccountBalanceReportSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/LeaveReportSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/V1AcceptanceSummarySchema.php`

- [x] **Step 1: Create request classes**

Use the exact rules and validation messages from `Request Classes`.

- [x] **Step 2: Create schemas**

Use the exact field lists from `Schemas`.

- [x] **Step 3: Verify static analysis for new classes**

Run:

```bash
cd mineadmin-education-saas/backend
composer analyse
```

Expected:

```text
Static analysis exits 0 for the new request and schema classes.
```

### Task 2: Backend Report Repository And Service

**Files:**
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/AcademicReportRepository.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/AcademicReportService.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/AcademicReportRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/AcademicReportServiceTest.php`

- [x] **Step 1: Write repository and service tests**

Expected cases:

```text
dashboard counts use tenant and campus scope.
consumption summary uses ledger rows.
account balance level mapping is correct.
date range over 366 days is rejected.
empty report returns zero summary and empty list.
```

- [x] **Step 2: Implement repository**

Required behavior:

```text
Filter tenant_id on every query.
Apply campus scope.
Use ledger rows for consumption math.
Return decimal strings with two decimal places.
```

- [x] **Step 3: Implement service**

Required behavior:

```text
Normalize date ranges, validate max days, call repository, and shape schema responses.
```

- [x] **Step 4: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter AcademicReportRepositoryTest
composer test -- --filter AcademicReportServiceTest
```

Expected:

```text
Both commands exit 0.
```

### Task 3: Backend Acceptance Service

**Files:**
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/AcademicAcceptanceRepository.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/AcademicAcceptanceService.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/AcademicAcceptanceServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/V1LedgerConsistencyTest.php`

- [x] **Step 1: Write acceptance tests**

Expected cases:

```text
gate catalog contains required V1 gates.
ledger mismatch returns fail status.
account balance formula matches ledger.
consumption rollback reconciles account balance.
```

- [x] **Step 2: Implement acceptance repository**

Required behavior:

```text
Read module table health, ledger consistency, fixture summary, mobile readiness, and PC readiness.
```

- [x] **Step 3: Implement acceptance service**

Required behavior:

```text
Return pass only when every required gate passes and ledger mismatch count is zero.
```

- [x] **Step 4: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter AcademicAcceptanceServiceTest
composer test -- --filter V1LedgerConsistencyTest
```

Expected:

```text
Both commands exit 0.
```

### Task 4: Backend Report APIs

**Files:**
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/AcademicReportController.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/AcademicDashboardApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/AttendanceReportApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/ConsumptionReportApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/AccountBalanceReportApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/LeaveReportApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/V1AcceptanceSummaryApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/V1TenantCampusIsolationAcceptanceTest.php`

- [x] **Step 1: Write API tests**

Expected cases:

```text
dashboard, attendance, consumption, account balance, leave, and acceptance summary contracts.
permission failure returns 403.
campus outside scope returns 403.
invalid filters return 422.
```

- [x] **Step 2: Implement controller**

Endpoints:

```text
GET /admin/education/academic/reports/dashboard
GET /admin/education/academic/reports/attendance
GET /admin/education/academic/reports/consumption
GET /admin/education/academic/reports/account-balances
GET /admin/education/academic/reports/leaves
GET /admin/education/academic/reports/v1-acceptance-summary
```

- [x] **Step 3: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter AcademicDashboardApiTest
composer test -- --filter AttendanceReportApiTest
composer test -- --filter ConsumptionReportApiTest
composer test -- --filter AccountBalanceReportApiTest
composer test -- --filter LeaveReportApiTest
composer test -- --filter V1AcceptanceSummaryApiTest
composer test -- --filter V1TenantCampusIsolationAcceptanceTest
```

Expected:

```text
All seven commands exit 0.
```

### Task 5: Full V1 Acceptance Tests

**Files:**
- Create: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/V1AcademicAcceptanceTest.php`
- Create: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/V1MobileAcceptanceTest.php`

- [x] **Step 1: Write full academic acceptance test**

Expected flow:

```text
create tenant and campus context.
create enabled student, guardian, teacher, and binding.
create course, package, enrollment, and account.
create class, class student, scheduled lesson, and lesson student.
submit attendance and create consumption.
create leave request and approve it.
publish notice and read it through guardian receipt.
assert dashboard, attendance, consumption, account balance, leave, and acceptance summary reflect the flow.
```

- [x] **Step 2: Write mobile acceptance regression**

Expected flow:

```text
teacher sees assigned lesson and attendance result.
guardian sees bound student, account, consumption, notice, and leave status.
teacher cannot access guardian route.
guardian cannot access teacher route.
```

- [x] **Step 3: Run acceptance tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter V1AcademicAcceptanceTest
composer test -- --filter V1MobileAcceptanceTest
```

Expected:

```text
Both commands exit 0.
```

### Task 6: PC Report API Client And Routes

**Files:**
- Create: `mineadmin-education-saas/admin-web/src/api/education/academic/report.ts`
- Modify: `mineadmin-education-saas/admin-web/src/router/modules/education.ts`

- [x] **Step 1: Create API client**

Functions must match the `API Client` section exactly.

- [x] **Step 2: Add routes**

Add all six report routes from `Routes And Menus`.

- [x] **Step 3: Run route and type checks**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm typecheck
```

Expected:

```text
TypeScript exits 0.
```

### Task 7: PC Report Shared Components

**Files:**
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/ReportDateRangeFilter.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/ReportMetricCard.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/ReportStateBlock.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/ReportTableToolbar.vue`
- Test: `mineadmin-education-saas/admin-web/src/views/education/academic/components/__tests__/ReportDateRangeFilter.spec.ts`
- Test: `mineadmin-education-saas/admin-web/src/views/education/academic/components/__tests__/ReportStateBlock.spec.ts`

- [x] **Step 1: Write component tests**

Expected cases:

```text
date filter rejects range over max days.
state block emits retry.
metric card renders long decimal values without layout shift.
```

- [x] **Step 2: Implement components**

Use the exact props and behavior from `Shared Components`.

- [x] **Step 3: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm test -- ReportDateRangeFilter
pnpm test -- ReportStateBlock
```

Expected:

```text
Both commands exit 0.
```

### Task 8: PC Report Pages

**Files:**
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/AcademicDashboard.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/AttendanceReport.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/ConsumptionReport.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/AccountBalanceReport.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/LeaveReport.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/V1AcceptanceReport.vue`
- Test: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/AcademicDashboard.spec.ts`
- Test: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/AttendanceReport.spec.ts`
- Test: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/ConsumptionReport.spec.ts`
- Test: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/AccountBalanceReport.spec.ts`
- Test: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/LeaveReport.spec.ts`
- Test: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/V1AcceptanceReport.spec.ts`

- [x] **Step 1: Write page tests**

Expected cases:

```text
dashboard metrics render.
attendance filters reload.
consumption summary renders.
account balance level badges render.
leave summary renders.
acceptance pass and fail states render.
permission-hidden drill links are hidden.
```

- [x] **Step 2: Implement report pages**

Use the exact tasks from `PC Admin Page Tasks`.

- [x] **Step 3: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm test -- AcademicDashboard
pnpm test -- AttendanceReport
pnpm test -- ConsumptionReport
pnpm test -- AccountBalanceReport
pnpm test -- LeaveReport
pnpm test -- V1AcceptanceReport
```

Expected:

```text
All six commands exit 0.
```

### Task 9: Final Verification

**Files:**
- Verify: `mineadmin-education-saas/backend`
- Verify: `mineadmin-education-saas/admin-web`
- Verify: `mineadmin-education-saas/mobile-uniapp`

- [x] **Step 1: Run backend gates**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter AcademicReport
composer test -- --filter V1
composer analyse
composer cs-fix -- --dry-run
```

Expected:

```text
AcademicReport and V1 tests pass.
Static analysis exits 0.
Formatting dry run exits 0.
```

- [x] **Step 2: Run PC gates**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm test -- AcademicDashboard AttendanceReport ConsumptionReport AccountBalanceReport LeaveReport V1AcceptanceReport
pnpm lint
pnpm typecheck
pnpm build
```

Expected:

```text
Report page tests pass.
Lint exits 0.
Typecheck exits 0.
Build exits 0.
```

- [x] **Step 3: Run mobile regression gates**

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm test -- teacher
pnpm test -- guardian
pnpm lint
pnpm typecheck
pnpm build:h5
```

Expected:

```text
Teacher and guardian mobile tests pass.
Lint exits 0.
Typecheck exits 0.
H5 build exits 0.
```

- [x] **Step 4: Confirm no V1-08 migration**

Run:

```bash
cd mineadmin-education-saas
find backend/databases/migrations -maxdepth 1 -name '*010800*' -o -name '*report*acceptance*'
```

Expected:

```text
No output.
```

## Self-Review

- Scope coverage: V1-08 covers dashboard, attendance report, consumption report, account balance report, leave report, acceptance summary, PC report pages, and final V1 verification gates.
- MineAdmin fit: Report APIs use `backend/app/Http/Admin`; shared logic uses `backend/app/Service`, `backend/app/Repository`, and `backend/app/Schema`; no mobile API or migration is introduced.
- Dependency fit: Reports read V1-01 through V1-07 tables and do not duplicate business logic from enrollment, scheduling, attendance, leave, teacher mobile, or guardian mobile modules.
- Data correctness: Consumption totals come from ledger rows, account balance checks use the V1-02/V1-04 balance formula, and acceptance fails on ledger mismatch.
- UI fit: PC report pages include API client, routes, filters, metric cards, tables, permission-aware drill links, state handling, and tests with exact file paths.
- Readiness: This plan has exact paths, no-migration design, Controller/Request/Service/Repository/Schema tasks, complete API request/success/validation/business failure examples, PC page tasks, mobile regression tasks, tests, commands, expected outputs, and acceptance gates, so V1-08 can be marked `ready`.
