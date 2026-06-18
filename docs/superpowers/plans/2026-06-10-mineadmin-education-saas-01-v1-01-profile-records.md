# MineAdmin Education SaaS V1-01 Profile Records Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V1 base records for classrooms, students, guardians, student-guardian relationships, and teachers with MineAdmin admin APIs, PC pages, tenant/campus isolation, audit logging, and verification tests.

**Architecture:** V1-01 is the first V1 resource layer after Foundation. It uses F01 tenant/campus, F02 education user context and campus scope, F03 dictionaries, F04 audit logging, and F05 PC conventions. Backend follows MineAdmin 3.x native paths under `app/Http/Admin`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, and `databases/migrations`; no mobile page is added in V1-01.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, MineAdmin-Vue, Vue3, TypeScript, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** incomplete / not implemented. `ready` means this plan is detailed enough to start coding.

---

## Scope Check

Included:

- Create classroom, student, guardian, student-guardian relationship, and teacher tables.
- Create academic status, gender, and guardian relation enums.
- Create models, repositories, services, request classes, schemas, and admin controllers for classrooms, students, guardians, and teachers.
- Manage student-guardian relationships from the student detail workflow.
- Link teacher records to F02 education user profiles when a teacher needs mobile access.
- Add admin APIs for page/create/update/status/delete and student guardian relationship save.
- Add PC API client, route/menu entries, list pages, forms, relationship drawer, permission-controlled buttons, and state tests.
- Add migration, repository, service, API, permission, tenant/campus isolation, audit, PC, and mobile regression tests.

Excluded:

- Course products and teacher-course authorization; V1-02 owns them.
- Class membership, lesson scheduling, and lesson snapshots; V1-03 owns them.
- Attendance and consumption; V1-04 owns them.
- Leave, make-up, and reschedule workflows; V1-05 owns them.
- Teacher mobile pages; V1-06 owns them.
- Guardian mobile pages and notice pages; V1-07 owns them.

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_010100_create_v1_profile_record_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/AcademicRecordStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/Gender.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/GuardianRelation.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationClassroom.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudent.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationGuardian.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudentGuardian.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationTeacher.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/ClassroomRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/StudentRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/GuardianRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/StudentGuardianRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/TeacherRepository.php
mineadmin-education-saas/backend/app/Service/Education/Academic/ClassroomService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/StudentService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/GuardianService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/TeacherService.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ClassroomPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ClassroomSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ClassroomStatusRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/StudentPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/StudentSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/StudentStatusRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/StudentGuardianSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/GuardianPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/GuardianSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/GuardianStatusRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/TeacherPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/TeacherSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/TeacherStatusRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/ClassroomController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/StudentController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/GuardianController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/TeacherController.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/ClassroomSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/StudentSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/GuardianSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/StudentGuardianSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/TeacherSchema.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Academic/ProfileRecordMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/ClassroomRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/StudentRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/GuardianRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/StudentGuardianRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/ClassroomServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/StudentServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/GuardianServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/ProfileRecordAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/ProfileRecordPermissionTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/ProfileRecordIsolationTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/ProfileRecordAuditTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/academic/profile.ts
mineadmin-education-saas/admin-web/src/views/education/academic/ClassroomList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/StudentList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/GuardianList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/TeacherList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/ClassroomForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/StudentForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/GuardianForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/StudentGuardianDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/TeacherForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/ClassroomList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/StudentList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/GuardianList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/TeacherList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/StudentGuardianDrawer.spec.ts
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
mineadmin-education-saas/backend/databases/migrations/2026_06_10_010100_create_v1_profile_record_tables.php
```

Tables:

```text
edu_classrooms
edu_students
edu_guardians
edu_student_guardians
edu_teachers
```

Column design:

### `edu_classrooms`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Classroom id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Campus id |
| `code` | varchar(64) | no | none | Classroom code inside campus |
| `name` | varchar(120) | no | none | Classroom name |
| `capacity` | int unsigned | no | 0 | Seat capacity |
| `location` | varchar(120) | yes | null | Location or room number |
| `equipment` | json | yes | null | Equipment metadata |
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
unique uk_edu_classrooms_tenant_campus_code (tenant_id, campus_id, code)
index idx_edu_classrooms_tenant_campus_status (tenant_id, campus_id, status)
index idx_edu_classrooms_tenant_campus_name (tenant_id, campus_id, name)
index idx_edu_classrooms_deleted_at (deleted_at)
```

### `edu_students`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Student id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Primary campus id |
| `student_no` | varchar(64) | no | none | Student number inside tenant |
| `name` | varchar(120) | no | none | Student name |
| `gender` | varchar(20) | no | `unknown` | male, female, or unknown |
| `birthday` | date | yes | null | Student birthday |
| `mobile` | varchar(30) | yes | null | Student contact mobile |
| `school` | varchar(120) | yes | null | Current school |
| `grade` | varchar(60) | yes | null | Current grade |
| `source` | varchar(80) | yes | null | Student source |
| `avatar` | varchar(255) | yes | null | Avatar URL |
| `enrolled_at` | date | yes | null | First enrollment date |
| `status` | varchar(20) | no | `enabled` | enabled or disabled |
| `remark` | varchar(500) | yes | null | Internal remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_students_tenant_student_no (tenant_id, student_no)
index idx_edu_students_tenant_campus_status (tenant_id, campus_id, status)
index idx_edu_students_tenant_name_mobile (tenant_id, name, mobile)
index idx_edu_students_tenant_campus_name (tenant_id, campus_id, name)
index idx_edu_students_deleted_at (deleted_at)
```

### `edu_guardians`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Guardian id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `name` | varchar(120) | no | none | Guardian name |
| `mobile` | varchar(30) | no | none | Guardian mobile |
| `gender` | varchar(20) | no | `unknown` | male, female, or unknown |
| `openid` | varchar(80) | yes | null | WeChat openid |
| `unionid` | varchar(80) | yes | null | WeChat unionid |
| `status` | varchar(20) | no | `enabled` | enabled or disabled |
| `remark` | varchar(500) | yes | null | Internal remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_guardians_tenant_mobile (tenant_id, mobile)
unique uk_edu_guardians_tenant_openid (tenant_id, openid)
unique uk_edu_guardians_tenant_unionid (tenant_id, unionid)
index idx_edu_guardians_tenant_status (tenant_id, status)
index idx_edu_guardians_tenant_name_mobile (tenant_id, name, mobile)
index idx_edu_guardians_deleted_at (deleted_at)
```

### `edu_student_guardians`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Student guardian relation id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `student_id` | bigint unsigned | no | none | Student id |
| `guardian_id` | bigint unsigned | no | none | Guardian id |
| `relation` | varchar(30) | no | `guardian` | father, mother, guardian, or other |
| `is_primary` | boolean | no | false | Primary contact for student |
| `can_receive_notice` | boolean | no | true | Can receive notices |
| `can_submit_leave` | boolean | no | true | Can submit leave requests |
| `remark` | varchar(500) | yes | null | Internal remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_student_guardians_tenant_student_guardian (tenant_id, student_id, guardian_id)
index idx_edu_student_guardians_tenant_student (tenant_id, student_id)
index idx_edu_student_guardians_tenant_guardian (tenant_id, guardian_id)
index idx_edu_student_guardians_tenant_relation (tenant_id, relation)
index idx_edu_student_guardians_deleted_at (deleted_at)
```

### `edu_teachers`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Teacher id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | no | none | Primary campus id |
| `user_profile_id` | bigint unsigned | yes | null | F02 education user profile id for teacher mobile login |
| `teacher_no` | varchar(64) | no | none | Teacher number inside tenant |
| `name` | varchar(120) | no | none | Teacher name |
| `mobile` | varchar(30) | yes | null | Teacher mobile |
| `gender` | varchar(20) | no | `unknown` | male, female, or unknown |
| `birthday` | date | yes | null | Teacher birthday |
| `title` | varchar(80) | yes | null | Teacher title |
| `hire_date` | date | yes | null | Hire date |
| `avatar` | varchar(255) | yes | null | Avatar URL |
| `introduction` | text | yes | null | Teacher introduction |
| `status` | varchar(20) | no | `enabled` | enabled or disabled |
| `remark` | varchar(500) | yes | null | Internal remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_teachers_tenant_teacher_no (tenant_id, teacher_no)
unique uk_edu_teachers_user_profile (user_profile_id)
index idx_edu_teachers_tenant_campus_status (tenant_id, campus_id, status)
index idx_edu_teachers_tenant_name_mobile (tenant_id, name, mobile)
index idx_edu_teachers_deleted_at (deleted_at)
```

Foreign-key policy:

```text
No physical foreign keys. Services validate tenant, campus, student, guardian, teacher, and user_profile visibility before write operations. This matches Foundation's service-level isolation strategy and avoids migration-order coupling across V1 modules.
```

Rollback behavior:

```text
Drop tables in this order: edu_student_guardians, edu_teachers, edu_guardians, edu_students, edu_classrooms.
```

Full migration:

```php
<?php

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edu_classrooms', static function (Blueprint $table): void {
            $table->comment('Education classrooms');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->string('code', 64)->comment('Classroom code inside campus');
            $table->string('name', 120)->comment('Classroom name');
            $table->unsignedInteger('capacity')->default(0)->comment('Seat capacity');
            $table->string('location', 120)->nullable()->comment('Location or room number');
            $table->json('equipment')->nullable()->comment('Equipment metadata');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'campus_id', 'code'], 'uk_edu_classrooms_tenant_campus_code');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_classrooms_tenant_campus_status');
            $table->index(['tenant_id', 'campus_id', 'name'], 'idx_edu_classrooms_tenant_campus_name');
            $table->index('deleted_at', 'idx_edu_classrooms_deleted_at');
        });

        Schema::create('edu_students', static function (Blueprint $table): void {
            $table->comment('Education students');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Primary campus id');
            $table->string('student_no', 64)->comment('Student number inside tenant');
            $table->string('name', 120)->comment('Student name');
            $table->string('gender', 20)->default('unknown')->comment('male, female, or unknown');
            $table->date('birthday')->nullable()->comment('Student birthday');
            $table->string('mobile', 30)->nullable()->comment('Student contact mobile');
            $table->string('school', 120)->nullable()->comment('Current school');
            $table->string('grade', 60)->nullable()->comment('Current grade');
            $table->string('source', 80)->nullable()->comment('Student source');
            $table->string('avatar', 255)->nullable()->comment('Avatar URL');
            $table->date('enrolled_at')->nullable()->comment('First enrollment date');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'student_no'], 'uk_edu_students_tenant_student_no');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_students_tenant_campus_status');
            $table->index(['tenant_id', 'name', 'mobile'], 'idx_edu_students_tenant_name_mobile');
            $table->index(['tenant_id', 'campus_id', 'name'], 'idx_edu_students_tenant_campus_name');
            $table->index('deleted_at', 'idx_edu_students_deleted_at');
        });

        Schema::create('edu_guardians', static function (Blueprint $table): void {
            $table->comment('Education guardians');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->string('name', 120)->comment('Guardian name');
            $table->string('mobile', 30)->comment('Guardian mobile');
            $table->string('gender', 20)->default('unknown')->comment('male, female, or unknown');
            $table->string('openid', 80)->nullable()->comment('WeChat openid');
            $table->string('unionid', 80)->nullable()->comment('WeChat unionid');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'mobile'], 'uk_edu_guardians_tenant_mobile');
            $table->unique(['tenant_id', 'openid'], 'uk_edu_guardians_tenant_openid');
            $table->unique(['tenant_id', 'unionid'], 'uk_edu_guardians_tenant_unionid');
            $table->index(['tenant_id', 'status'], 'idx_edu_guardians_tenant_status');
            $table->index(['tenant_id', 'name', 'mobile'], 'idx_edu_guardians_tenant_name_mobile');
            $table->index('deleted_at', 'idx_edu_guardians_deleted_at');
        });

        Schema::create('edu_student_guardians', static function (Blueprint $table): void {
            $table->comment('Education student guardian relations');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('student_id')->comment('Student id');
            $table->unsignedBigInteger('guardian_id')->comment('Guardian id');
            $table->string('relation', 30)->default('guardian')->comment('father, mother, guardian, or other');
            $table->boolean('is_primary')->default(false)->comment('Primary contact for student');
            $table->boolean('can_receive_notice')->default(true)->comment('Can receive notices');
            $table->boolean('can_submit_leave')->default(true)->comment('Can submit leave requests');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'student_id', 'guardian_id'], 'uk_edu_student_guardians_tenant_student_guardian');
            $table->index(['tenant_id', 'student_id'], 'idx_edu_student_guardians_tenant_student');
            $table->index(['tenant_id', 'guardian_id'], 'idx_edu_student_guardians_tenant_guardian');
            $table->index(['tenant_id', 'relation'], 'idx_edu_student_guardians_tenant_relation');
            $table->index('deleted_at', 'idx_edu_student_guardians_deleted_at');
        });

        Schema::create('edu_teachers', static function (Blueprint $table): void {
            $table->comment('Education teachers');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('campus_id')->comment('Primary campus id');
            $table->unsignedBigInteger('user_profile_id')->nullable()->comment('Education user profile id for teacher mobile login');
            $table->string('teacher_no', 64)->comment('Teacher number inside tenant');
            $table->string('name', 120)->comment('Teacher name');
            $table->string('mobile', 30)->nullable()->comment('Teacher mobile');
            $table->string('gender', 20)->default('unknown')->comment('male, female, or unknown');
            $table->date('birthday')->nullable()->comment('Teacher birthday');
            $table->string('title', 80)->nullable()->comment('Teacher title');
            $table->date('hire_date')->nullable()->comment('Hire date');
            $table->string('avatar', 255)->nullable()->comment('Avatar URL');
            $table->text('introduction')->nullable()->comment('Teacher introduction');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->string('remark', 500)->nullable()->comment('Internal remark');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'teacher_no'], 'uk_edu_teachers_tenant_teacher_no');
            $table->unique('user_profile_id', 'uk_edu_teachers_user_profile');
            $table->index(['tenant_id', 'campus_id', 'status'], 'idx_edu_teachers_tenant_campus_status');
            $table->index(['tenant_id', 'name', 'mobile'], 'idx_edu_teachers_tenant_name_mobile');
            $table->index('deleted_at', 'idx_edu_teachers_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_student_guardians');
        Schema::dropIfExists('edu_teachers');
        Schema::dropIfExists('edu_guardians');
        Schema::dropIfExists('edu_students');
        Schema::dropIfExists('edu_classrooms');
    }
};
```

## MineAdmin Backend Module Design

### Enums

Create `AcademicRecordStatus`:

```php
<?php

namespace App\Model\Enums\Education\Academic;

enum AcademicRecordStatus: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
}
```

Create `Gender`:

```php
<?php

namespace App\Model\Enums\Education\Academic;

enum Gender: string
{
    case Male = 'male';
    case Female = 'female';
    case Unknown = 'unknown';
}
```

Create `GuardianRelation`:

```php
<?php

namespace App\Model\Enums\Education\Academic;

enum GuardianRelation: string
{
    case Father = 'father';
    case Mother = 'mother';
    case Grandfather = 'grandfather';
    case Grandmother = 'grandmother';
    case Guardian = 'guardian';
    case Other = 'other';
}
```

### Models

All models:

```text
Use MineAdmin generated model base.
Use SoftDeletes.
Fill `tenant_id`, `campus_id` where present, business fields, `created_by`, and `updated_by`.
Cast JSON fields to array, boolean fields to bool, birthday/hire/enrolled fields to date, timestamps to datetime.
Do not expose cross-tenant relations without repository scope methods.
```

Model details:

| Model | Table | Casts |
| --- | --- | --- |
| `EducationClassroom` | `edu_classrooms` | `tenant_id` int, `campus_id` int, `capacity` int, `equipment` array, `sort_order` int |
| `EducationStudent` | `edu_students` | `tenant_id` int, `campus_id` int, `birthday` date, `enrolled_at` date |
| `EducationGuardian` | `edu_guardians` | `tenant_id` int |
| `EducationStudentGuardian` | `edu_student_guardians` | `tenant_id` int, `student_id` int, `guardian_id` int, `is_primary` bool, `can_receive_notice` bool, `can_submit_leave` bool |
| `EducationTeacher` | `edu_teachers` | `tenant_id` int, `campus_id` int, `user_profile_id` int, `birthday` date, `hire_date` date |

### Repositories

All repositories extend:

```text
App\Repository\IRepository
```

`ClassroomRepository` methods:

```php
public function getModel(): string
public function pageByContext(array $filters, int $page, int $pageSize, EducationUserContext $context): array
public function findVisibleById(int $id, EducationUserContext $context): ?EducationClassroom
public function existsCode(int $tenantId, int $campusId, string $code, ?int $exceptId = null): bool
```

`StudentRepository` methods:

```php
public function getModel(): string
public function pageByContext(array $filters, int $page, int $pageSize, EducationUserContext $context): array
public function findVisibleById(int $id, EducationUserContext $context): ?EducationStudent
public function existsStudentNo(int $tenantId, string $studentNo, ?int $exceptId = null): bool
```

`GuardianRepository` methods:

```php
public function getModel(): string
public function pageByContext(array $filters, int $page, int $pageSize, EducationUserContext $context): array
public function findVisibleById(int $id, EducationUserContext $context): ?EducationGuardian
public function existsMobile(int $tenantId, string $mobile, ?int $exceptId = null): bool
public function findManyVisible(array $ids, EducationUserContext $context): array
```

`StudentGuardianRepository` methods:

```php
public function getModel(): string
public function listByStudent(int $studentId, EducationUserContext $context): array
public function replaceForStudent(int $tenantId, int $studentId, array $relations, ?int $operatorId): void
public function guardianIdsForStudent(int $tenantId, int $studentId): array
```

`TeacherRepository` methods:

```php
public function getModel(): string
public function pageByContext(array $filters, int $page, int $pageSize, EducationUserContext $context): array
public function findVisibleById(int $id, EducationUserContext $context): ?EducationTeacher
public function existsTeacherNo(int $tenantId, string $teacherNo, ?int $exceptId = null): bool
public function existsUserProfile(int $userProfileId, ?int $exceptId = null): bool
```

Repository filters:

```text
keyword matches code/no, name, and mobile where present.
status exact match.
campus_id applies only when platform user or campus id is allowed by current context.
tenant_id filter is accepted only for platform roles; tenant roles always use context tenant id.
```

### Services

All services extend:

```text
App\Service\IService
```

Common service rules:

```text
Resolve `EducationUserContext` from F02 before every operation.
Validate tenant id and campus id through Foundation services.
Reject writes outside tenant or campus scope with code 403.
Reject duplicate codes/numbers/mobile with code 409.
Write F04 audit events after successful create/update/status/delete/relationship-save operations.
Use MineAdmin OperationMiddleware on write controllers.
Soft delete only when the row is not referenced by later V1 data.
```

`ClassroomService` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationClassroom
public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationClassroom
public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationClassroom
public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
```

`StudentService` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationStudent
public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationStudent
public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationStudent
public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
public function guardians(int $id, EducationUserContext $context): array
public function saveGuardians(int $id, array $relations, EducationUserContext $context, ?int $operatorId): array
```

`GuardianService` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationGuardian
public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationGuardian
public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationGuardian
public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
```

`TeacherService` methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationTeacher
public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationTeacher
public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationTeacher
public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
```

Teacher profile rule:

```text
When `user_profile_id` is present, it must point to an enabled F02 profile in the same tenant with role_code `teacher`. The same user_profile_id cannot be linked to another teacher.
```

Student guardian rule:

```text
`saveGuardians` replaces the active relation set for the student. Every guardian must belong to the same tenant and be visible to the current context. At most one relation can have `is_primary = true`; when none is primary, the first relation becomes primary.
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
    'status' => ['nullable', 'in:enabled,disabled'],
]
```

`ClassroomSaveRequest` rules:

```php
[
    'campus_id' => ['required', 'integer', 'min:1'],
    'code' => ['required', 'string', 'max:64'],
    'name' => ['required', 'string', 'max:120'],
    'capacity' => ['nullable', 'integer', 'min:0', 'max:9999'],
    'location' => ['nullable', 'string', 'max:120'],
    'equipment' => ['nullable', 'array'],
    'status' => ['required', 'in:enabled,disabled'],
    'sort_order' => ['nullable', 'integer', 'between:-9999,9999'],
    'remark' => ['nullable', 'string', 'max:500'],
]
```

`StudentSaveRequest` rules:

```php
[
    'campus_id' => ['required', 'integer', 'min:1'],
    'student_no' => ['required', 'string', 'max:64'],
    'name' => ['required', 'string', 'max:120'],
    'gender' => ['required', 'in:male,female,unknown'],
    'birthday' => ['nullable', 'date_format:Y-m-d'],
    'mobile' => ['nullable', 'string', 'max:30'],
    'school' => ['nullable', 'string', 'max:120'],
    'grade' => ['nullable', 'string', 'max:60'],
    'source' => ['nullable', 'string', 'max:80'],
    'avatar' => ['nullable', 'string', 'max:255'],
    'enrolled_at' => ['nullable', 'date_format:Y-m-d'],
    'status' => ['required', 'in:enabled,disabled'],
    'remark' => ['nullable', 'string', 'max:500'],
]
```

`StudentGuardianSaveRequest` rules:

```php
[
    'relations' => ['required', 'array', 'min:1'],
    'relations.*.guardian_id' => ['required', 'integer', 'min:1'],
    'relations.*.relation' => ['required', 'in:father,mother,grandfather,grandmother,guardian,other'],
    'relations.*.is_primary' => ['nullable', 'boolean'],
    'relations.*.can_receive_notice' => ['nullable', 'boolean'],
    'relations.*.can_submit_leave' => ['nullable', 'boolean'],
    'relations.*.remark' => ['nullable', 'string', 'max:500'],
]
```

`GuardianSaveRequest` rules:

```php
[
    'name' => ['required', 'string', 'max:120'],
    'mobile' => ['required', 'string', 'max:30'],
    'gender' => ['required', 'in:male,female,unknown'],
    'openid' => ['nullable', 'string', 'max:80'],
    'unionid' => ['nullable', 'string', 'max:80'],
    'status' => ['required', 'in:enabled,disabled'],
    'remark' => ['nullable', 'string', 'max:500'],
]
```

`TeacherSaveRequest` rules:

```php
[
    'campus_id' => ['required', 'integer', 'min:1'],
    'user_profile_id' => ['nullable', 'integer', 'min:1'],
    'teacher_no' => ['required', 'string', 'max:64'],
    'name' => ['required', 'string', 'max:120'],
    'mobile' => ['nullable', 'string', 'max:30'],
    'gender' => ['required', 'in:male,female,unknown'],
    'birthday' => ['nullable', 'date_format:Y-m-d'],
    'title' => ['nullable', 'string', 'max:80'],
    'hire_date' => ['nullable', 'date_format:Y-m-d'],
    'avatar' => ['nullable', 'string', 'max:255'],
    'introduction' => ['nullable', 'string', 'max:5000'],
    'status' => ['required', 'in:enabled,disabled'],
    'remark' => ['nullable', 'string', 'max:500'],
]
```

Status request rules:

```php
[
    'status' => ['required', 'in:enabled,disabled'],
]
```

Validation messages:

```text
page.required: page is required
pageSize.between: pageSize must be between 1 and 100
campus_id.required: campus_id is required
code.required: code is required
student_no.required: student_no is required
teacher_no.required: teacher_no is required
mobile.required: mobile is required
status.in: status must be one of enabled, disabled
gender.in: gender must be one of male, female, unknown
relations.required: relations is required
relations.*.guardian_id.required: guardian_id is required
```

### Controllers

Controller base:

```text
Use `#[Controller(prefix: 'admin/education/academic/<resource>')]`.
Use MineAdmin auth middleware.
Use Permission attributes on every endpoint.
Use OperationMiddleware on create/update/status/delete/save-guardians endpoints.
Return MineAdmin Result shape through `$this->success(...)`.
Resolve current EducationUserContext through F02 context resolver.
```

Controller methods:

```text
ClassroomController: page, create, update, status, delete
StudentController: page, create, update, status, delete, guardians, saveGuardians
GuardianController: page, create, update, status, delete
TeacherController: page, create, update, status, delete
```

### Schemas

Schema files expose API documentation fields:

```text
ClassroomSchema: id, tenant_id, campus_id, code, name, capacity, location, equipment, status, sort_order, remark, created_at, updated_at
StudentSchema: id, tenant_id, campus_id, student_no, name, gender, birthday, mobile, school, grade, source, avatar, enrolled_at, status, remark, guardian_count, created_at, updated_at
GuardianSchema: id, tenant_id, name, mobile, gender, openid, unionid, status, remark, student_count, created_at, updated_at
StudentGuardianSchema: id, tenant_id, student_id, guardian_id, guardian_name, guardian_mobile, relation, is_primary, can_receive_notice, can_submit_leave, remark
TeacherSchema: id, tenant_id, campus_id, user_profile_id, teacher_no, name, mobile, gender, birthday, title, hire_date, avatar, introduction, status, remark, created_at, updated_at
```

## API Contract

### Endpoint Matrix

| API | Permission | Caller | Isolation | Audit |
| --- | --- | --- | --- | --- |
| `GET /admin/education/academic/classrooms/page` | `education:academic:classroom:page` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `POST /admin/education/academic/classrooms` | `education:academic:classroom:create` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.classroom.created` |
| `PUT /admin/education/academic/classrooms/{id}` | `education:academic:classroom:update` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.classroom.updated` |
| `PUT /admin/education/academic/classrooms/{id}/status` | `education:academic:classroom:status` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.classroom.status_changed` |
| `DELETE /admin/education/academic/classrooms/{id}` | `education:academic:classroom:delete` | tenant admin, principal | tenant and campus scope | `education.academic.classroom.deleted` |
| `GET /admin/education/academic/students/page` | `education:academic:student:page` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `POST /admin/education/academic/students` | `education:academic:student:create` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | `education.academic.student.created` |
| `PUT /admin/education/academic/students/{id}` | `education:academic:student:update` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | `education.academic.student.updated` |
| `PUT /admin/education/academic/students/{id}/status` | `education:academic:student:status` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.student.status_changed` |
| `DELETE /admin/education/academic/students/{id}` | `education:academic:student:delete` | tenant admin, principal | tenant and campus scope | `education.academic.student.deleted` |
| `GET /admin/education/academic/students/{id}/guardians` | `education:academic:student-guardian:page` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | none |
| `PUT /admin/education/academic/students/{id}/guardians` | `education:academic:student-guardian:save` | tenant admin, principal, academic_staff, front_desk | tenant and campus scope | `education.academic.student_guardian.saved` |
| `GET /admin/education/academic/guardians/page` | `education:academic:guardian:page` | tenant admin, principal, academic_staff, front_desk | tenant scope | none |
| `POST /admin/education/academic/guardians` | `education:academic:guardian:create` | tenant admin, principal, academic_staff, front_desk | tenant scope | `education.academic.guardian.created` |
| `PUT /admin/education/academic/guardians/{id}` | `education:academic:guardian:update` | tenant admin, principal, academic_staff, front_desk | tenant scope | `education.academic.guardian.updated` |
| `PUT /admin/education/academic/guardians/{id}/status` | `education:academic:guardian:status` | tenant admin, principal, academic_staff | tenant scope | `education.academic.guardian.status_changed` |
| `DELETE /admin/education/academic/guardians/{id}` | `education:academic:guardian:delete` | tenant admin, principal | tenant scope | `education.academic.guardian.deleted` |
| `GET /admin/education/academic/teachers/page` | `education:academic:teacher:page` | tenant admin, principal, academic_staff | tenant and campus scope | none |
| `POST /admin/education/academic/teachers` | `education:academic:teacher:create` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.teacher.created` |
| `PUT /admin/education/academic/teachers/{id}` | `education:academic:teacher:update` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.teacher.updated` |
| `PUT /admin/education/academic/teachers/{id}/status` | `education:academic:teacher:status` | tenant admin, principal, academic_staff | tenant and campus scope | `education.academic.teacher.status_changed` |
| `DELETE /admin/education/academic/teachers/{id}` | `education:academic:teacher:delete` | tenant admin, principal | tenant and campus scope | `education.academic.teacher.deleted` |

Headers for tenant-scoped callers:

```text
Authorization: Bearer test-admin-token
X-Tenant-Id: 1001
X-Campus-Id: 2001
X-Request-Id: req-v1-profile-001
```

### Classroom Examples

Page request:

```json
{
  "page": 1,
  "pageSize": 20,
  "campus_id": 2001,
  "keyword": "A101",
  "status": "enabled"
}
```

Page success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "tenant_id": 1001,
        "campus_id": 2001,
        "code": "A101",
        "name": "A101",
        "capacity": 20,
        "location": "Building A 1F",
        "status": "enabled",
        "created_at": "2026-06-10 09:00:00"
      }
    ],
    "total": 1
  }
}
```

Save request:

```json
{
  "campus_id": 2001,
  "code": "A101",
  "name": "A101",
  "capacity": 20,
  "location": "Building A 1F",
  "equipment": {
    "piano": true,
    "projector": false
  },
  "status": "enabled",
  "sort_order": 10,
  "remark": "Main classroom"
}
```

Business failure:

```json
{
  "code": 409,
  "message": "classroom code already exists",
  "data": {
    "campus_id": 2001,
    "code": "A101"
  }
}
```

### Student Examples

Save request:

```json
{
  "campus_id": 2001,
  "student_no": "S20260610001",
  "name": "Student Zhang",
  "gender": "female",
  "birthday": "2016-03-01",
  "mobile": "13600000000",
  "school": "Demo Primary School",
  "grade": "Grade 3",
  "source": "walk_in",
  "avatar": null,
  "enrolled_at": "2026-06-10",
  "status": "enabled",
  "remark": "Likes painting"
}
```

Save success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 101,
    "tenant_id": 1001,
    "campus_id": 2001,
    "student_no": "S20260610001",
    "name": "Student Zhang",
    "gender": "female",
    "status": "enabled"
  }
}
```

Validation failure:

```json
{
  "code": 422,
  "message": "student_no is required",
  "data": {
    "field": "student_no"
  }
}
```

Business failure:

```json
{
  "code": 409,
  "message": "student number already exists",
  "data": {
    "student_no": "S20260610001"
  }
}
```

### Student Guardian Examples

Guardian list success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 501,
        "guardian_id": 301,
        "guardian_name": "Guardian Li",
        "guardian_mobile": "13900000000",
        "relation": "mother",
        "is_primary": true,
        "can_receive_notice": true,
        "can_submit_leave": true
      }
    ]
  }
}
```

Save relations request:

```json
{
  "relations": [
    {
      "guardian_id": 301,
      "relation": "mother",
      "is_primary": true,
      "can_receive_notice": true,
      "can_submit_leave": true,
      "remark": "Main contact"
    },
    {
      "guardian_id": 302,
      "relation": "father",
      "is_primary": false,
      "can_receive_notice": true,
      "can_submit_leave": false,
      "remark": null
    }
  ]
}
```

Business failure:

```json
{
  "code": 403,
  "message": "guardian is outside current tenant",
  "data": {
    "guardian_id": 302
  }
}
```

### Guardian Examples

Save request:

```json
{
  "name": "Guardian Li",
  "mobile": "13900000000",
  "gender": "female",
  "openid": "wx-openid-001",
  "unionid": "wx-unionid-001",
  "status": "enabled",
  "remark": "Mother"
}
```

Business failure:

```json
{
  "code": 409,
  "message": "guardian mobile already exists",
  "data": {
    "mobile": "13900000000"
  }
}
```

### Teacher Examples

Save request:

```json
{
  "campus_id": 2001,
  "user_profile_id": 701,
  "teacher_no": "T20260610001",
  "name": "Teacher Wang",
  "mobile": "13800000000",
  "gender": "male",
  "birthday": "1990-01-01",
  "title": "Senior Teacher",
  "hire_date": "2026-06-01",
  "avatar": null,
  "introduction": "Art teacher",
  "status": "enabled",
  "remark": "Full time"
}
```

Business failure:

```json
{
  "code": 422,
  "message": "user profile must be an enabled teacher profile",
  "data": {
    "user_profile_id": 701
  }
}
```

Status request:

```json
{
  "status": "disabled"
}
```

Status success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 201,
    "status": "disabled"
  }
}
```

Delete business failure for referenced rows:

```json
{
  "code": 409,
  "message": "record is referenced by V1 business data",
  "data": {
    "id": 201,
    "resource": "teacher"
  }
}
```

### Endpoint-Level Request/Response/Failure Catalog

Use this catalog as the controller test fixture set. Each API has a concrete request, success response, validation failure response, and business failure response.

#### Classroom Endpoint Catalog

```json
[
  {
    "api": "GET /admin/education/academic/classrooms/page",
    "request": {
      "query": {
        "page": 1,
        "pageSize": 20,
        "campus_id": 2001,
        "keyword": "A101",
        "status": "enabled"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "list": [
          {
            "id": 1,
            "tenant_id": 1001,
            "campus_id": 2001,
            "code": "A101",
            "name": "A101",
            "status": "enabled"
          }
        ],
        "total": 1
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "page is required",
      "data": {
        "field": "page"
      }
    },
    "business_failure": {
      "code": 403,
      "message": "campus is outside current context",
      "data": {
        "campus_id": 9999
      }
    }
  },
  {
    "api": "POST /admin/education/academic/classrooms",
    "request": {
      "body": {
        "campus_id": 2001,
        "code": "A101",
        "name": "A101",
        "capacity": 20,
        "location": "Building A 1F",
        "equipment": {
          "piano": true,
          "projector": false
        },
        "status": "enabled",
        "sort_order": 10,
        "remark": "Main classroom"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "id": 1,
        "tenant_id": 1001,
        "campus_id": 2001,
        "code": "A101",
        "name": "A101",
        "status": "enabled"
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "code is required",
      "data": {
        "field": "code"
      }
    },
    "business_failure": {
      "code": 409,
      "message": "classroom code already exists",
      "data": {
        "campus_id": 2001,
        "code": "A101"
      }
    }
  },
  {
    "api": "PUT /admin/education/academic/classrooms/{id}",
    "request": {
      "path": {
        "id": 1
      },
      "body": {
        "campus_id": 2001,
        "code": "A102",
        "name": "A102",
        "capacity": 24,
        "location": "Building A 1F",
        "equipment": {
          "piano": false,
          "projector": true
        },
        "status": "enabled",
        "sort_order": 20,
        "remark": "Updated classroom"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "id": 1,
        "code": "A102",
        "name": "A102",
        "status": "enabled"
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "name is required",
      "data": {
        "field": "name"
      }
    },
    "business_failure": {
      "code": 404,
      "message": "classroom not found in current context",
      "data": {
        "id": 1
      }
    }
  },
  {
    "api": "PUT /admin/education/academic/classrooms/{id}/status",
    "request": {
      "path": {
        "id": 1
      },
      "body": {
        "status": "disabled"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "id": 1,
        "status": "disabled"
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "status must be one of enabled, disabled",
      "data": {
        "field": "status"
      }
    },
    "business_failure": {
      "code": 403,
      "message": "classroom is outside current campus scope",
      "data": {
        "id": 1,
        "campus_id": 9999
      }
    }
  },
  {
    "api": "DELETE /admin/education/academic/classrooms/{id}",
    "request": {
      "path": {
        "id": 1
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": true
    },
    "validation_failure": {
      "code": 422,
      "message": "id must be a positive integer",
      "data": {
        "field": "id"
      }
    },
    "business_failure": {
      "code": 409,
      "message": "record is referenced by V1 business data",
      "data": {
        "id": 1,
        "resource": "classroom"
      }
    }
  }
]
```

#### Student Endpoint Catalog

```json
[
  {
    "api": "GET /admin/education/academic/students/page",
    "request": {
      "query": {
        "page": 1,
        "pageSize": 20,
        "campus_id": 2001,
        "keyword": "Student Zhang",
        "status": "enabled"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "list": [
          {
            "id": 101,
            "tenant_id": 1001,
            "campus_id": 2001,
            "student_no": "S20260610001",
            "name": "Student Zhang",
            "gender": "female",
            "status": "enabled",
            "guardian_count": 1
          }
        ],
        "total": 1
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "pageSize must be between 1 and 100",
      "data": {
        "field": "pageSize"
      }
    },
    "business_failure": {
      "code": 403,
      "message": "campus is outside current context",
      "data": {
        "campus_id": 9999
      }
    }
  },
  {
    "api": "POST /admin/education/academic/students",
    "request": {
      "body": {
        "campus_id": 2001,
        "student_no": "S20260610001",
        "name": "Student Zhang",
        "gender": "female",
        "birthday": "2016-03-01",
        "mobile": "13600000000",
        "school": "Demo Primary School",
        "grade": "Grade 3",
        "source": "walk_in",
        "avatar": null,
        "enrolled_at": "2026-06-10",
        "status": "enabled",
        "remark": "Likes painting"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "id": 101,
        "tenant_id": 1001,
        "campus_id": 2001,
        "student_no": "S20260610001",
        "name": "Student Zhang",
        "gender": "female",
        "status": "enabled"
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "student_no is required",
      "data": {
        "field": "student_no"
      }
    },
    "business_failure": {
      "code": 409,
      "message": "student number already exists",
      "data": {
        "student_no": "S20260610001"
      }
    }
  },
  {
    "api": "PUT /admin/education/academic/students/{id}",
    "request": {
      "path": {
        "id": 101
      },
      "body": {
        "campus_id": 2001,
        "student_no": "S20260610002",
        "name": "Student Zhang Updated",
        "gender": "female",
        "birthday": "2016-03-01",
        "mobile": "13600000001",
        "school": "Demo Primary School",
        "grade": "Grade 4",
        "source": "walk_in",
        "avatar": null,
        "enrolled_at": "2026-06-10",
        "status": "enabled",
        "remark": "Updated profile"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "id": 101,
        "student_no": "S20260610002",
        "name": "Student Zhang Updated",
        "status": "enabled"
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "gender must be one of male, female, unknown",
      "data": {
        "field": "gender"
      }
    },
    "business_failure": {
      "code": 404,
      "message": "student not found in current context",
      "data": {
        "id": 101
      }
    }
  },
  {
    "api": "PUT /admin/education/academic/students/{id}/status",
    "request": {
      "path": {
        "id": 101
      },
      "body": {
        "status": "disabled"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "id": 101,
        "status": "disabled"
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "status must be one of enabled, disabled",
      "data": {
        "field": "status"
      }
    },
    "business_failure": {
      "code": 403,
      "message": "student is outside current campus scope",
      "data": {
        "id": 101,
        "campus_id": 9999
      }
    }
  },
  {
    "api": "DELETE /admin/education/academic/students/{id}",
    "request": {
      "path": {
        "id": 101
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": true
    },
    "validation_failure": {
      "code": 422,
      "message": "id must be a positive integer",
      "data": {
        "field": "id"
      }
    },
    "business_failure": {
      "code": 409,
      "message": "record is referenced by V1 business data",
      "data": {
        "id": 101,
        "resource": "student"
      }
    }
  }
]
```

#### Student Guardian Endpoint Catalog

```json
[
  {
    "api": "GET /admin/education/academic/students/{id}/guardians",
    "request": {
      "path": {
        "id": 101
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "list": [
          {
            "id": 501,
            "tenant_id": 1001,
            "student_id": 101,
            "guardian_id": 301,
            "guardian_name": "Guardian Li",
            "guardian_mobile": "13900000000",
            "relation": "mother",
            "is_primary": true,
            "can_receive_notice": true,
            "can_submit_leave": true
          }
        ]
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "id must be a positive integer",
      "data": {
        "field": "id"
      }
    },
    "business_failure": {
      "code": 404,
      "message": "student not found in current context",
      "data": {
        "id": 101
      }
    }
  },
  {
    "api": "PUT /admin/education/academic/students/{id}/guardians",
    "request": {
      "path": {
        "id": 101
      },
      "body": {
        "relations": [
          {
            "guardian_id": 301,
            "relation": "mother",
            "is_primary": true,
            "can_receive_notice": true,
            "can_submit_leave": true,
            "remark": "Main contact"
          },
          {
            "guardian_id": 302,
            "relation": "father",
            "is_primary": false,
            "can_receive_notice": true,
            "can_submit_leave": false,
            "remark": null
          }
        ]
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "list": [
          {
            "id": 501,
            "student_id": 101,
            "guardian_id": 301,
            "relation": "mother",
            "is_primary": true
          },
          {
            "id": 502,
            "student_id": 101,
            "guardian_id": 302,
            "relation": "father",
            "is_primary": false
          }
        ]
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "relations is required",
      "data": {
        "field": "relations"
      }
    },
    "business_failure": {
      "code": 403,
      "message": "guardian is outside current tenant",
      "data": {
        "guardian_id": 302
      }
    }
  }
]
```

#### Guardian Endpoint Catalog

```json
[
  {
    "api": "GET /admin/education/academic/guardians/page",
    "request": {
      "query": {
        "page": 1,
        "pageSize": 20,
        "keyword": "13900000000",
        "status": "enabled"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "list": [
          {
            "id": 301,
            "tenant_id": 1001,
            "name": "Guardian Li",
            "mobile": "13900000000",
            "gender": "female",
            "status": "enabled",
            "student_count": 1
          }
        ],
        "total": 1
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "page is required",
      "data": {
        "field": "page"
      }
    },
    "business_failure": {
      "code": 403,
      "message": "tenant is outside current context",
      "data": {
        "tenant_id": 9999
      }
    }
  },
  {
    "api": "POST /admin/education/academic/guardians",
    "request": {
      "body": {
        "name": "Guardian Li",
        "mobile": "13900000000",
        "gender": "female",
        "openid": "wx-openid-001",
        "unionid": "wx-unionid-001",
        "status": "enabled",
        "remark": "Mother"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "id": 301,
        "tenant_id": 1001,
        "name": "Guardian Li",
        "mobile": "13900000000",
        "status": "enabled"
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "mobile is required",
      "data": {
        "field": "mobile"
      }
    },
    "business_failure": {
      "code": 409,
      "message": "guardian mobile already exists",
      "data": {
        "mobile": "13900000000"
      }
    }
  },
  {
    "api": "PUT /admin/education/academic/guardians/{id}",
    "request": {
      "path": {
        "id": 301
      },
      "body": {
        "name": "Guardian Li Updated",
        "mobile": "13900000001",
        "gender": "female",
        "openid": "wx-openid-001",
        "unionid": "wx-unionid-001",
        "status": "enabled",
        "remark": "Updated guardian"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "id": 301,
        "name": "Guardian Li Updated",
        "mobile": "13900000001",
        "status": "enabled"
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "name is required",
      "data": {
        "field": "name"
      }
    },
    "business_failure": {
      "code": 404,
      "message": "guardian not found in current context",
      "data": {
        "id": 301
      }
    }
  },
  {
    "api": "PUT /admin/education/academic/guardians/{id}/status",
    "request": {
      "path": {
        "id": 301
      },
      "body": {
        "status": "disabled"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "id": 301,
        "status": "disabled"
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "status must be one of enabled, disabled",
      "data": {
        "field": "status"
      }
    },
    "business_failure": {
      "code": 403,
      "message": "guardian is outside current tenant scope",
      "data": {
        "id": 301,
        "tenant_id": 9999
      }
    }
  },
  {
    "api": "DELETE /admin/education/academic/guardians/{id}",
    "request": {
      "path": {
        "id": 301
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": true
    },
    "validation_failure": {
      "code": 422,
      "message": "id must be a positive integer",
      "data": {
        "field": "id"
      }
    },
    "business_failure": {
      "code": 409,
      "message": "record is referenced by V1 business data",
      "data": {
        "id": 301,
        "resource": "guardian"
      }
    }
  }
]
```

#### Teacher Endpoint Catalog

```json
[
  {
    "api": "GET /admin/education/academic/teachers/page",
    "request": {
      "query": {
        "page": 1,
        "pageSize": 20,
        "campus_id": 2001,
        "keyword": "Teacher Wang",
        "status": "enabled"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "list": [
          {
            "id": 201,
            "tenant_id": 1001,
            "campus_id": 2001,
            "user_profile_id": 701,
            "teacher_no": "T20260610001",
            "name": "Teacher Wang",
            "mobile": "13800000000",
            "status": "enabled"
          }
        ],
        "total": 1
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "page is required",
      "data": {
        "field": "page"
      }
    },
    "business_failure": {
      "code": 403,
      "message": "campus is outside current context",
      "data": {
        "campus_id": 9999
      }
    }
  },
  {
    "api": "POST /admin/education/academic/teachers",
    "request": {
      "body": {
        "campus_id": 2001,
        "user_profile_id": 701,
        "teacher_no": "T20260610001",
        "name": "Teacher Wang",
        "mobile": "13800000000",
        "gender": "male",
        "birthday": "1990-01-01",
        "title": "Senior Teacher",
        "hire_date": "2026-06-01",
        "avatar": null,
        "introduction": "Art teacher",
        "status": "enabled",
        "remark": "Full time"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "id": 201,
        "tenant_id": 1001,
        "campus_id": 2001,
        "user_profile_id": 701,
        "teacher_no": "T20260610001",
        "name": "Teacher Wang",
        "status": "enabled"
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "teacher_no is required",
      "data": {
        "field": "teacher_no"
      }
    },
    "business_failure": {
      "code": 409,
      "message": "user profile is already linked to another teacher",
      "data": {
        "user_profile_id": 701
      }
    }
  },
  {
    "api": "PUT /admin/education/academic/teachers/{id}",
    "request": {
      "path": {
        "id": 201
      },
      "body": {
        "campus_id": 2001,
        "user_profile_id": 701,
        "teacher_no": "T20260610002",
        "name": "Teacher Wang Updated",
        "mobile": "13800000001",
        "gender": "male",
        "birthday": "1990-01-01",
        "title": "Senior Teacher",
        "hire_date": "2026-06-01",
        "avatar": null,
        "introduction": "Updated art teacher",
        "status": "enabled",
        "remark": "Updated teacher"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "id": 201,
        "teacher_no": "T20260610002",
        "name": "Teacher Wang Updated",
        "status": "enabled"
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "user profile must be an enabled teacher profile",
      "data": {
        "field": "user_profile_id"
      }
    },
    "business_failure": {
      "code": 409,
      "message": "teacher number already exists",
      "data": {
        "teacher_no": "T20260610002"
      }
    }
  },
  {
    "api": "PUT /admin/education/academic/teachers/{id}/status",
    "request": {
      "path": {
        "id": 201
      },
      "body": {
        "status": "disabled"
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": {
        "id": 201,
        "status": "disabled"
      }
    },
    "validation_failure": {
      "code": 422,
      "message": "status must be one of enabled, disabled",
      "data": {
        "field": "status"
      }
    },
    "business_failure": {
      "code": 403,
      "message": "teacher is outside current campus scope",
      "data": {
        "id": 201,
        "campus_id": 9999
      }
    }
  },
  {
    "api": "DELETE /admin/education/academic/teachers/{id}",
    "request": {
      "path": {
        "id": 201
      }
    },
    "success": {
      "code": 200,
      "message": "success",
      "data": true
    },
    "validation_failure": {
      "code": 422,
      "message": "id must be a positive integer",
      "data": {
        "field": "id"
      }
    },
    "business_failure": {
      "code": 409,
      "message": "record is referenced by V1 business data",
      "data": {
        "id": 201,
        "resource": "teacher"
      }
    }
  }
]
```

## PC Admin Page Tasks

### API Client

Create:

```text
mineadmin-education-saas/admin-web/src/api/education/academic/profile.ts
```

Types and methods:

```ts
export type AcademicRecordStatus = 'enabled' | 'disabled'
export type Gender = 'male' | 'female' | 'unknown'
export type GuardianRelation = 'father' | 'mother' | 'grandfather' | 'grandmother' | 'guardian' | 'other'

export function pageClassrooms(params: ClassroomPageParams)
export function createClassroom(data: ClassroomSavePayload)
export function updateClassroom(id: number, data: ClassroomSavePayload)
export function updateClassroomStatus(id: number, status: AcademicRecordStatus)
export function deleteClassroom(id: number)

export function pageStudents(params: StudentPageParams)
export function createStudent(data: StudentSavePayload)
export function updateStudent(id: number, data: StudentSavePayload)
export function updateStudentStatus(id: number, status: AcademicRecordStatus)
export function deleteStudent(id: number)
export function listStudentGuardians(id: number)
export function saveStudentGuardians(id: number, relations: StudentGuardianPayload[])

export function pageGuardians(params: GuardianPageParams)
export function createGuardian(data: GuardianSavePayload)
export function updateGuardian(id: number, data: GuardianSavePayload)
export function updateGuardianStatus(id: number, status: AcademicRecordStatus)
export function deleteGuardian(id: number)

export function pageTeachers(params: TeacherPageParams)
export function createTeacher(data: TeacherSavePayload)
export function updateTeacher(id: number, data: TeacherSavePayload)
export function updateTeacherStatus(id: number, status: AcademicRecordStatus)
export function deleteTeacher(id: number)
```

### Router and Menu

Modify:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Routes:

| Route name | Path | Component | Auth |
| --- | --- | --- | --- |
| `EducationAcademicClassroomList` | `/education/academic/classrooms` | `ClassroomList.vue` | `education:academic:classroom:page` |
| `EducationAcademicStudentList` | `/education/academic/students` | `StudentList.vue` | `education:academic:student:page` |
| `EducationAcademicGuardianList` | `/education/academic/guardians` | `GuardianList.vue` | `education:academic:guardian:page` |
| `EducationAcademicTeacherList` | `/education/academic/teachers` | `TeacherList.vue` | `education:academic:teacher:page` |

Menu labels:

```text
教务 SaaS / V1 课消制 / 教室管理
教务 SaaS / V1 课消制 / 学员档案
教务 SaaS / V1 课消制 / 家长档案
教务 SaaS / V1 课消制 / 教师档案
```

### Shared Page States

Every page:

```text
loading: table loading while page API is pending.
empty: rendered when total is 0.
error: display API message and keep filters.
success: update rows and pagination.
search: reset page to 1 and reload.
reset: clear filters and reload page 1.
permission: hide action buttons without the exact permission code.
submit: disable form submit button while save request is pending.
```

### Classroom Page

Files:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/ClassroomList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/ClassroomForm.vue
```

Filters:

```text
campus_id, keyword, status
```

Columns:

```text
code, name, campus_id, capacity, location, status, sort_order, updated_at, actions
```

Actions:

```text
create: education:academic:classroom:create
edit: education:academic:classroom:update
enable/disable: education:academic:classroom:status
delete: education:academic:classroom:delete
```

### Student Page

Files:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/StudentList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/StudentForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/StudentGuardianDrawer.vue
```

Filters:

```text
campus_id, keyword, gender, status
```

Columns:

```text
student_no, name, gender, campus_id, mobile, school, grade, guardian_count, status, updated_at, actions
```

Actions:

```text
create: education:academic:student:create
edit: education:academic:student:update
enable/disable: education:academic:student:status
delete: education:academic:student:delete
manage guardians: education:academic:student-guardian:save
```

Guardian drawer behavior:

```text
load listStudentGuardians when opened.
allow adding existing guardians selected from guardian list.
show one primary relation.
save calls saveStudentGuardians.
validation failure keeps drawer open.
success closes drawer and refreshes student list.
```

### Guardian Page

Files:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/GuardianList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/GuardianForm.vue
```

Filters:

```text
keyword, gender, status
```

Columns:

```text
name, mobile, gender, student_count, openid, unionid, status, updated_at, actions
```

Actions:

```text
create: education:academic:guardian:create
edit: education:academic:guardian:update
enable/disable: education:academic:guardian:status
delete: education:academic:guardian:delete
```

### Teacher Page

Files:

```text
mineadmin-education-saas/admin-web/src/views/education/academic/TeacherList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/TeacherForm.vue
```

Filters:

```text
campus_id, keyword, gender, status
```

Columns:

```text
teacher_no, name, mobile, campus_id, user_profile_id, title, status, updated_at, actions
```

Actions:

```text
create: education:academic:teacher:create
edit: education:academic:teacher:update
enable/disable: education:academic:teacher:status
delete: education:academic:teacher:delete
```

Teacher form behavior:

```text
user_profile_id selector lists enabled F02 profiles with role_code teacher inside current tenant.
teacher_no is disabled in edit mode.
business failure for duplicate user_profile_id keeps form open and displays API message.
```

## Teacher / Guardian Mobile Page Tasks

This module has no teacher or guardian page because V1-01 only creates base records for later academic workflows.

Mobile impact:

```text
No mobile route is added.
No mobile API client is added.
No teacher page is added.
No guardian page is added.
V1-06 consumes teacher records.
V1-07 consumes student and guardian records.
```

Mobile regression verification:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm build:h5
```

Expected:

```text
Mobile H5 build passes with no V1-01 mobile route changes.
```

## Test Plan

Backend migration tests:

| Test file | Case | Assertion |
| --- | --- | --- |
| `ProfileRecordMigrationTest.php` | `test_profile_record_tables_have_required_columns_and_indexes` | all five tables, required columns, unique indexes, ordinary indexes, and soft delete columns exist |
| `ProfileRecordMigrationTest.php` | `test_migration_rolls_back_profile_record_tables` | rollback removes `edu_student_guardians`, `edu_teachers`, `edu_guardians`, `edu_students`, and `edu_classrooms` |

Repository tests:

| Test file | Case | Assertion |
| --- | --- | --- |
| `ClassroomRepositoryTest.php` | `test_page_filters_by_tenant_and_campus_scope` | other tenant and unauthorized campus rows are excluded |
| `StudentRepositoryTest.php` | `test_student_no_uniqueness_is_tenant_scoped` | same tenant duplicate is detected and other tenant same number is allowed |
| `GuardianRepositoryTest.php` | `test_mobile_uniqueness_is_tenant_scoped` | same tenant duplicate mobile is detected |
| `StudentGuardianRepositoryTest.php` | `test_list_by_student_returns_only_same_tenant_relations` | relation from another tenant is excluded |
| `TeacherRepositoryTest.php` | `test_user_profile_uniqueness_excludes_current_row` | same profile on another teacher is detected and current row update is allowed |

Service tests:

| Test file | Case | Assertion |
| --- | --- | --- |
| `ClassroomServiceTest.php` | `test_create_rejects_campus_outside_context` | business exception code 403 with campus id |
| `StudentServiceTest.php` | `test_save_guardians_rejects_guardian_outside_tenant` | business exception code 403 with guardian id |
| `StudentServiceTest.php` | `test_save_guardians_normalizes_primary_relation` | exactly one relation is primary after save |
| `GuardianServiceTest.php` | `test_duplicate_mobile_returns_conflict` | business exception code 409 with mobile |
| `TeacherServiceTest.php` | `test_teacher_profile_must_have_teacher_role` | non-teacher profile returns 422 |
| `TeacherServiceTest.php` | `test_duplicate_teacher_number_returns_conflict` | business exception code 409 with teacher_no |

Feature tests:

| Test file | Case | Assertion |
| --- | --- | --- |
| `ProfileRecordAdminApiTest.php` | `test_classroom_crud_returns_mineadmin_result_shape` | page/create/update/status/delete return code/message/data |
| `ProfileRecordAdminApiTest.php` | `test_student_crud_and_guardian_save_workflow` | create student, create guardians, save relations, list relations |
| `ProfileRecordAdminApiTest.php` | `test_guardian_crud_returns_expected_fields` | guardian create/update/page returns mobile and student_count |
| `ProfileRecordAdminApiTest.php` | `test_teacher_crud_validates_user_profile` | teacher linked to teacher profile succeeds |
| `ProfileRecordPermissionTest.php` | `test_student_page_requires_permission` | missing page permission returns 403 |
| `ProfileRecordPermissionTest.php` | `test_teacher_create_requires_permission` | missing create permission returns 403 |
| `ProfileRecordIsolationTest.php` | `test_tenant_user_cannot_read_other_tenant_student` | other tenant row is excluded from page and detail operations |
| `ProfileRecordIsolationTest.php` | `test_campus_scoped_user_cannot_update_other_campus_classroom` | update returns 403 |
| `ProfileRecordAuditTest.php` | `test_profile_writes_create_audit_logs` | create/update/status/delete and guardian save write expected F04 action codes |

PC tests:

| Test file | Case | Assertion |
| --- | --- | --- |
| `ClassroomList.spec.ts` | `loads_classrooms_with_filters` | page API receives campus_id, keyword, status |
| `ClassroomList.spec.ts` | `hides_delete_without_permission` | delete button is not rendered |
| `StudentList.spec.ts` | `opens_guardian_drawer_and_saves_relations` | list and save guardian APIs are called |
| `StudentList.spec.ts` | `validation_failure_keeps_student_form_open` | form remains visible after 422 |
| `GuardianList.spec.ts` | `duplicate_mobile_error_keeps_form_open` | form remains open after 409 |
| `TeacherList.spec.ts` | `teacher_profile_selector_filters_teacher_profiles` | selector request includes role_code teacher |
| `StudentGuardianDrawer.spec.ts` | `renders_one_primary_relation` | only one primary marker is visible |

Mobile regression:

| Command | Assertion |
| --- | --- |
| `pnpm build:h5` | mobile workspace still builds with no V1-01 mobile page |

## Execution Commands

### Backend Migration Gate

Run:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter ProfileRecordMigrationTest
php bin/hyperf.php migrate:rollback --step=1
php bin/hyperf.php migrate
```

Expected:

```text
Migrated: 2026_06_10_010100_create_v1_profile_record_tables
ProfileRecordMigrationTest passes.
Rolled back: 2026_06_10_010100_create_v1_profile_record_tables
Migrated: 2026_06_10_010100_create_v1_profile_record_tables
```

### Backend Unit Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter ClassroomRepositoryTest
composer test -- --filter StudentRepositoryTest
composer test -- --filter GuardianRepositoryTest
composer test -- --filter StudentGuardianRepositoryTest
composer test -- --filter TeacherRepositoryTest
composer test -- --filter ClassroomServiceTest
composer test -- --filter StudentServiceTest
composer test -- --filter GuardianServiceTest
composer test -- --filter TeacherServiceTest
```

Expected:

```text
V1-01 repository and service tests pass.
```

### Backend Feature Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter ProfileRecordAdminApiTest
composer test -- --filter ProfileRecordPermissionTest
composer test -- --filter ProfileRecordIsolationTest
composer test -- --filter ProfileRecordAuditTest
```

Expected:

```text
V1-01 admin API, permission, isolation, and audit tests pass.
```

### PC Gate

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- ClassroomList
pnpm test -- StudentList
pnpm test -- GuardianList
pnpm test -- TeacherList
pnpm test -- StudentGuardianDrawer
pnpm build
```

Expected:

```text
PC lint, V1-01 page tests, and production build pass.
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

### V1-01 Final Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter ProfileRecord
composer cs-fix -- --dry-run
composer analyse
cd ../admin-web
pnpm lint
pnpm test -- ClassroomList
pnpm test -- StudentList
pnpm test -- GuardianList
pnpm test -- TeacherList
pnpm test -- StudentGuardianDrawer
pnpm build
cd ../mobile-uniapp
pnpm build:h5
```

Expected:

```text
All V1-01 backend tests pass.
Backend code style dry run passes.
Backend static analysis passes.
PC lint, page tests, and build pass.
Mobile H5 build passes.
```

## Acceptance Gate

V1-01 is accepted only when all conditions are true:

```text
- `edu_classrooms`, `edu_students`, `edu_guardians`, `edu_student_guardians`, and `edu_teachers` exist with documented columns and indexes.
- Migration rollback drops all V1-01 tables in dependency-safe order.
- Classroom, student, guardian, student-guardian, and teacher models cast fields correctly.
- Repositories apply tenant and campus scope filters.
- Services reject duplicate codes, numbers, mobile values, and linked teacher profiles with documented business codes.
- Student guardian save enforces same-tenant visibility and exactly one primary relation.
- Teacher user_profile_id validates an enabled F02 teacher profile.
- Admin APIs return MineAdmin result shape and documented validation/business failures.
- Permission tests prove missing MineAdmin permission codes return 403.
- Isolation tests prove tenant and campus scoped users cannot read or mutate unauthorized rows.
- F04 audit logs are created for all write operations.
- PC API client, routes, list pages, forms, relation drawer, permission buttons, loading, empty, error, success, and submit states pass tests.
- V1-01 adds no mobile pages and mobile H5 build still passes.
```

## Task Breakdown

### Task 1: Create Migration, Enums, and Models

**Files:**

- Create: `mineadmin-education-saas/backend/databases/migrations/2026_06_10_010100_create_v1_profile_record_tables.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/AcademicRecordStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/Gender.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/GuardianRelation.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationClassroom.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudent.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationGuardian.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudentGuardian.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationTeacher.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/ProfileRecordMigrationTest.php`

- [x] **Step 1: Create migration**

Use the full migration from `Database Migration Design`.

- [x] **Step 2: Create enums**

Create `AcademicRecordStatus`, `Gender`, and `GuardianRelation` exactly as defined in `MineAdmin Backend Module Design`.

- [x] **Step 3: Create models**

Create all five models with table names, fillable fields, casts, timestamps, and soft delete behavior defined in `MineAdmin Backend Module Design`.

- [x] **Step 4: Write migration test**

Create `ProfileRecordMigrationTest` with cases listed in `Test Plan`.

- [x] **Step 5: Run migration gate**

Run commands from `Backend Migration Gate`.

Expected:

```text
Migration, rollback, re-migration, and migration tests pass.
```

### Task 2: Create Repositories and Services

**Files:**

- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/ClassroomRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/StudentRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/GuardianRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/StudentGuardianRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/TeacherRepository.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/ClassroomService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/StudentService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/GuardianService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/TeacherService.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/ClassroomRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/StudentRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/GuardianRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/StudentGuardianRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/ClassroomServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/StudentServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/GuardianServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/TeacherServiceTest.php`

- [x] **Step 1: Create repositories**

Implement repository methods, filters, tenant scope, and campus scope rules from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create services**

Implement service methods, duplicate validation, same-tenant validation, teacher profile validation, audit dispatch, and delete guards from `MineAdmin Backend Module Design`.

- [x] **Step 3: Write repository tests**

Create repository tests listed in `Test Plan`.

- [x] **Step 4: Write service tests**

Create service tests listed in `Test Plan`.

- [x] **Step 5: Run backend unit gate**

Run commands from `Backend Unit Gate`.

Expected:

```text
V1-01 repository and service tests pass.
```

### Task 3: Create Requests, Schemas, and Controllers

**Files:**

- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ClassroomPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ClassroomSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/ClassroomStatusRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/StudentPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/StudentSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/StudentStatusRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/StudentGuardianSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/GuardianPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/GuardianSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/GuardianStatusRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/TeacherPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/TeacherSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/TeacherStatusRequest.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/ClassroomSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/StudentSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/GuardianSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/StudentGuardianSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/TeacherSchema.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/ClassroomController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/StudentController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/GuardianController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/TeacherController.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/ProfileRecordAdminApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/ProfileRecordPermissionTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/ProfileRecordIsolationTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/ProfileRecordAuditTest.php`

- [x] **Step 1: Create request classes**

Use the request rules and messages from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create schema classes**

Use schema fields from `MineAdmin Backend Module Design`.

- [x] **Step 3: Create admin controllers**

Use endpoint matrix, permissions, middleware, context resolver, and response envelope from `API Contract`.

- [x] **Step 4: Write feature tests**

Create API, permission, isolation, and audit tests listed in `Test Plan`.

- [x] **Step 5: Run backend feature gate**

Run commands from `Backend Feature Gate`.

Expected:

```text
V1-01 admin API, permission, isolation, and audit tests pass.
```

### Task 4: Create PC API Client, Routes, Pages, and Forms

**Files:**

- Create: `mineadmin-education-saas/admin-web/src/api/education/academic/profile.ts`
- Modify: `mineadmin-education-saas/admin-web/src/router/modules/education.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/ClassroomList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/StudentList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/GuardianList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/TeacherList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/ClassroomForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/StudentForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/GuardianForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/StudentGuardianDrawer.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/TeacherForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/ClassroomList.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/StudentList.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/GuardianList.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/TeacherList.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/StudentGuardianDrawer.spec.ts`

- [x] **Step 1: Create typed API client**

Implement all types and methods listed in `PC Admin Page Tasks`.

- [x] **Step 2: Add routes and menus**

Add V1-01 route entries and auth meta to `admin-web/src/router/modules/education.ts`.

- [x] **Step 3: Create list pages and forms**

Implement classroom, student, guardian, and teacher pages and form components using page tasks from `PC Admin Page Tasks`.

- [x] **Step 4: Create student guardian drawer**

Implement relationship load, edit, save, primary relation display, validation failure, and success reload behavior.

- [x] **Step 5: Write PC tests**

Create PC tests listed in `Test Plan`.

- [x] **Step 6: Run PC gate**

Run commands from `PC Gate`.

Expected:

```text
PC lint, V1-01 page tests, and production build pass.
```

### Task 5: Run V1-01 Final Gate

**Files:**

- Verify: all backend, PC, and mobile paths listed in `File Structure`.

- [x] **Step 1: Run backend final gate**

Run backend commands from `V1-01 Final Gate`.

- [x] **Step 2: Run PC final gate**

Run PC commands from `V1-01 Final Gate`.

- [x] **Step 3: Run mobile regression gate**

Run mobile command from `V1-01 Final Gate`.

- [x] **Step 4: Commit V1-01**

Run:

```bash
cd mineadmin-education-saas
git add backend admin-web mobile-uniapp
git commit -m "feat: add v1 academic profile records"
```

Expected:

```text
Commit succeeds with V1-01 backend, PC, tests, and verification changes.
```

## Self-Review

- Spec coverage: V1-01 covers classrooms, students, guardians, student-guardian relationships, and teachers from the V1 profile record scope.
- MineAdmin fit: The plan uses MineAdmin 3.x `app/Http/Admin`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, `databases/migrations`, permission attributes, OperationMiddleware for writes, and MineAdmin result shape.
- Tenant isolation: All records are tenant-scoped; classroom, student, and teacher records are campus-scoped; guardians are tenant-scoped; relation saves validate same tenant.
- V1 dependency fit: V1-02 can use teachers and students; V1-03 can use classrooms, teachers, and students; V1-06 can use linked teacher profiles; V1-07 can use student-guardian relationships.
- PC fit: Pages include typed API client, route/menu entries, list/search/form/action behavior, permission buttons, relation drawer, loading, empty, error, success, and submit states.
- Mobile fit: V1-01 adds no visible mobile page and keeps mobile build verification.
- Readiness: This plan has exact paths, full migration design, backend layer tasks, API request/response/failure examples, PC tasks, mobile rationale, tests, commands, expected outputs, and acceptance gates, so V1-01 can be marked `ready`.
