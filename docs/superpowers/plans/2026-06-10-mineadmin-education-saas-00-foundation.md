# MineAdmin Education SaaS Foundation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deliver the complete MineAdmin Education SaaS Foundation needed before V1 core academic development: environment, tenant/campus, user role scope, dictionary, feature flag, audit log, PC admin integration, and mobile context entry.

**Architecture:** Foundation is executed through seven strict child plans, F00-F06. Backend follows MineAdmin 3.x native layers: admin controllers and requests under `app/Http/Admin`, mobile API controllers and requests under `app/Http/Api`, shared logic under `app/Service`, data access under `app/Repository`, models under `app/Model`, schemas under `app/Schema`, and migrations under `databases/migrations`. PC work stays in `admin-web`; teacher, guardian, and operator entry work stays in `mobile-uniapp`.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, Redis 7, Docker Compose, MineAdmin-Vue, Vue3, TypeScript, uni-app, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. F00-F06 have been executed and the Foundation aggregate verification gate has passed.

---

## Scope Check

Included through child plans:

- F00 Environment: repository, Docker, MineAdmin install, environment files, Makefile, and smoke checks.
- F01 Tenant Campus: tenant and campus migrations, backend layers, admin APIs, PC pages, permissions, tests.
- F02 User Profile Role Scope: education profiles, role codes, campus scopes, context middleware, admin APIs, PC pages, tests.
- F03 Dictionary Feature Flag: dictionaries, dictionary items, feature flags, seeders, lookup services, admin APIs, PC pages, tests.
- F04 Audit Log: immutable education-domain audit logs, listener, logger, admin APIs, PC page, write-operation audit tests.
- F05 PC Admin Foundation: route tree, menus, shared frontend types, API clients, list pages, forms, permission buttons, state flows, tests.
- F06 Mobile Foundation: teacher, guardian, and operator context APIs, uni-app API client, entry pages, role isolation, page states, tests.

Excluded:

- V1 academic business resources: students, guardians, teachers, courses, course accounts, classes, lessons, attendance, consumption, leave, and notices.
- V2-V12 expansion modules: academic operations, admissions CRM, finance, payroll, group management, family service, AI, workflow, growth, course standards, and learning content.
- WeChat login and token issuing; F06 uses the authenticated current user and F02 profile records.
- Production deployment, observability, data retention, and compliance hash chains.

Execution rule:

```text
Do not treat this parent plan as completed work. Execute F00-F06 in order. The child plans contain the code-level file paths, API examples, tests, commands, and acceptance gates.
```

## File Structure

Strict child plans:

```text
docs/superpowers/plans/2026-06-10-mineadmin-education-saas-00-f00-environment.md
docs/superpowers/plans/2026-06-10-mineadmin-education-saas-00-f01-tenant-campus.md
docs/superpowers/plans/2026-06-10-mineadmin-education-saas-00-f02-user-profile-role-scope.md
docs/superpowers/plans/2026-06-10-mineadmin-education-saas-00-f03-dictionary-feature-flag.md
docs/superpowers/plans/2026-06-10-mineadmin-education-saas-00-f04-audit-log.md
docs/superpowers/plans/2026-06-10-mineadmin-education-saas-00-f05-pc-admin-foundation.md
docs/superpowers/plans/2026-06-10-mineadmin-education-saas-00-f06-mobile-foundation.md
```

Target project structure:

```text
mineadmin-education-saas/
├── backend/
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Admin/
│   │   │   │   ├── Controller/Education/
│   │   │   │   ├── Request/Education/
│   │   │   │   └── Middleware/Education/
│   │   │   └── Api/
│   │   │       ├── Controller/Education/
│   │   │       ├── Request/Education/
│   │   │       └── Middleware/Education/
│   │   ├── Contract/Education/
│   │   ├── Event/Education/
│   │   ├── Listener/Education/
│   │   ├── Model/Education/
│   │   ├── Repository/Education/
│   │   ├── Schema/Education/
│   │   └── Service/Education/
│   ├── config/autoload/
│   ├── databases/migrations/
│   ├── databases/seeders/
│   └── tests/
├── admin-web/
│   └── src/
│       ├── api/education/
│       ├── composables/education/
│       ├── router/modules/education.ts
│       └── views/education/
└── mobile-uniapp/
    ├── src/api/foundation/
    ├── pages/teacher/
    ├── pages/guardian/
    └── pages/operator/
```

## Database Migration Design

Foundation migrations are owned by child plans:

| Child plan | Migration file | Tables |
| --- | --- | --- |
| F01 | `mineadmin-education-saas/backend/databases/migrations/2026_06_10_000100_create_education_tenant_campus_tables.php` | `edu_tenants`, `edu_campuses` |
| F02 | `mineadmin-education-saas/backend/databases/migrations/2026_06_10_000200_create_education_user_profile_scope_tables.php` | `edu_user_profiles`, `edu_user_campus_scopes` |
| F03 | `mineadmin-education-saas/backend/databases/migrations/2026_06_10_000300_create_education_dictionary_feature_tables.php` | `edu_dict_types`, `edu_dict_items`, `edu_feature_flags` |
| F04 | `mineadmin-education-saas/backend/databases/migrations/2026_06_10_000400_create_education_audit_logs_table.php` | `edu_audit_logs` |
| F05 | none | PC-only integration |
| F06 | none | mobile context only |

Rollback order:

```text
F04 audit logs
F03 dictionary and feature flag tables
F02 user profile and campus scope tables
F01 tenant and campus tables
```

Foreign-key policy:

```text
Foundation uses service-level validation and indexed tenant/campus fields. Audit rows keep no physical foreign keys so historical logs survive source row deletion or archiving.
```

## MineAdmin Backend Module Design

Backend ownership:

| Child plan | MineAdmin layers |
| --- | --- |
| F00 | repository structure, config, Makefile, smoke commands |
| F01 | `app/Http/Admin/Controller`, `app/Http/Admin/Request`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, `databases/migrations` |
| F02 | `app/Http/Admin/Middleware`, `app/Http/Admin/Controller`, `app/Http/Admin/Request`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, `databases/migrations` |
| F03 | `app/Http/Admin/Controller`, `app/Http/Admin/Request`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, `databases/migrations`, `databases/seeders` |
| F04 | `app/Event`, `app/Listener`, `app/Contract`, `app/Http/Admin/Controller`, `app/Http/Admin/Request`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, `databases/migrations` |
| F05 | no backend module changes |
| F06 | `app/Http/Api/Controller`, `app/Http/Api/Request`, `app/Http/Api/Middleware`, `app/Service`, `app/Repository`, `app/Schema` |

MineAdmin rules:

```text
Admin endpoints use MineAdmin Auth, PermissionMiddleware, OperationMiddleware where write operations require operation tracking, and MineAdmin Result response shape.
Mobile context endpoints use authenticated current user context and role checks; they do not use Admin PermissionMiddleware.
Repositories extend MineAdmin `App\Repository\IRepository`.
Services extend MineAdmin `App\Service\IService` where the generated project supports it.
Schemas live under `app/Schema/Education`.
Migrations live under `databases/migrations`.
```

## API Contract

Foundation API groups:

| Child plan | Endpoint group | Contract location |
| --- | --- | --- |
| F01 | `/admin/education/foundation/tenants/*`, `/admin/education/foundation/campuses/*` | F01 API Contract |
| F02 | `/admin/education/foundation/user-profiles/*` and campus scope APIs | F02 API Contract |
| F03 | `/admin/education/foundation/dict-*`, dictionary lookup, feature flag APIs | F03 API Contract |
| F04 | `/admin/education/foundation/audit-logs/*` | F04 API Contract |
| F06 | `/mobile/education/foundation/teacher/context`, `/guardian/context`, `/operator/context` | F06 API Contract |

Response envelope:

```json
{
  "code": 200,
  "message": "success",
  "data": {}
}
```

Failure contracts:

```text
422: validation failure with `data.field`.
403: permission, role, tenant, or campus isolation failure.
404: invisible or missing resource where hiding existence is safer.
409: duplicate business key conflict.
```

The exact request payloads, success payloads, validation failures, and business failures are defined in the F01-F04 and F06 child plans.

## PC Admin Page Tasks

PC implementation is owned by F01-F05:

| Page | Owner | Path |
| --- | --- | --- |
| Tenant list/form | F01 and F05 consolidation | `mineadmin-education-saas/admin-web/src/views/education/foundation/TenantList.vue` |
| Campus list/form | F01 and F05 consolidation | `mineadmin-education-saas/admin-web/src/views/education/foundation/CampusList.vue` |
| User profile and campus scope | F02 and F05 consolidation | `mineadmin-education-saas/admin-web/src/views/education/foundation/UserProfileList.vue` |
| Dictionary type/item | F03 and F05 consolidation | `mineadmin-education-saas/admin-web/src/views/education/foundation/DictionaryList.vue` |
| Feature flags | F03 and F05 consolidation | `mineadmin-education-saas/admin-web/src/views/education/foundation/FeatureFlagList.vue` |
| Audit logs | F04 and F05 consolidation | `mineadmin-education-saas/admin-web/src/views/education/foundation/AuditLogList.vue` |

Required PC behavior:

```text
Route meta auth arrays are defined in `admin-web/src/router/modules/education.ts`.
API clients are typed under `admin-web/src/api/education/foundation`.
Mutation buttons are permission-gated.
Every list page has loading, empty, error, success, search, reset, and pagination states.
Every form or drawer has visible, mode, submitting/loading, validation error, business error, and success behavior.
Audit log page remains read-only.
```

## Teacher / Guardian Mobile Page Tasks

Mobile implementation is owned by F06:

| Page | Path | Context API |
| --- | --- | --- |
| Teacher entry | `mineadmin-education-saas/mobile-uniapp/pages/teacher/index.vue` | `getTeacherContext()` |
| Guardian entry | `mineadmin-education-saas/mobile-uniapp/pages/guardian/index.vue` | `getGuardianContext()` |
| Operator entry | `mineadmin-education-saas/mobile-uniapp/pages/operator/index.vue` | `getOperatorContext()` |

Required mobile behavior:

```text
pages.json registers teacher, guardian, and operator entries.
Context API client unwraps MineAdmin result envelope only when code is 200.
Teacher page renders tenant, profile, campus scope, feature flags, empty, forbidden, error, retry, and refresh states.
Guardian page renders tenant, profile, empty student state, forbidden, error, retry, and refresh states.
Operator page renders tenant, profile, campus scope, feature flags, forbidden, error, retry, and refresh states.
Wrong-role access returns 403.
Unauthenticated access returns 401.
```

## Test Plan

Foundation test gates:

| Child plan | Test focus |
| --- | --- |
| F00 | environment smoke tests, Docker, install, Makefile, backend/admin/mobile build smoke |
| F01 | migration, tenant/campus repository, service, API, permission, tenant isolation, PC pages |
| F02 | profile/scope migration, role validation, context middleware, tenant/campus scope isolation, PC pages |
| F03 | migration, seeders, dictionary and feature flag repositories/services, API, tenant override, PC pages |
| F04 | audit migration, logger, listener, repository/service, API, permission, isolation, PC audit page |
| F05 | PC route tree, API clients, permission helper, list/form/page states, read-only audit behavior |
| F06 | mobile middleware, context APIs, role isolation, campus scope, feature flags, uni-app pages |

Final Foundation assertions:

```text
All F00-F06 child plan tests pass.
No plan uses the old migration directory shape; migrations use `backend/databases/migrations`.
Admin backend paths use `app/Http/Admin`.
Mobile backend paths use `app/Http/Api`.
PC build passes.
Mobile H5 build passes.
```

## Execution Commands

Run child plans in order:

```bash
cd mineadmin-education-saas
```

Then execute:

```text
F00 final gate from 2026-06-10-mineadmin-education-saas-00-f00-environment.md
F01 final gate from 2026-06-10-mineadmin-education-saas-00-f01-tenant-campus.md
F02 final gate from 2026-06-10-mineadmin-education-saas-00-f02-user-profile-role-scope.md
F03 final gate from 2026-06-10-mineadmin-education-saas-00-f03-dictionary-feature-flag.md
F04 final gate from 2026-06-10-mineadmin-education-saas-00-f04-audit-log.md
F05 final gate from 2026-06-10-mineadmin-education-saas-00-f05-pc-admin-foundation.md
F06 final gate from 2026-06-10-mineadmin-education-saas-00-f06-mobile-foundation.md
```

Foundation aggregate verification:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter Education\\\\Foundation
composer cs-fix -- --dry-run
composer analyse
cd ../admin-web
pnpm lint
pnpm test -- EducationFoundation
pnpm build
cd ../mobile-uniapp
pnpm lint
pnpm test -- context
pnpm test -- teacher
pnpm test -- guardian
pnpm test -- operator
pnpm build:h5
```

Expected:

```text
Foundation migrations run successfully.
All Education Foundation backend tests pass.
Backend code style dry run passes.
Backend static analysis passes.
Admin-web lint, tests, and production build pass.
Mobile lint, context/page tests, and H5 build pass.
```

## Acceptance Gate

Foundation is accepted only when all conditions are true:

```text
- F00-F06 child plans are ready and executed in order.
- MineAdmin project, Docker services, env files, and Makefile commands are available.
- Tenant, campus, profile, campus scope, dictionary, feature flag, and audit migrations run cleanly.
- Admin APIs return documented MineAdmin result shapes and enforce permission/isolation rules.
- Foundation write operations create audit rows where required.
- PC Foundation route tree, API clients, list pages, forms, drawers, permission buttons, and states pass tests.
- Teacher, guardian, and operator mobile context APIs enforce role and campus isolation.
- uni-app teacher, guardian, and operator entry pages pass state tests and H5 build.
- Backend, admin-web, and mobile final gates pass with expected output.
```

## Task Breakdown

### Task 1: Execute F00 Environment

**Files:**

- Execute: `docs/superpowers/plans/2026-06-10-mineadmin-education-saas-00-f00-environment.md`

- [x] **Step 1: Run every F00 task in order**

Expected:

```text
Repository, Docker, MineAdmin install, env, Makefile, and smoke checks pass.
```

### Task 2: Execute F01 Tenant Campus

**Files:**

- Execute: `docs/superpowers/plans/2026-06-10-mineadmin-education-saas-00-f01-tenant-campus.md`

- [x] **Step 1: Run every F01 task in order**

Expected:

```text
Tenant/campus backend, PC, permission, isolation, and verification gates pass.
```

### Task 3: Execute F02 User Profile Role Scope

**Files:**

- Execute: `docs/superpowers/plans/2026-06-10-mineadmin-education-saas-00-f02-user-profile-role-scope.md`

- [x] **Step 1: Run every F02 task in order**

Expected:

```text
Profile, role code, campus scope, context middleware, PC, and verification gates pass.
```

### Task 4: Execute F03 Dictionary Feature Flag

**Files:**

- Execute: `docs/superpowers/plans/2026-06-10-mineadmin-education-saas-00-f03-dictionary-feature-flag.md`

- [x] **Step 1: Run every F03 task in order**

Expected:

```text
Dictionary, feature flag, seeders, lookup services, PC, and verification gates pass.
```

### Task 5: Execute F04 Audit Log

**Files:**

- Execute: `docs/superpowers/plans/2026-06-10-mineadmin-education-saas-00-f04-audit-log.md`

- [x] **Step 1: Run every F04 task in order**

Expected:

```text
Audit log backend, listener, write integration, PC audit page, and verification gates pass.
```

### Task 6: Execute F05 PC Admin Foundation

**Files:**

- Execute: `docs/superpowers/plans/2026-06-10-mineadmin-education-saas-00-f05-pc-admin-foundation.md`

- [x] **Step 1: Run every F05 task in order**

Expected:

```text
PC route tree, API clients, permission helper, page state tests, and build gates pass.
```

### Task 7: Execute F06 Mobile Foundation

**Files:**

- Execute: `docs/superpowers/plans/2026-06-10-mineadmin-education-saas-00-f06-mobile-foundation.md`

- [x] **Step 1: Run every F06 task in order**

Expected:

```text
Mobile context APIs, role isolation, uni-app pages, and build gates pass.
```

## Self-Review

- Spec coverage: Foundation now has code-level child plans for environment, tenant/campus, profile/scope, dictionary/feature flag, audit log, PC admin, and mobile context.
- MineAdmin fit: Parent and child plans use MineAdmin 3.x native `app/Http/Admin`, `app/Http/Api`, `app/Service`, `app/Repository`, `app/Model`, `app/Schema`, and `databases/migrations` boundaries.
- Plan granularity: Code-level migration, backend, API, PC, mobile, test, command, and acceptance details live in F00-F06 child plans.
- Scope control: V1-V12 business modules remain outside Foundation and stay in the detailed plan backlog.
- Acceptance: All Foundation child plans F00-F06 have been implemented and verified, so the Foundation parent is marked `accepted`.
