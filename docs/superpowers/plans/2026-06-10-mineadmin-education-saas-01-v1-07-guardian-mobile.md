# MineAdmin Education SaaS V1-07 Guardian Mobile Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement guardian mobile workflows for bound student selection, schedules, course accounts, consumption ledger, notices, and leave request creation, plus the PC notice publishing backend needed by those mobile pages.

**Architecture:** V1-07 depends on F06 mobile context, V1-01 guardian/student binding, V1-02 student course accounts, V1-03 lessons and lesson-student snapshots, V1-04 consumption ledger, and V1-05 leave requests. Backend mobile APIs live under `backend/app/Http/Api`, PC notice APIs live under `backend/app/Http/Admin`, and shared business logic stays in `backend/app/Service`, `backend/app/Repository`, `backend/app/Model`, and `backend/app/Schema`. V1-07 adds only notice and notice receipt tables; guardian account, schedule, consumption, and leave flows consume existing V1 data.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, MineAdmin current user context, MineAdmin-Vue, Vue3, TypeScript, uni-app, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. V1 core academic gates have passed.

---

## Scope Check

Included:

- Create V1-07 notice and notice receipt migrations.
- Create notice enums, models, repositories, services, request classes, schemas, and admin/mobile controllers.
- Resolve current guardian through F06 guardian mobile context and V1-01 `edu_guardians` openid, unionid, or mobile.
- Enforce student isolation through V1-01 `edu_student_guardians`.
- Return bound student selector data for the current guardian.
- Return bound student lesson schedule from V1-03 lessons and lesson-student snapshots.
- Return bound student course accounts from V1-02.
- Return bound student consumption ledger from V1-04.
- Create guardian leave requests through V1-05-compatible leave creation rules.
- Publish PC notices to guardian receipt rows by target type: all, campus, class, or student.
- Return guardian notice list, detail, and read state from mobile APIs.
- Add PC NoticeList page, notice form, receipt drawer, API client, route/menu entries, button permissions, loading/empty/error states, and tests.
- Add uni-app guardian API client, student selector, schedule, account, consumption, notice list/detail, leave create pages, shared components, state handling, and tests.
- Add backend migration, repository, service, API, permission, tenant/campus isolation, guardian isolation, notice receipt, PC, mobile, and regression tests.

Excluded:

- WeChat OAuth, openid binding, mobile token issuing, and guardian identity onboarding. Foundation/F06 and later auth work own them.
- Guardian payment, refund, invoice, and finance reconciliation pages. V4 owns finance.
- Learning report, family service growth features, homework, and study content. Later V7 Family Service and V12 Content own them.
- Teacher mobile pages. V1-06 owns teacher mobile.
- PC leave management and make-up scheduling. V1-05 owns PC leave and lesson change workflows.
- Attendance submission or account balance mutation from guardian mobile. V1-04 owns attendance and account mutations.
- Push notification delivery to WeChat template messages. V1-07 stores notice receipts and read state only.

Business rules:

```text
All guardian mobile APIs require F06 MobileEducationContextMiddleware.
The current education profile role_code must be guardian.
The current guardian profile must map to one enabled V1-01 edu_guardians row in the same tenant.
Guardian mapping tries unionid first, openid second, and mobile third; every match is tenant-scoped.
If no enabled guardian record is found, guardian mobile APIs return code 403 with message guardian record is not bound.
Guardian can access only students linked through edu_student_guardians for the resolved guardian_id.
Student binding requires edu_student_guardians.deleted_at null and edu_students.status enabled.
can_receive_notice controls whether a guardian receives generated notice receipts for that student.
can_submit_leave controls whether a guardian can create leave requests for that student.
Guardian schedule, account, consumption, notice, and leave APIs must validate bound student visibility before reading or writing.
Guardian mobile account and consumption APIs are read-only.
Guardian leave creation does not approve leave, schedule make-up lessons, or mutate course account balances.
Notice publishing creates immutable receipt rows for target guardian-student pairs.
Notice read state is stored per notice, guardian, and student.
Withdrawn notices remain visible to PC admins but are hidden from guardian mobile lists unless already read detail is explicitly requested by receipt id.
```

Notice target rules:

```text
target_type all publishes to all enabled students with guardians who can_receive_notice.
target_type campus publishes to enabled students in target campus with guardians who can_receive_notice.
target_type class publishes to active class students in target class with guardians who can_receive_notice.
target_type student publishes to one enabled student and guardians who can_receive_notice.
Notice publish is allowed only from draft status.
Notice withdraw is allowed only from published status.
Publish can be immediate or scheduled by published_at, but V1-07 implementation treats publish API as the moment receipts are created.
Receipt uniqueness is tenant_id + notice_id + guardian_id + student_id.
Reading a notice marks only the current guardian-student receipt as read.
```

State machines:

```text
Notice status: draft -> published -> withdrawn.
Notice receipt status: unread -> read.
Notice target type: all, campus, class, student.
Notice type: academic, activity, fee, system.
Notice priority: normal, important, urgent.
Leave source for guardian mobile: guardian.
Leave status after guardian creation: pending.
```

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_010700_create_v1_notice_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/NoticeTargetType.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/NoticeType.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/NoticePriority.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/NoticeStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/NoticeReceiptStatus.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationNotice.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationNoticeReceipt.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/GuardianMobileRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/NoticeRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/NoticeReceiptRepository.php
mineadmin-education-saas/backend/app/Service/Education/Academic/GuardianMobileContextResolver.php
mineadmin-education-saas/backend/app/Service/Education/Academic/GuardianMobileService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/GuardianMobileLeaveService.php
mineadmin-education-saas/backend/app/Service/Education/Academic/NoticeService.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianStudentListRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianStudentLessonPageRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianStudentAccountPageRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianStudentConsumptionPageRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianNoticePageRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianNoticeDetailRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianNoticeReadRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianLeaveCreateRequest.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/GuardianStudentController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/GuardianScheduleController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/GuardianAccountController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/GuardianConsumptionController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/GuardianNoticeController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/GuardianLeaveController.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/NoticePageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/NoticeSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/NoticePublishRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/NoticeWithdrawRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/NoticeReceiptPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/NoticeController.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/GuardianStudentSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/GuardianLessonSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/GuardianAccountSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/GuardianConsumptionSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/GuardianLeaveSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/NoticeSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Academic/NoticeReceiptSchema.php
```

Read backend dependencies:

```text
mineadmin-education-saas/backend/app/Http/Api/Middleware/Education/Foundation/MobileEducationContextMiddleware.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/EducationUserContext.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusScopeService.php
mineadmin-education-saas/backend/app/Event/Education/Foundation/EducationAuditEvent.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudent.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationGuardian.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudentGuardian.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLesson.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonStudent.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationStudentCourseAccount.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLessonConsumption.php
mineadmin-education-saas/backend/app/Model/Education/Academic/EducationLeaveRequest.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/StudentRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/GuardianRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/StudentGuardianRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/LessonStudentRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/StudentCourseAccountRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/ConsumptionRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Academic/LeaveRequestRepository.php
mineadmin-education-saas/backend/app/Service/Education/Academic/LeaveRequestService.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Academic/GuardianNoticeMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/GuardianMobileContextResolverTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/GuardianMobileRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/NoticeRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/NoticeReceiptRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/GuardianMobileServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/GuardianMobileLeaveServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Academic/NoticeServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/GuardianMobileStudentApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/GuardianMobileAcademicApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/GuardianMobileNoticeApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/GuardianMobileLeaveApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/GuardianMobileRoleIsolationTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/GuardianMobileStudentIsolationTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/NoticeAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/NoticePermissionTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/NoticeAuditTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Academic/GuardianMobileRegressionTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/academic/notice.ts
mineadmin-education-saas/admin-web/src/views/education/academic/NoticeList.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/NoticeForm.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/NoticeReceiptDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/components/NoticeDetailDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/NoticeList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/NoticeForm.spec.ts
mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/NoticeReceiptDrawer.spec.ts
```

Modify PC:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Create mobile:

```text
mineadmin-education-saas/mobile-uniapp/src/api/academic/guardian.ts
mineadmin-education-saas/mobile-uniapp/src/api/academic/__tests__/guardian.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/guardian/student/index.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/student/__tests__/index.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/guardian/schedule/index.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/schedule/__tests__/index.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/guardian/account/index.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/account/__tests__/index.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/guardian/consumption/index.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/consumption/__tests__/index.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/guardian/notice/index.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/notice/detail.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/notice/__tests__/index.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/guardian/notice/__tests__/detail.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/guardian/leave/create.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/leave/__tests__/create.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/guardian/components/GuardianStateBlock.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/components/StudentSelector.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/components/NoticeStatusBadge.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/components/AccountBalanceCard.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/components/__tests__/GuardianStateBlock.spec.ts
mineadmin-education-saas/mobile-uniapp/tests/academic/guardianPagesJson.spec.ts
```

Modify mobile:

```text
mineadmin-education-saas/mobile-uniapp/pages.json
mineadmin-education-saas/mobile-uniapp/pages/guardian/index.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/__tests__/index.spec.ts
```

## Database Migration Design

Migration file:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_010700_create_v1_notice_tables.php
```

Tables:

```text
edu_notices
edu_notice_receipts
```

Foreign-key policy:

```text
Use no physical foreign keys in V1-07.
Use service-level validation against V1-01 students/guardians/student_guardians, V1-03 classes/lessons, and V1-04/V1-05 references.
Reason: MineAdmin business tables use soft deletes, tenant isolation, and staged module migrations; service validation keeps rollback and soft-delete behavior predictable.
```

Rollback order:

```text
Schema::dropIfExists('edu_notice_receipts');
Schema::dropIfExists('edu_notices');
```

### `edu_notices`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Notice id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | yes | null | Target campus id when target is campus, class, or student |
| `notice_no` | varchar(64) | no | none | Notice number |
| `notice_type` | varchar(30) | no | `academic` | academic, activity, fee, or system |
| `target_type` | varchar(30) | no | `all` | all, campus, class, or student |
| `target_id` | bigint unsigned | yes | null | Campus, class, or student id depending on target_type |
| `title` | varchar(160) | no | none | Notice title |
| `content` | text | no | none | Notice content |
| `priority` | varchar(20) | no | `normal` | normal, important, or urgent |
| `status` | varchar(20) | no | `draft` | draft, published, or withdrawn |
| `published_at` | timestamp | yes | null | Publish time |
| `published_by` | bigint unsigned | yes | null | Publisher user id |
| `withdrawn_at` | timestamp | yes | null | Withdraw time |
| `withdrawn_by` | bigint unsigned | yes | null | Withdraw operator user id |
| `withdraw_reason` | varchar(500) | yes | null | Withdraw reason |
| `expire_at` | timestamp | yes | null | Expiry time |
| `receipt_count` | int unsigned | no | 0 | Generated receipt count |
| `read_count` | int unsigned | no | 0 | Read receipt count |
| `remark` | varchar(500) | yes | null | Internal remark |
| `created_by` | bigint unsigned | yes | null | Creator user id |
| `updated_by` | bigint unsigned | yes | null | Updater user id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_notices_tenant_no (tenant_id, notice_no)
index idx_edu_notices_tenant_status_publish (tenant_id, status, published_at)
index idx_edu_notices_tenant_target (tenant_id, target_type, target_id)
index idx_edu_notices_tenant_campus_status (tenant_id, campus_id, status)
index idx_edu_notices_tenant_type_priority (tenant_id, notice_type, priority)
index idx_edu_notices_expire_at (expire_at)
index idx_edu_notices_deleted_at (deleted_at)
```

### `edu_notice_receipts`

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Notice receipt id |
| `tenant_id` | bigint unsigned | no | none | Tenant id |
| `campus_id` | bigint unsigned | yes | null | Student campus id |
| `notice_id` | bigint unsigned | no | none | Notice id |
| `guardian_id` | bigint unsigned | no | none | Guardian id |
| `student_id` | bigint unsigned | no | none | Student id |
| `relation` | varchar(30) | yes | null | Snapshot relation from student guardian binding |
| `guardian_name_snapshot` | varchar(120) | no | none | Guardian name snapshot |
| `student_name_snapshot` | varchar(120) | no | none | Student name snapshot |
| `status` | varchar(20) | no | `unread` | unread or read |
| `delivered_at` | timestamp | yes | null | Receipt creation or delivery time |
| `read_at` | timestamp | yes | null | Read time |
| `read_by_profile_id` | bigint unsigned | yes | null | Mobile guardian profile id |
| `created_at` | timestamp | yes | null | Created time |
| `updated_at` | timestamp | yes | null | Updated time |
| `deleted_at` | timestamp | yes | null | Soft delete time |

Indexes:

```text
unique uk_edu_notice_receipts_tenant_notice_guardian_student (tenant_id, notice_id, guardian_id, student_id)
index idx_edu_notice_receipts_tenant_guardian_status (tenant_id, guardian_id, status)
index idx_edu_notice_receipts_tenant_student_status (tenant_id, student_id, status)
index idx_edu_notice_receipts_tenant_notice_status (tenant_id, notice_id, status)
index idx_edu_notice_receipts_read_at (read_at)
index idx_edu_notice_receipts_deleted_at (deleted_at)
```

## MineAdmin Backend Module Design

### Enums

Create `NoticeTargetType`:

```php
enum NoticeTargetType: string
{
    case All = 'all';
    case Campus = 'campus';
    case Class = 'class';
    case Student = 'student';
}
```

Create `NoticeType`:

```php
enum NoticeType: string
{
    case Academic = 'academic';
    case Activity = 'activity';
    case Fee = 'fee';
    case System = 'system';
}
```

Create `NoticePriority`:

```php
enum NoticePriority: string
{
    case Normal = 'normal';
    case Important = 'important';
    case Urgent = 'urgent';
}
```

Create `NoticeStatus`:

```php
enum NoticeStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Withdrawn = 'withdrawn';
}
```

Create `NoticeReceiptStatus`:

```php
enum NoticeReceiptStatus: string
{
    case Unread = 'unread';
    case Read = 'read';
}
```

### Models

`EducationNotice`:

```text
table: edu_notices
fillable: tenant_id, campus_id, notice_no, notice_type, target_type, target_id, title, content, priority, status, published_at, published_by, withdrawn_at, withdrawn_by, withdraw_reason, expire_at, receipt_count, read_count, remark, created_by, updated_by
casts: tenant_id integer, campus_id integer, target_id integer, published_at datetime, published_by integer, withdrawn_at datetime, withdrawn_by integer, expire_at datetime, receipt_count integer, read_count integer
relationships: receipts hasMany EducationNoticeReceipt
```

`EducationNoticeReceipt`:

```text
table: edu_notice_receipts
fillable: tenant_id, campus_id, notice_id, guardian_id, student_id, relation, guardian_name_snapshot, student_name_snapshot, status, delivered_at, read_at, read_by_profile_id
casts: tenant_id integer, campus_id integer, notice_id integer, guardian_id integer, student_id integer, delivered_at datetime, read_at datetime, read_by_profile_id integer
relationships: notice belongsTo EducationNotice
```

### Context Resolver

Create `GuardianMobileContextResolver`.

Methods:

```php
public function resolveGuardian(EducationUserContext $context): EducationGuardian
public function assertGuardianRole(EducationUserContext $context): void
public function currentOperatorId(EducationUserContext $context): ?int
```

Behavior:

```text
Reject non-guardian role with code 403 and message guardian mobile role required.
Find enabled edu_guardians by tenant_id and unionid when profile unionid is present.
If unionid is absent or not matched, find by tenant_id and openid when profile openid is present.
If openid is absent or not matched, find by tenant_id and mobile when profile mobile is present.
Reject no match or disabled guardian with code 403 and message guardian record is not bound.
Return the matched guardian id for all downstream isolation checks.
```

### Repositories

`GuardianMobileRepository` methods:

```php
public function listBoundStudents(int $tenantId, int $guardianId): array
public function assertBoundStudent(int $tenantId, int $guardianId, int $studentId): array
public function canSubmitLeave(int $tenantId, int $guardianId, int $studentId): bool
public function pageStudentLessons(int $tenantId, int $guardianId, int $studentId, array $params, int $page, int $pageSize): array
public function pageStudentAccounts(int $tenantId, int $guardianId, int $studentId, array $params, int $page, int $pageSize): array
public function pageStudentConsumptions(int $tenantId, int $guardianId, int $studentId, array $params, int $page, int $pageSize): array
public function findLessonStudentForLeave(int $tenantId, int $guardianId, int $lessonStudentId): ?EducationLessonStudent
```

Query requirements:

```text
Every query filters tenant_id.
Every student-specific query first proves edu_student_guardians.tenant_id, guardian_id, student_id, and deleted_at null.
Bound student list joins edu_students and returns enabled students only.
Schedule reads edu_lesson_students joined to edu_lessons and filters by student_id.
Account page reads edu_student_course_accounts by student_id and tenant_id.
Consumption page reads edu_lesson_consumptions by student_id and tenant_id.
Leave lookup reads a lesson_student row only when its student_id is bound to the guardian.
```

`NoticeRepository` methods:

```php
public function pageAdmin(array $params, int $page, int $pageSize, EducationUserContext $context): array
public function findAdminVisible(int $id, EducationUserContext $context): ?EducationNotice
public function createDraft(array $data, EducationUserContext $context, ?int $operatorId): EducationNotice
public function updateDraft(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationNotice
public function markPublished(int $id, int $receiptCount, ?int $operatorId): EducationNotice
public function markWithdrawn(int $id, string $reason, ?int $operatorId): EducationNotice
```

`NoticeReceiptRepository` methods:

```php
public function buildPublishTargets(EducationNotice $notice, EducationUserContext $context): array
public function createReceipts(EducationNotice $notice, array $targets): int
public function pageAdminReceipts(int $noticeId, array $params, int $page, int $pageSize, EducationUserContext $context): array
public function pageGuardianReceipts(int $tenantId, int $guardianId, array $params, int $page, int $pageSize): array
public function findGuardianReceipt(int $tenantId, int $guardianId, int $receiptId): ?EducationNoticeReceipt
public function markRead(int $receiptId, int $profileId): EducationNoticeReceipt
```

Publish target row:

```text
guardian_id, student_id, campus_id, relation, guardian_name_snapshot, student_name_snapshot
```

### Services

`GuardianMobileService` methods:

```php
public function students(array $params, EducationUserContext $context): array
public function lessons(int $studentId, array $params, EducationUserContext $context): array
public function accounts(int $studentId, array $params, EducationUserContext $context): array
public function consumptions(int $studentId, array $params, EducationUserContext $context): array
public function notices(array $params, EducationUserContext $context): array
public function noticeDetail(int $receiptId, array $params, EducationUserContext $context): array
public function markNoticeRead(int $receiptId, array $params, EducationUserContext $context): array
```

Notice read behavior:

```text
Resolve current guardian.
Find receipt by receipt id, tenant, and guardian.
Reject hidden or deleted receipt with code 404.
If receipt is unread, set status read, read_at now, and read_by_profile_id.
Increment edu_notices.read_count only when status changes from unread to read.
Return receipt and notice detail.
```

`GuardianMobileLeaveService` methods:

```php
public function create(array $payload, EducationUserContext $context): array
```

Leave creation transaction:

```text
Resolve current guardian.
Find lesson_student_id in current tenant and verify its student_id is bound to guardian.
Verify can_submit_leave is true for the guardian-student relation.
Reject cancelled lesson-student rows.
Reject duplicate leave request for the same lesson_student_id through V1-05 rules.
Create leave request with source guardian, guardian_id, leave_type, reason, makeup_required, and requested_at.
Do not approve leave, create attendance, create make-up lesson, or change account balance.
Write audit event education.academic.guardian_mobile.leave_created.
Return GuardianLeaveSchema.
```

`NoticeService` methods:

```php
public function page(array $params, EducationUserContext $context): array
public function detail(int $id, EducationUserContext $context): array
public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationNotice
public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationNotice
public function publish(int $id, array $payload, EducationUserContext $context, ?int $operatorId): array
public function withdraw(int $id, array $payload, EducationUserContext $context, ?int $operatorId): EducationNotice
public function receipts(int $id, array $params, EducationUserContext $context): array
```

Publish transaction:

```text
Load draft notice in current tenant and campus scope.
Validate target_type and target_id point to visible campus, class, or student when required.
Build publish targets from student_guardians where can_receive_notice true.
Reject publish with code 409 when target set is empty.
Insert receipt rows using unique key tenant_id + notice_id + guardian_id + student_id.
Set notice status published, published_at, published_by, and receipt_count.
Dispatch audit event education.academic.notice.published.
Commit transaction and return notice plus receipt_count.
```

Audit events:

```text
education.academic.notice.created
education.academic.notice.updated
education.academic.notice.published
education.academic.notice.withdrawn
education.academic.guardian_mobile.notice_read
education.academic.guardian_mobile.leave_created
```

### Request Classes

`GuardianStudentListRequest` rules:

```php
[
    'client_type' => ['nullable', 'in:wechat_service,wechat_miniprogram,h5'],
]
```

`GuardianStudentLessonPageRequest` rules:

```php
[
    'page' => ['nullable', 'integer', 'min:1'],
    'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
    'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_at'],
    'status' => ['nullable', 'in:scheduled,cancelled,completed'],
]
```

Messages:

```text
start_at.required: start_at is required
end_at.after: end_at must be after start_at
status.in: status has an invalid value
```

`GuardianStudentAccountPageRequest` rules:

```php
[
    'page' => ['nullable', 'integer', 'min:1'],
    'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
    'status' => ['nullable', 'in:active,frozen,closed'],
]
```

`GuardianStudentConsumptionPageRequest` rules:

```php
[
    'page' => ['nullable', 'integer', 'min:1'],
    'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
    'account_id' => ['nullable', 'integer', 'min:1'],
    'source_type' => ['nullable', 'in:attendance,rollback'],
    'status' => ['nullable', 'in:active,reversed'],
    'start_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['nullable', 'date_format:Y-m-d H:i:s', 'after:start_at'],
]
```

`GuardianNoticePageRequest` rules:

```php
[
    'page' => ['nullable', 'integer', 'min:1'],
    'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
    'status' => ['nullable', 'in:unread,read,all'],
    'notice_type' => ['nullable', 'in:academic,activity,fee,system'],
]
```

`GuardianNoticeDetailRequest` and `GuardianNoticeReadRequest` rules:

```php
[]
```

`GuardianLeaveCreateRequest` rules:

```php
[
    'lesson_student_id' => ['required', 'integer', 'min:1'],
    'leave_type' => ['required', 'in:sick,personal,school,other'],
    'reason' => ['required', 'string', 'max:500'],
    'makeup_required' => ['nullable', 'boolean'],
]
```

Messages:

```text
lesson_student_id.required: lesson_student_id is required
leave_type.in: leave_type has an invalid value
reason.required: reason is required
reason.max: reason must not exceed 500 characters
```

`NoticePageRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'notice_type' => ['nullable', 'in:academic,activity,fee,system'],
    'target_type' => ['nullable', 'in:all,campus,class,student'],
    'status' => ['nullable', 'in:draft,published,withdrawn'],
    'keyword' => ['nullable', 'string', 'max:120'],
]
```

`NoticeSaveRequest` rules:

```php
[
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'notice_type' => ['required', 'in:academic,activity,fee,system'],
    'target_type' => ['required', 'in:all,campus,class,student'],
    'target_id' => ['nullable', 'integer', 'min:1'],
    'title' => ['required', 'string', 'max:160'],
    'content' => ['required', 'string', 'max:5000'],
    'priority' => ['required', 'in:normal,important,urgent'],
    'expire_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
    'remark' => ['nullable', 'string', 'max:500'],
]
```

Service-level target validation:

```text
target_type all requires target_id null.
target_type campus requires campus_id or target_id to point to an enabled campus.
target_type class requires target_id to point to a visible V1-03 class.
target_type student requires target_id to point to a visible V1-01 student.
```

`NoticePublishRequest` rules:

```php
[
    'published_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
]
```

`NoticeWithdrawRequest` rules:

```php
[
    'withdraw_reason' => ['required', 'string', 'max:500'],
]
```

`NoticeReceiptPageRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'status' => ['nullable', 'in:unread,read'],
    'student_id' => ['nullable', 'integer', 'min:1'],
    'guardian_id' => ['nullable', 'integer', 'min:1'],
    'keyword' => ['nullable', 'string', 'max:120'],
]
```

### Controllers

Mobile controllers:

```text
GuardianStudentController
- students(GuardianStudentListRequest $request): Result

GuardianScheduleController
- lessons(int $studentId, GuardianStudentLessonPageRequest $request): Result

GuardianAccountController
- accounts(int $studentId, GuardianStudentAccountPageRequest $request): Result

GuardianConsumptionController
- consumptions(int $studentId, GuardianStudentConsumptionPageRequest $request): Result

GuardianNoticeController
- page(GuardianNoticePageRequest $request): Result
- detail(int $receiptId, GuardianNoticeDetailRequest $request): Result
- read(int $receiptId, GuardianNoticeReadRequest $request): Result

GuardianLeaveController
- create(GuardianLeaveCreateRequest $request): Result
```

Admin controller:

```text
NoticeController
- page(NoticePageRequest $request): Result
- detail(int $id): Result
- create(NoticeSaveRequest $request): Result
- update(int $id, NoticeSaveRequest $request): Result
- publish(int $id, NoticePublishRequest $request): Result
- withdraw(int $id, NoticeWithdrawRequest $request): Result
- receipts(int $id, NoticeReceiptPageRequest $request): Result
```

Middleware:

```text
Mobile guardian APIs use MobileEducationContextMiddleware.
Mobile mutating APIs use OperationMiddleware only for notice read and leave create.
Admin notice create/update/publish/withdraw use OperationMiddleware.
Admin notice page/detail/receipts are read-only.
Do not use PC Admin PermissionMiddleware on /mobile endpoints.
```

Permissions:

```text
education:academic:notice:page
education:academic:notice:detail
education:academic:notice:create
education:academic:notice:update
education:academic:notice:publish
education:academic:notice:withdraw
education:academic:notice:receipt
```

### Schemas

`GuardianStudentSchema`:

```text
id, student_no, name, gender, campus_id, campus_name, relation, is_primary, can_receive_notice, can_submit_leave, status
```

`GuardianLessonSchema`:

```text
lesson_id, lesson_student_id, class_id, class_name_snapshot, course_id, course_name_snapshot, teacher_name_snapshot, classroom_name_snapshot, title, start_at, end_at, lesson_units, lesson_status, lesson_student_status
```

`GuardianAccountSchema`:

```text
id, campus_id, student_id, course_id, course_name, purchased_units, bonus_units, consumed_units, adjusted_units, refunded_units, frozen_units, available_units, status, opened_at, expires_at
```

`GuardianConsumptionSchema`:

```text
id, consumption_no, account_id, student_id, course_id, course_name, lesson_id, lesson_title, lesson_start_at, source_type, direction, units, before_available_units, after_available_units, status, created_at
```

`GuardianLeaveSchema`:

```text
id, leave_no, source, leave_type, lesson_id, lesson_student_id, student_id, student_name, course_id, reason, status, requested_at, makeup_required, reviewed_at, review_remark
```

`NoticeSchema`:

```text
id, tenant_id, campus_id, notice_no, notice_type, target_type, target_id, title, content, priority, status, published_at, published_by, withdrawn_at, withdrawn_by, withdraw_reason, expire_at, receipt_count, read_count, remark, created_at, updated_at
```

`NoticeReceiptSchema`:

```text
id, notice_id, guardian_id, student_id, guardian_name_snapshot, student_name_snapshot, relation, status, delivered_at, read_at, notice.title, notice.notice_type, notice.priority, notice.published_at
```

## API Contract

Common mobile headers:

```text
Authorization: Bearer mineadmin-mobile-token
X-Tenant-Id: 1001
X-Client-Type: wechat_service
```

Common admin headers:

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
GET /mobile/education/academic/guardian/students
GET /mobile/education/academic/guardian/students/{studentId}/lessons
GET /mobile/education/academic/guardian/students/{studentId}/accounts
GET /mobile/education/academic/guardian/students/{studentId}/consumptions
GET /mobile/education/academic/guardian/notices/page
GET /mobile/education/academic/guardian/notices/{receiptId}
PUT /mobile/education/academic/guardian/notices/{receiptId}/read
POST /mobile/education/academic/guardian/leave-requests
GET /admin/education/academic/notices/page
GET /admin/education/academic/notices/{id}
POST /admin/education/academic/notices
PUT /admin/education/academic/notices/{id}/publish
PUT /admin/education/academic/notices/{id}/withdraw
GET /admin/education/academic/notices/{id}/receipts/page
```

Endpoint examples:

```json
[
  {
    "api": "GET /mobile/education/academic/guardian/students",
    "request": {"query": {"client_type": "wechat_service"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 101, "student_no": "S20260610001", "name": "Student Zhang", "relation": "mother", "is_primary": true, "can_receive_notice": true, "can_submit_leave": true}], "total": 1}},
    "validation_failure": {"code": 422, "message": "client_type has an invalid value", "data": {"field": "client_type"}},
    "business_failure": {"code": 403, "message": "guardian record is not bound", "data": {"profile_id": 3002}}
  },
  {
    "api": "GET /mobile/education/academic/guardian/students/{studentId}/lessons",
    "request": {"path": {"studentId": 101}, "query": {"page": 1, "pageSize": 20, "start_at": "2026-06-01 00:00:00", "end_at": "2026-06-30 23:59:59", "status": "scheduled"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"lesson_id": 9001, "lesson_student_id": 10001, "title": "Art Basics Lesson 1", "course_name_snapshot": "Art Basics", "teacher_name_snapshot": "Teacher Wang", "start_at": "2026-06-12 10:00:00", "lesson_status": "scheduled"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "end_at must be after start_at", "data": {"field": "end_at"}},
    "business_failure": {"code": 403, "message": "student is not bound to current guardian", "data": {"student_id": 101}}
  },
  {
    "api": "GET /mobile/education/academic/guardian/students/{studentId}/accounts",
    "request": {"path": {"studentId": 101}, "query": {"page": 1, "pageSize": 20, "status": "active"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 601, "course_id": 301, "course_name": "Art Basics", "purchased_units": "24.00", "consumed_units": "1.00", "available_units": "23.00", "status": "active"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "status has an invalid value", "data": {"field": "status"}},
    "business_failure": {"code": 403, "message": "student is not bound to current guardian", "data": {"student_id": 999}}
  },
  {
    "api": "GET /mobile/education/academic/guardian/students/{studentId}/consumptions",
    "request": {"path": {"studentId": 101}, "query": {"page": 1, "pageSize": 20, "account_id": 601, "source_type": "attendance", "status": "active"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 201, "consumption_no": "CON2026061211050010019912", "course_name": "Art Basics", "lesson_title": "Art Basics Lesson 1", "direction": "decrease", "units": "1.00", "after_available_units": "23.00", "status": "active"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "source_type has an invalid value", "data": {"field": "source_type"}},
    "business_failure": {"code": 403, "message": "account does not belong to bound student", "data": {"account_id": 601, "student_id": 999}}
  },
  {
    "api": "GET /mobile/education/academic/guardian/notices/page",
    "request": {"query": {"page": 1, "pageSize": 20, "status": "unread", "notice_type": "academic"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"receipt_id": 7001, "notice_id": 501, "title": "Class reminder", "notice_type": "academic", "priority": "important", "student_name_snapshot": "Student Zhang", "status": "unread", "published_at": "2026-06-12 09:00:00"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "notice_type has an invalid value", "data": {"field": "notice_type"}},
    "business_failure": {"code": 403, "message": "guardian mobile role required", "data": {"role_code": "teacher"}}
  },
  {
    "api": "GET /mobile/education/academic/guardian/notices/{receiptId}",
    "request": {"path": {"receiptId": 7001}},
    "success": {"code": 200, "message": "success", "data": {"receipt_id": 7001, "notice_id": 501, "title": "Class reminder", "content": "Bring drawing tools tomorrow.", "student_name_snapshot": "Student Zhang", "status": "unread"}},
    "validation_failure": {"code": 422, "message": "receiptId must be an integer", "data": {"field": "receiptId"}},
    "business_failure": {"code": 404, "message": "notice receipt not found in current guardian context", "data": {"receipt_id": 7001}}
  },
  {
    "api": "PUT /mobile/education/academic/guardian/notices/{receiptId}/read",
    "request": {"path": {"receiptId": 7001}},
    "success": {"code": 200, "message": "success", "data": {"receipt_id": 7001, "status": "read", "read_at": "2026-06-12 10:10:00"}},
    "validation_failure": {"code": 422, "message": "receiptId must be an integer", "data": {"field": "receiptId"}},
    "business_failure": {"code": 404, "message": "notice receipt not found in current guardian context", "data": {"receipt_id": 7001}}
  },
  {
    "api": "POST /mobile/education/academic/guardian/leave-requests",
    "request": {"body": {"lesson_student_id": 10001, "leave_type": "sick", "reason": "Fever at home", "makeup_required": true}},
    "success": {"code": 200, "message": "success", "data": {"id": 801, "leave_no": "LEA2026061210000010014821", "source": "guardian", "lesson_student_id": 10001, "status": "pending", "reason": "Fever at home"}},
    "validation_failure": {"code": 422, "message": "lesson_student_id is required", "data": {"field": "lesson_student_id"}},
    "business_failure": {"code": 409, "message": "leave request already exists for lesson student", "data": {"lesson_student_id": 10001}}
  },
  {
    "api": "GET /admin/education/academic/notices/page",
    "request": {"query": {"page": 1, "pageSize": 20, "campus_id": 2001, "status": "published", "keyword": "Class"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 501, "notice_no": "NOT2026061209000010014821", "title": "Class reminder", "target_type": "class", "status": "published", "receipt_count": 12, "read_count": 3}], "total": 1}},
    "validation_failure": {"code": 422, "message": "page is required", "data": {"field": "page"}},
    "business_failure": {"code": 403, "message": "campus is outside current scope", "data": {"campus_id": 2999}}
  },
  {
    "api": "GET /admin/education/academic/notices/{id}",
    "request": {"path": {"id": 501}},
    "success": {"code": 200, "message": "success", "data": {"id": 501, "notice_no": "NOT2026061209000010014821", "title": "Class reminder", "content": "Bring drawing tools tomorrow.", "status": "published"}},
    "validation_failure": {"code": 422, "message": "id must be an integer", "data": {"field": "id"}},
    "business_failure": {"code": 404, "message": "notice not found in current context", "data": {"id": 501}}
  },
  {
    "api": "POST /admin/education/academic/notices",
    "request": {"body": {"campus_id": 2001, "notice_type": "academic", "target_type": "class", "target_id": 701, "title": "Class reminder", "content": "Bring drawing tools tomorrow.", "priority": "important", "expire_at": "2026-06-30 23:59:59"}},
    "success": {"code": 200, "message": "success", "data": {"id": 501, "notice_no": "NOT2026061209000010014821", "status": "draft", "title": "Class reminder"}},
    "validation_failure": {"code": 422, "message": "title is required", "data": {"field": "title"}},
    "business_failure": {"code": 409, "message": "target class has no receivable guardians", "data": {"target_type": "class", "target_id": 701}}
  },
  {
    "api": "PUT /admin/education/academic/notices/{id}/publish",
    "request": {"path": {"id": 501}, "body": {"published_at": "2026-06-12 09:00:00"}},
    "success": {"code": 200, "message": "success", "data": {"notice": {"id": 501, "status": "published", "published_at": "2026-06-12 09:00:00"}, "receipt_count": 12}},
    "validation_failure": {"code": 422, "message": "published_at must use Y-m-d H:i:s", "data": {"field": "published_at"}},
    "business_failure": {"code": 409, "message": "only draft notice can be published", "data": {"id": 501, "status": "published"}}
  },
  {
    "api": "PUT /admin/education/academic/notices/{id}/withdraw",
    "request": {"path": {"id": 501}, "body": {"withdraw_reason": "Wrong class target"}},
    "success": {"code": 200, "message": "success", "data": {"id": 501, "status": "withdrawn", "withdraw_reason": "Wrong class target"}},
    "validation_failure": {"code": 422, "message": "withdraw_reason is required", "data": {"field": "withdraw_reason"}},
    "business_failure": {"code": 409, "message": "only published notice can be withdrawn", "data": {"id": 501, "status": "draft"}}
  },
  {
    "api": "GET /admin/education/academic/notices/{id}/receipts/page",
    "request": {"path": {"id": 501}, "query": {"page": 1, "pageSize": 20, "status": "unread", "keyword": "Student Zhang"}},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 7001, "guardian_name_snapshot": "Guardian Li", "student_name_snapshot": "Student Zhang", "status": "unread", "delivered_at": "2026-06-12 09:00:00"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "status has an invalid value", "data": {"field": "status"}},
    "business_failure": {"code": 404, "message": "notice not found in current context", "data": {"id": 501}}
  }
]
```

## PC Admin Page Tasks

### API Client

Create `admin-web/src/api/education/academic/notice.ts`.

Types:

```ts
export type NoticeTargetType = 'all' | 'campus' | 'class' | 'student'
export type NoticeType = 'academic' | 'activity' | 'fee' | 'system'
export type NoticePriority = 'normal' | 'important' | 'urgent'
export type NoticeStatus = 'draft' | 'published' | 'withdrawn'
export type NoticeReceiptStatus = 'unread' | 'read'
```

Functions:

```ts
export function pageNotices(params: NoticePageParams): Promise<PageResult<NoticeRecord>>
export function getNotice(id: number): Promise<NoticeRecord>
export function createNotice(payload: NoticeSavePayload): Promise<NoticeRecord>
export function updateNotice(id: number, payload: NoticeSavePayload): Promise<NoticeRecord>
export function publishNotice(id: number, payload: NoticePublishPayload): Promise<NoticePublishResult>
export function withdrawNotice(id: number, payload: NoticeWithdrawPayload): Promise<NoticeRecord>
export function pageNoticeReceipts(id: number, params: NoticeReceiptPageParams): Promise<PageResult<NoticeReceiptRecord>>
```

### Route And Menu

Modify `admin-web/src/router/modules/education.ts`.

Route:

```text
Name: EducationAcademicNoticeList
Path: /education/academic/notices
Component: NoticeList.vue
Permission: education:academic:notice:page
Menu title: 家校通知
```

### NoticeList Page

Create `admin-web/src/views/education/academic/NoticeList.vue`.

Tasks:

```text
Render filters: campus_id, notice_type, target_type, status, keyword.
Render columns: notice_no, title, notice_type, target_type, priority, status, receipt_count, read_count, published_at, updated_at, actions.
Create button requires education:academic:notice:create.
Edit button appears only for draft notices and requires education:academic:notice:update.
Publish button appears only for draft notices and requires education:academic:notice:publish.
Withdraw button appears only for published notices and requires education:academic:notice:withdraw.
Receipts button requires education:academic:notice:receipt.
Detail drawer opens for education:academic:notice:detail.
Implement loading, empty, error, permission-hidden button, publish success, withdraw success, and API failure states.
```

### NoticeForm

Create `admin-web/src/views/education/academic/components/NoticeForm.vue`.

Fields:

```text
campus_id selector
notice_type selector
target_type segmented control
target_id selector with target-specific source
title input max 160
content textarea max 5000
priority selector
expire_at datetime picker
remark textarea max 500
```

Behavior:

```text
target_type all disables target_id.
target_type campus uses campus selector.
target_type class uses class selector scoped by campus.
target_type student uses student selector scoped by campus.
Submit createNotice for new record.
Submit updateNotice for draft edit.
Show validation error next to field from API data.field.
Keep form open on business failure.
```

### NoticeReceiptDrawer

Create `admin-web/src/views/education/academic/components/NoticeReceiptDrawer.vue`.

Tasks:

```text
Load pageNoticeReceipts when drawer opens.
Render filters: status, student_id, guardian_id, keyword.
Render columns: guardian_name_snapshot, student_name_snapshot, relation, status, delivered_at, read_at.
Show unread/read summary from notice receipt_count and read_count.
Support pagination and reload.
Render loading, empty, error, and retry states.
```

### NoticeDetailDrawer

Create `admin-web/src/views/education/academic/components/NoticeDetailDrawer.vue`.

Tasks:

```text
Render title, content, notice_type, priority, target_type, target_id, status, published_at, withdrawn_at, withdraw_reason, receipt_count, read_count.
Use status and priority badges.
No mutation is performed inside the detail drawer.
```

## Teacher / Guardian Mobile Page Tasks

### API Client

Create `mobile-uniapp/src/api/academic/guardian.ts`.

Types:

```ts
export type GuardianLessonStatus = 'scheduled' | 'cancelled' | 'completed'
export type GuardianAccountStatus = 'active' | 'frozen' | 'closed'
export type GuardianConsumptionStatus = 'active' | 'reversed'
export type GuardianNoticeStatus = 'unread' | 'read'
export type GuardianLeaveType = 'sick' | 'personal' | 'school' | 'other'
```

Functions:

```ts
export function getGuardianStudents(params?: GuardianStudentParams): Promise<GuardianStudentListResult>
export function pageGuardianStudentLessons(studentId: number, params: GuardianLessonPageParams): Promise<PageResult<GuardianLessonRecord>>
export function pageGuardianStudentAccounts(studentId: number, params: GuardianAccountPageParams): Promise<PageResult<GuardianAccountRecord>>
export function pageGuardianStudentConsumptions(studentId: number, params: GuardianConsumptionPageParams): Promise<PageResult<GuardianConsumptionRecord>>
export function pageGuardianNotices(params: GuardianNoticePageParams): Promise<PageResult<GuardianNoticeCard>>
export function getGuardianNotice(receiptId: number): Promise<GuardianNoticeDetail>
export function readGuardianNotice(receiptId: number): Promise<GuardianNoticeReadResult>
export function createGuardianLeave(payload: GuardianLeaveCreatePayload): Promise<GuardianLeaveRecord>
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
{"path": "pages/guardian/student/index", "style": {"navigationBarTitleText": "学生"}}
```

```json
{"path": "pages/guardian/schedule/index", "style": {"navigationBarTitleText": "课表"}}
```

```json
{"path": "pages/guardian/account/index", "style": {"navigationBarTitleText": "课时账户"}}
```

```json
{"path": "pages/guardian/consumption/index", "style": {"navigationBarTitleText": "课消记录"}}
```

```json
{"path": "pages/guardian/notice/index", "style": {"navigationBarTitleText": "通知"}}
```

```json
{"path": "pages/guardian/notice/detail", "style": {"navigationBarTitleText": "通知详情"}}
```

```json
{"path": "pages/guardian/leave/create", "style": {"navigationBarTitleText": "请假"}}
```

### Guardian Entry Page

Modify `mobile-uniapp/pages/guardian/index.vue`.

Tasks:

```text
Load F06 guardian context on mount.
Load getGuardianStudents after context success.
Store selected student_id in local storage.
Render entry buttons for student selector, schedule, account, consumption, notice, and leave.
Disable student-specific entries when no bound student exists.
Navigate with selected student_id query for schedule, account, consumption, and leave pages.
Show forbidden state when context role is not guardian.
Show empty binding state when no bound students exist.
Show retry state when context or student request fails.
```

### Student Selector Page

Create `mobile-uniapp/pages/guardian/student/index.vue`.

Tasks:

```text
Call getGuardianStudents on load.
Render bound student cards with name, student_no, relation, primary marker, can_receive_notice, and can_submit_leave.
Tap student card saves selected student_id and navigates back.
Show loading, empty, error, forbidden, retry, and refresh states.
```

### Schedule Page

Create `mobile-uniapp/pages/guardian/schedule/index.vue`.

Tasks:

```text
Read studentId from route query or local storage.
Call pageGuardianStudentLessons with current month range by default.
Provide month selector and status segmented control.
Render lesson cards with title, time, course, teacher, classroom, lesson_status, and lesson_student_status.
Show create leave action on future scheduled lesson rows when selected student relation can_submit_leave is true.
Tap create leave navigates to pages/guardian/leave/create?lessonStudentId={lesson_student_id}&studentId={studentId}.
Show loading, empty, error, forbidden, retry, refresh, and pagination states.
```

### Account Page

Create `mobile-uniapp/pages/guardian/account/index.vue`.

Tasks:

```text
Read studentId from route query or local storage.
Call pageGuardianStudentAccounts.
Render AccountBalanceCard for each account.
Show purchased_units, bonus_units, consumed_units, adjusted_units, refunded_units, frozen_units, available_units, status, and expires_at.
Tap an account card navigates to consumption page with account_id.
Show loading, empty, error, forbidden, retry, and refresh states.
```

### Consumption Page

Create `mobile-uniapp/pages/guardian/consumption/index.vue`.

Tasks:

```text
Read studentId and optional accountId from route query.
Call pageGuardianStudentConsumptions.
Provide account filter and date range filter.
Render ledger rows with course_name, lesson_title, direction, units, after_available_units, status, and created_at.
Show reversed rows with a distinct neutral badge.
Show loading, empty, error, forbidden, retry, refresh, and pagination states.
```

### Notice List Page

Create `mobile-uniapp/pages/guardian/notice/index.vue`.

Tasks:

```text
Call pageGuardianNotices with status unread by default.
Provide tabs unread, read, all.
Render notice cards with title, notice_type, priority, student_name_snapshot, status, and published_at.
Tap notice card navigates to pages/guardian/notice/detail?receiptId={receipt_id}.
Pull down refresh resets to page 1.
Reach bottom loads next page until total is reached.
Show loading, empty, error, forbidden, retry, and refresh states.
```

### Notice Detail Page

Create `mobile-uniapp/pages/guardian/notice/detail.vue`.

Tasks:

```text
Read receiptId from route query.
Call getGuardianNotice.
Render title, content, student_name_snapshot, notice_type, priority, published_at, and read status.
If status is unread, call readGuardianNotice after detail content renders.
When read succeeds, update status locally.
If read fails with 404, show not found state.
Show loading, error, forbidden, not found, retry, and read success states.
```

### Leave Create Page

Create `mobile-uniapp/pages/guardian/leave/create.vue`.

Tasks:

```text
Read lessonStudentId and studentId from route query.
Render selected lesson summary when passed from schedule page or load schedule row by lessonStudentId if needed.
Render leave_type selector, reason textarea, makeup_required toggle, and submit button.
Validate leave_type and reason before API call.
Disable submit while submitting.
Call createGuardianLeave.
After success, show leave_no and pending status.
On duplicate leave 409, show conflict state and navigate back to schedule.
Show loading, error, forbidden, validation, submitting, success, and conflict states.
```

### Shared Components

`GuardianStateBlock.vue`:

```text
Props: state loading | empty | error | forbidden | not_found | conflict, message, retryText.
Emits: retry.
Used by every V1-07 guardian page.
```

`StudentSelector.vue`:

```text
Props: students, selectedStudentId.
Emits: select.
Renders stable cards with primary relation badge and disabled state when student cannot submit leave.
```

`NoticeStatusBadge.vue`:

```text
Props: status unread | read, priority normal | important | urgent.
Maps unread to warning, read to neutral, important to highlighted, urgent to danger.
```

`AccountBalanceCard.vue`:

```text
Props: account.
Renders course_name, available_units, consumed_units, purchased_units, bonus_units, status, and expires_at with fixed label/value rows.
```

Teacher mobile tasks:

```text
No teacher page is created or modified in V1-07.
V1-07 tests must verify a teacher context receives code 403 from guardian mobile APIs.
```

## Test Plan

Backend tests:

| File | Case | Assertions |
| --- | --- | --- |
| `GuardianNoticeMigrationTest.php` | `test_notice_tables_exist` | `edu_notices` and `edu_notice_receipts` exist |
| `GuardianNoticeMigrationTest.php` | `test_notice_columns_and_indexes_exist` | documented columns and unique keys exist |
| `GuardianNoticeMigrationTest.php` | `test_rollback_drops_receipts_before_notices` | rollback removes both tables in dependency order |
| `GuardianMobileContextResolverTest.php` | `test_resolves_guardian_by_unionid` | enabled guardian row is returned |
| `GuardianMobileContextResolverTest.php` | `test_resolves_guardian_by_mobile_when_openid_missing` | mobile fallback works inside tenant |
| `GuardianMobileContextResolverTest.php` | `test_teacher_role_is_rejected` | code 403 with message `guardian mobile role required` |
| `GuardianMobileContextResolverTest.php` | `test_unbound_profile_is_rejected` | code 403 with message `guardian record is not bound` |
| `GuardianMobileRepositoryTest.php` | `test_list_bound_students_returns_only_current_guardian_students` | other guardian student is absent |
| `GuardianMobileRepositoryTest.php` | `test_assert_bound_student_rejects_unbound_student` | code 403 |
| `GuardianMobileRepositoryTest.php` | `test_student_accounts_are_bound_student_only` | only bound student accounts returned |
| `GuardianMobileRepositoryTest.php` | `test_consumptions_are_bound_student_only` | only bound student consumptions returned |
| `NoticeRepositoryTest.php` | `test_create_draft_generates_notice_no` | notice_no unique and status draft |
| `NoticeReceiptRepositoryTest.php` | `test_build_class_targets_uses_can_receive_notice` | guardians with can_receive_notice false excluded |
| `NoticeReceiptRepositoryTest.php` | `test_receipt_unique_key_prevents_duplicates` | duplicate publish target inserts once |
| `GuardianMobileServiceTest.php` | `test_students_returns_bound_student_schema` | relation and leave permissions present |
| `GuardianMobileServiceTest.php` | `test_notice_read_increments_read_count_once` | second read does not increment again |
| `GuardianMobileLeaveServiceTest.php` | `test_create_guardian_leave_sets_source_and_guardian_id` | source guardian, pending status, guardian_id set |
| `GuardianMobileLeaveServiceTest.php` | `test_create_leave_rejects_relation_without_submit_permission` | code 403 |
| `NoticeServiceTest.php` | `test_publish_draft_creates_receipts_and_updates_counts` | status published and receipt_count matches targets |
| `NoticeServiceTest.php` | `test_publish_empty_target_returns_conflict` | code 409 |
| `NoticeServiceTest.php` | `test_withdraw_published_notice_sets_reason` | status withdrawn and reason stored |
| `GuardianMobileStudentApiTest.php` | `test_students_contract` | response envelope and schema match API contract |
| `GuardianMobileAcademicApiTest.php` | `test_lessons_accounts_consumptions_contracts` | all three read APIs return documented envelopes |
| `GuardianMobileNoticeApiTest.php` | `test_notice_page_detail_read_contracts` | page, detail, read APIs match examples |
| `GuardianMobileLeaveApiTest.php` | `test_leave_create_success_contract` | code 200 and pending leave returned |
| `GuardianMobileRoleIsolationTest.php` | `test_teacher_cannot_access_guardian_routes` | every guardian route returns 403 |
| `GuardianMobileStudentIsolationTest.php` | `test_guardian_cannot_access_unbound_student` | schedule/account/consumption/leave return 403 |
| `NoticeAdminApiTest.php` | `test_notice_create_publish_withdraw_receipts_contracts` | admin APIs match examples |
| `NoticePermissionTest.php` | `test_publish_requires_permission` | missing permission returns 403 |
| `NoticeAuditTest.php` | `test_notice_publish_and_guardian_read_write_audit_events` | audit rows exist |
| `GuardianMobileRegressionTest.php` | `test_v1_05_leave_admin_api_still_passes` | V1-05 admin leave behavior unchanged |

PC tests:

| File | Case | Assertions |
| --- | --- | --- |
| `NoticeList.spec.ts` | `renders_notice_table_and_filters` | filters and columns visible |
| `NoticeList.spec.ts` | `hides_publish_button_without_permission` | publish button absent |
| `NoticeList.spec.ts` | `publish_success_reloads_table` | publishNotice called and pageNotices reloaded |
| `NoticeList.spec.ts` | `withdraw_failure_keeps_dialog_open` | API 409 message displayed |
| `NoticeForm.spec.ts` | `target_type_all_disables_target_id` | target selector disabled |
| `NoticeForm.spec.ts` | `class_target_uses_class_selector` | class selector visible |
| `NoticeForm.spec.ts` | `validation_error_maps_to_title_field` | title field error rendered |
| `NoticeReceiptDrawer.spec.ts` | `renders_receipt_status_rows` | unread/read rows rendered |
| `NoticeReceiptDrawer.spec.ts` | `receipt_filter_reloads_page` | pageNoticeReceipts called with status |

Mobile tests:

| File | Case | Assertions |
| --- | --- | --- |
| `src/api/academic/__tests__/guardian.spec.ts` | `getGuardianStudents unwraps envelope` | returns `data.list` |
| `src/api/academic/__tests__/guardian.spec.ts` | `createGuardianLeave preserves validation field` | thrown error contains `data.field` |
| `tests/academic/guardianPagesJson.spec.ts` | `guardian academic pages are registered` | pages.json has seven V1-07 routes |
| `pages/guardian/__tests__/index.spec.ts` | `entry_disables_student_features_without_bound_student` | student-specific buttons disabled |
| `pages/guardian/student/__tests__/index.spec.ts` | `select_student_saves_local_storage` | selected student id stored |
| `pages/guardian/schedule/__tests__/index.spec.ts` | `schedule_renders_lessons_and_leave_action` | leave action appears for scheduled row |
| `pages/guardian/account/__tests__/index.spec.ts` | `account_cards_render_balances` | available and consumed units visible |
| `pages/guardian/consumption/__tests__/index.spec.ts` | `consumption_rows_render_direction_and_status` | decrease and reversed badges visible |
| `pages/guardian/notice/__tests__/index.spec.ts` | `notice_list_defaults_to_unread` | API called with status unread |
| `pages/guardian/notice/__tests__/detail.spec.ts` | `notice_detail_marks_unread_as_read` | readGuardianNotice called after detail load |
| `pages/guardian/leave/__tests__/create.spec.ts` | `leave_create_requires_reason` | validation shown before API call |
| `pages/guardian/leave/__tests__/create.spec.ts` | `duplicate_leave_conflict_state` | 409 state rendered |
| `pages/guardian/components/__tests__/GuardianStateBlock.spec.ts` | `retry_emits_retry_event` | click emits retry |

## Execution Commands

Backend focused tests:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter GuardianNoticeMigrationTest
composer test -- --filter GuardianMobileContextResolverTest
composer test -- --filter GuardianMobileRepositoryTest
composer test -- --filter NoticeRepositoryTest
composer test -- --filter NoticeReceiptRepositoryTest
composer test -- --filter GuardianMobileServiceTest
composer test -- --filter GuardianMobileLeaveServiceTest
composer test -- --filter NoticeServiceTest
composer test -- --filter GuardianMobileStudentApiTest
composer test -- --filter GuardianMobileAcademicApiTest
composer test -- --filter GuardianMobileNoticeApiTest
composer test -- --filter GuardianMobileLeaveApiTest
composer test -- --filter GuardianMobileRoleIsolationTest
composer test -- --filter GuardianMobileStudentIsolationTest
composer test -- --filter NoticeAdminApiTest
composer test -- --filter NoticePermissionTest
composer test -- --filter NoticeAuditTest
composer test -- --filter GuardianMobileRegressionTest
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
pnpm test -- NoticeList
pnpm test -- NoticeForm
pnpm test -- NoticeReceiptDrawer
```

Expected:

```text
Every listed PC test command exits 0.
Notice list, form, and receipt drawer suites pass.
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

Mobile focused tests:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm test -- guardian.spec.ts
pnpm test -- guardianPagesJson
pnpm test -- pages/guardian
pnpm test -- GuardianStateBlock
```

Expected:

```text
Every listed mobile test command exits 0.
The test output reports all guardian mobile suites passing.
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
composer test -- --filter LeaveMakeupReschedule
composer test -- --filter TeacherMobile
composer test -- --filter CourseAccount
composer test -- --filter Attendance
cd ../admin-web
pnpm test -- LeaveRequestList AttendanceReview AccountLedgerList
pnpm build
cd ../mobile-uniapp
pnpm test -- teacher
pnpm build:h5
```

Expected:

```text
V1-02 account tests pass.
V1-04 attendance/consumption tests pass.
V1-05 leave/change tests pass.
V1-06 teacher mobile tests pass.
Existing PC and mobile regression gates pass.
```

Migration verification:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
php bin/hyperf.php migrate:rollback --step=1
php bin/hyperf.php migrate
```

Expected:

```text
First migrate creates edu_notices and edu_notice_receipts.
Rollback drops edu_notice_receipts before edu_notices.
Final migrate recreates both V1-07 tables.
```

## Acceptance Gate

V1-07 can be accepted only when all items below are true:

```text
Notice migration creates edu_notices and edu_notice_receipts with documented columns, indexes, and rollback order.
Guardian mobile APIs live under backend/app/Http/Api and use /mobile/education/academic/guardian/*.
Notice PC APIs live under backend/app/Http/Admin and use /admin/education/academic/notices/*.
Current guardian is resolved from F06 guardian context to V1-01 edu_guardians by unionid, openid, or mobile.
Teacher and non-guardian profiles receive code 403 on every guardian mobile route.
Guardian cannot read schedule, accounts, consumptions, notices, or leave data for unbound students.
Guardian account and consumption APIs are read-only and do not mutate balances.
Guardian leave creation creates a pending V1-05-compatible leave request with source guardian.
Guardian leave creation respects can_submit_leave on edu_student_guardians.
Notice publish creates receipt rows only for guardians with can_receive_notice true.
Notice receipt read is idempotent and increments read_count exactly once.
PC NoticeList supports create, edit draft, publish, withdraw, detail, receipt drawer, permissions, and page states.
Guardian mobile pages implement loading, empty, error, forbidden, retry, refresh, submitting, success, and conflict states where applicable.
pages.json registers student selector, schedule, account, consumption, notice list, notice detail, and leave create routes.
Backend focused tests, backend quality gate, PC tests, PC quality gate, mobile tests, mobile quality gate, cross-module regression gate, and migration verification all exit 0.
```

## Task Breakdown

### Task 1: Notice Migration, Enums, And Models

**Files:**
- Create: `mineadmin-education-saas/backend/databases/migrations/2026_06_10_010700_create_v1_notice_tables.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/NoticeTargetType.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/NoticeType.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/NoticePriority.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/NoticeStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Academic/NoticeReceiptStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationNotice.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Academic/EducationNoticeReceipt.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/GuardianNoticeMigrationTest.php`

- [x] **Step 1: Write migration tests**

Expected cases:

```text
notice tables exist.
notice columns and indexes exist.
receipt unique key exists.
rollback drops receipts before notices.
```

- [x] **Step 2: Create migration**

Use the exact table definitions, indexes, and rollback order from `Database Migration Design`.

- [x] **Step 3: Create enums and models**

Use the exact enum values, fillable fields, casts, and relationships from `MineAdmin Backend Module Design`.

- [x] **Step 4: Run focused test**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter GuardianNoticeMigrationTest
```

Expected:

```text
Command exits 0.
```

### Task 2: Guardian Context And Read Repositories

**Files:**
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/GuardianMobileContextResolver.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/GuardianMobileRepository.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/GuardianMobileContextResolverTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/GuardianMobileRepositoryTest.php`

- [x] **Step 1: Write context resolver tests**

Expected cases:

```text
resolve by unionid.
resolve by openid.
resolve by mobile fallback.
teacher role rejected.
unbound guardian profile rejected.
```

- [x] **Step 2: Implement context resolver**

Required methods:

```php
resolveGuardian(EducationUserContext $context): EducationGuardian
assertGuardianRole(EducationUserContext $context): void
currentOperatorId(EducationUserContext $context): ?int
```

- [x] **Step 3: Write repository tests**

Expected cases:

```text
bound student list excludes other guardian students.
schedule excludes unbound student lessons.
accounts and consumptions are bound student only.
can_submit_leave false is returned for leave checks.
```

- [x] **Step 4: Implement repository**

Required behavior:

```text
Filter tenant_id on every query.
Prove student binding before every student-specific read.
Return no disabled or soft-deleted students.
Do not mutate account or consumption data.
```

- [x] **Step 5: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter GuardianMobileContextResolverTest
composer test -- --filter GuardianMobileRepositoryTest
```

Expected:

```text
Both commands exit 0.
```

### Task 3: Notice Repository And Service

**Files:**
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/NoticeRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Academic/NoticeReceiptRepository.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/NoticeService.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/NoticeSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/NoticeReceiptSchema.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/NoticeRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/NoticeReceiptRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/NoticeServiceTest.php`

- [x] **Step 1: Write notice repository and service tests**

Expected cases:

```text
draft creation generates notice_no.
publish class target creates receipts only for can_receive_notice guardians.
empty target publish returns 409.
read_count updates only through receipt read service path.
withdraw published notice stores reason.
```

- [x] **Step 2: Implement notice repositories**

Required behavior:

```text
Admin queries respect tenant and campus scope.
Publish target builder supports all, campus, class, and student target types.
Receipt insert uses tenant_id + notice_id + guardian_id + student_id uniqueness.
```

- [x] **Step 3: Implement notice service**

Required operations:

```text
create draft, update draft, publish draft, withdraw published, page receipts.
```

- [x] **Step 4: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter NoticeRepositoryTest
composer test -- --filter NoticeReceiptRepositoryTest
composer test -- --filter NoticeServiceTest
```

Expected:

```text
All three commands exit 0.
```

### Task 4: Guardian Mobile APIs

**Files:**
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianStudentListRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianStudentLessonPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianStudentAccountPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianStudentConsumptionPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianNoticePageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianNoticeDetailRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianNoticeReadRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Academic/GuardianLeaveCreateRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/GuardianStudentController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/GuardianScheduleController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/GuardianAccountController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/GuardianConsumptionController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/GuardianNoticeController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Academic/GuardianLeaveController.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/GuardianMobileService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Academic/GuardianMobileLeaveService.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/GuardianStudentSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/GuardianLessonSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/GuardianAccountSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/GuardianConsumptionSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Academic/GuardianLeaveSchema.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/GuardianMobileServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Academic/GuardianMobileLeaveServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/GuardianMobileStudentApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/GuardianMobileAcademicApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/GuardianMobileNoticeApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/GuardianMobileLeaveApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/GuardianMobileRoleIsolationTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/GuardianMobileStudentIsolationTest.php`

- [x] **Step 1: Write mobile API tests**

Expected cases:

```text
students contract.
lessons, accounts, consumptions contracts.
notice page, detail, read contracts.
leave create success and duplicate conflict.
teacher role rejected.
unbound student rejected.
```

- [x] **Step 2: Implement mobile request classes**

Use the exact rules documented in `Request Classes`.

- [x] **Step 3: Implement guardian mobile services and schemas**

Required output:

```text
GuardianStudentSchema, GuardianLessonSchema, GuardianAccountSchema, GuardianConsumptionSchema, GuardianLeaveSchema, NoticeReceiptSchema.
```

- [x] **Step 4: Implement mobile controllers**

Endpoints:

```text
GET /mobile/education/academic/guardian/students
GET /mobile/education/academic/guardian/students/{studentId}/lessons
GET /mobile/education/academic/guardian/students/{studentId}/accounts
GET /mobile/education/academic/guardian/students/{studentId}/consumptions
GET /mobile/education/academic/guardian/notices/page
GET /mobile/education/academic/guardian/notices/{receiptId}
PUT /mobile/education/academic/guardian/notices/{receiptId}/read
POST /mobile/education/academic/guardian/leave-requests
```

- [x] **Step 5: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter GuardianMobileServiceTest
composer test -- --filter GuardianMobileLeaveServiceTest
composer test -- --filter GuardianMobileStudentApiTest
composer test -- --filter GuardianMobileAcademicApiTest
composer test -- --filter GuardianMobileNoticeApiTest
composer test -- --filter GuardianMobileLeaveApiTest
composer test -- --filter GuardianMobileRoleIsolationTest
composer test -- --filter GuardianMobileStudentIsolationTest
```

Expected:

```text
All eight commands exit 0.
```

### Task 5: Admin Notice APIs

**Files:**
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/NoticePageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/NoticeSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/NoticePublishRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/NoticeWithdrawRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Academic/NoticeReceiptPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Academic/NoticeController.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/NoticeAdminApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/NoticePermissionTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Academic/NoticeAuditTest.php`

- [x] **Step 1: Write admin API tests**

Expected cases:

```text
page/detail/create/publish/withdraw/receipts contracts.
publish requires permission.
create/update/publish/withdraw audit events exist.
```

- [x] **Step 2: Implement admin request classes**

Use the exact admin request rules documented above.

- [x] **Step 3: Implement NoticeController**

Endpoints:

```text
GET /admin/education/academic/notices/page
GET /admin/education/academic/notices/{id}
POST /admin/education/academic/notices
PUT /admin/education/academic/notices/{id}
PUT /admin/education/academic/notices/{id}/publish
PUT /admin/education/academic/notices/{id}/withdraw
GET /admin/education/academic/notices/{id}/receipts/page
```

- [x] **Step 4: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter NoticeAdminApiTest
composer test -- --filter NoticePermissionTest
composer test -- --filter NoticeAuditTest
```

Expected:

```text
All three commands exit 0.
```

### Task 6: PC Notice Page

**Files:**
- Create: `mineadmin-education-saas/admin-web/src/api/education/academic/notice.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/NoticeList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/NoticeForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/NoticeReceiptDrawer.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/academic/components/NoticeDetailDrawer.vue`
- Modify: `mineadmin-education-saas/admin-web/src/router/modules/education.ts`
- Test: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/NoticeList.spec.ts`
- Test: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/NoticeForm.spec.ts`
- Test: `mineadmin-education-saas/admin-web/src/views/education/academic/__tests__/NoticeReceiptDrawer.spec.ts`

- [x] **Step 1: Write PC tests**

Expected cases:

```text
notice table filters and columns render.
permission buttons hide correctly.
publish success reloads table.
withdraw failure keeps dialog open.
form target controls switch by target_type.
receipt drawer paginates and filters.
```

- [x] **Step 2: Implement API client**

Functions must match the `PC Admin Page Tasks` section exactly.

- [x] **Step 3: Implement route and NoticeList**

Add route `/education/academic/notices` and wire list actions to API client.

- [x] **Step 4: Implement NoticeForm and drawers**

Implement create/edit form, detail drawer, and receipt drawer with documented states.

- [x] **Step 5: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm test -- NoticeList
pnpm test -- NoticeForm
pnpm test -- NoticeReceiptDrawer
```

Expected:

```text
All three commands exit 0.
```

### Task 7: Mobile API Client And Routes

**Files:**
- Create: `mineadmin-education-saas/mobile-uniapp/src/api/academic/guardian.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/src/api/academic/__tests__/guardian.spec.ts`
- Modify: `mineadmin-education-saas/mobile-uniapp/pages.json`
- Create: `mineadmin-education-saas/mobile-uniapp/tests/academic/guardianPagesJson.spec.ts`

- [x] **Step 1: Write mobile API client tests**

Expected cases:

```text
successful envelope unwraps to data.
validation failure preserves data.field.
business failure preserves code and message.
```

- [x] **Step 2: Implement guardian API client**

Functions must match the `API Client` section exactly.

- [x] **Step 3: Write pages.json test**

Expected routes:

```text
pages/guardian/student/index
pages/guardian/schedule/index
pages/guardian/account/index
pages/guardian/consumption/index
pages/guardian/notice/index
pages/guardian/notice/detail
pages/guardian/leave/create
```

- [x] **Step 4: Modify pages.json**

Add the seven V1-07 routes with the documented navigation titles.

- [x] **Step 5: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm test -- guardian.spec.ts
pnpm test -- guardianPagesJson
```

Expected:

```text
Both commands exit 0.
```

### Task 8: Mobile Guardian Pages

**Files:**
- Modify: `mineadmin-education-saas/mobile-uniapp/pages/guardian/index.vue`
- Modify: `mineadmin-education-saas/mobile-uniapp/pages/guardian/__tests__/index.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/student/index.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/student/__tests__/index.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/schedule/index.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/schedule/__tests__/index.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/account/index.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/account/__tests__/index.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/consumption/index.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/consumption/__tests__/index.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/notice/index.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/notice/detail.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/notice/__tests__/index.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/notice/__tests__/detail.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/leave/create.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/leave/__tests__/create.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/components/GuardianStateBlock.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/components/StudentSelector.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/components/NoticeStatusBadge.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/components/AccountBalanceCard.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/components/__tests__/GuardianStateBlock.spec.ts`

- [x] **Step 1: Write mobile page tests**

Expected states:

```text
loading, empty, error, forbidden, retry, refresh, submitting, success, conflict.
```

- [x] **Step 2: Implement shared components**

Implement GuardianStateBlock, StudentSelector, NoticeStatusBadge, and AccountBalanceCard.

- [x] **Step 3: Update guardian entry and student selector**

Entry page loads context and bound students; selector saves selected student id.

- [x] **Step 4: Implement schedule, account, and consumption pages**

All three pages read selected student id and enforce empty/forbidden/error states.

- [x] **Step 5: Implement notice pages**

Notice list defaults to unread; detail marks unread receipt as read.

- [x] **Step 6: Implement leave create page**

Validate leave type and reason, submit createGuardianLeave, and render duplicate conflict state.

- [x] **Step 7: Run focused tests**

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm test -- pages/guardian
pnpm test -- GuardianStateBlock
```

Expected:

```text
Both commands exit 0.
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
composer test -- --filter GuardianMobile
composer test -- --filter Notice
composer analyse
composer cs-fix -- --dry-run
```

Expected:

```text
GuardianMobile and Notice tests pass.
Static analysis exits 0.
Formatting dry run exits 0.
```

- [x] **Step 2: Run PC gates**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm test -- Notice
pnpm lint
pnpm typecheck
pnpm build
```

Expected:

```text
Notice PC tests pass.
Lint exits 0.
Typecheck exits 0.
Build exits 0.
```

- [x] **Step 3: Run mobile gates**

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm test -- guardian
pnpm lint
pnpm typecheck
pnpm build:h5
```

Expected:

```text
Guardian mobile tests pass.
Lint exits 0.
Typecheck exits 0.
H5 build exits 0.
```

- [x] **Step 4: Run cross-module regression**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter CourseAccount
composer test -- --filter Attendance
composer test -- --filter LeaveMakeupReschedule
composer test -- --filter TeacherMobile
cd ../admin-web
pnpm test -- LeaveRequestList AttendanceReview AccountLedgerList
pnpm build
cd ../mobile-uniapp
pnpm test -- teacher
pnpm build:h5
```

Expected:

```text
V1-02 through V1-06 regression gates pass.
Existing PC and teacher mobile build gates pass.
```

- [x] **Step 5: Run migration verification**

Run:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
php bin/hyperf.php migrate:rollback --step=1
php bin/hyperf.php migrate
```

Expected:

```text
V1-07 notice tables migrate, roll back, and migrate again successfully.
```

## Self-Review

- Scope coverage: V1-07 covers guardian student selector, schedule, course account, consumption ledger, notice list/detail/read state, leave creation, and PC notice publishing.
- MineAdmin fit: Admin notice APIs use `backend/app/Http/Admin`; guardian mobile APIs use `backend/app/Http/Api`; shared logic uses `backend/app/Service`, `backend/app/Repository`, `backend/app/Model`, and `backend/app/Schema`; migrations live under `backend/databases/migrations`.
- Dependency fit: Guardian identity comes from F06 context and V1-01 guardian records; student isolation comes from V1-01 student guardian bindings; accounts come from V1-02; lessons come from V1-03; consumptions come from V1-04; leave creation follows V1-05 rules.
- Data safety: Guardian mobile reads account and consumption data only, and leave creation does not approve leave, create attendance, schedule make-up, or mutate balances.
- UI fit: PC notice admin tasks and guardian uni-app pages include API clients, routes, states, permissions, and tests with exact file paths.
- Readiness: This plan has exact paths, full migration fields and indexes, Controller/Request/Service/Repository/Model/Schema tasks, complete API request/success/validation/business failure examples, PC and mobile page tasks, tests, commands, expected outputs, and acceptance gates, so V1-07 can be marked `ready`.
