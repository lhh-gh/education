# MineAdmin Education SaaS F06 Mobile Foundation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Foundation mobile context layer for teacher, guardian, and operator clients, including MineAdmin-compatible mobile APIs, uni-app API clients, entry pages, role isolation tests, page state tests, and build verification.

**Architecture:** F06 consumes F02 education profiles and campus scopes, F03 feature flags, and F04 audit conventions. Mobile public APIs live under MineAdmin's HTTP API layer (`app/Http/Api`) while business logic stays in shared `app/Service`, `app/Repository`, and `app/Schema` layers. uni-app pages render role-specific context only; V1 owns class schedules, attendance, leave, students, and course accounts.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MineAdmin current user context, uni-app Vue3, TypeScript, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. F06 mobile foundation gates have passed.

---

## Scope Check

Included:

- Create mobile context API request, middleware, controllers, service, repository, and schema.
- Add teacher, guardian, and operator context endpoints under `/mobile/education/foundation/*/context`.
- Resolve the current authenticated user through MineAdmin auth, then resolve education role context through F02 profile and campus scope services.
- Return enabled feature flags from F03 in every context response.
- Enforce role isolation for teacher, guardian, and operator endpoints.
- Add uni-app context API client, shared context types, pages.json entries, teacher entry page, guardian entry page, and operator entry page.
- Add backend tests for API contracts, role isolation, disabled profile rejection, tenant/campus scope filtering, and feature flag visibility.
- Add mobile tests for API client envelope handling, page loading, empty, error, retry, forbidden, and success states.
- Run backend tests, mobile lint/tests/build, and PC build regression.

Excluded:

- WeChat login, mobile token issuing, OAuth callback, and openid binding. F06 uses the currently authenticated MineAdmin user and F02 profile records.
- Teacher lesson schedule, attendance, leave approval, and notices. V1 owns teacher academic workflows.
- Guardian student binding, course accounts, lesson consumption, leave request, and notices. V1 and V7 own guardian business workflows.
- Operator PC admin pages. F05 owns PC admin integration.
- New database tables. F06 consumes F02 and F03 tables.

Dependencies:

```text
F02 User Profile Role Scope ready
F03 Dictionary Feature Flag ready
F04 Audit Log ready
F05 PC Admin Foundation ready
```

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/app/Http/Api/Middleware/Education/Foundation/MobileEducationContextMiddleware.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Foundation/MobileContextRequest.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Foundation/TeacherFoundationController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Foundation/GuardianFoundationController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Foundation/OperatorFoundationController.php
mineadmin-education-saas/backend/app/Repository/Education/Foundation/MobileContextRepository.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/MobileContextService.php
mineadmin-education-saas/backend/app/Schema/Education/Foundation/MobileContextSchema.php
```

Modify backend:

```text
mineadmin-education-saas/backend/config/autoload/middlewares.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/MobileContextRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/MobileContextServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/MobileEducationContextMiddlewareTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/FoundationMobileContextApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/FoundationMobileRoleIsolationTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/FoundationMobileCampusScopeTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/FoundationMobileFeatureFlagTest.php
```

Create mobile:

```text
mineadmin-education-saas/mobile-uniapp/src/api/foundation/types.ts
mineadmin-education-saas/mobile-uniapp/src/api/foundation/context.ts
mineadmin-education-saas/mobile-uniapp/src/api/foundation/__tests__/context.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/teacher/index.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/__tests__/index.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/guardian/index.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/__tests__/index.spec.ts
mineadmin-education-saas/mobile-uniapp/pages/operator/index.vue
mineadmin-education-saas/mobile-uniapp/pages/operator/__tests__/index.spec.ts
mineadmin-education-saas/mobile-uniapp/tests/foundation/pagesJson.spec.ts
```

Modify mobile:

```text
mineadmin-education-saas/mobile-uniapp/pages.json
```

Verify PC:

```text
mineadmin-education-saas/admin-web/package.json
```

## Database Migration Design

F06 creates no database migration.

Existing tables consumed:

```text
edu_user_profiles from F02
edu_user_campus_scopes from F02
edu_tenants from F01
edu_campuses from F01
edu_feature_flags from F03
edu_audit_logs from F04 is not queried by F06
```

Tenant and campus isolation fields:

```text
Profile tenant isolation uses edu_user_profiles.tenant_id.
Campus isolation uses edu_user_campus_scopes.tenant_id and edu_user_campus_scopes.campus_id.
Teacher, principal, academic_staff, front_desk, and finance contexts require campus scopes when enabled.
Guardian context does not require campus scope in Foundation.
```

Foreign-key policy:

```text
No F06 physical foreign keys are added. F06 validates all profile, tenant, campus, and feature flag visibility through services and repositories from F01-F03.
```

Rollback behavior:

```text
No database rollback exists for F06. Reverting F06 means reverting API controller, middleware, service, repository, schema, mobile page, mobile API client, and test changes.
```

Verification command:

```bash
cd mineadmin-education-saas
find backend/databases/migrations -maxdepth 1 -name '*000600*mobile*' -o -name '*f06*'
```

Expected:

```text
No output.
```

## MineAdmin Backend Module Design

### HTTP Layer Decision

Admin APIs from F01-F05 remain under:

```text
mineadmin-education-saas/backend/app/Http/Admin/Controller
mineadmin-education-saas/backend/app/Http/Admin/Request
```

F06 mobile APIs use MineAdmin's public API layer:

```text
mineadmin-education-saas/backend/app/Http/Api/Controller
mineadmin-education-saas/backend/app/Http/Api/Request
mineadmin-education-saas/backend/app/Http/Api/Middleware
```

Shared business code stays under:

```text
mineadmin-education-saas/backend/app/Service/Education/Foundation
mineadmin-education-saas/backend/app/Repository/Education/Foundation
mineadmin-education-saas/backend/app/Schema/Education/Foundation
```

### Mobile Context Middleware

Create `MobileEducationContextMiddleware`.

Path:

```text
mineadmin-education-saas/backend/app/Http/Api/Middleware/Education/Foundation/MobileEducationContextMiddleware.php
```

Responsibilities:

```text
Read MineAdmin authenticated current user.
Resolve F02 EducationUserContext for the current user.
Reject unauthenticated requests with code 401.
Reject disabled profiles with code 403.
Store context in Hyperf Context key education.mobile_context.
Continue request only when context is valid.
```

Response examples:

```json
{
  "code": 401,
  "message": "mobile authentication required",
  "data": {
    "required": "Authorization"
  }
}
```

```json
{
  "code": 403,
  "message": "education profile is disabled",
  "data": {
    "status": "disabled"
  }
}
```

Register middleware in:

```text
mineadmin-education-saas/backend/config/autoload/middlewares.php
```

Rule:

```text
Apply middleware only to `/mobile/education/*` routes.
Do not apply this middleware to `/admin/*` routes.
```

### Request

Create `MobileContextRequest`.

Rules:

```php
[
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'client_type' => ['nullable', 'in:wechat_service,wechat_miniprogram,h5'],
]
```

Messages:

```text
campus_id.integer: campus_id must be an integer
campus_id.min: campus_id must be at least 1
client_type.in: client_type must be one of wechat_service, wechat_miniprogram, h5
```

Source:

```text
Read `campus_id` and `client_type` from query parameters.
Read `X-Client-Type` as fallback for client_type when query client_type is empty.
```

### Repository

Create `MobileContextRepository`.

Extends:

```text
App\Repository\IRepository
```

Methods:

```php
public function findProfileForUser(int $userId, ?int $tenantId): ?EducationUserProfile
public function listCampusScopes(int $tenantId, int $userId): array
public function findTenant(int $tenantId): ?EducationTenant
public function findCampus(int $tenantId, int $campusId): ?EducationCampus
public function listEnabledFeatureFlags(?int $tenantId): array
```

Rules:

```text
findProfileForUser filters status enabled and deleted_at null.
When tenantId is null, return platform profile only.
When tenantId is not null, return profile matching tenant_id and user_id.
listCampusScopes returns enabled campuses only.
listEnabledFeatureFlags uses F03 FeatureFlagService resolved values for current tenant and returns feature_code => enabled.
```

### Service

Create `MobileContextService`.

Extends:

```text
App\Service\IService
```

Methods:

```php
public function teacherContext(EducationUserContext $context, array $params): array
public function guardianContext(EducationUserContext $context, array $params): array
public function operatorContext(EducationUserContext $context, array $params): array
private function assertRole(EducationUserContext $context, array $allowedRoles, string $message): void
private function assertCampusAllowed(EducationUserContext $context, ?int $campusId): void
private function baseContext(EducationUserContext $context, array $params): array
```

Allowed roles:

```text
teacher endpoint: teacher
guardian endpoint: guardian
operator endpoint: tenant_admin, principal, academic_staff, front_desk, finance
```

Teacher rules:

```text
role_code must be teacher.
tenant_id must not be null.
campus_scopes must contain at least one enabled campus.
When campus_id query is present, it must be inside campus_scopes.
Return teacher entry tabs for today overview, messages, and profile; V1 later replaces overview content.
```

Guardian rules:

```text
role_code must be guardian.
tenant_id must not be null.
openid or unionid is accepted when present but not required in F06.
Return bound_students as an empty array because V1 owns student binding tables.
Return empty_state code guardian_students_pending_v1 when bound_students is empty.
```

Operator rules:

```text
role_code must be tenant_admin, principal, academic_staff, front_desk, or finance.
tenant_id must not be null.
principal, academic_staff, front_desk, and finance use campus scope filtering.
tenant_admin can see tenant-wide context and all enabled campuses in the tenant.
```

Common response fields:

```text
tenant: id, name, short_name
profile: id, user_id, role_code, display_name, mobile, avatar, current_campus_id
campus_scopes: campus_id, campus_name, is_current
feature_flags: feature_code => boolean
entry: default_path, tabs
empty_state: null or { code, message }
```

### Controllers

Create `TeacherFoundationController`.

```php
#[Controller(prefix: 'mobile/education/foundation/teacher')]
final class TeacherFoundationController extends AbstractController
{
    #[GetMapping('context')]
    public function context(MobileContextRequest $request): Result
    {
        return $this->success(
            $this->service->teacherContext($this->contextResolver->mobile(), $request->validated())
        );
    }
}
```

Create `GuardianFoundationController`.

```php
#[Controller(prefix: 'mobile/education/foundation/guardian')]
final class GuardianFoundationController extends AbstractController
{
    #[GetMapping('context')]
    public function context(MobileContextRequest $request): Result
    {
        return $this->success(
            $this->service->guardianContext($this->contextResolver->mobile(), $request->validated())
        );
    }
}
```

Create `OperatorFoundationController`.

```php
#[Controller(prefix: 'mobile/education/foundation/operator')]
final class OperatorFoundationController extends AbstractController
{
    #[GetMapping('context')]
    public function context(MobileContextRequest $request): Result
    {
        return $this->success(
            $this->service->operatorContext($this->contextResolver->mobile(), $request->validated())
        );
    }
}
```

Controller rules:

```text
Use MobileEducationContextMiddleware for authentication and context.
Use MineAdmin Result response envelope.
Do not use Admin PermissionMiddleware on mobile endpoints.
Do not use OperationMiddleware because context reads do not mutate data.
```

### Schema

Create `MobileContextSchema`.

Fields:

```text
tenant.id integer
tenant.name string
tenant.short_name string nullable
profile.id integer
profile.user_id integer
profile.role_code string
profile.display_name string
profile.mobile string nullable
profile.avatar string nullable
profile.current_campus_id integer nullable
campus_scopes[].campus_id integer
campus_scopes[].campus_name string
campus_scopes[].is_current boolean
feature_flags object<string, boolean>
entry.default_path string
entry.tabs[].key string
entry.tabs[].label string
entry.tabs[].path string
empty_state.code string nullable
empty_state.message string nullable
```

## API Contract

### API 1: Teacher Context

```text
GET /mobile/education/foundation/teacher/context
Auth: mobile authenticated MineAdmin user token
Role: teacher
Caller: teacher mobile H5, WeChat service account page, or mini program shell
Isolation: tenant_id equals teacher profile tenant_id; campus scopes limited to F02 scope rows
Audit: read operation, no audit log
```

Headers:

```text
Authorization: Bearer test-mobile-teacher-token
X-Tenant-Id: 1001
X-Client-Type: wechat_miniprogram
```

Query:

```json
{
  "campus_id": 2001,
  "client_type": "wechat_miniprogram"
}
```

Success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "tenant": {
      "id": 1001,
      "name": "Demo Education",
      "short_name": "Demo"
    },
    "profile": {
      "id": 3001,
      "user_id": 501,
      "role_code": "teacher",
      "display_name": "Teacher Wang",
      "mobile": "138****0000",
      "avatar": null,
      "current_campus_id": 2001
    },
    "campus_scopes": [
      {
        "campus_id": 2001,
        "campus_name": "East Campus",
        "is_current": true
      }
    ],
    "feature_flags": {
      "education.v1.core_academic": true,
      "education.v7.family_service": false
    },
    "entry": {
      "default_path": "/pages/teacher/index",
      "tabs": [
        {
          "key": "overview",
          "label": "Overview",
          "path": "/pages/teacher/index"
        },
        {
          "key": "profile",
          "label": "Profile",
          "path": "/pages/teacher/index"
        }
      ]
    },
    "empty_state": null
  }
}
```

Validation failure:

```json
{
  "code": 422,
  "message": "campus_id must be at least 1",
  "data": {
    "field": "campus_id"
  }
}
```

Business failure:

```json
{
  "code": 403,
  "message": "teacher profile is required",
  "data": {
    "role_code": "guardian"
  }
}
```

### API 2: Guardian Context

```text
GET /mobile/education/foundation/guardian/context
Auth: mobile authenticated MineAdmin user token
Role: guardian
Caller: guardian mobile H5, WeChat service account page, or mini program shell
Isolation: tenant_id equals guardian profile tenant_id; no campus scope required in Foundation
Audit: read operation, no audit log
```

Headers:

```text
Authorization: Bearer test-mobile-guardian-token
X-Tenant-Id: 1001
X-Client-Type: wechat_service
```

Query:

```json
{
  "client_type": "wechat_service"
}
```

Success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "tenant": {
      "id": 1001,
      "name": "Demo Education",
      "short_name": "Demo"
    },
    "profile": {
      "id": 3002,
      "user_id": 502,
      "role_code": "guardian",
      "display_name": "Guardian Li",
      "mobile": "139****0000",
      "avatar": null,
      "current_campus_id": null
    },
    "campus_scopes": [],
    "bound_students": [],
    "feature_flags": {
      "education.v1.core_academic": true,
      "education.v7.family_service": false
    },
    "entry": {
      "default_path": "/pages/guardian/index",
      "tabs": [
        {
          "key": "home",
          "label": "Home",
          "path": "/pages/guardian/index"
        },
        {
          "key": "profile",
          "label": "Profile",
          "path": "/pages/guardian/index"
        }
      ]
    },
    "empty_state": {
      "code": "guardian_students_pending_v1",
      "message": "Student binding will be available in V1"
    }
  }
}
```

Validation failure:

```json
{
  "code": 422,
  "message": "client_type must be one of wechat_service, wechat_miniprogram, h5",
  "data": {
    "field": "client_type"
  }
}
```

Business failure:

```json
{
  "code": 403,
  "message": "guardian profile is not bound",
  "data": {
    "required_action": "contact_campus"
  }
}
```

### API 3: Operator Context

```text
GET /mobile/education/foundation/operator/context
Auth: mobile authenticated MineAdmin user token
Role: tenant_admin, principal, academic_staff, front_desk, or finance
Caller: operator mobile H5 or mini program shell
Isolation: tenant_id equals operator profile tenant_id; campus-scoped roles see only allowed campus scopes
Audit: read operation, no audit log
```

Headers:

```text
Authorization: Bearer test-mobile-operator-token
X-Tenant-Id: 1001
X-Client-Type: h5
```

Query:

```json
{
  "campus_id": 2001,
  "client_type": "h5"
}
```

Success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "tenant": {
      "id": 1001,
      "name": "Demo Education",
      "short_name": "Demo"
    },
    "profile": {
      "id": 3003,
      "user_id": 503,
      "role_code": "front_desk",
      "display_name": "Front Desk Chen",
      "mobile": "137****0000",
      "avatar": null,
      "current_campus_id": 2001
    },
    "campus_scopes": [
      {
        "campus_id": 2001,
        "campus_name": "East Campus",
        "is_current": true
      }
    ],
    "feature_flags": {
      "education.v1.core_academic": true,
      "education.v3.admissions_crm": true
    },
    "entry": {
      "default_path": "/pages/operator/index",
      "tabs": [
        {
          "key": "overview",
          "label": "Overview",
          "path": "/pages/operator/index"
        },
        {
          "key": "profile",
          "label": "Profile",
          "path": "/pages/operator/index"
        }
      ]
    },
    "empty_state": null
  }
}
```

Validation failure:

```json
{
  "code": 422,
  "message": "campus_id must be an integer",
  "data": {
    "field": "campus_id"
  }
}
```

Business failure:

```json
{
  "code": 403,
  "message": "operator role is required",
  "data": {
    "role_code": "teacher"
  }
}
```

## PC Admin Page Tasks

F06 creates no PC admin page because F06 is the mobile foundation entry layer.

PC impact:

```text
No admin-web route is added.
No admin-web API client is added.
No admin-web menu item is added.
F05 remains the PC Foundation integration owner.
```

PC regression verification:

```bash
cd mineadmin-education-saas/admin-web
pnpm build
```

Expected:

```text
Admin-web production build passes with no F06 PC route changes.
```

## Teacher / Guardian Mobile Page Tasks

### Mobile API Types

Create:

```text
mineadmin-education-saas/mobile-uniapp/src/api/foundation/types.ts
```

Types:

```ts
export type MobileClientType = 'wechat_service' | 'wechat_miniprogram' | 'h5'
export type MobileRoleCode = 'teacher' | 'guardian' | 'tenant_admin' | 'principal' | 'academic_staff' | 'front_desk' | 'finance'

export interface MobileTenant {
  id: number
  name: string
  short_name: string | null
}

export interface MobileProfile {
  id: number
  user_id: number
  role_code: MobileRoleCode
  display_name: string
  mobile: string | null
  avatar: string | null
  current_campus_id: number | null
}

export interface MobileCampusScope {
  campus_id: number
  campus_name: string
  is_current: boolean
}

export interface MobileEntryTab {
  key: string
  label: string
  path: string
}

export interface MobileEmptyState {
  code: string
  message: string
}

export interface MobileFoundationContext {
  tenant: MobileTenant
  profile: MobileProfile
  campus_scopes: MobileCampusScope[]
  feature_flags: Record<string, boolean>
  entry: {
    default_path: string
    tabs: MobileEntryTab[]
  }
  empty_state: MobileEmptyState | null
}

export interface GuardianFoundationContext extends MobileFoundationContext {
  bound_students: Array<{ id: number; name: string }>
}
```

### Mobile API Client

Create:

```text
mineadmin-education-saas/mobile-uniapp/src/api/foundation/context.ts
```

Methods:

```ts
export function getTeacherContext(params?: { campus_id?: number; client_type?: MobileClientType }): Promise<MobileFoundationContext>
export function getGuardianContext(params?: { client_type?: MobileClientType }): Promise<GuardianFoundationContext>
export function getOperatorContext(params?: { campus_id?: number; client_type?: MobileClientType }): Promise<MobileFoundationContext>
```

Rules:

```text
Call the matching `/mobile/education/foundation/*/context` endpoint.
Unwrap MineAdmin result envelope only when code is 200.
Throw an Error with response message when code is not 200.
Preserve response data on the thrown error as `error.data` for forbidden and empty-state screens.
Pass Authorization token through the shared mobile request helper.
```

### pages.json

Modify:

```text
mineadmin-education-saas/mobile-uniapp/pages.json
```

Entries:

```json
{
  "pages": [
    {
      "path": "pages/teacher/index",
      "style": {
        "navigationBarTitleText": "Teacher"
      }
    },
    {
      "path": "pages/guardian/index",
      "style": {
        "navigationBarTitleText": "Guardian"
      }
    },
    {
      "path": "pages/operator/index",
      "style": {
        "navigationBarTitleText": "Operator"
      }
    }
  ]
}
```

### Shared Page State

Every F06 mobile page must implement:

```text
loading: initial state before API response.
success: context object rendered.
empty: empty_state exists or campus_scopes is empty for teacher/operator.
forbidden: API code 401 or 403; show message and retry button.
error: network or server failure; show message and retry button.
retry: calls the same context API again and returns to loading state.
refresh: pull-down refresh calls context API and stops refresh after completion.
```

### Teacher Page

Create:

```text
mineadmin-education-saas/mobile-uniapp/pages/teacher/index.vue
```

API:

```text
getTeacherContext()
```

Success content:

```text
tenant name
teacher display_name
current campus name
campus scope list
enabled feature flag chips
```

Empty content:

```text
When campus_scopes is empty, show "No campus scope assigned".
When feature_flags has no true value, show "No enabled mobile features".
```

Forbidden content:

```text
Show API message.
Show retry button.
Do not render tenant or profile data.
```

### Guardian Page

Create:

```text
mineadmin-education-saas/mobile-uniapp/pages/guardian/index.vue
```

API:

```text
getGuardianContext()
```

Success content:

```text
tenant name
guardian display_name
bound student count
enabled feature flag chips
```

Empty content:

```text
When bound_students is empty, show empty_state.message from API.
When API returns guardian profile is not bound, show "Contact campus to bind guardian profile".
```

Forbidden content:

```text
Show API message.
Show retry button.
Do not render profile data.
```

### Operator Page

Create:

```text
mineadmin-education-saas/mobile-uniapp/pages/operator/index.vue
```

API:

```text
getOperatorContext()
```

Success content:

```text
tenant name
operator display_name
role code
current campus name when present
campus scope list
enabled feature flag chips
```

Empty content:

```text
When campus_scopes is empty for campus-scoped role, show "No campus scope assigned".
When feature_flags has no true value, show "No enabled mobile features".
```

Forbidden content:

```text
Show API message.
Show retry button.
Do not render tenant or profile data.
```

## Test Plan

Backend unit tests:

| Test file | Case | Assertion |
| --- | --- | --- |
| `MobileContextRepositoryTest.php` | `test_find_profile_for_user_is_tenant_scoped` | tenant 1 profile is returned; tenant 2 profile is excluded |
| `MobileContextRepositoryTest.php` | `test_list_campus_scopes_returns_enabled_campuses_only` | disabled campuses are excluded |
| `MobileContextRepositoryTest.php` | `test_list_enabled_feature_flags_uses_tenant_override` | tenant flag overrides system flag |
| `MobileContextServiceTest.php` | `test_teacher_context_requires_teacher_role` | guardian context passed to teacher method throws 403 |
| `MobileContextServiceTest.php` | `test_teacher_context_requires_campus_scope` | teacher with empty campus scopes throws 403 |
| `MobileContextServiceTest.php` | `test_guardian_context_returns_empty_students_until_v1` | bound_students is empty and empty_state code is guardian_students_pending_v1 |
| `MobileContextServiceTest.php` | `test_operator_context_accepts_front_desk` | front_desk role returns operator context |
| `MobileContextServiceTest.php` | `test_operator_context_rejects_teacher` | teacher role throws 403 |

Backend feature tests:

| Test file | Case | Assertion |
| --- | --- | --- |
| `MobileEducationContextMiddlewareTest.php` | `test_unauthenticated_mobile_request_returns_401` | response code is 401 |
| `MobileEducationContextMiddlewareTest.php` | `test_disabled_profile_returns_403` | response code is 403 and status is disabled |
| `FoundationMobileContextApiTest.php` | `test_teacher_context_returns_mineadmin_result_shape` | code, message, data.tenant, data.profile, data.campus_scopes exist |
| `FoundationMobileContextApiTest.php` | `test_guardian_context_returns_empty_students` | bound_students is empty array and empty_state exists |
| `FoundationMobileContextApiTest.php` | `test_operator_context_returns_feature_flags` | feature_flags object exists |
| `FoundationMobileRoleIsolationTest.php` | `test_guardian_cannot_access_teacher_context` | teacher endpoint returns 403 for guardian |
| `FoundationMobileRoleIsolationTest.php` | `test_teacher_cannot_access_guardian_context` | guardian endpoint returns 403 for teacher |
| `FoundationMobileRoleIsolationTest.php` | `test_teacher_cannot_access_operator_context` | operator endpoint returns 403 for teacher |
| `FoundationMobileCampusScopeTest.php` | `test_teacher_cannot_request_out_of_scope_campus` | response code is 403 and campus_id is returned |
| `FoundationMobileCampusScopeTest.php` | `test_front_desk_sees_only_allowed_campus_scope` | other tenant and other campus scopes excluded |
| `FoundationMobileFeatureFlagTest.php` | `test_context_includes_enabled_tenant_feature_flags` | enabled tenant feature flag appears true |
| `FoundationMobileFeatureFlagTest.php` | `test_disabled_feature_flags_are_returned_false` | disabled flag appears false or is absent according to F03 service contract |

Mobile API tests:

| Test file | Case | Assertion |
| --- | --- | --- |
| `context.spec.ts` | `getTeacherContext_unwraps_success_result` | resolves response data |
| `context.spec.ts` | `getGuardianContext_throws_api_message_on_403` | rejected error message matches API message |
| `context.spec.ts` | `getOperatorContext_sends_campus_id_and_client_type` | request params include campus_id and client_type |

Mobile page tests:

| Test file | Case | Assertion |
| --- | --- | --- |
| `pagesJson.spec.ts` | `registers_teacher_guardian_operator_pages` | pages.json contains three F06 pages |
| `pages/teacher/__tests__/index.spec.ts` | `renders_loading_then_teacher_context` | loading state appears, then tenant/profile/campus render |
| `pages/teacher/__tests__/index.spec.ts` | `renders_no_campus_empty_state` | empty campus message appears |
| `pages/teacher/__tests__/index.spec.ts` | `retry_calls_context_api_again` | retry triggers second API call |
| `pages/guardian/__tests__/index.spec.ts` | `renders_empty_student_state` | empty_state.message renders |
| `pages/guardian/__tests__/index.spec.ts` | `renders_forbidden_without_profile_data` | forbidden message renders and profile data hidden |
| `pages/operator/__tests__/index.spec.ts` | `renders_operator_role_and_feature_flags` | role code and enabled features render |
| `pages/operator/__tests__/index.spec.ts` | `renders_error_and_retry` | network error shows retry button |

PC regression:

| Command | Assertion |
| --- | --- |
| `pnpm build` in admin-web | PC build remains green with no F06 route changes |

## Execution Commands

### Backend Unit Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter MobileContextRepositoryTest
composer test -- --filter MobileContextServiceTest
```

Expected:

```text
Mobile context repository and service tests pass.
```

### Backend Feature Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter MobileEducationContextMiddlewareTest
composer test -- --filter FoundationMobileContextApiTest
composer test -- --filter FoundationMobileRoleIsolationTest
composer test -- --filter FoundationMobileCampusScopeTest
composer test -- --filter FoundationMobileFeatureFlagTest
```

Expected:

```text
Mobile middleware, context API, role isolation, campus scope, and feature flag tests pass.
```

### Mobile Dependency Gate

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm install
```

Expected:

```text
Dependencies are installed and lockfile remains consistent.
```

### Mobile API and Page Test Gate

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm test -- context
pnpm test -- pagesJson
pnpm test -- teacher
pnpm test -- guardian
pnpm test -- operator
```

Expected:

```text
Mobile context API, pages.json, teacher page, guardian page, and operator page tests pass.
```

### Mobile Build Gate

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm lint
pnpm build:h5
```

Expected:

```text
Mobile lint passes.
Mobile H5 build succeeds and dist/build/h5/index.html exists.
```

### PC Regression Gate

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm build
```

Expected:

```text
Admin-web production build passes.
```

### F06 Final Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter MobileContext
composer test -- --filter FoundationMobile
composer cs-fix -- --dry-run
composer analyse
cd ../mobile-uniapp
pnpm lint
pnpm test -- context
pnpm test -- teacher
pnpm test -- guardian
pnpm test -- operator
pnpm build:h5
cd ../admin-web
pnpm build
```

Expected:

```text
Backend mobile context tests pass.
Backend code style dry run passes.
Backend static analysis passes.
Mobile lint, tests, and H5 build pass.
Admin-web production build passes.
```

## Acceptance Gate

F06 is accepted only when all conditions are true:

```text
- Mobile API controllers live under `backend/app/Http/Api/Controller`.
- Mobile request and middleware live under `backend/app/Http/Api`.
- No F06 database migration is created.
- Teacher context returns only teacher profile, tenant, scoped campuses, feature flags, and teacher entry metadata.
- Guardian context returns only guardian profile, tenant, feature flags, empty student list, and V1 pending empty state.
- Operator context returns only allowed operator roles, tenant, campus scopes, feature flags, and operator entry metadata.
- Wrong-role access returns 403 for all three context APIs.
- Unauthenticated mobile access returns 401.
- Disabled profile access returns 403.
- Out-of-scope campus query returns 403.
- uni-app context API client unwraps MineAdmin result envelope and throws API messages on non-200 codes.
- Teacher, guardian, and operator entry pages render loading, success, empty, forbidden, error, retry, and refresh states.
- `pages.json` registers teacher, guardian, and operator pages.
- Mobile lint, tests, and H5 build pass.
- Admin-web build still passes with no F06 PC route changes.
```

## Task Breakdown

### Task 1: Create Mobile Backend Context Layer

**Files:**

- Create: `mineadmin-education-saas/backend/app/Http/Api/Middleware/Education/Foundation/MobileEducationContextMiddleware.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Request/Education/Foundation/MobileContextRequest.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Foundation/MobileContextRepository.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Foundation/MobileContextService.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Foundation/MobileContextSchema.php`
- Modify: `mineadmin-education-saas/backend/config/autoload/middlewares.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/MobileContextRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/MobileContextServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/MobileEducationContextMiddlewareTest.php`

- [x] **Step 1: Create request class**

Implement `MobileContextRequest` rules, messages, and header fallback behavior from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create mobile context middleware**

Implement authentication, profile resolution, disabled profile rejection, and Hyperf Context storage.

- [x] **Step 3: Register middleware**

Apply `MobileEducationContextMiddleware` to `/mobile/education/*` routes only.

- [x] **Step 4: Create repository**

Implement repository methods and visibility rules from `MineAdmin Backend Module Design`.

- [x] **Step 5: Create service and schema**

Implement teacher, guardian, operator, role assertion, campus assertion, and base context mapping.

- [x] **Step 6: Write backend unit and middleware tests**

Create repository, service, and middleware tests listed in `Test Plan`.

- [x] **Step 7: Run backend unit gate**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter MobileContextRepositoryTest
composer test -- --filter MobileContextServiceTest
composer test -- --filter MobileEducationContextMiddlewareTest
```

Expected:

```text
Mobile repository, service, and middleware tests pass.
```

### Task 2: Create Mobile Context Controllers

**Files:**

- Create: `mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Foundation/TeacherFoundationController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Foundation/GuardianFoundationController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Foundation/OperatorFoundationController.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/FoundationMobileContextApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/FoundationMobileRoleIsolationTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/FoundationMobileCampusScopeTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/FoundationMobileFeatureFlagTest.php`

- [x] **Step 1: Create teacher controller**

Implement `GET mobile/education/foundation/teacher/context` using `MobileContextService::teacherContext`.

- [x] **Step 2: Create guardian controller**

Implement `GET mobile/education/foundation/guardian/context` using `MobileContextService::guardianContext`.

- [x] **Step 3: Create operator controller**

Implement `GET mobile/education/foundation/operator/context` using `MobileContextService::operatorContext`.

- [x] **Step 4: Write API contract and isolation tests**

Create context API, role isolation, campus scope, and feature flag tests listed in `Test Plan`.

- [x] **Step 5: Run backend feature gate**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter FoundationMobileContextApiTest
composer test -- --filter FoundationMobileRoleIsolationTest
composer test -- --filter FoundationMobileCampusScopeTest
composer test -- --filter FoundationMobileFeatureFlagTest
```

Expected:

```text
Teacher, guardian, operator API contracts and isolation tests pass.
```

### Task 3: Create Mobile API Client and Page Registration

**Files:**

- Create: `mineadmin-education-saas/mobile-uniapp/src/api/foundation/types.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/src/api/foundation/context.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/src/api/foundation/__tests__/context.spec.ts`
- Modify: `mineadmin-education-saas/mobile-uniapp/pages.json`
- Create: `mineadmin-education-saas/mobile-uniapp/tests/foundation/pagesJson.spec.ts`

- [x] **Step 1: Create mobile context types**

Implement TypeScript types from `Teacher / Guardian Mobile Page Tasks`.

- [x] **Step 2: Create mobile context API client**

Implement `getTeacherContext`, `getGuardianContext`, and `getOperatorContext` with result envelope handling.

- [x] **Step 3: Register pages**

Add teacher, guardian, and operator page entries to `pages.json`.

- [x] **Step 4: Write mobile API and pages.json tests**

Create `context.spec.ts` and `pagesJson.spec.ts` cases from `Test Plan`.

- [x] **Step 5: Run mobile API gate**

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm test -- context
pnpm test -- pagesJson
```

Expected:

```text
Mobile context API client and pages.json tests pass.
```

### Task 4: Create Teacher, Guardian, and Operator Entry Pages

**Files:**

- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/index.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/teacher/__tests__/index.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/index.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/guardian/__tests__/index.spec.ts`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/operator/index.vue`
- Create: `mineadmin-education-saas/mobile-uniapp/pages/operator/__tests__/index.spec.ts`

- [x] **Step 1: Create teacher page**

Implement teacher API call, success content, empty content, forbidden content, loading, error, retry, and refresh states.

- [x] **Step 2: Create guardian page**

Implement guardian API call, success content, empty content, forbidden content, loading, error, retry, and refresh states.

- [x] **Step 3: Create operator page**

Implement operator API call, success content, empty content, forbidden content, loading, error, retry, and refresh states.

- [x] **Step 4: Write mobile page tests**

Create teacher, guardian, and operator page tests listed in `Test Plan`.

- [x] **Step 5: Run mobile page tests**

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm test -- teacher
pnpm test -- guardian
pnpm test -- operator
```

Expected:

```text
Teacher, guardian, and operator mobile page tests pass.
```

### Task 5: Run F06 Final Gate

**Files:**

- Verify: all backend, mobile, and PC paths listed in `File Structure`.

- [x] **Step 1: Run backend final gate**

Run backend commands from `F06 Final Gate`.

- [x] **Step 2: Run mobile final gate**

Run mobile commands from `F06 Final Gate`.

- [x] **Step 3: Run PC regression gate**

Run PC command from `F06 Final Gate`.

- [x] **Step 4: Commit F06**

Run:

```bash
cd mineadmin-education-saas
git add backend mobile-uniapp admin-web
git commit -m "feat: add education mobile foundation context"
```

Expected:

```text
Commit succeeds with F06 backend API, mobile pages, and verification changes.
```

## Self-Review

- Spec coverage: F06 covers teacher, guardian, and operator mobile context APIs, role isolation, campus scope isolation, feature flags, uni-app API clients, pages, tests, commands, and acceptance gates.
- MineAdmin fit: Admin APIs remain under `app/Http/Admin`; mobile APIs use `app/Http/Api`; shared logic uses `app/Service`, `app/Repository`, and `app/Schema`; responses keep MineAdmin result shape.
- Mobile boundary: F06 does not implement WeChat login, V1 schedule, guardian student binding, attendance, leave, notices, or course accounts.
- Tenant isolation: Context is resolved from F02 profile and campus scope; wrong tenant, wrong campus, wrong role, disabled profile, and unauthenticated requests are rejected.
- Feature flag fit: Context responses expose F03 resolved feature flags for the current tenant.
- PC fit: F06 adds no admin-web route and keeps PC build regression in the final gate.
- Readiness: This plan has exact paths, migration non-change constraints, backend layer tasks, API request/response/failure examples, PC rationale, mobile page tasks, tests, commands, expected outputs, and acceptance gates, so F06 can be marked `ready`.
