# MineAdmin Education SaaS V1-03 Class Schedule Lesson Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V1 classes, class-student membership, single/batch lesson scheduling, lesson records, lesson student snapshots, and conflict checks for teacher, classroom, class, and student time collisions.

**Architecture:** V1-03 depends on Foundation F01 tenant/campus, F02 education user context and campus scope, F04 audit logging, F05 PC conventions, V1-01 classrooms/students/teachers, and V1-02 courses/teacher-course authorization/student course accounts. Backend follows MineAdmin 3.x native paths under `app/Http/Admin`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, and `databases/migrations`; scheduling writes lessons and lesson-student snapshots inside database transactions after conflict checks. No teacher or guardian mobile page is added in V1-03.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, Redis queue where project defaults require it, MineAdmin-Vue, Vue3, TypeScript, calendar UI component used by the MineAdmin admin frontend, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** incomplete / not implemented. `ready` means this plan is detailed enough to start coding.

---

## Scope Check

Included:

- Create class, class-student, lesson, and lesson-student snapshot tables.
- Create V1-03 enums for class type, class-student status, lesson status, lesson-student status, and schedule source type.
- Create models, repositories, services, request classes, schemas, and admin controllers for classes, lessons, and schedule operations.
- Manage class membership by linking enabled V1-01 students to an enabled class course account from V1-02.
- Create single lesson schedules and batch recurring schedules.
- Generate lesson-student snapshots from active class students at scheduling time.
- Validate teacher-course authorization from V1-02 before scheduling lessons.
- Reject teacher, classroom, class, and student time conflicts before lesson creation or lesson time update.
- Cancel scheduled lessons without attendance or consumption side effects.
- Add admin APIs for class CRUD/status/delete, class-student read/save, lesson page/detail/update/cancel/delete, schedule calendar, conflict check, single schedule, and batch schedule.
- Add PC API client, route/menu entries, class list, schedule calendar, lesson list, forms, drawers, permission-controlled buttons, loading/empty/error states, and tests.
- Add migration, repository, service, API, permission, tenant/campus isolation, audit, PC, and mobile regression tests.

Excluded:

- Attendance submission, attendance status, lesson consumption, supplement deduction, and rollback ledger; V1-04 owns them.
- Leave, make-up, and reschedule workflows; V1-05 owns them.
- Teacher mobile today lesson list/detail pages; V1-06 owns them.
- Guardian mobile schedule and account pages; V1-07 owns them.
- Advanced lesson change center, bulk make-up optimization, and teaching operations analytics; V2 owns them.
- Course product, lesson package, enrollment, and account initialization; V1-02 owns them.

Business rules:

```text
Classes, class students, lessons, and lesson students are tenant-scoped and campus-scoped.
Class code is unique inside tenant + campus.
Class course_id must point to an enabled V1-02 course in the same tenant and campus.
Class main_teacher_id must point to an enabled V1-01 teacher in the same tenant and campus and must be authorized for the course through V1-02 teacher-course authorization.
Class default classroom_id must point to an enabled V1-01 classroom in the same tenant and campus when present.
Class-student membership accepts only enabled students in the same tenant and campus.
Class-student membership requires an active V1-02 student course account for the class course.
Scheduling accepts only enabled classes and active class students.
Scheduling creates lesson-student snapshots from active class students at the time the lesson is created.
Later class-student changes do not mutate existing lesson-student snapshots.
Lesson update may change title, teacher, classroom, start_at, end_at, lesson_units, and remark while status is scheduled.
Lesson cancellation is allowed only while status is scheduled.
Lesson deletion is allowed only while status is scheduled and no V1-04 attendance or consumption reference exists.
```

Conflict rules:

```text
Time overlap condition: existing.start_at < target.end_at and existing.end_at > target.start_at.
Cancelled lessons are ignored by conflict checks.
The lesson currently being updated is excluded from its own conflict check.
Teacher conflict checks existing non-cancelled lessons with the same teacher_id.
Classroom conflict checks existing non-cancelled lessons with the same classroom_id.
Class conflict checks existing non-cancelled lessons with the same class_id.
Student conflict checks existing non-cancelled lesson-student rows for every target student_id.
Batch scheduling is all-or-nothing; if any generated lesson conflicts, no lesson from that batch is persisted.
Conflict responses use code 409 and include conflict_type plus conflicting lesson ids.
```

Decimal and time rules:

```text
Lesson units use decimal(10,2), allowing values such as 1.00, 1.50, and 0.50.
Lesson end_at must be later than start_at.
duration_minutes is computed by the service from start_at and end_at.
lesson_units is copied to lesson-student snapshots.
Batch scheduling supports up to 180 generated lessons per request.
Batch weekdays use integers 1 through 7 where 1 is Monday and 7 is Sunday.
```

Status machines:

```text
Class status: enabled -> disabled, disabled -> enabled.
Class-student status: active -> paused, paused -> active, active/paused -> left.
Lesson status: scheduled -> cancelled. V1-04 may later move scheduled -> completed after attendance.
Lesson-student status: planned -> cancelled. V1-04 may later move planned -> attended/absent/leave in its own tables.
Schedule source type: manual or batch.
```

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_010300_create_v1_class_lesson_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/ClassType.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/ClassStudentStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LessonStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LessonStudentStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/ScheduleSourceType.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationClass.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationClassStudent.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLesson.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonStudent.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/ClassRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/ClassStudentRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonStudentRepository.php
mineadmin-education-saas/backend/app/Service/Education/Academic/ClassService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/ClassStudentService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/LessonService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/SchedulingService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/SchedulingConflictService.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ClassPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ClassSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ClassStatusRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ClassStudentSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LessonPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LessonUpdateRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LessonCancelRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ScheduleCalendarRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ScheduleConflictCheckRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/SingleLessonScheduleRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/BatchLessonScheduleRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/ClassController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/LessonController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/LessonScheduleController.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/ClassSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/ClassStudentSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/LessonSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/LessonStudentSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/SchedulingConflictSchema.php
```

Read backend dependencies:

```text
mineadmin-education-saas/backend/app/Service/Education/Foundation/EducationUserContext.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusScopeService.php
mineadmin-education-saas/backend/app/Event/Education/Foundation/EducationAuditEvent.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/AcademicRecordStatus.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationClassroom.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudent.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationTeacher.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationCourse.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationTeacherCourse.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudentCourseAccount.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/ClassroomRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/StudentRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/TeacherRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/CourseRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/TeacherCourseRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/StudentCourseAccountRepository.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Academic/ClassScheduleMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/ClassRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/ClassStudentRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/LessonRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/LessonStudentRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/ClassServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/ClassStudentServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/LessonServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/SchedulingServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/SchedulingConflictServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/ClassScheduleAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/ClassSchedulePermissionTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/ClassScheduleIsolationTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/ClassScheduleAuditTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/academic/classSchedule.ts
mineadmin-education-saas/admin-web/src/views/education/academic/ClassList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/LessonScheduleCalendar.vue
mineadmin-education-saas/admin-web/src/views/education/academic/LessonList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/ClassForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/ClassStudentDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/SingleLessonScheduleDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/BatchLessonScheduleDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/ScheduleConflictDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/LessonForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/LessonDetailDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/ClassList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/ClassStudentDrawer.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/LessonScheduleCalendar.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/SingleLessonScheduleDrawer.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/BatchLessonScheduleDrawer.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/LessonList.spec.ts
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
mineadmin-education-saas/backend/databases/migrations/2026_06_10_010300_create_v1_class_lesson_tables.php
```

Tables:

```text
edu_classes
edu_class_students
edu_lessons
edu_lesson_students
```

Foreign-key policy:

```text
Use no physical foreign keys in V1-03.
Use service-level validation against V1-01 classrooms/students/teachers and V1-02 courses/teacher-course authorizations/student course accounts.
Reason: MineAdmin business tables use soft deletes, tenant isolation, and staged module migrations; service validation keeps rollback and soft-delete behavior predictable.
```

Rollback order:

```text
Schema::dropIfExists('edu_lesson_students');
Schema::dropIfExists('edu_lessons');
Schema::dropIfExists('edu_class_students');
Schema::dropIfExists('edu_classes');
```

### `edu_classes`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Class id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Campus id |
| `course_id` | bigint unsigned | no | none | V1-02 course id |
| `main_teacher_id` | bigint unsigned | yes | null | Default teacher id |
| `classroom_id` | bigint unsigned | yes | null | Default classroom id |
| `code` | varchar(64) | no | none | Class code inside campus |
| `name` | varchar(120) | no | none | Class name |
| `class_type` | varchar(20) | no | `group` | group or one_to_one |
| `max_students` | int unsigned | no | 0 | Maximum student count, 0 means unlimited |
| `start_date` | date | yes | null | Class start date |
| `end_date` | date | yes | null | Class end date |
| `lesson_units` | decimal(10,2) | no | 1.00 | Default lesson units per lesson |
| `status` | varchar(20) | no | `enabled` | enabled or disabled |
| `schedule_note` | varchar(500) | yes | null | Scheduling note |
| `remark` | varchar(500) | yes | null | Internal remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_classes_tenant_campus_code (tenant_id, campus_id, code)
index idx_edu_classes_tenant_campus_status (tenant_id, campus_id, status)
index idx_edu_classes_tenant_course_status (tenant_id, course_id, status)
index idx_edu_classes_tenant_teacher_status (tenant_id, main_teacher_id, status)
index idx_edu_classes_deleted_at (deleted_at)
```

### `edu_class_students`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Class student id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Campus id |
| `class_id` | bigint unsigned | no | none | Class id |
| `course_id` | bigint unsigned | no | none | Course id snapshot from class |
| `student_id` | bigint unsigned | no | none | V1-01 student id |
| `account_id` | bigint unsigned | no | none | V1-02 student course account id |
| `student_name_snapshot` | varchar(120) | no | none | Student name at join time |
| `student_no_snapshot` | varchar(64) | no | none | Student number at join time |
| `status` | varchar(20) | no | `active` | active, paused, or left |
| `joined_at` | timestamp | yes | null | Joined time |
| `left_at` | timestamp | yes | null | Left time |
| `remark` | varchar(500) | yes | null | Internal remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_class_students_tenant_class_student (tenant_id, class_id, student_id)
index idx_edu_class_students_tenant_student_status (tenant_id, student_id, status)
index idx_edu_class_students_tenant_class_status (tenant_id, class_id, status)
index idx_edu_class_students_tenant_account (tenant_id, account_id)
index idx_edu_class_students_deleted_at (deleted_at)
```

### `edu_lessons`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Lesson id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Campus id |
| `lesson_no` | varchar(64) | no | none | Lesson number |
| `class_id` | bigint unsigned | no | none | Class id |
| `course_id` | bigint unsigned | no | none | Course id |
| `teacher_id` | bigint unsigned | no | none | Teacher id |
| `classroom_id` | bigint unsigned | yes | null | Classroom id |
| `title` | varchar(160) | no | none | Lesson title |
| `start_at` | timestamp | no | none | Lesson start time |
| `end_at` | timestamp | no | none | Lesson end time |
| `duration_minutes` | int unsigned | no | 0 | Duration in minutes |
| `lesson_units` | decimal(10,2) | no | 1.00 | Lesson units to consume in V1-04 |
| `student_count` | int unsigned | no | 0 | Snapshot student count |
| `status` | varchar(20) | no | `scheduled` | scheduled, cancelled, or completed |
| `source_type` | varchar(20) | no | `manual` | manual or batch |
| `schedule_batch_no` | varchar(64) | yes | null | Batch schedule number |
| `class_name_snapshot` | varchar(120) | no | none | Class name snapshot |
| `course_name_snapshot` | varchar(120) | no | none | Course name snapshot |
| `teacher_name_snapshot` | varchar(120) | no | none | Teacher name snapshot |
| `classroom_name_snapshot` | varchar(120) | yes | null | Classroom name snapshot |
| `cancelled_at` | timestamp | yes | null | Cancellation time |
| `cancel_reason` | varchar(500) | yes | null | Cancellation reason |
| `remark` | varchar(500) | yes | null | Internal remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_lessons_tenant_lesson_no (tenant_id, lesson_no)
index idx_edu_lessons_tenant_campus_time (tenant_id, campus_id, start_at, end_at)
index idx_edu_lessons_tenant_teacher_time (tenant_id, teacher_id, start_at, end_at)
index idx_edu_lessons_tenant_classroom_time (tenant_id, classroom_id, start_at, end_at)
index idx_edu_lessons_tenant_class_time (tenant_id, class_id, start_at, end_at)
index idx_edu_lessons_tenant_status_time (tenant_id, status, start_at)
index idx_edu_lessons_schedule_batch_no (schedule_batch_no)
index idx_edu_lessons_deleted_at (deleted_at)
```

### `edu_lesson_students`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Lesson student id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Campus id |
| `lesson_id` | bigint unsigned | no | none | Lesson id |
| `class_id` | bigint unsigned | no | none | Class id |
| `course_id` | bigint unsigned | no | none | Course id |
| `student_id` | bigint unsigned | no | none | Student id |
| `account_id` | bigint unsigned | no | none | Student course account id |
| `student_name_snapshot` | varchar(120) | no | none | Student name snapshot |
| `student_no_snapshot` | varchar(64) | no | none | Student number snapshot |
| `lesson_units` | decimal(10,2) | no | 1.00 | Planned lesson units |
| `status` | varchar(20) | no | `planned` | planned or cancelled |
| `remark` | varchar(500) | yes | null | Internal remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_lesson_students_tenant_lesson_student (tenant_id, lesson_id, student_id)
index idx_edu_lesson_students_tenant_student (tenant_id, student_id)
index idx_edu_lesson_students_tenant_lesson_status (tenant_id, lesson_id, status)
index idx_edu_lesson_students_tenant_account (tenant_id, account_id)
index idx_edu_lesson_students_deleted_at (deleted_at)
```

## MineAdmin Backend Module Design

### Enums

Create `ClassType`:

```php
enum ClassType: string
{
    case Group = 'group';
    case OneToOne = 'one_to_one';
}
```

Create `ClassStudentStatus`:

```php
enum ClassStudentStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Left = 'left';
}
```

Create `LessonStatus`:

```php
enum LessonStatus: string
{
    case Scheduled = 'scheduled';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
}
```

Create `LessonStudentStatus`:

```php
enum LessonStudentStatus: string
{
    case Planned = 'planned';
    case Cancelled = 'cancelled';
}
```

Create `ScheduleSourceType`:

```php
enum ScheduleSourceType: string
{
    case Manual = 'manual';
    case Batch = 'batch';
}
```

Reuse V1-01 `AcademicRecordStatus` for class status values:

```text
enabled
disabled
```

### Models

All models use MineAdmin/Hyperf model conventions, timestamps, soft deletes, and guarded tenant fields only through service methods.

`EducationClass`:

```text
table: edu_classes
fillable: tenant_id, campus_id, course_id, main_teacher_id, classroom_id, code, name, class_type, max_students, start_date, end_date, lesson_units, status, schedule_note, remark, created_by, updated_by
casts: tenant_id integer, campus_id integer, course_id integer, main_teacher_id integer, classroom_id integer, max_students integer, start_date date, end_date date, lesson_units decimal:2
soft delete: yes
relationships: course belongsTo EducationCourse, mainTeacher belongsTo EducationTeacher, classroom belongsTo EducationClassroom, students hasMany EducationClassStudent, lessons hasMany EducationLesson
```

`EducationClassStudent`:

```text
table: edu_class_students
fillable: tenant_id, campus_id, class_id, course_id, student_id, account_id, student_name_snapshot, student_no_snapshot, status, joined_at, left_at, remark, created_by, updated_by
casts: tenant_id integer, campus_id integer, class_id integer, course_id integer, student_id integer, account_id integer, joined_at datetime, left_at datetime
soft delete: yes
relationships: class belongsTo EducationClass, student belongsTo EducationStudent, account belongsTo EducationStudentCourseAccount
```

`EducationLesson`:

```text
table: edu_lessons
fillable: tenant_id, campus_id, lesson_no, class_id, course_id, teacher_id, classroom_id, title, start_at, end_at, duration_minutes, lesson_units, student_count, status, source_type, schedule_batch_no, class_name_snapshot, course_name_snapshot, teacher_name_snapshot, classroom_name_snapshot, cancelled_at, cancel_reason, remark, created_by, updated_by
casts: tenant_id integer, campus_id integer, class_id integer, course_id integer, teacher_id integer, classroom_id integer, start_at datetime, end_at datetime, duration_minutes integer, lesson_units decimal:2, student_count integer, cancelled_at datetime
soft delete: yes
relationships: class belongsTo EducationClass, course belongsTo EducationCourse, teacher belongsTo EducationTeacher, classroom belongsTo EducationClassroom, lessonStudents hasMany EducationLessonStudent
```

`EducationLessonStudent`:

```text
table: edu_lesson_students
fillable: tenant_id, campus_id, lesson_id, class_id, course_id, student_id, account_id, student_name_snapshot, student_no_snapshot, lesson_units, status, remark, created_by, updated_by
casts: tenant_id integer, campus_id integer, lesson_id integer, class_id integer, course_id integer, student_id integer, account_id integer, lesson_units decimal:2
soft delete: yes
relationships: lesson belongsTo EducationLesson, student belongsTo EducationStudent, account belongsTo EducationStudentCourseAccount
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
keyword matches code, name, lesson_no, title, teacher_name_snapshot, and student snapshot fields where present.
status exact match.
page and pageSize use MineAdmin pagination defaults and cap pageSize at 100.
```

`ClassRepository` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function findScoped(int $id, EducationUserContext $context): ?EducationClass
public function findEnabledForScheduling(int $id, int $tenantId, int $campusId): ?EducationClass
public function existsCode(int $tenantId, int $campusId, string $code, ?int $excludeId = null): bool
public function hasLessonReferences(int $id, int $tenantId): bool
public function options(array $filters, EducationUserContext $context): array
```

`ClassStudentRepository` methods:

```php
public function listByClass(int $classId, EducationUserContext $context): array
public function activeStudentsByClass(int $classId, int $tenantId, int $campusId): array
public function replaceStudents(int $classId, array $studentRows, int $tenantId, int $campusId, ?int $operatorId): array
public function studentIdsByClass(int $classId, int $tenantId): array
public function studentIsActiveInClass(int $classId, int $studentId, int $tenantId): bool
```

`LessonRepository` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function calendar(array $filters, EducationUserContext $context): array
public function findScoped(int $id, EducationUserContext $context): ?EducationLesson
public function createScheduled(array $data): EducationLesson
public function updateScheduled(int $id, array $data, ?int $operatorId): EducationLesson
public function cancel(int $id, string $reason, ?int $operatorId): EducationLesson
public function nextLessonNo(int $tenantId, int $campusId): string
public function overlappingLessons(array $filters, ?int $excludeLessonId = null): array
public function hasAttendanceOrConsumptionReferences(int $id, int $tenantId): bool
```

`LessonStudentRepository` methods:

```php
public function listByLesson(int $lessonId, EducationUserContext $context): array
public function bulkCreateSnapshots(int $lessonId, array $rows): array
public function replacePlannedSnapshots(int $lessonId, array $rows, ?int $operatorId): array
public function cancelByLesson(int $lessonId, ?int $operatorId): int
public function overlappingStudentLessons(array $studentIds, string $startAt, string $endAt, int $tenantId, int $campusId, ?int $excludeLessonId = null): array
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
Reject duplicate class code with code 409.
Reject disabled course, teacher, classroom, class, student, or course account inputs with code 422.
Write F04 audit events after successful class create/update/status/delete, class-student save, lesson schedule/update/cancel/delete, and batch schedule operations.
Use MineAdmin OperationMiddleware on write controllers.
Soft delete only when the row is not referenced by later V1 data.
```

`ClassService` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationClass
public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationClass
public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationClass
public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
public function options(array $filters, EducationUserContext $context): array
```

Class rules:

```text
course_id must point to an enabled course in the same tenant and campus.
main_teacher_id must point to an enabled teacher in the same tenant and campus when present.
main_teacher_id must be authorized for course_id when present.
classroom_id must point to an enabled classroom in the same tenant and campus when present.
end_date must be greater than or equal to start_date when both are present.
lesson_units must be greater than 0.00 and rounded to two decimals.
Class cannot be deleted when any lesson references it.
```

`ClassStudentService` methods:

```php
public function listStudents(int $classId, EducationUserContext $context): array
public function saveStudents(int $classId, array $students, EducationUserContext $context, ?int $operatorId): array
public function activeSnapshotsForScheduling(int $classId, EducationUserContext $context): array
```

Class-student rules:

```text
Class must exist and be visible in current tenant and campus scope.
Every student_id must point to an enabled V1-01 student in the same tenant and campus.
Every student must have an active V1-02 course account for the class course.
The number of active students cannot exceed max_students when max_students is greater than 0.
One-to-one classes accept exactly one active student.
saveStudents replaces active membership status; removed active students become left with left_at set.
Paused and left students are excluded from future lesson snapshots.
```

`SchedulingConflictService` methods:

```php
public function checkSingle(array $payload, EducationUserContext $context, ?int $excludeLessonId = null): array
public function assertNoConflict(array $payload, EducationUserContext $context, ?int $excludeLessonId = null): void
public function checkBatch(array $generatedLessons, EducationUserContext $context): array
public function teacherConflicts(int $teacherId, string $startAt, string $endAt, int $tenantId, int $campusId, ?int $excludeLessonId = null): array
public function classroomConflicts(?int $classroomId, string $startAt, string $endAt, int $tenantId, int $campusId, ?int $excludeLessonId = null): array
public function classConflicts(int $classId, string $startAt, string $endAt, int $tenantId, int $campusId, ?int $excludeLessonId = null): array
public function studentConflicts(array $studentIds, string $startAt, string $endAt, int $tenantId, int $campusId, ?int $excludeLessonId = null): array
```

Conflict result shape:

```json
{
  "has_conflict": true,
  "conflicts": [
    {
      "conflict_type": "teacher",
      "message": "teacher time conflict",
      "lesson_ids": [9001],
      "start_at": "2026-06-12 10:00:00",
      "end_at": "2026-06-12 11:00:00"
    }
  ]
}
```

`SchedulingService` methods:

```php
public function calendar(array $filters, EducationUserContext $context): array
public function conflictCheck(array $payload, EducationUserContext $context): array
public function scheduleSingle(array $data, EducationUserContext $context, ?int $operatorId): array
public function scheduleBatch(array $data, EducationUserContext $context, ?int $operatorId): array
protected function generateBatchLessonPayloads(array $data): array
protected function buildLessonSnapshots(EducationClass $class, string $lessonUnits, EducationUserContext $context): array
```

Single scheduling transaction:

```text
1. Validate campus scope from EducationUserContext.
2. Validate class is enabled and belongs to the same tenant and campus.
3. Validate course matches the class course.
4. Validate teacher is enabled, same tenant/campus, and authorized for the class course.
5. Validate classroom is enabled and same tenant/campus when classroom_id is present.
6. Validate start_at < end_at and compute duration_minutes.
7. Load active class-student snapshots with active course accounts.
8. Reject empty active class-student set with code 422.
9. Run teacher, classroom, class, and student conflict checks.
10. Generate lesson_no as LES + yyyyMMddHHmmss + tenant short id + random 4 digits and retry up to 3 times on unique-key collision.
11. Create scheduled lesson with class, course, teacher, classroom, and snapshot fields.
12. Bulk create planned lesson-student snapshots.
13. Dispatch audit event `education.academic.lesson.scheduled`.
14. Commit the transaction and return lesson plus lesson_students summary.
```

Batch scheduling transaction:

```text
1. Validate recurrence date range, weekdays, start_time, and end_time.
2. Generate target lesson payloads for every matching date.
3. Reject when generated lesson count is 0 with code 422.
4. Reject when generated lesson count is greater than 180 with code 422.
5. Run conflict checks for every generated lesson and also detect conflicts within the generated batch itself.
6. If any conflict exists, return code 409 and persist no lesson.
7. Generate one schedule_batch_no as BATCH + yyyyMMddHHmmss + tenant short id + random 4 digits.
8. Create every lesson and its lesson-student snapshots inside one transaction.
9. Dispatch audit event `education.academic.lesson.batch_scheduled`.
10. Commit the transaction and return batch_no, created_count, and lesson ids.
```

`LessonService` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function detail(int $id, EducationUserContext $context): array
public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationLesson
public function cancel(int $id, string $reason, EducationUserContext $context, ?int $operatorId): EducationLesson
public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
```

Lesson update and cancellation rules:

```text
Only scheduled lessons can be updated.
Updating teacher_id requires teacher-course authorization for the lesson course.
Updating start_at/end_at/classroom_id/teacher_id reruns conflict checks.
Updating lesson_units updates planned lesson-student snapshot lesson_units when lesson status is scheduled.
Only scheduled lessons can be cancelled.
Cancelling a lesson marks lesson status cancelled and lesson-student rows cancelled.
Cancelling a lesson does not change any V1-02 course account balance.
Completed lessons cannot be cancelled or deleted in V1-03.
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

`ClassSaveRequest` rules:

```php
[
    'campus_id' => ['required', 'integer', 'min:1'],
    'course_id' => ['required', 'integer', 'min:1'],
    'main_teacher_id' => ['nullable', 'integer', 'min:1'],
    'classroom_id' => ['nullable', 'integer', 'min:1'],
    'code' => ['required', 'string', 'max:64'],
    'name' => ['required', 'string', 'max:120'],
    'class_type' => ['required', 'in:group,one_to_one'],
    'max_students' => ['required', 'integer', 'min:0', 'max:9999'],
    'start_date' => ['nullable', 'date_format:Y-m-d'],
    'end_date' => ['nullable', 'date_format:Y-m-d'],
    'lesson_units' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
    'status' => ['required', 'in:enabled,disabled'],
    'schedule_note' => ['nullable', 'string', 'max:500'],
    'remark' => ['nullable', 'string', 'max:500'],
]
```

`ClassStatusRequest` rules:

```php
[
    'status' => ['required', 'in:enabled,disabled'],
]
```

`ClassStudentSaveRequest` rules:

```php
[
    'students' => ['required', 'array'],
    'students.*.student_id' => ['required', 'integer', 'min:1'],
    'students.*.status' => ['nullable', 'in:active,paused,left'],
    'students.*.remark' => ['nullable', 'string', 'max:500'],
]
```

`LessonPageRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'tenant_id' => ['nullable', 'integer', 'min:1'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'class_id' => ['nullable', 'integer', 'min:1'],
    'course_id' => ['nullable', 'integer', 'min:1'],
    'teacher_id' => ['nullable', 'integer', 'min:1'],
    'classroom_id' => ['nullable', 'integer', 'min:1'],
    'status' => ['nullable', 'in:scheduled,cancelled,completed'],
    'keyword' => ['nullable', 'string', 'max:120'],
    'start_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
]
```

`LessonUpdateRequest` rules:

```php
[
    'teacher_id' => ['required', 'integer', 'min:1'],
    'classroom_id' => ['nullable', 'integer', 'min:1'],
    'title' => ['required', 'string', 'max:160'],
    'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'lesson_units' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
    'remark' => ['nullable', 'string', 'max:500'],
]
```

`LessonCancelRequest` rules:

```php
[
    'cancel_reason' => ['required', 'string', 'max:500'],
]
```

`ScheduleCalendarRequest` rules:

```php
[
    'campus_id' => ['required', 'integer', 'min:1'],
    'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'class_id' => ['nullable', 'integer', 'min:1'],
    'teacher_id' => ['nullable', 'integer', 'min:1'],
    'classroom_id' => ['nullable', 'integer', 'min:1'],
    'status' => ['nullable', 'in:scheduled,cancelled,completed'],
]
```

`ScheduleConflictCheckRequest` rules:

```php
[
    'campus_id' => ['required', 'integer', 'min:1'],
    'class_id' => ['required', 'integer', 'min:1'],
    'teacher_id' => ['required', 'integer', 'min:1'],
    'classroom_id' => ['nullable', 'integer', 'min:1'],
    'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'exclude_lesson_id' => ['nullable', 'integer', 'min:1'],
]
```

`SingleLessonScheduleRequest` rules:

```php
[
    'campus_id' => ['required', 'integer', 'min:1'],
    'class_id' => ['required', 'integer', 'min:1'],
    'teacher_id' => ['required', 'integer', 'min:1'],
    'classroom_id' => ['nullable', 'integer', 'min:1'],
    'title' => ['required', 'string', 'max:160'],
    'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'lesson_units' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
    'remark' => ['nullable', 'string', 'max:500'],
]
```

`BatchLessonScheduleRequest` rules:

```php
[
    'campus_id' => ['required', 'integer', 'min:1'],
    'class_id' => ['required', 'integer', 'min:1'],
    'teacher_id' => ['required', 'integer', 'min:1'],
    'classroom_id' => ['nullable', 'integer', 'min:1'],
    'title_template' => ['required', 'string', 'max:160'],
    'start_date' => ['required', 'date_format:Y-m-d'],
    'end_date' => ['required', 'date_format:Y-m-d'],
    'weekdays' => ['required', 'array', 'min:1', 'max:7'],
    'weekdays.*' => ['required', 'integer', 'between:1,7'],
    'start_time' => ['required', 'date_format:H:i'],
    'end_time' => ['required', 'date_format:H:i'],
    'lesson_units' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
    'remark' => ['nullable', 'string', 'max:500'],
]
```

Validation messages:

```text
page.required: page is required
pageSize.between: pageSize must be between 1 and 100
campus_id.required: campus_id is required
course_id.required: course_id is required
class_id.required: class_id is required
teacher_id.required: teacher_id is required
code.required: code is required
name.required: name is required
title.required: title is required
start_at.required: start_at is required
end_at.required: end_at is required
lesson_units.min: lesson_units must be greater than 0
students.required: students is required
students.*.student_id.required: student_id is required
weekdays.required: weekdays is required
cancel_reason.required: cancel_reason is required
status.in: status has an invalid value
```

### Controllers

Controller base:

```text
Use `#[Controller(prefix: 'admin/education/academic/<resource>')]`.
Use MineAdmin auth middleware.
Use Permission attributes on every endpoint.
Use OperationMiddleware on create/update/status/delete/save-students/schedule/cancel endpoints.
Return MineAdmin Result shape through `$this->success(...)`.
Resolve current EducationUserContext through F02 context resolver.
```

`ClassController`:

```php
public function page(ClassPageRequest $request): Result
public function create(ClassSaveRequest $request): Result
public function update(int $id, ClassSaveRequest $request): Result
public function status(int $id, ClassStatusRequest $request): Result
public function delete(int $id): Result
public function students(int $id): Result
public function saveStudents(int $id, ClassStudentSaveRequest $request): Result
```

`LessonController`:

```php
public function page(LessonPageRequest $request): Result
public function detail(int $id): Result
public function update(int $id, LessonUpdateRequest $request): Result
public function cancel(int $id, LessonCancelRequest $request): Result
public function delete(int $id): Result
```

`LessonScheduleController`:

```php
public function calendar(ScheduleCalendarRequest $request): Result
public function conflictCheck(ScheduleConflictCheckRequest $request): Result
public function scheduleSingle(SingleLessonScheduleRequest $request): Result
public function scheduleBatch(BatchLessonScheduleRequest $request): Result
```

### Schemas

Schema fields:

```text
ClassSchema: id, tenant_id, campus_id, course_id, course_name, main_teacher_id, main_teacher_name, classroom_id, classroom_name, code, name, class_type, max_students, active_student_count, start_date, end_date, lesson_units, status, schedule_note, remark, created_at, updated_at
ClassStudentSchema: id, tenant_id, campus_id, class_id, course_id, student_id, account_id, student_name_snapshot, student_no_snapshot, available_units, status, joined_at, left_at, remark
LessonSchema: id, tenant_id, campus_id, lesson_no, class_id, class_name_snapshot, course_id, course_name_snapshot, teacher_id, teacher_name_snapshot, classroom_id, classroom_name_snapshot, title, start_at, end_at, duration_minutes, lesson_units, student_count, status, source_type, schedule_batch_no, cancelled_at, cancel_reason, remark, created_at, updated_at
LessonStudentSchema: id, tenant_id, campus_id, lesson_id, class_id, course_id, student_id, account_id, student_name_snapshot, student_no_snapshot, lesson_units, status, remark
SchedulingConflictSchema: has_conflict, conflicts.conflict_type, conflicts.message, conflicts.lesson_ids, conflicts.student_ids, conflicts.start_at, conflicts.end_at
```

## API Contract

### Endpoint Matrix

| API | Permission | Caller | Isolation | Audit |
| --- | --- | --- | --- | --- |
| `GET /admin/education/academic/classes/page` | `education:academic:class:page` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `POST /admin/education/academic/classes` | `education:academic:class:create` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.class.created` |
| `PUT /admin/education/academic/classes/{id}` | `education:academic:class:update` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.class.updated` |
| `PUT /admin/education/academic/classes/{id}/status` | `education:academic:class:status` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.class.status_changed` |
| `DELETE /admin/education/academic/classes/{id}` | `education:academic:class:delete` | tenant admin, principal | tenant and campus scope | `education.academic.class.deleted` |
| `GET /admin/education/academic/classes/{id}/students` | `education:academic:class-student:page` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `PUT /admin/education/academic/classes/{id}/students` | `education:academic:class-student:save` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.class_student.saved` |
| `GET /admin/education/academic/lessons/page` | `education:academic:lesson:page` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `GET /admin/education/academic/lessons/{id}` | `education:academic:lesson:detail` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `PUT /admin/education/academic/lessons/{id}` | `education:academic:lesson:update` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.lesson.updated` |
| `PUT /admin/education/academic/lessons/{id}/cancel` | `education:academic:lesson:cancel` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.lesson.cancelled` |
| `DELETE /admin/education/academic/lessons/{id}` | `education:academic:lesson:delete` | tenant admin, principal | tenant and campus scope | `education.academic.lesson.deleted` |
| `GET /admin/education/academic/lesson-schedule/calendar` | `education:academic:lesson-schedule:calendar` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `POST /admin/education/academic/lesson-schedule/conflict-check` | `education:academic:lesson-schedule:conflict-check` | tenant admin, principal, academic_staff | tenant and campus scope | none |
| `POST /admin/education/academic/lesson-schedule/single` | `education:academic:lesson-schedule:create` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.lesson.scheduled` |
| `POST /admin/education/academic/lesson-schedule/batch` | `education:academic:lesson-schedule:batch` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.lesson.batch_scheduled` |

Headers for tenant-scoped callers:

```text
Authorization: Bearer test-admin-token
X-Tenant-Id: 1001
X-Campus-Id: 2001
X-Request-Id: req-v1-class-schedule-001
```

### Resource Examples

Single schedule request:

```json
{
  "campus_id": 2001,
  "class_id": 701,
  "teacher_id": 201,
  "classroom_id": 1,
  "title": "Art Basics Lesson 1",
  "start_at": "2026-06-12 10:00:00",
  "end_at": "2026-06-12 11:00:00",
  "lesson_units": 1.00,
  "remark": "First lesson"
}
```

Single schedule success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "lesson": {
      "id": 9001,
      "lesson_no": "LES2026061210000010014821",
      "class_id": 701,
      "teacher_id": 201,
      "classroom_id": 1,
      "title": "Art Basics Lesson 1",
      "start_at": "2026-06-12 10:00:00",
      "end_at": "2026-06-12 11:00:00",
      "lesson_units": "1.00",
      "student_count": 2,
      "status": "scheduled"
    },
    "lesson_students": {
      "created_count": 2
    }
  }
}
```

Teacher conflict failure:

```json
{
  "code": 409,
  "message": "teacher time conflict",
  "data": {
    "conflict_type": "teacher",
    "lesson_ids": [9000],
    "teacher_id": 201
  }
}
```

Student conflict failure:

```json
{
  "code": 409,
  "message": "student time conflict",
  "data": {
    "conflict_type": "student",
    "lesson_ids": [8999],
    "student_ids": [101]
  }
}
```

### Endpoint-Level Request/Response/Failure Catalog

Use this catalog as the controller test fixture set. Each API has a concrete request, success response, validation failure response, and business failure response.

```json
[
  {
    "api": "GET /admin/education/academic/classes/page",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "course_id": 301, "keyword": "Art A", "status": "enabled"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 701, "tenant_id": 1001, "campus_id": 2001, "course_id": 301, "code": "ART-A", "name": "Art A", "class_type": "group", "status": "enabled"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "page is required", "data": {"field": "page"}},
    "business_failure": {"code": 403, "message": "campus is outside current context", "data": {"campus_id": 9999}}
  },
  {
    "api": "POST /admin/education/academic/classes",
    "request": {"body": {"campus_id": 2001, "course_id": 301, "main_teacher_id": 201, "classroom_id": 1, "code": "ART-A", "name": "Art A", "class_type": "group", "max_students": 12, "start_date": "2026-06-10", "end_date": "2026-12-31", "lesson_units": 1, "status": "enabled", "schedule_note": "Tuesday and Thursday", "remark": "New class"}},
    "success": {"code": 200, "message": "success", "data": {"id": 701, "tenant_id": 1001, "campus_id": 2001, "code": "ART-A", "name": "Art A", "status": "enabled"}},
    "validation_failure": {"code": 422, "message": "code is required", "data": {"field": "code"}},
    "business_failure": {"code": 409, "message": "class code already exists", "data": {"campus_id": 2001, "code": "ART-A"}}
  },
  {
    "api": "PUT /admin/education/academic/classes/{id}",
    "request": {"path": {"id": 701}, "body": {"campus_id": 2001, "course_id": 301, "main_teacher_id": 201, "classroom_id": 1, "code": "ART-A2", "name": "Art A Updated", "class_type": "group", "max_students": 16, "start_date": "2026-06-10", "end_date": "2026-12-31", "lesson_units": 1, "status": "enabled", "schedule_note": "Updated", "remark": "Updated class"}},
    "success": {"code": 200, "message": "success", "data": {"id": 701, "code": "ART-A2", "name": "Art A Updated", "status": "enabled"}},
    "validation_failure": {"code": 422, "message": "name is required", "data": {"field": "name"}},
    "business_failure": {"code": 404, "message": "class not found in current context", "data": {"id": 701}}
  },
  {
    "api": "PUT /admin/education/academic/classes/{id}/status",
    "request": {"path": {"id": 701}, "body": {"status": "disabled"}},
    "success": {"code": 200, "message": "success", "data": {"id": 701, "status": "disabled"}},
    "validation_failure": {"code": 422, "message": "status has an invalid value", "data": {"field": "status"}},
    "business_failure": {"code": 403, "message": "class is outside current campus scope", "data": {"id": 701, "campus_id": 9999}}
  },
  {
    "api": "DELETE /admin/education/academic/classes/{id}",
    "request": {"path": {"id": 701}},
    "success": {"code": 200, "message": "success", "data": true},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 409, "message": "class is referenced by lessons", "data": {"id": 701}}
  },
  {
    "api": "GET /admin/education/academic/classes/{id}/students",
    "request": {"path": {"id": 701}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"student_id": 101, "student_name_snapshot": "Student Zhang", "student_no_snapshot": "S20260610001", "account_id": 601, "status": "active"}]}},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 404, "message": "class not found in current context", "data": {"id": 701}}
  },
  {
    "api": "PUT /admin/education/academic/classes/{id}/students",
    "request": {"path": {"id": 701}, "body": {"students": [{"student_id": 101, "status": "active", "remark": "Main student"}, {"student_id": 102, "status": "active", "remark": null}]}},
    "success": {"code": 200, "message": "success", "data": {"class_id": 701, "active_count": 2, "students": [{"student_id": 101, "status": "active"}, {"student_id": 102, "status": "active"}]}},
    "validation_failure": {"code": 422, "message": "students is required", "data": {"field": "students"}},
    "business_failure": {"code": 422, "message": "student has no active course account for this course", "data": {"student_id": 102, "course_id": 301}}
  },
  {
    "api": "GET /admin/education/academic/lessons/page",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "class_id": 701, "status": "scheduled", "start_at": "2026-06-12 00:00:00", "end_at": "2026-06-12 23:59:59"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 9001, "lesson_no": "LES2026061210000010014821", "title": "Art Basics Lesson 1", "class_name_snapshot": "Art A", "teacher_name_snapshot": "Teacher Wang", "start_at": "2026-06-12 10:00:00", "status": "scheduled"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "pageSize must be between 1 and 100", "data": {"field": "pageSize"}},
    "business_failure": {"code": 403, "message": "campus is outside current context", "data": {"campus_id": 9999}}
  },
  {
    "api": "GET /admin/education/academic/lessons/{id}",
    "request": {"path": {"id": 9001}},
    "success": {"code": 200, "message": "success", "data": {"id": 9001, "lesson_no": "LES2026061210000010014821", "title": "Art Basics Lesson 1", "status": "scheduled", "lesson_students": [{"student_id": 101, "student_name_snapshot": "Student Zhang", "status": "planned"}]}},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 404, "message": "lesson not found in current context", "data": {"id": 9001}}
  },
  {
    "api": "PUT /admin/education/academic/lessons/{id}",
    "request": {"path": {"id": 9001}, "body": {"teacher_id": 201, "classroom_id": 1, "title": "Art Basics Lesson 1 Updated", "start_at": "2026-06-12 10:30:00", "end_at": "2026-06-12 11:30:00", "lesson_units": 1, "remark": "Updated time"}},
    "success": {"code": 200, "message": "success", "data": {"id": 9001, "title": "Art Basics Lesson 1 Updated", "start_at": "2026-06-12 10:30:00", "end_at": "2026-06-12 11:30:00", "status": "scheduled"}},
    "validation_failure": {"code": 422, "message": "start_at is required", "data": {"field": "start_at"}},
    "business_failure": {"code": 409, "message": "classroom time conflict", "data": {"conflict_type": "classroom", "lesson_ids": [9002]}}
  },
  {
    "api": "PUT /admin/education/academic/lessons/{id}/cancel",
    "request": {"path": {"id": 9001}, "body": {"cancel_reason": "Teacher unavailable"}},
    "success": {"code": 200, "message": "success", "data": {"id": 9001, "status": "cancelled", "cancel_reason": "Teacher unavailable"}},
    "validation_failure": {"code": 422, "message": "cancel_reason is required", "data": {"field": "cancel_reason"}},
    "business_failure": {"code": 409, "message": "only scheduled lessons can be cancelled", "data": {"id": 9001, "status": "completed"}}
  },
  {
    "api": "DELETE /admin/education/academic/lessons/{id}",
    "request": {"path": {"id": 9001}},
    "success": {"code": 200, "message": "success", "data": true},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 409, "message": "lesson is referenced by attendance or consumption data", "data": {"id": 9001}}
  },
  {
    "api": "GET /admin/education/academic/lesson-schedule/calendar",
    "request": {"query": {"campus_id": 2001, "start_at": "2026-06-01 00:00:00", "end_at": "2026-06-30 23:59:59", "teacher_id": 201, "status": "scheduled"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 9001, "title": "Art Basics Lesson 1", "start_at": "2026-06-12 10:00:00", "end_at": "2026-06-12 11:00:00", "teacher_name_snapshot": "Teacher Wang", "classroom_name_snapshot": "A101", "status": "scheduled"}]}},
    "validation_failure": {"code": 422, "message": "campus_id is required", "data": {"field": "campus_id"}},
    "business_failure": {"code": 403, "message": "campus is outside current context", "data": {"campus_id": 9999}}
  },
  {
    "api": "POST /admin/education/academic/lesson-schedule/conflict-check",
    "request": {"body": {"campus_id": 2001, "class_id": 701, "teacher_id": 201, "classroom_id": 1, "start_at": "2026-06-12 10:00:00", "end_at": "2026-06-12 11:00:00"}},
    "success": {"code": 200, "message": "success", "data": {"has_conflict": false, "conflicts": []}},
    "validation_failure": {"code": 422, "message": "class_id is required", "data": {"field": "class_id"}},
    "business_failure": {"code": 409, "message": "student time conflict", "data": {"conflict_type": "student", "lesson_ids": [8999], "student_ids": [101]}}
  },
  {
    "api": "POST /admin/education/academic/lesson-schedule/single",
    "request": {"body": {"campus_id": 2001, "class_id": 701, "teacher_id": 201, "classroom_id": 1, "title": "Art Basics Lesson 1", "start_at": "2026-06-12 10:00:00", "end_at": "2026-06-12 11:00:00", "lesson_units": 1, "remark": "First lesson"}},
    "success": {"code": 200, "message": "success", "data": {"lesson": {"id": 9001, "lesson_no": "LES2026061210000010014821", "status": "scheduled", "student_count": 2}, "lesson_students": {"created_count": 2}}},
    "validation_failure": {"code": 422, "message": "title is required", "data": {"field": "title"}},
    "business_failure": {"code": 409, "message": "teacher time conflict", "data": {"conflict_type": "teacher", "lesson_ids": [9000], "teacher_id": 201}}
  },
  {
    "api": "POST /admin/education/academic/lesson-schedule/batch",
    "request": {"body": {"campus_id": 2001, "class_id": 701, "teacher_id": 201, "classroom_id": 1, "title_template": "Art Basics {date}", "start_date": "2026-06-12", "end_date": "2026-07-31", "weekdays": [2, 4], "start_time": "10:00", "end_time": "11:00", "lesson_units": 1, "remark": "Summer schedule"}},
    "success": {"code": 200, "message": "success", "data": {"schedule_batch_no": "BATCH2026061210000010014821", "created_count": 14, "lesson_ids": [9001, 9002]}},
    "validation_failure": {"code": 422, "message": "weekdays is required", "data": {"field": "weekdays"}},
    "business_failure": {"code": 409, "message": "batch schedule has conflicts", "data": {"conflicts": [{"conflict_type": "classroom", "lesson_ids": [8801], "start_at": "2026-06-18 10:00:00"}]}}
  }
]
```

## PC Admin Page Tasks

### API Client

Create:

```text
mineadmin-education-saas/admin-web/src/api/education/academic/classSchedule.ts
```

Types:

```ts
export type AcademicRecordStatus = 'enabled' | 'disabled'
export type ClassType = 'group' | 'one_to_one'
export type ClassStudentStatus = 'active' | 'paused' | 'left'
export type LessonStatus = 'scheduled' | 'cancelled' | 'completed'
export type LessonStudentStatus = 'planned' | 'cancelled'
export type ScheduleSourceType = 'manual' | 'batch'

export interface PageResult<T> {
  list: T[]
  total: number
}

export interface ClassRecord {
  id: number
  tenant_id: number
  campus_id: number
  course_id: number
  course_name?: string
  main_teacher_id?: number | null
  main_teacher_name?: string | null
  classroom_id?: number | null
  classroom_name?: string | null
  code: string
  name: string
  class_type: ClassType
  max_students: number
  active_student_count?: number
  lesson_units: string
  status: AcademicRecordStatus
}

export interface ClassSavePayload {
  campus_id: number
  course_id: number
  main_teacher_id?: number | null
  classroom_id?: number | null
  code: string
  name: string
  class_type: ClassType
  max_students: number
  start_date?: string | null
  end_date?: string | null
  lesson_units: number
  status: AcademicRecordStatus
  schedule_note?: string | null
  remark?: string | null
}

export interface SingleLessonSchedulePayload {
  campus_id: number
  class_id: number
  teacher_id: number
  classroom_id?: number | null
  title: string
  start_at: string
  end_at: string
  lesson_units: number
  remark?: string | null
}

export interface BatchLessonSchedulePayload {
  campus_id: number
  class_id: number
  teacher_id: number
  classroom_id?: number | null
  title_template: string
  start_date: string
  end_date: string
  weekdays: number[]
  start_time: string
  end_time: string
  lesson_units: number
  remark?: string | null
}

export interface LessonRecord {
  id: number
  lesson_no: string
  class_id: number
  class_name_snapshot: string
  course_id: number
  course_name_snapshot: string
  teacher_id: number
  teacher_name_snapshot: string
  classroom_id?: number | null
  classroom_name_snapshot?: string | null
  title: string
  start_at: string
  end_at: string
  lesson_units: string
  student_count: number
  status: LessonStatus
}
```

Methods:

```ts
export function pageClasses(params: Record<string, unknown>): Promise<PageResult<ClassRecord>>
export function createClass(payload: ClassSavePayload): Promise<ClassRecord>
export function updateClass(id: number, payload: ClassSavePayload): Promise<ClassRecord>
export function changeClassStatus(id: number, status: AcademicRecordStatus): Promise<ClassRecord>
export function deleteClass(id: number): Promise<boolean>
export function getClassStudents(id: number): Promise<{ list: Array<Record<string, unknown>> }>
export function saveClassStudents(id: number, payload: { students: Array<Record<string, unknown>> }): Promise<Record<string, unknown>>
export function pageLessons(params: Record<string, unknown>): Promise<PageResult<LessonRecord>>
export function getLesson(id: number): Promise<Record<string, unknown>>
export function updateLesson(id: number, payload: SingleLessonSchedulePayload): Promise<LessonRecord>
export function cancelLesson(id: number, cancel_reason: string): Promise<LessonRecord>
export function deleteLesson(id: number): Promise<boolean>
export function calendarLessons(params: Record<string, unknown>): Promise<{ list: LessonRecord[] }>
export function checkScheduleConflict(payload: Record<string, unknown>): Promise<Record<string, unknown>>
export function scheduleSingleLesson(payload: SingleLessonSchedulePayload): Promise<Record<string, unknown>>
export function scheduleBatchLessons(payload: BatchLessonSchedulePayload): Promise<Record<string, unknown>>
```

### Routes and Menus

Modify:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Route entries:

```text
Route: /education/academic/classes
Route name: EducationAcademicClassList
Menu: 教务 SaaS / 教务基础 / 班级
Permission: education:academic:class:page
Page file: admin-web/src/views/education/academic/ClassList.vue

Route: /education/academic/lesson-schedule
Route name: EducationAcademicLessonScheduleCalendar
Menu: 教务 SaaS / 排课课节 / 排课日历
Permission: education:academic:lesson-schedule:calendar
Page file: admin-web/src/views/education/academic/LessonScheduleCalendar.vue

Route: /education/academic/lessons
Route name: EducationAcademicLessonList
Menu: 教务 SaaS / 排课课节 / 课节列表
Permission: education:academic:lesson:page
Page file: admin-web/src/views/education/academic/LessonList.vue
```

### ClassList

File:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/ClassList.vue
```

Component files:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/components/ClassForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/ClassStudentDrawer.vue
```

Search fields:

```text
campus_id, course_id, main_teacher_id, keyword, status
```

Table columns:

```text
code, name, course_name, main_teacher_name, classroom_name, class_type, max_students, active_student_count, lesson_units, status, updated_at
```

Form fields:

```text
campus_id selector
course_id selector filtered by enabled course
main_teacher_id selector filtered by teacher-course authorization
classroom_id selector filtered by enabled classroom
code input
name input
class_type segmented control
max_students number input
start_date and end_date date pickers
lesson_units decimal input
status segmented control
schedule_note textarea
remark textarea
```

Actions and states:

```text
create button: education:academic:class:create
edit button: education:academic:class:update
enable/disable button: education:academic:class:status
student membership button: education:academic:class-student:page
save student membership button: education:academic:class-student:save
delete button: education:academic:class:delete
loading: table skeleton while pageClasses is pending
empty: show MineAdmin empty state when total is 0
error: show API message and keep previous filters
permission: hide action buttons without matching permission code
submit success: close form drawer and reload first page
validation failure: keep drawer open and show field message
business failure: keep drawer open and show duplicate code, disabled course, or teacher authorization message
student drawer success: close drawer and reload row active_student_count
```

### LessonScheduleCalendar

File:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/LessonScheduleCalendar.vue
```

Component files:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/components/SingleLessonScheduleDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/BatchLessonScheduleDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/ScheduleConflictDrawer.vue
```

Filter fields:

```text
campus_id, class_id, teacher_id, classroom_id, status, calendar range
```

Calendar event fields:

```text
title, start_at, end_at, class_name_snapshot, teacher_name_snapshot, classroom_name_snapshot, status
```

Single schedule drawer fields:

```text
campus_id selector
class_id selector filtered by enabled class
teacher_id selector filtered by class course authorization
classroom_id selector filtered by enabled classroom
title input
start_at and end_at datetime pickers
lesson_units decimal input defaulting to class.lesson_units
remark textarea
conflict check button
```

Batch schedule drawer fields:

```text
campus_id selector
class_id selector
teacher_id selector
classroom_id selector
title_template input
start_date and end_date date pickers
weekdays checkbox group
start_time and end_time time pickers
lesson_units decimal input
remark textarea
conflict preview button
```

Actions and states:

```text
calendar read: education:academic:lesson-schedule:calendar
conflict check button: education:academic:lesson-schedule:conflict-check
single schedule button: education:academic:lesson-schedule:create
batch schedule button: education:academic:lesson-schedule:batch
loading: calendar skeleton while calendarLessons is pending
empty: show no lessons in selected range
error: show API message and keep filters
conflict success: show green no-conflict result
conflict failure: open conflict drawer with conflict_type and lesson_ids
single schedule success: close drawer and reload calendar and lesson list
batch schedule success: show schedule_batch_no and created_count, reload calendar
batch conflict: keep drawer open and show conflict table
```

### LessonList

File:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/LessonList.vue
```

Component files:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/components/LessonForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/LessonDetailDrawer.vue
```

Search fields:

```text
campus_id, class_id, course_id, teacher_id, classroom_id, status, start_at, end_at, keyword
```

Table columns:

```text
lesson_no, title, class_name_snapshot, course_name_snapshot, teacher_name_snapshot, classroom_name_snapshot, start_at, end_at, lesson_units, student_count, status, source_type
```

Detail drawer sections:

```text
lesson base info
class/course/teacher/classroom snapshots
lesson student snapshot list
cancel reason when cancelled
```

Actions and states:

```text
detail button: education:academic:lesson:detail
edit button: education:academic:lesson:update
cancel button: education:academic:lesson:cancel
delete button: education:academic:lesson:delete
loading/empty/error/permission states match ClassList
edit success: close form and reload row
edit conflict: keep form open and show conflict drawer
cancel success: reload row and mark lesson cancelled
cancel business failure: keep confirmation dialog open and show status message
delete business failure: keep row and show attendance/consumption reference message
```

## Teacher / Guardian Mobile Page Tasks

This module has no teacher or guardian page because V1-03 creates admin-side scheduling data only; visible teacher and guardian lesson experiences are implemented in V1-06 and V1-07.

Mobile dependency notes:

```text
V1-06 teacher mobile will read `edu_lessons` and `edu_lesson_students` through teacher-scoped APIs created in V1-06.
V1-07 guardian mobile will read bound student schedules through guardian-scoped APIs created in V1-07.
No mobile-uniapp API client or pages are created in V1-03.
Run the mobile H5 build to prove V1-03 did not break the existing mobile shell.
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
| `ClassScheduleMigrationTest.php` | `test_class_schedule_tables_exist` | all four V1-03 tables exist |
| `ClassScheduleMigrationTest.php` | `test_class_columns_and_indexes_exist` | `edu_classes` has documented columns and `uk_edu_classes_tenant_campus_code` |
| `ClassScheduleMigrationTest.php` | `test_class_student_account_columns_exist` | `edu_class_students` has `account_id` and unique class-student index |
| `ClassScheduleMigrationTest.php` | `test_lesson_time_conflict_indexes_exist` | teacher, classroom, class, and campus time indexes exist |
| `ClassScheduleMigrationTest.php` | `test_lesson_student_snapshot_columns_exist` | lesson student snapshot columns exist |
| `ClassScheduleMigrationTest.php` | `test_rollback_drops_tables_in_dependency_order` | rollback drops lesson students before lessons and classes last |

### Repository Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `ClassRepositoryTest.php` | `test_page_filters_by_tenant_campus_course_keyword_status` | cross-tenant and cross-campus classes are absent |
| `ClassRepositoryTest.php` | `test_exists_code_ignores_deleted_rows_and_current_id` | duplicate active code returns true and current id update returns false |
| `ClassStudentRepositoryTest.php` | `test_replace_students_marks_removed_students_left` | removed active student status becomes left with left_at |
| `ClassStudentRepositoryTest.php` | `test_active_students_by_class_excludes_paused_and_left` | only active students are returned |
| `LessonRepositoryTest.php` | `test_calendar_filters_by_range_teacher_classroom_and_status` | returned events match requested range and filters |
| `LessonRepositoryTest.php` | `test_overlapping_lessons_excludes_cancelled_and_current_lesson` | cancelled lesson and excludeLessonId are absent |
| `LessonStudentRepositoryTest.php` | `test_overlapping_student_lessons_returns_conflicting_student_rows` | overlapping planned lesson student is returned |
| `LessonStudentRepositoryTest.php` | `test_cancel_by_lesson_marks_snapshots_cancelled` | all lesson student rows become cancelled |

### Service Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `ClassServiceTest.php` | `test_create_rejects_duplicate_class_code` | service throws code 409 with code |
| `ClassServiceTest.php` | `test_create_rejects_teacher_without_course_authorization` | service throws code 422 with teacher_id |
| `ClassServiceTest.php` | `test_delete_rejects_class_with_lesson_reference` | service throws code 409 |
| `ClassStudentServiceTest.php` | `test_save_students_requires_active_course_account` | service throws code 422 with student_id and course_id |
| `ClassStudentServiceTest.php` | `test_save_students_rejects_max_student_overflow` | service throws code 409 with max_students |
| `ClassStudentServiceTest.php` | `test_one_to_one_class_accepts_exactly_one_active_student` | two active students return code 409 |
| `SchedulingConflictServiceTest.php` | `test_teacher_conflict_detects_overlap` | conflict_type teacher and lesson id are returned |
| `SchedulingConflictServiceTest.php` | `test_classroom_conflict_detects_overlap` | conflict_type classroom and lesson id are returned |
| `SchedulingConflictServiceTest.php` | `test_class_conflict_detects_overlap` | conflict_type class and lesson id are returned |
| `SchedulingConflictServiceTest.php` | `test_student_conflict_detects_overlap` | conflict_type student and student id are returned |
| `SchedulingConflictServiceTest.php` | `test_cancelled_lessons_do_not_conflict` | no conflict returned |
| `SchedulingServiceTest.php` | `test_schedule_single_creates_lesson_and_student_snapshots_transactionally` | lesson is scheduled and snapshot count equals active class students |
| `SchedulingServiceTest.php` | `test_schedule_single_rejects_empty_active_class_students` | service throws code 422 |
| `SchedulingServiceTest.php` | `test_batch_schedule_is_all_or_nothing_when_conflict_exists` | no generated lessons persist after conflict |
| `SchedulingServiceTest.php` | `test_batch_schedule_generates_expected_weekdays` | created_count matches requested weekdays and date range |
| `LessonServiceTest.php` | `test_update_scheduled_lesson_reruns_conflict_checks` | conflicting update returns code 409 |
| `LessonServiceTest.php` | `test_update_lesson_units_updates_planned_snapshots` | lesson student lesson_units equals updated value |
| `LessonServiceTest.php` | `test_cancel_lesson_cancels_lesson_students_without_account_change` | lesson and snapshots cancelled, account available_units unchanged |
| `LessonServiceTest.php` | `test_delete_rejects_completed_lesson` | service throws code 409 |

### Controller/API Feature Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `ClassScheduleAdminApiTest.php` | `test_class_crud_returns_mineadmin_shape` | create/update/status/page responses use `{code,message,data}` |
| `ClassScheduleAdminApiTest.php` | `test_class_student_save_returns_active_count` | response contains class_id and active_count |
| `ClassScheduleAdminApiTest.php` | `test_lesson_page_and_detail_return_snapshots` | detail response includes lesson_students |
| `ClassScheduleAdminApiTest.php` | `test_schedule_calendar_returns_calendar_events` | response contains event start_at and end_at |
| `ClassScheduleAdminApiTest.php` | `test_conflict_check_returns_documented_conflicts` | teacher/student conflicts match catalog shape |
| `ClassScheduleAdminApiTest.php` | `test_single_schedule_returns_lesson_and_snapshot_summary` | response contains lesson and lesson_students blocks |
| `ClassScheduleAdminApiTest.php` | `test_batch_schedule_returns_batch_no_and_created_count` | response contains schedule_batch_no and created_count |
| `ClassScheduleAdminApiTest.php` | `test_lesson_cancel_returns_cancelled_status` | response lesson status is cancelled |
| `ClassScheduleAdminApiTest.php` | `test_validation_failures_match_catalog` | missing required fields return documented 422 messages |
| `ClassScheduleAdminApiTest.php` | `test_business_failures_match_catalog` | duplicate code, missing course account, and conflicts return documented codes |

### Permission, Isolation, and Audit Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `ClassSchedulePermissionTest.php` | `test_missing_class_create_permission_returns_403` | API returns code 403 |
| `ClassSchedulePermissionTest.php` | `test_front_desk_cannot_delete_lesson` | delete API returns code 403 |
| `ClassScheduleIsolationTest.php` | `test_tenant_user_cannot_read_other_tenant_class` | page and detail responses exclude other tenant rows |
| `ClassScheduleIsolationTest.php` | `test_campus_scoped_user_cannot_schedule_other_campus_class` | schedule API returns code 403 |
| `ClassScheduleIsolationTest.php` | `test_student_conflict_check_respects_campus_scope` | cross-campus lesson is not exposed |
| `ClassScheduleAuditTest.php` | `test_class_write_creates_audit_log` | audit action `education.academic.class.created` exists |
| `ClassScheduleAuditTest.php` | `test_class_student_save_creates_audit_log` | audit action `education.academic.class_student.saved` exists |
| `ClassScheduleAuditTest.php` | `test_single_and_batch_schedule_create_audit_logs` | schedule audit actions exist with lesson ids or batch no |
| `ClassScheduleAuditTest.php` | `test_lesson_update_cancel_delete_create_audit_logs` | update/cancel/delete audit actions exist |

### PC Tests

| Test file | Case | Assert |
| --- | --- | --- |
| `ClassList.spec.ts` | `renders_class_table_and_filters` | table shows code/name/course/status after pageClasses resolves |
| `ClassList.spec.ts` | `permission_buttons_are_hidden_without_permission` | create/edit/delete buttons are hidden |
| `ClassList.spec.ts` | `duplicate_class_error_keeps_form_open` | form remains visible after 409 |
| `ClassStudentDrawer.spec.ts` | `loads_and_saves_class_students` | drawer sends students payload and reloads active_student_count |
| `ClassStudentDrawer.spec.ts` | `missing_account_error_keeps_drawer_open` | 422 message is displayed and drawer remains open |
| `LessonScheduleCalendar.spec.ts` | `renders_calendar_events` | calendar displays lesson title and teacher name |
| `LessonScheduleCalendar.spec.ts` | `conflict_check_opens_conflict_drawer` | conflict drawer displays conflict_type and lesson id |
| `SingleLessonScheduleDrawer.spec.ts` | `schedule_success_shows_lesson_summary` | success summary displays lesson_no and created_count |
| `SingleLessonScheduleDrawer.spec.ts` | `teacher_conflict_keeps_drawer_open` | 409 message is displayed and drawer remains open |
| `BatchLessonScheduleDrawer.spec.ts` | `batch_schedule_success_shows_batch_no` | success message displays schedule_batch_no and created_count |
| `BatchLessonScheduleDrawer.spec.ts` | `batch_conflict_persists_no_events_in_ui` | conflict table displays and calendar event count does not increase |
| `LessonList.spec.ts` | `renders_lesson_rows_and_detail_drawer` | detail drawer displays lesson student snapshots |
| `LessonList.spec.ts` | `cancel_success_updates_row_status` | row status changes to cancelled after API success |
| `LessonList.spec.ts` | `delete_reference_failure_keeps_row` | 409 message is displayed and row remains visible |

### Mobile Regression Test

| Verification | Assert |
| --- | --- |
| `pnpm build:h5` in `mobile-uniapp` | existing teacher/guardian shell still builds because V1-03 adds no mobile files |

## Execution Commands

### Backend Migration Gate

Run:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter ClassScheduleMigrationTest
php bin/hyperf.php migrate:rollback --step=1
php bin/hyperf.php migrate
```

Expected:

```text
V1-03 migration runs successfully.
ClassScheduleMigrationTest passes.
Rollback drops V1-03 tables in dependency-safe order.
Re-running migration succeeds.
```

### Backend Unit Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter ClassRepositoryTest
composer test -- --filter ClassStudentRepositoryTest
composer test -- --filter LessonRepositoryTest
composer test -- --filter LessonStudentRepositoryTest
composer test -- --filter ClassServiceTest
composer test -- --filter ClassStudentServiceTest
composer test -- --filter LessonServiceTest
composer test -- --filter SchedulingServiceTest
composer test -- --filter SchedulingConflictServiceTest
```

Expected:

```text
V1-03 repository and service tests pass.
Teacher, classroom, class, and student conflict tests pass.
Single and batch scheduling transaction tests pass.
```

### Backend Feature Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter ClassScheduleAdminApiTest
composer test -- --filter ClassSchedulePermissionTest
composer test -- --filter ClassScheduleIsolationTest
composer test -- --filter ClassScheduleAuditTest
```

Expected:

```text
V1-03 admin API, permission, isolation, and audit tests pass.
Every API returns the documented MineAdmin result shape.
```

### PC Gate

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- ClassList
pnpm test -- ClassStudentDrawer
pnpm test -- LessonScheduleCalendar
pnpm test -- SingleLessonScheduleDrawer
pnpm test -- BatchLessonScheduleDrawer
pnpm test -- LessonList
pnpm build
```

Expected:

```text
PC lint passes.
V1-03 page tests pass.
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

### V1-03 Final Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter ClassSchedule
composer cs-fix -- --dry-run
composer analyse
cd ../admin-web
pnpm lint
pnpm test -- ClassList
pnpm test -- ClassStudentDrawer
pnpm test -- LessonScheduleCalendar
pnpm test -- SingleLessonScheduleDrawer
pnpm test -- BatchLessonScheduleDrawer
pnpm test -- LessonList
pnpm build
cd ../mobile-uniapp
pnpm build:h5
```

Expected:

```text
All V1-03 backend tests pass.
Backend code style dry run passes.
Backend static analysis passes.
PC lint, page tests, and build pass.
Mobile H5 build passes.
```

## Acceptance Gate

V1-03 is accepted only when all conditions are true:

```text
- `edu_classes`, `edu_class_students`, `edu_lessons`, and `edu_lesson_students` exist with documented columns and indexes.
- Migration rollback drops all V1-03 tables in dependency-safe order.
- Class, class-student, lesson, and lesson-student models cast decimal, date, status, and id fields correctly.
- Repositories apply tenant and campus scope filters consistently.
- Services reject duplicate class code, disabled dependencies, unauthorized teachers, missing student course accounts, and class capacity violations with documented codes.
- Class-student save enforces active V1-02 course accounts and one-to-one/max-student rules.
- Scheduling creates lessons and lesson-student snapshots transactionally.
- Scheduling rejects teacher, classroom, class, and student conflicts with code 409 and documented conflict payloads.
- Batch scheduling is all-or-nothing and persists no lessons when any generated lesson conflicts.
- Lesson update reruns conflict checks and updates planned snapshot lesson_units.
- Lesson cancellation marks lesson and lesson-student snapshots cancelled without changing course account balance.
- Admin APIs return MineAdmin result shape and documented validation/business failures.
- Permission tests prove missing MineAdmin permission codes return 403.
- Isolation tests prove tenant and campus scoped users cannot read or mutate unauthorized rows.
- F04 audit logs are created for all V1-03 write operations.
- PC API client, routes, class list, class-student drawer, schedule calendar, single/batch scheduling drawers, conflict drawer, lesson list, detail drawer, permission buttons, loading, empty, error, success, and submit states pass tests.
- V1-03 adds no mobile pages and mobile H5 build still passes.
```

## Task Breakdown

### Task 1: Create Migration, Enums, and Models

**Files:**

- Create: `mineadmin-education-saas/backend/databases/migrations/2026_06_10_010300_create_v1_class_lesson_tables.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/ClassType.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/ClassStudentStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LessonStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/LessonStudentStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/ScheduleSourceType.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationClass.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationClassStudent.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLesson.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonStudent.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/ClassScheduleMigrationTest.php`

- [x] **Step 1: Create migration**

Use the full table, column, index, foreign-key policy, and rollback order from `Database Migration Design`.

- [x] **Step 2: Create enums**

Create `ClassType`, `ClassStudentStatus`, `LessonStatus`, `LessonStudentStatus`, and `ScheduleSourceType` exactly as defined in `MineAdmin Backend Module Design`.

- [x] **Step 3: Create models**

Create all four models with table names, fillable fields, casts, relationships, timestamps, and soft delete behavior defined in `MineAdmin Backend Module Design`.

- [x] **Step 4: Write migration test**

Create `ClassScheduleMigrationTest` with cases listed in `Test Plan`.

- [x] **Step 5: Run migration gate**

Run commands from `Backend Migration Gate`.

Expected:

```text
Migration, rollback, re-migration, and migration tests pass.
```

### Task 2: Create Repositories and Services

**Files:**

- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/ClassRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/ClassStudentRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonStudentRepository.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/ClassService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/ClassStudentService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/LessonService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/SchedulingService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/SchedulingConflictService.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/ClassRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/ClassStudentRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/LessonRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/LessonStudentRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/ClassServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/ClassStudentServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/LessonServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/SchedulingServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/SchedulingConflictServiceTest.php`

- [x] **Step 1: Create repositories**

Implement repository methods, filters, tenant scope, campus scope, duplicate checks, overlap queries, snapshot queries, and calendar query rules from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create services**

Implement service methods, class validation, class-student account validation, single scheduling transaction, batch scheduling transaction, conflict checks, lesson update/cancel/delete rules, audit dispatch, and delete guards from `MineAdmin Backend Module Design`.

- [x] **Step 3: Write repository tests**

Create repository tests listed in `Test Plan`.

- [x] **Step 4: Write service tests**

Create service tests listed in `Test Plan`.

- [x] **Step 5: Run backend unit gate**

Run commands from `Backend Unit Gate`.

Expected:

```text
V1-03 repository, service, conflict, scheduling, and snapshot tests pass.
```

### Task 3: Create Requests, Schemas, and Controllers

**Files:**

- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ClassPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ClassSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ClassStatusRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ClassStudentSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LessonPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LessonUpdateRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/LessonCancelRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ScheduleCalendarRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ScheduleConflictCheckRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/SingleLessonScheduleRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/BatchLessonScheduleRequest.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/ClassSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/ClassStudentSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/LessonSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/LessonStudentSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/SchedulingConflictSchema.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/ClassController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/LessonController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/LessonScheduleController.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/ClassScheduleAdminApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/ClassSchedulePermissionTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/ClassScheduleIsolationTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/ClassScheduleAuditTest.php`

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
V1-03 admin API, permission, isolation, and audit tests pass.
```

### Task 4: Create PC API Client, Routes, Pages, Forms, Calendar, and Drawers

**Files:**

- Create: `mineadmin-education-saas/admin-web/src/api/education/academic/classSchedule.ts`
- Modify: `mineadmin-education-saas/admin-web/src/router/modules/education.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/ClassList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/LessonScheduleCalendar.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/LessonList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/ClassForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/ClassStudentDrawer.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/SingleLessonScheduleDrawer.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/BatchLessonScheduleDrawer.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/ScheduleConflictDrawer.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/LessonForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/LessonDetailDrawer.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/ClassList.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/ClassStudentDrawer.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/LessonScheduleCalendar.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/SingleLessonScheduleDrawer.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/BatchLessonScheduleDrawer.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/LessonList.spec.ts`

- [x] **Step 1: Create typed API client**

Implement all types and methods listed in `PC Admin Page Tasks`.

- [x] **Step 2: Add routes and menus**

Add V1-03 route entries and auth meta to `admin-web/src/router/modules/education.ts`.

- [x] **Step 3: Create class page and student membership drawer**

Implement ClassList, ClassForm, and ClassStudentDrawer using page tasks from `PC Admin Page Tasks`.

- [x] **Step 4: Create schedule calendar and scheduling drawers**

Implement LessonScheduleCalendar, SingleLessonScheduleDrawer, BatchLessonScheduleDrawer, and ScheduleConflictDrawer with conflict preview and schedule result states.

- [x] **Step 5: Create lesson list and detail/edit/cancel flows**

Implement LessonList, LessonForm, and LessonDetailDrawer with snapshot display, edit conflict handling, cancellation, and delete failure handling.

- [x] **Step 6: Write PC tests**

Create PC tests listed in `Test Plan`.

- [x] **Step 7: Run PC gate**

Run commands from `PC Gate`.

Expected:

```text
PC lint, V1-03 page tests, and production build pass.
```

### Task 5: Run V1-03 Final Gate

**Files:**

- Verify: all backend, PC, and mobile paths listed in `File Structure`.

- [x] **Step 1: Run backend final gate**

Run backend commands from `V1-03 Final Gate`.

- [x] **Step 2: Run PC final gate**

Run PC commands from `V1-03 Final Gate`.

- [x] **Step 3: Run mobile regression gate**

Run mobile command from `V1-03 Final Gate`.

- [x] **Step 4: Commit V1-03**

Run:

```bash
cd mineadmin-education-saas
git add backend admin-web mobile-uniapp
git commit -m "feat: add v1 class scheduling lessons"
```

Expected:

```text
Commit succeeds with V1-03 backend, PC, tests, and verification changes.
```

## Self-Review

- Spec coverage: V1-03 covers classes, class students, scheduling, lesson records, lesson student snapshots, and conflict checks from the V1 class-schedule-lesson scope.
- MineAdmin fit: The plan uses MineAdmin 3.x `app/Http/Admin`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, `databases/migrations`, permission attributes, OperationMiddleware for writes, and MineAdmin result shape.
- Tenant isolation: All records are tenant-scoped and campus-scoped; services validate campus scope before every write.
- V1 dependency fit: V1-03 consumes V1-01 classrooms/students/teachers and V1-02 courses/teacher authorization/accounts; V1-04 can use lessons and lesson-student snapshots for attendance and consumption; V1-06/V1-07 can expose teacher/guardian schedule pages through scoped mobile APIs.
- Conflict fit: Teacher, classroom, class, and student overlaps use one consistent time-overlap rule and ignore cancelled lessons.
- Transaction fit: Single scheduling and batch scheduling are explicit transactions and create lessons plus student snapshots atomically.
- PC fit: Pages include typed API client, route/menu entries, class list, class-student drawer, schedule calendar, single/batch scheduling drawers, conflict drawer, lesson list, detail drawer, permission buttons, loading, empty, error, success, and submit states.
- Mobile fit: V1-03 adds no visible mobile page and keeps mobile build verification.
- Readiness: This plan has exact paths, full migration design, backend layer tasks, API request/response/failure examples, PC tasks, mobile rationale, tests, commands, expected outputs, and acceptance gates, so V1-03 can be marked `ready`.
