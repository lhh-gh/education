# MineAdmin Education SaaS V1 Core Academic Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:writing-plans to expand each child plan below into code-level implementation tasks before coding. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Split the V1 课消制培训机构 MVP into resource-level implementation plans that can later be promoted to code-level `ready`.

**Architecture:** V1 depends on Foundation F00-F06. Backend must use MineAdmin 3.x native layers: admin APIs under `app/Http/Admin`, mobile APIs under `app/Http/Api`, shared services under `app/Service`, repositories under `app/Repository`, models under `app/Model`, schemas under `app/Schema`, and migrations under `databases/migrations`. PC pages live in `admin-web`; teacher and guardian pages live in `mobile-uniapp`.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, Redis queue, MineAdmin-Vue, Vue3, TypeScript, uni-app, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. V1 core academic gates have passed.

---

## Scope Check

Included in V1:

- Student, guardian, teacher, and classroom/course-facing base records.
- Course products, lesson packages, enrollment, and student course accounts.
- Classes, class students, scheduling, lessons, and lesson student snapshots.
- Attendance, lesson consumption, manual supplement deduction, and rollback ledger.
- Lightweight leave, make-up, and reschedule workflows for daily teaching operations.
- Teacher mobile lesson list, detail, attendance, and leave handling pages.
- Guardian mobile student selector, schedule, account, consumption, notice, and leave pages.
- V1 reports and final acceptance gates.

Excluded from V1:

- Admissions CRM and trial conversion; V3 owns them.
- Online payment, financial reconciliation, invoice, and multi-account finance; V4 owns them.
- Teacher payroll and performance settlement; V5 owns them.
- Advanced multi-campus group analytics; V6 owns them.
- AI, automation, growth, course standards, and learning content; V8-V12 own them.

Boundary note:

```text
V1-05 includes lightweight leave/make-up/reschedule needed for daily class operation. V2 can still add a more advanced lesson change center, bulk operations, and complex make-up optimization.
```

## File Structure

V1 child plans:

```text
docs/superpowers/plans/2026-06-10-mineadmin-education-saas-01-v1-01-profile-records.md
docs/superpowers/plans/2026-06-10-mineadmin-education-saas-01-v1-02-course-package-account.md
docs/superpowers/plans/2026-06-10-mineadmin-education-saas-01-v1-03-class-schedule-lesson.md
docs/superpowers/plans/2026-06-10-mineadmin-education-saas-01-v1-04-attendance-consumption-adjustment.md
docs/superpowers/plans/2026-06-10-mineadmin-education-saas-01-v1-05-leave-makeup-reschedule.md
docs/superpowers/plans/2026-06-10-mineadmin-education-saas-01-v1-06-teacher-mobile.md
docs/superpowers/plans/2026-06-10-mineadmin-education-saas-01-v1-07-guardian-mobile.md
docs/superpowers/plans/2026-06-10-mineadmin-education-saas-01-v1-08-reports-acceptance.md
```

Target backend roots:

```text
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/
mineadmin-education-saas/backend/app/Service/Education/Academic/
mineadmin-education-saas/backend/app/Repository/Education/Academic/
mineadmin-education-saas/backend/app/Model/Education/Academic/
mineadmin-education-saas/backend/app/Schema/Education/Academic/
mineadmin-education-saas/backend/databases/migrations/
mineadmin-education-saas/backend/tests/Feature/Education/Academic/
mineadmin-education-saas/backend/tests/Unit/Education/Academic/
```

Target PC roots:

```text
mineadmin-education-saas/admin-web/src/api/education/academic/
mineadmin-education-saas/admin-web/src/views/education/academic/
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Target mobile roots:

```text
mineadmin-education-saas/mobile-uniapp/src/api/academic/
mineadmin-education-saas/mobile-uniapp/pages/teacher/
mineadmin-education-saas/mobile-uniapp/pages/guardian/
```

## Database Migration Design

Migration ownership:

| Child plan | Migration file | Tables |
| --- | --- | --- |
| V1-01 | `2026_06_10_010100_create_v1_profile_record_tables.php` | `edu_classrooms`, `edu_students`, `edu_guardians`, `edu_student_guardians`, `edu_teachers` |
| V1-02 | `2026_06_10_010200_create_v1_course_account_tables.php` | `edu_courses`, `edu_teacher_courses`, `edu_lesson_packages`, `edu_enrollments`, `edu_student_course_accounts` |
| V1-03 | `2026_06_10_010300_create_v1_class_lesson_tables.php` | `edu_classes`, `edu_class_students`, `edu_lessons`, `edu_lesson_students` |
| V1-04 | `2026_06_10_010400_create_v1_attendance_consumption_tables.php` | `edu_lesson_attendances`, `edu_lesson_consumptions`, `edu_account_adjustments` |
| V1-05 | `2026_06_10_010500_create_v1_leave_change_tables.php` | `edu_leave_requests`, `edu_lesson_change_records` |
| V1-06 | none | teacher mobile uses V1-03 to V1-05 tables |
| V1-07 | `2026_06_10_010700_create_v1_notice_tables.php` | `edu_notices`, `edu_notice_receipts` |
| V1-08 | none | reports read V1 tables |

Shared fields:

```text
tenant_id, campus_id, created_by, updated_by, created_at, updated_at, deleted_at where the record is business-owned.
```

## MineAdmin Backend Module Design

Backend implementation is split by child plan:

| Child plan | Main backend responsibility |
| --- | --- |
| V1-01 | classroom, student, guardian, teacher CRUD and relationship management |
| V1-02 | course, teacher authorization, lesson package, enrollment, student course account |
| V1-03 | class, class student, lesson scheduling, conflict checks |
| V1-04 | attendance save, consumption ledger, account adjustment and rollback |
| V1-05 | leave request, make-up lesson, reschedule record |
| V1-06 | teacher mobile APIs |
| V1-07 | guardian mobile APIs and notice read APIs |
| V1-08 | reports, dashboard APIs, full V1 acceptance tests |

Each child plan must define exact Controller, Request, Service, Repository, Model, Schema, and test files before its status can become `ready`.

## API Contract

API groups by child plan:

| Child plan | API group |
| --- | --- |
| V1-01 | `/admin/education/academic/students/*`, `/guardians/*`, `/teachers/*`, `/classrooms/*` |
| V1-02 | `/admin/education/academic/courses/*`, `/lesson-packages/*`, `/enrollments/*`, `/student-course-accounts/*` |
| V1-03 | `/admin/education/academic/classes/*`, `/lessons/*`, `/lesson-schedule/*` |
| V1-04 | `/admin/education/academic/attendance/*`, `/consumptions/*`, `/account-adjustments/*` |
| V1-05 | `/admin/education/academic/leave-requests/*`, `/lesson-changes/*` |
| V1-06 | `/mobile/education/academic/teacher/*` |
| V1-07 | `/mobile/education/academic/guardian/*`, guardian notice APIs |
| V1-08 | `/admin/education/academic/reports/*`, `/dashboard/*` |

All API contracts must include method, path, permission, caller, headers, request JSON, success response, validation failure, business failure, tenant/campus isolation rule, and audit rule.

## PC Admin Page Tasks

PC page ownership:

| Child plan | PC pages |
| --- | --- |
| V1-01 | ClassroomList, StudentList, GuardianList, TeacherList |
| V1-02 | CourseList, LessonPackageList, EnrollmentWorkbench, AccountLedgerList |
| V1-03 | ClassList, LessonScheduleCalendar, LessonList |
| V1-04 | AttendanceReview, ConsumptionLedgerList, AccountAdjustmentList |
| V1-05 | LeaveRequestList, LessonChangeList |
| V1-06 | no PC page |
| V1-07 | NoticeList and guardian notice delivery status |
| V1-08 | AcademicDashboard, ConsumptionReport, AttendanceReport |

Every PC page must include typed API client, route/menu registration, list/search/form/action states, permission buttons, loading, empty, error, and success behavior.

## Teacher / Guardian Mobile Page Tasks

Mobile page ownership:

| Child plan | Mobile pages |
| --- | --- |
| V1-06 | teacher today lessons, lesson detail, attendance submit, leave approval list |
| V1-07 | guardian student selector, schedule, course account, consumption ledger, notice list/detail, leave request create |

Teacher isolation:

```text
Teacher can read and submit only assigned lessons inside current tenant and campus scope.
```

Guardian isolation:

```text
Guardian can read only students linked through `edu_student_guardians`.
```

## Test Plan

V1 test gates:

| Child plan | Test focus |
| --- | --- |
| V1-01 | profile migrations, relationships, admin APIs, tenant/campus isolation |
| V1-02 | enrollment transaction, account creation/increment, course/package constraints |
| V1-03 | class membership, scheduling conflict checks, lesson snapshot |
| V1-04 | attendance idempotency, consumption ledger, supplement deduction, rollback |
| V1-05 | leave request flow, make-up generation, reschedule audit trail |
| V1-06 | teacher mobile role isolation and attendance submission |
| V1-07 | guardian student isolation, account/consumption/notice/leave mobile pages |
| V1-08 | end-to-end enrollment to consumption acceptance, reports, final gates |

## Execution Commands

Run child plans in order:

```text
V1-01 Profile Records
V1-02 Course Package Account
V1-03 Class Schedule Lesson
V1-04 Attendance Consumption Adjustment
V1-05 Leave Makeup Reschedule
V1-06 Teacher Mobile
V1-07 Guardian Mobile
V1-08 Reports Acceptance
```

Aggregate V1 final gate after all child plans are ready and implemented:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter Education\\\\Academic
composer cs-fix -- --dry-run
composer analyse
cd ../admin-web
pnpm lint
pnpm test -- Academic
pnpm build
cd ../mobile-uniapp
pnpm lint
pnpm test -- teacher
pnpm test -- guardian
pnpm build:h5
```

Expected:

```text
All V1 backend, PC, and mobile gates pass.
```

## Acceptance Gate

V1 is accepted only when:

```text
- V1-01 through V1-08 are each promoted to code-level ready before implementation.
- Student enrollment creates or increments a student course account.
- Scheduling rejects teacher, classroom, class, and student time conflicts.
- Teacher attendance deducts course account balance exactly once.
- Manual supplement deduction and rollback preserve ledger consistency.
- Guardian can view only bound student data.
- Teacher can operate only assigned lessons.
- PC pages and mobile pages pass state and permission tests.
- V1 reports match ledger data.
```

## Self-Review

- Product fit: This split follows the课消制核心链路 from profile records through enrollment, scheduling, attendance, consumption, leave/change, mobile usage, and reports.
- MineAdmin fit: The plan uses MineAdmin 3.x path conventions and keeps admin/mobile API layers separate.
- Acceptance status: V1 has been implemented and accepted after aggregate backend, PC, and mobile gates passed.
- Completion note: Foundation F00-F06 and V1-01 through V1-08 have been implemented and verified in dependency order.
