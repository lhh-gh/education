# MineAdmin Education SaaS F05 PC Admin Foundation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Consolidate the MineAdmin PC admin foundation for F01-F04 resources: route module, menu hierarchy, typed API clients, page state contracts, permission-controlled buttons, cross-page tests, lint, and build verification.

**Architecture:** F01-F04 own backend APIs and initial page implementations. F05 is a PC-only integration pass that standardizes the MineAdmin-Vue route tree, shared frontend types, permission helper, API client signatures, list/form/drawer state behavior, and regression tests across tenant, campus, user profile, dictionary, feature flag, and audit log pages.

**Tech Stack:** MineAdmin-Vue, Vue3, TypeScript, pnpm, Vitest or the generated MineAdmin frontend test runner, MineAdmin permission store.

**Status:** accepted

**Completion:** implemented / accepted. F05 PC admin foundation gates have passed.

---

## Scope Check

Included:

- Create shared Foundation frontend types and a permission composable for education PC pages.
- Consolidate `admin-web/src/router/modules/education.ts` with a stable education menu tree.
- Standardize typed API clients for F01 tenant/campus, F02 user profile/campus scope, F03 dictionary/feature flag, and F04 audit log.
- Standardize list pages, form components, drawers, button permissions, loading/empty/error states, submit states, status flows, delete confirmations, and read-only audit behavior.
- Add aggregate route, API client, permission, and page state tests for the Foundation PC admin surface.
- Run frontend lint, unit tests, production build, and a mobile H5 regression build to prove PC changes do not break the workspace.

Excluded:

- Backend Controller, Request, Service, Repository, Model, Schema, migration, seeder, and backend tests; F01-F04 own backend behavior.
- Teacher and guardian mobile pages; F06 owns mobile context and entry pages.
- New product functionality beyond Foundation PC admin integration.
- UI redesign outside Foundation pages.

Dependencies:

```text
F01 Tenant Campus ready
F02 User Profile Role Scope ready
F03 Dictionary Feature Flag ready
F04 Audit Log ready
```

## File Structure

Create PC shared files:

```text
mineadmin-education-saas/admin-web/src/api/education/foundation/types.ts
mineadmin-education-saas/admin-web/src/composables/education/useEducationPermission.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/EducationFoundationRoutes.spec.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/EducationFoundationApiClients.spec.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/EducationFoundationPermission.spec.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/EducationFoundationPageStates.spec.ts
```

Modify PC API clients:

```text
mineadmin-education-saas/admin-web/src/api/education/foundation/tenant.ts
mineadmin-education-saas/admin-web/src/api/education/foundation/campus.ts
mineadmin-education-saas/admin-web/src/api/education/foundation/userProfile.ts
mineadmin-education-saas/admin-web/src/api/education/foundation/dictionary.ts
mineadmin-education-saas/admin-web/src/api/education/foundation/featureFlag.ts
mineadmin-education-saas/admin-web/src/api/education/foundation/auditLog.ts
```

Modify PC route:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Modify PC pages:

```text
mineadmin-education-saas/admin-web/src/views/education/foundation/TenantList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/CampusList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/UserProfileList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/DictionaryList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/FeatureFlagList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/AuditLogList.vue
```

Modify PC components:

```text
mineadmin-education-saas/admin-web/src/views/education/foundation/components/TenantForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/CampusForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/UserProfileForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/CampusScopeForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/DictTypeForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/DictItemForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/FeatureFlagForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/AuditPayloadDrawer.vue
```

Modify or create focused page tests:

```text
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/TenantList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/CampusList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/UserProfileList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/CampusScopeForm.spec.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/DictionaryList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/FeatureFlagList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/AuditLogList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/AuditPayloadDrawer.spec.ts
```

Verify mobile:

```text
mineadmin-education-saas/mobile-uniapp/package.json
```

## Database Migration Design

F05 creates no database migration because it is a PC admin integration plan.

Verification command:

```bash
cd mineadmin-education-saas
find backend/databases/migrations -maxdepth 1 -name '*000500*pc*' -o -name '*f05*'
```

Expected:

```text
No output.
```

Database ownership:

```text
F01 owns edu_tenants and edu_campuses.
F02 owns edu_user_profiles and edu_user_campus_scopes.
F03 owns edu_dict_types, edu_dict_items, and edu_feature_flags.
F04 owns edu_audit_logs.
```

Rollback behavior:

```text
No database rollback exists for F05. Reverting F05 means reverting admin-web route, API client, page, component, and test changes.
```

## MineAdmin Backend Module Design

F05 creates no backend Controller, Request, Service, Repository, Model, Schema, middleware, seeder, event, listener, or migration.

Backend contract source:

```text
F01 provides tenant and campus admin APIs.
F02 provides user profile and campus scope admin APIs.
F03 provides dictionary and feature flag admin APIs.
F04 provides audit log admin APIs.
```

Backend verification command:

```bash
cd mineadmin-education-saas
find backend/app backend/databases -path '*F05*' -o -path '*PcAdminFoundation*'
```

Expected:

```text
No output.
```

MineAdmin compatibility rule:

```text
All F05 frontend requests must keep MineAdmin result envelope handling:
{ "code": 200, "message": "success", "data": ... }
Permission checks must use MineAdmin user permissions and route meta auth arrays.
```

## API Contract

### Shared Result Types

Create:

```text
mineadmin-education-saas/admin-web/src/api/education/foundation/types.ts
```

Types:

```ts
export type FoundationStatus = 'enabled' | 'disabled'

export interface MinePage<T> {
  list: T[]
  total: number
}

export interface MineResult<T> {
  code: number
  message: string
  data: T
}

export interface PageParams {
  page: number
  pageSize: number
}

export interface ApiFailure {
  code: number
  message: string
  data?: Record<string, unknown>
}
```

Client error handling rule:

```text
Validation failure code 422: keep the current form or filters open and display field message.
Business failure code 403, 404, or 409: keep current page state, display message, and do not mutate local rows.
Network failure: show generic request failure message and keep the last successful rows.
```

### Tenant Client

File:

```text
mineadmin-education-saas/admin-web/src/api/education/foundation/tenant.ts
```

Methods:

```ts
pageTenants(params: TenantPageParams): Promise<MineResult<MinePage<TenantListItem>>>
createTenant(data: TenantSavePayload): Promise<MineResult<TenantDetail>>
updateTenant(id: number, data: TenantSavePayload): Promise<MineResult<TenantDetail>>
updateTenantStatus(id: number, status: FoundationStatus): Promise<MineResult<TenantDetail>>
deleteTenant(id: number): Promise<MineResult<true>>
```

Endpoint matrix:

| Method | Endpoint | Permission | Success data |
| --- | --- | --- | --- |
| GET | `/admin/education/foundation/tenants/page` | `education:foundation:tenant:page` | `{ list, total }` |
| POST | `/admin/education/foundation/tenants` | `education:foundation:tenant:create` | tenant detail |
| PUT | `/admin/education/foundation/tenants/{id}` | `education:foundation:tenant:update` | tenant detail |
| PUT | `/admin/education/foundation/tenants/{id}/status` | `education:foundation:tenant:status` | tenant detail |
| DELETE | `/admin/education/foundation/tenants/{id}` | `education:foundation:tenant:delete` | `true` |

Example request:

```json
{
  "name": "Demo Education",
  "code": "demo",
  "short_name": "Demo",
  "contact_name": "Admin",
  "contact_mobile": "13800000000",
  "status": "enabled"
}
```

Example success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1001,
    "name": "Demo Education",
    "code": "demo",
    "status": "enabled"
  }
}
```

Example validation failure:

```json
{
  "code": 422,
  "message": "code is required",
  "data": {
    "field": "code"
  }
}
```

Example business failure:

```json
{
  "code": 409,
  "message": "tenant code already exists",
  "data": {
    "code": "demo"
  }
}
```

### Campus Client

File:

```text
mineadmin-education-saas/admin-web/src/api/education/foundation/campus.ts
```

Methods:

```ts
pageCampuses(params: CampusPageParams): Promise<MineResult<MinePage<CampusListItem>>>
createCampus(data: CampusSavePayload): Promise<MineResult<CampusDetail>>
updateCampus(id: number, data: CampusSavePayload): Promise<MineResult<CampusDetail>>
updateCampusStatus(id: number, status: FoundationStatus): Promise<MineResult<CampusDetail>>
deleteCampus(id: number): Promise<MineResult<true>>
```

Endpoint matrix:

| Method | Endpoint | Permission | Success data |
| --- | --- | --- | --- |
| GET | `/admin/education/foundation/campuses/page` | `education:foundation:campus:page` | `{ list, total }` |
| POST | `/admin/education/foundation/campuses` | `education:foundation:campus:create` | campus detail |
| PUT | `/admin/education/foundation/campuses/{id}` | `education:foundation:campus:update` | campus detail |
| PUT | `/admin/education/foundation/campuses/{id}/status` | `education:foundation:campus:status` | campus detail |
| DELETE | `/admin/education/foundation/campuses/{id}` | `education:foundation:campus:delete` | `true` |

Headers:

```text
X-Tenant-Id: current tenant id for tenant-scoped users
```

Business failure example:

```json
{
  "code": 403,
  "message": "campus is outside current tenant",
  "data": {
    "campus_id": 2002
  }
}
```

### User Profile Client

File:

```text
mineadmin-education-saas/admin-web/src/api/education/foundation/userProfile.ts
```

Methods:

```ts
pageUserProfiles(params: UserProfilePageParams): Promise<MineResult<MinePage<UserProfileListItem>>>
createUserProfile(data: UserProfileSavePayload): Promise<MineResult<UserProfileDetail>>
updateUserProfile(id: number, data: UserProfileSavePayload): Promise<MineResult<UserProfileDetail>>
updateUserProfileStatus(id: number, status: FoundationStatus): Promise<MineResult<UserProfileDetail>>
getCampusScopes(id: number): Promise<MineResult<{ campus_ids: number[] }>>
saveCampusScopes(id: number, campus_ids: number[]): Promise<MineResult<{ campus_ids: number[] }>>
```

Endpoint matrix:

| Method | Endpoint | Permission | Success data |
| --- | --- | --- | --- |
| GET | `/admin/education/foundation/user-profiles/page` | `education:foundation:user-profile:page` | `{ list, total }` |
| POST | `/admin/education/foundation/user-profiles` | `education:foundation:user-profile:create` | profile detail |
| PUT | `/admin/education/foundation/user-profiles/{id}` | `education:foundation:user-profile:update` | profile detail |
| PUT | `/admin/education/foundation/user-profiles/{id}/status` | `education:foundation:user-profile:status` | profile detail |
| GET | `/admin/education/foundation/user-profiles/{id}/campus-scopes` | `education:foundation:campus-scope:page` | `{ campus_ids }` |
| PUT | `/admin/education/foundation/user-profiles/{id}/campus-scopes` | `education:foundation:campus-scope:save` | `{ campus_ids }` |

Validation failure example:

```json
{
  "code": 422,
  "message": "tenant_id is required for tenant role",
  "data": {
    "field": "tenant_id"
  }
}
```

Business failure example:

```json
{
  "code": 409,
  "message": "user profile already exists",
  "data": {
    "profile_key": "tenant:1001:501"
  }
}
```

### Dictionary Client

File:

```text
mineadmin-education-saas/admin-web/src/api/education/foundation/dictionary.ts
```

Methods:

```ts
pageDictTypes(params: DictTypePageParams): Promise<MineResult<MinePage<DictTypeListItem>>>
createDictType(data: DictTypeSavePayload): Promise<MineResult<DictTypeDetail>>
updateDictType(id: number, data: DictTypeSavePayload): Promise<MineResult<DictTypeDetail>>
updateDictTypeStatus(id: number, status: FoundationStatus): Promise<MineResult<DictTypeDetail>>
deleteDictType(id: number): Promise<MineResult<true>>
pageDictItems(params: DictItemPageParams): Promise<MineResult<MinePage<DictItemListItem>>>
createDictItem(data: DictItemSavePayload): Promise<MineResult<DictItemDetail>>
updateDictItem(id: number, data: DictItemSavePayload): Promise<MineResult<DictItemDetail>>
updateDictItemStatus(id: number, status: FoundationStatus): Promise<MineResult<DictItemDetail>>
deleteDictItem(id: number): Promise<MineResult<true>>
lookupDictItems(code: string): Promise<MineResult<DictOption[]>>
```

Endpoint matrix:

| Method | Endpoint | Permission | Success data |
| --- | --- | --- | --- |
| GET | `/admin/education/foundation/dict-types/page` | `education:foundation:dictionary:page` | `{ list, total }` |
| POST | `/admin/education/foundation/dict-types` | `education:foundation:dictionary:create` | dictionary type detail |
| PUT | `/admin/education/foundation/dict-types/{id}` | `education:foundation:dictionary:update` | dictionary type detail |
| PUT | `/admin/education/foundation/dict-types/{id}/status` | `education:foundation:dictionary:status` | dictionary type detail |
| DELETE | `/admin/education/foundation/dict-types/{id}` | `education:foundation:dictionary:delete` | `true` |
| GET | `/admin/education/foundation/dict-items/page` | `education:foundation:dictionary-item:page` | `{ list, total }` |
| POST | `/admin/education/foundation/dict-items` | `education:foundation:dictionary-item:create` | dictionary item detail |
| PUT | `/admin/education/foundation/dict-items/{id}` | `education:foundation:dictionary-item:update` | dictionary item detail |
| PUT | `/admin/education/foundation/dict-items/{id}/status` | `education:foundation:dictionary-item:status` | dictionary item detail |
| DELETE | `/admin/education/foundation/dict-items/{id}` | `education:foundation:dictionary-item:delete` | `true` |
| GET | `/admin/education/foundation/dictionaries/{code}/items` | `education:foundation:dictionary-item:lookup` | option list |

Business failure example:

```json
{
  "code": 403,
  "message": "locked system dictionary cannot be modified by tenant",
  "data": {
    "dict_type_id": 1
  }
}
```

### Feature Flag Client

File:

```text
mineadmin-education-saas/admin-web/src/api/education/foundation/featureFlag.ts
```

Methods:

```ts
pageFeatureFlags(params: FeatureFlagPageParams): Promise<MineResult<MinePage<FeatureFlagListItem>>>
createFeatureFlag(data: FeatureFlagSavePayload): Promise<MineResult<FeatureFlagDetail>>
updateFeatureFlag(id: number, data: FeatureFlagSavePayload): Promise<MineResult<FeatureFlagDetail>>
updateFeatureFlagStatus(id: number, status: FoundationStatus): Promise<MineResult<FeatureFlagDetail>>
deleteFeatureFlag(id: number): Promise<MineResult<true>>
resolveFeatureFlag(featureCode: string): Promise<MineResult<{ enabled: boolean; config: Record<string, unknown> }>>
```

Endpoint matrix:

| Method | Endpoint | Permission | Success data |
| --- | --- | --- | --- |
| GET | `/admin/education/foundation/feature-flags/page` | `education:foundation:feature-flag:page` | `{ list, total }` |
| POST | `/admin/education/foundation/feature-flags` | `education:foundation:feature-flag:create` | feature flag detail |
| PUT | `/admin/education/foundation/feature-flags/{id}` | `education:foundation:feature-flag:update` | feature flag detail |
| PUT | `/admin/education/foundation/feature-flags/{id}/status` | `education:foundation:feature-flag:status` | feature flag detail |
| DELETE | `/admin/education/foundation/feature-flags/{id}` | `education:foundation:feature-flag:delete` | `true` |
| GET | `/admin/education/foundation/feature-flags/{featureCode}/resolved` | `education:foundation:feature-flag:lookup` | resolved flag |

Validation failure example:

```json
{
  "code": 422,
  "message": "effective_to must be greater than or equal to effective_from",
  "data": {
    "field": "effective_to"
  }
}
```

### Audit Log Client

File:

```text
mineadmin-education-saas/admin-web/src/api/education/foundation/auditLog.ts
```

Methods:

```ts
pageAuditLogs(params: AuditLogPageParams): Promise<MineResult<MinePage<AuditLogListItem>>>
getAuditLogDetail(id: number): Promise<MineResult<AuditLogDetail>>
```

Endpoint matrix:

| Method | Endpoint | Permission | Success data |
| --- | --- | --- | --- |
| GET | `/admin/education/foundation/audit-logs/page` | `education:foundation:audit-log:page` | `{ list, total }` |
| GET | `/admin/education/foundation/audit-logs/{id}` | `education:foundation:audit-log:detail` | audit log detail |

Business failure example:

```json
{
  "code": 404,
  "message": "audit log not found",
  "data": {
    "id": 9001
  }
}
```

## PC Admin Page Tasks

### Permission Helper

Create:

```text
mineadmin-education-saas/admin-web/src/composables/education/useEducationPermission.ts
```

Exports:

```ts
export type EducationPermissionCode =
  | 'education:foundation:tenant:page'
  | 'education:foundation:tenant:create'
  | 'education:foundation:tenant:update'
  | 'education:foundation:tenant:status'
  | 'education:foundation:tenant:delete'
  | 'education:foundation:campus:page'
  | 'education:foundation:campus:create'
  | 'education:foundation:campus:update'
  | 'education:foundation:campus:status'
  | 'education:foundation:campus:delete'
  | 'education:foundation:user-profile:page'
  | 'education:foundation:user-profile:create'
  | 'education:foundation:user-profile:update'
  | 'education:foundation:user-profile:status'
  | 'education:foundation:campus-scope:page'
  | 'education:foundation:campus-scope:save'
  | 'education:foundation:dictionary:page'
  | 'education:foundation:dictionary:create'
  | 'education:foundation:dictionary:update'
  | 'education:foundation:dictionary:status'
  | 'education:foundation:dictionary:delete'
  | 'education:foundation:dictionary-item:page'
  | 'education:foundation:dictionary-item:create'
  | 'education:foundation:dictionary-item:update'
  | 'education:foundation:dictionary-item:status'
  | 'education:foundation:dictionary-item:delete'
  | 'education:foundation:dictionary-item:lookup'
  | 'education:foundation:feature-flag:page'
  | 'education:foundation:feature-flag:create'
  | 'education:foundation:feature-flag:update'
  | 'education:foundation:feature-flag:status'
  | 'education:foundation:feature-flag:delete'
  | 'education:foundation:feature-flag:lookup'
  | 'education:foundation:audit-log:page'
  | 'education:foundation:audit-log:detail'

export function useEducationPermission(): {
  has: (code: EducationPermissionCode) => boolean
  hasAny: (codes: EducationPermissionCode[]) => boolean
}
```

Implementation rule:

```text
Read MineAdmin current user permission codes from the generated user store.
Treat `*` and `education:*` as super permissions when MineAdmin exposes wildcard permission codes.
Do not hard-code tenant role names in the frontend permission helper.
```

### Route and Menu Tree

Modify:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Route tree:

```text
/education
  /education/foundation/tenants
  /education/foundation/campuses
  /education/foundation/user-profiles
  /education/foundation/dictionaries
  /education/foundation/feature-flags
  /education/foundation/audit-logs
```

Route meta:

| Route name | Path | Component | Auth |
| --- | --- | --- | --- |
| `EducationRoot` | `/education` | layout | `education:*` |
| `EducationFoundationTenantList` | `/education/foundation/tenants` | `TenantList.vue` | `education:foundation:tenant:page` |
| `EducationFoundationCampusList` | `/education/foundation/campuses` | `CampusList.vue` | `education:foundation:campus:page` |
| `EducationFoundationUserProfileList` | `/education/foundation/user-profiles` | `UserProfileList.vue` | `education:foundation:user-profile:page` |
| `EducationFoundationDictionaryList` | `/education/foundation/dictionaries` | `DictionaryList.vue` | `education:foundation:dictionary:page` |
| `EducationFoundationFeatureFlagList` | `/education/foundation/feature-flags` | `FeatureFlagList.vue` | `education:foundation:feature-flag:page` |
| `EducationFoundationAuditLogList` | `/education/foundation/audit-logs` | `AuditLogList.vue` | `education:foundation:audit-log:page` |

Menu labels:

```text
EducationRoot: 教务 SaaS
Foundation group: 基础设置
Tenant: 机构管理
Campus: 校区管理
User profile: 人员权限
Dictionary: 字典配置
Feature flag: 功能开关
Audit log: 审计日志
```

### Shared Page State Contract

Every Foundation list page must implement:

```text
loading: true while the page API promise is pending.
empty: rendered when loading is false and total equals 0.
error: message shown from API failure; filters remain unchanged.
success: rows and pagination update from response data.
pagination: page and pageSize are sent to API and updated from user actions.
search: resets page to 1 before loading.
reset: clears filters, restores default filter values, and reloads page 1.
permission: action buttons render only when permission helper returns true.
```

Every Foundation form or drawer must implement:

```text
visible: parent controls open/close.
mode: create or edit for forms; view for audit drawer.
initial load: edit forms load selected row data from parent row or detail API.
submitting: true while create/update/status/delete/save request is pending.
validation error: form remains open and field error is shown.
business error: form remains open and message is shown.
success: form closes and parent list reloads.
```

### Tenant Page

Modify:

```text
mineadmin-education-saas/admin-web/src/views/education/foundation/TenantList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/TenantForm.vue
```

Search fields:

```text
keyword: name, code, short_name, contact_name, contact_mobile
status: enabled or disabled
```

Columns:

```text
name, code, short_name, contact_name, contact_mobile, status, created_at, updated_at, actions
```

Actions:

| Action | Permission | Flow |
| --- | --- | --- |
| create | `education:foundation:tenant:create` | open create form, submit `createTenant`, reload |
| edit | `education:foundation:tenant:update` | open edit form, submit `updateTenant`, reload |
| enable/disable | `education:foundation:tenant:status` | confirm, call `updateTenantStatus`, reload |
| delete | `education:foundation:tenant:delete` | confirm, call `deleteTenant`, reload |

Form fields:

```text
name required max 120
code required max 64 disabled in edit mode
short_name optional max 60
contact_name optional max 60
contact_mobile optional max 30
status enabled/disabled
```

### Campus Page

Modify:

```text
mineadmin-education-saas/admin-web/src/views/education/foundation/CampusList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/CampusForm.vue
```

Search fields:

```text
tenant_id: visible for platform users
keyword: name, code, address
status: enabled or disabled
```

Columns:

```text
name, code, tenant_id, address, contact_name, contact_mobile, status, created_at, updated_at, actions
```

Actions:

| Action | Permission | Flow |
| --- | --- | --- |
| create | `education:foundation:campus:create` | open create form, submit `createCampus`, reload |
| edit | `education:foundation:campus:update` | open edit form, submit `updateCampus`, reload |
| enable/disable | `education:foundation:campus:status` | confirm, call `updateCampusStatus`, reload |
| delete | `education:foundation:campus:delete` | confirm, call `deleteCampus`, reload |

Form fields:

```text
tenant_id required for platform users and hidden for tenant users
name required max 120
code required max 64 disabled in edit mode
address optional max 255
contact_name optional max 60
contact_mobile optional max 30
status enabled/disabled
```

### User Profile Page

Modify:

```text
mineadmin-education-saas/admin-web/src/views/education/foundation/UserProfileList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/UserProfileForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/CampusScopeForm.vue
```

Search fields:

```text
tenant_id: visible for platform users
keyword: display_name, mobile, openid, unionid
role_code: platform_super_admin, platform_operator, tenant_admin, principal, academic_staff, front_desk, teacher, finance, guardian
status: enabled or disabled
```

Columns:

```text
display_name, mobile, role_code, tenant_id, current_campus_id, campus_scope_count, status, updated_at, actions
```

Actions:

| Action | Permission | Flow |
| --- | --- | --- |
| create | `education:foundation:user-profile:create` | open create form, submit `createUserProfile`, reload |
| edit | `education:foundation:user-profile:update` | open edit form, submit `updateUserProfile`, reload |
| enable/disable | `education:foundation:user-profile:status` | confirm, call `updateUserProfileStatus`, reload |
| campus scope | `education:foundation:campus-scope:save` | load `getCampusScopes`, submit `saveCampusScopes`, reload |

Form fields:

```text
tenant_id required for tenant roles and empty for platform roles
user_id required MineAdmin user selector
role_code required select
display_name required max 80
mobile optional max 30
avatar optional max 255
openid optional max 80
unionid optional max 80
current_campus_id optional campus selector
status enabled/disabled
```

### Dictionary Page

Modify:

```text
mineadmin-education-saas/admin-web/src/views/education/foundation/DictionaryList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/DictTypeForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/DictItemForm.vue
```

Layout:

```text
Left pane: dictionary types.
Right pane: dictionary items for selected type.
Selecting a dictionary type loads dict items page 1.
```

Dictionary type filters:

```text
owner_type: system or tenant
keyword: code, name
status: enabled or disabled
```

Dictionary item filters:

```text
keyword: label, value
status: enabled or disabled
```

Actions:

| Action | Permission | Flow |
| --- | --- | --- |
| create type | `education:foundation:dictionary:create` | open type form, submit `createDictType`, reload type list |
| edit type | `education:foundation:dictionary:update` | open type form, submit `updateDictType`, reload type list |
| type status | `education:foundation:dictionary:status` | confirm, call `updateDictTypeStatus`, reload |
| delete type | `education:foundation:dictionary:delete` | confirm, call `deleteDictType`, reload |
| create item | `education:foundation:dictionary-item:create` | open item form, submit `createDictItem`, reload item list |
| edit item | `education:foundation:dictionary-item:update` | open item form, submit `updateDictItem`, reload item list |
| item status | `education:foundation:dictionary-item:status` | confirm, call `updateDictItemStatus`, reload |
| delete item | `education:foundation:dictionary-item:delete` | confirm, call `deleteDictItem`, reload |

Locked row rule:

```text
Rows with is_locked true show no edit, status, or delete buttons unless the current user has platform-level dictionary permissions.
```

### Feature Flag Page

Modify:

```text
mineadmin-education-saas/admin-web/src/views/education/foundation/FeatureFlagList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/FeatureFlagForm.vue
```

Filters:

```text
owner_type: system or tenant
keyword: feature_code, feature_name
enabled: true or false
status: enabled or disabled
effective date range
```

Columns:

```text
feature_code, feature_name, owner_type, tenant_id, enabled, status, effective_from, effective_to, updated_at, actions
```

Actions:

| Action | Permission | Flow |
| --- | --- | --- |
| create | `education:foundation:feature-flag:create` | open form, submit `createFeatureFlag`, reload |
| edit | `education:foundation:feature-flag:update` | open form, submit `updateFeatureFlag`, reload |
| status | `education:foundation:feature-flag:status` | confirm, call `updateFeatureFlagStatus`, reload |
| delete | `education:foundation:feature-flag:delete` | confirm, call `deleteFeatureFlag`, reload |

Form fields:

```text
owner_type required system or tenant
tenant_id required when owner_type is tenant
feature_code required max 120 disabled in edit mode
feature_name required max 120
description optional max 255
enabled boolean
config JSON editor
effective_from optional datetime
effective_to optional datetime
status enabled/disabled
```

### Audit Log Page

Modify:

```text
mineadmin-education-saas/admin-web/src/views/education/foundation/AuditLogList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/AuditPayloadDrawer.vue
```

Filters:

```text
keyword: action, summary, business_type, business_id, request_id
module: foundation default
resource: tenant, campus, user_profile, campus_scope, dict_type, dict_item, feature_flag
action exact action code
business_type exact value
business_id exact value
actor_type admin, teacher, guardian, system
actor_user_id numeric
date range mapped to start_at and end_at
```

Columns:

```text
created_at, module, resource, action, business_type, business_id, actor_type, actor_user_id, campus_id, ip_address, summary, actions
```

Actions:

| Action | Permission | Flow |
| --- | --- | --- |
| view payload | `education:foundation:audit-log:detail` | call `getAuditLogDetail`, open drawer |

Read-only rule:

```text
AuditLogList renders no create, edit, enable, disable, delete, import, or export button.
```

## Teacher / Guardian Mobile Page Tasks

This module has no teacher or guardian page because F05 only integrates the MineAdmin PC admin frontend.

Mobile impact:

```text
No mobile route is added.
No mobile API client is added.
No teacher navigation item is added.
No guardian navigation item is added.
```

Mobile regression verification:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm build:h5
```

Expected:

```text
Mobile H5 build passes.
```

## Test Plan

Shared tests:

| Test file | Case | Assertion |
| --- | --- | --- |
| `EducationFoundationRoutes.spec.ts` | `registers_foundation_route_tree` | route names, paths, components, and auth meta match route matrix |
| `EducationFoundationRoutes.spec.ts` | `has_no_duplicate_route_names` | education route names are unique |
| `EducationFoundationApiClients.spec.ts` | `tenant_client_uses_expected_endpoints` | tenant methods call documented URLs and methods |
| `EducationFoundationApiClients.spec.ts` | `campus_client_uses_expected_endpoints` | campus methods call documented URLs and methods |
| `EducationFoundationApiClients.spec.ts` | `user_profile_client_uses_expected_endpoints` | profile and campus scope methods call documented URLs and methods |
| `EducationFoundationApiClients.spec.ts` | `dictionary_client_uses_expected_endpoints` | dictionary type, item, and lookup methods call documented URLs and methods |
| `EducationFoundationApiClients.spec.ts` | `feature_flag_client_uses_expected_endpoints` | feature flag page/write/lookup methods call documented URLs and methods |
| `EducationFoundationApiClients.spec.ts` | `audit_log_client_uses_expected_endpoints` | audit page/detail methods call documented URLs and methods |
| `EducationFoundationPermission.spec.ts` | `has_supports_exact_and_wildcard_permissions` | exact code, `education:*`, and `*` return true |
| `EducationFoundationPermission.spec.ts` | `has_returns_false_for_missing_permission` | missing permission returns false |
| `EducationFoundationPageStates.spec.ts` | `list_pages_keep_filters_after_error` | failed API call does not clear filters |
| `EducationFoundationPageStates.spec.ts` | `forms_disable_submit_while_pending` | submit buttons are disabled while request promise is pending |

Page tests:

| Test file | Case | Assertion |
| --- | --- | --- |
| `TenantList.spec.ts` | `loads_tenants_on_mount` | `pageTenants` called with page 1 and default pageSize |
| `TenantList.spec.ts` | `hides_create_without_permission` | create button is not rendered |
| `TenantList.spec.ts` | `status_flow_calls_updateTenantStatus` | confirm status action calls API and reloads |
| `CampusList.spec.ts` | `passes_tenant_filter_for_platform_user` | tenant_id filter is sent to `pageCampuses` |
| `CampusList.spec.ts` | `hides_delete_without_permission` | delete button is not rendered |
| `UserProfileList.spec.ts` | `campus_scope_button_requires_permission` | scope action is hidden without save permission |
| `UserProfileList.spec.ts` | `status_flow_calls_updateUserProfileStatus` | status API called and list reloads |
| `CampusScopeForm.spec.ts` | `loads_scope_before_open` | `getCampusScopes` called with profile id |
| `CampusScopeForm.spec.ts` | `save_submits_selected_campus_ids` | `saveCampusScopes` receives selected ids |
| `DictionaryList.spec.ts` | `selecting_type_loads_items` | item list reloads when selected type changes |
| `DictionaryList.spec.ts` | `locked_rows_hide_mutation_buttons` | locked dictionary rows hide edit/status/delete |
| `FeatureFlagList.spec.ts` | `search_maps_date_range_to_params` | effective dates are sent to API params |
| `FeatureFlagList.spec.ts` | `config_validation_failure_keeps_form_open` | form remains visible on 422 |
| `AuditLogList.spec.ts` | `loads_with_foundation_default_module` | `pageAuditLogs` receives module foundation |
| `AuditLogList.spec.ts` | `renders_no_write_buttons` | create/edit/status/delete/import/export buttons do not exist |
| `AuditPayloadDrawer.spec.ts` | `renders_payload_json_sections` | before, after, diff, and metadata sections render |

Verification tests:

| Command | Assertion |
| --- | --- |
| `pnpm lint` | admin-web lint passes |
| `pnpm test -- EducationFoundation` | shared route, client, permission, and page state tests pass |
| `pnpm test -- TenantList CampusList UserProfileList DictionaryList FeatureFlagList AuditLogList` | focused page tests pass |
| `pnpm build` | admin-web production build passes |
| `pnpm build:h5` in mobile workspace | mobile build remains green |

## Execution Commands

### PC Dependency Gate

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm install
```

Expected:

```text
Dependencies are installed and lockfile remains consistent.
```

### PC Type and Route Gate

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm test -- EducationFoundationRoutes
pnpm test -- EducationFoundationApiClients
pnpm test -- EducationFoundationPermission
```

Expected:

```text
Route tree, API client endpoint, and permission helper tests pass.
```

### PC Page State Gate

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm test -- EducationFoundationPageStates
pnpm test -- TenantList
pnpm test -- CampusList
pnpm test -- UserProfileList
pnpm test -- CampusScopeForm
pnpm test -- DictionaryList
pnpm test -- FeatureFlagList
pnpm test -- AuditLogList
pnpm test -- AuditPayloadDrawer
```

Expected:

```text
All Foundation page, form, drawer, state, and permission tests pass.
```

### PC Final Gate

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- EducationFoundation
pnpm build
```

Expected:

```text
Lint passes.
Shared Foundation PC tests pass.
Production build succeeds.
```

### Backend No-Change Gate

Run:

```bash
cd mineadmin-education-saas
find backend/app backend/databases -path '*F05*' -o -path '*PcAdminFoundation*'
```

Expected:

```text
No output.
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

### F05 Final Gate

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- EducationFoundation
pnpm test -- TenantList
pnpm test -- CampusList
pnpm test -- UserProfileList
pnpm test -- DictionaryList
pnpm test -- FeatureFlagList
pnpm test -- AuditLogList
pnpm build
cd ../mobile-uniapp
pnpm build:h5
```

Expected:

```text
Admin-web lint passes.
All Foundation PC shared and page tests pass.
Admin-web production build succeeds.
Mobile H5 build succeeds.
```

## Acceptance Gate

F05 is accepted only when all conditions are true:

```text
- Education route module contains the full Foundation route tree with stable route names and auth meta.
- Tenant, campus, user profile, dictionary, feature flag, and audit log API clients expose the documented typed methods.
- Every Foundation page implements loading, empty, error, success, search, reset, pagination, and permission states.
- Every Foundation form or drawer implements visible, mode, submitting, validation error, business error, and success behavior.
- All mutation buttons use exact education permission codes.
- Audit log page remains read-only and renders no write buttons.
- Locked dictionary and feature flag rows hide mutation buttons unless platform-level permission allows mutation.
- PC route, API client, permission helper, page state, page component, and drawer tests pass.
- `pnpm lint`, `pnpm test`, and `pnpm build` pass in admin-web.
- F05 creates no backend migration or backend module file.
- Mobile H5 build passes with no F05 mobile route changes.
```

## Task Breakdown

### Task 1: Create Shared Foundation Frontend Types and Permission Helper

**Files:**

- Create: `mineadmin-education-saas/admin-web/src/api/education/foundation/types.ts`
- Create: `mineadmin-education-saas/admin-web/src/composables/education/useEducationPermission.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/EducationFoundationPermission.spec.ts`

- [x] **Step 1: Create shared API types**

Implement `FoundationStatus`, `MinePage`, `MineResult`, `PageParams`, and `ApiFailure` from `API Contract`.

- [x] **Step 2: Create permission helper**

Implement `EducationPermissionCode`, `has`, and `hasAny` from `PC Admin Page Tasks`.

- [x] **Step 3: Write permission tests**

Create exact and wildcard permission tests from `Test Plan`.

- [x] **Step 4: Run permission test**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm test -- EducationFoundationPermission
```

Expected:

```text
Permission helper tests pass.
```

### Task 2: Consolidate Route and Menu Module

**Files:**

- Modify: `mineadmin-education-saas/admin-web/src/router/modules/education.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/EducationFoundationRoutes.spec.ts`

- [x] **Step 1: Add route tree**

Implement the route paths, names, components, auth meta, and menu labels from `Route and Menu Tree`.

- [x] **Step 2: Write route tests**

Create route registration and duplicate route name tests from `Test Plan`.

- [x] **Step 3: Run route tests**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm test -- EducationFoundationRoutes
```

Expected:

```text
Education Foundation route tests pass.
```

### Task 3: Standardize API Clients

**Files:**

- Modify: `mineadmin-education-saas/admin-web/src/api/education/foundation/tenant.ts`
- Modify: `mineadmin-education-saas/admin-web/src/api/education/foundation/campus.ts`
- Modify: `mineadmin-education-saas/admin-web/src/api/education/foundation/userProfile.ts`
- Modify: `mineadmin-education-saas/admin-web/src/api/education/foundation/dictionary.ts`
- Modify: `mineadmin-education-saas/admin-web/src/api/education/foundation/featureFlag.ts`
- Modify: `mineadmin-education-saas/admin-web/src/api/education/foundation/auditLog.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/EducationFoundationApiClients.spec.ts`

- [x] **Step 1: Import shared types**

Update all Foundation API clients to use `MineResult`, `MinePage`, `PageParams`, and `FoundationStatus`.

- [x] **Step 2: Implement documented client methods**

Ensure every method listed in `API Contract` exists and calls the documented method and URL.

- [x] **Step 3: Standardize error propagation**

Return request promises without swallowing API errors so page components can apply the shared error state contract.

- [x] **Step 4: Write API client endpoint tests**

Create endpoint tests for tenant, campus, user profile, dictionary, feature flag, and audit log clients.

- [x] **Step 5: Run API client tests**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm test -- EducationFoundationApiClients
```

Expected:

```text
Foundation API client endpoint tests pass.
```

### Task 4: Standardize Tenant, Campus, and User Profile Pages

**Files:**

- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/TenantList.vue`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/CampusList.vue`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/UserProfileList.vue`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/TenantForm.vue`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/CampusForm.vue`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/UserProfileForm.vue`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/CampusScopeForm.vue`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/TenantList.spec.ts`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/CampusList.spec.ts`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/UserProfileList.spec.ts`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/CampusScopeForm.spec.ts`

- [x] **Step 1: Apply shared list state contract**

Update tenant, campus, and user profile list pages to implement loading, empty, error, success, pagination, search, reset, and permission states.

- [x] **Step 2: Apply form state contract**

Update tenant, campus, user profile, and campus scope forms to implement visible, mode, submitting, validation error, business error, and success behavior.

- [x] **Step 3: Wire exact permission codes**

Apply action permission codes from Tenant Page, Campus Page, and User Profile Page.

- [x] **Step 4: Update focused tests**

Update tenant, campus, user profile, and campus scope tests from `Test Plan`.

- [x] **Step 5: Run page tests**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm test -- TenantList
pnpm test -- CampusList
pnpm test -- UserProfileList
pnpm test -- CampusScopeForm
```

Expected:

```text
Tenant, campus, user profile, and campus scope PC tests pass.
```

### Task 5: Standardize Dictionary, Feature Flag, and Audit Pages

**Files:**

- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/DictionaryList.vue`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/FeatureFlagList.vue`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/AuditLogList.vue`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/DictTypeForm.vue`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/DictItemForm.vue`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/FeatureFlagForm.vue`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/AuditPayloadDrawer.vue`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/DictionaryList.spec.ts`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/FeatureFlagList.spec.ts`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/AuditLogList.spec.ts`
- Modify: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/AuditPayloadDrawer.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/EducationFoundationPageStates.spec.ts`

- [x] **Step 1: Apply shared list state contract**

Update dictionary, feature flag, and audit log list pages to implement loading, empty, error, success, pagination, search, reset, and permission states.

- [x] **Step 2: Apply form and drawer state contract**

Update dictionary forms, feature flag form, and audit payload drawer to implement the documented component state contract.

- [x] **Step 3: Wire exact permission codes**

Apply action permission codes from Dictionary Page, Feature Flag Page, and Audit Log Page.

- [x] **Step 4: Enforce read-only audit behavior**

Remove audit log write actions from UI and tests.

- [x] **Step 5: Update focused tests and shared page state tests**

Create or update tests listed in `Test Plan`.

- [x] **Step 6: Run page tests**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm test -- DictionaryList
pnpm test -- FeatureFlagList
pnpm test -- AuditLogList
pnpm test -- AuditPayloadDrawer
pnpm test -- EducationFoundationPageStates
```

Expected:

```text
Dictionary, feature flag, audit log, payload drawer, and page state tests pass.
```

### Task 6: Run F05 Final Gate

**Files:**

- Verify: all F05 PC and mobile paths listed in `File Structure`.

- [x] **Step 1: Run PC final gate**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- EducationFoundation
pnpm test -- TenantList
pnpm test -- CampusList
pnpm test -- UserProfileList
pnpm test -- DictionaryList
pnpm test -- FeatureFlagList
pnpm test -- AuditLogList
pnpm build
```

Expected:

```text
Admin-web lint, shared tests, page tests, and production build pass.
```

- [x] **Step 2: Run backend no-change gate**

Run:

```bash
cd mineadmin-education-saas
find backend/app backend/databases -path '*F05*' -o -path '*PcAdminFoundation*'
```

Expected:

```text
No output.
```

- [x] **Step 3: Run mobile regression gate**

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm build:h5
```

Expected:

```text
Mobile H5 build passes.
```

- [x] **Step 4: Commit F05**

Run:

```bash
cd mineadmin-education-saas
git add admin-web mobile-uniapp
git commit -m "feat: consolidate education foundation admin pages"
```

Expected:

```text
Commit succeeds with F05 PC admin integration changes.
```

## Self-Review

- Spec coverage: F05 covers MineAdmin PC route/menu integration, shared API types, API clients, list pages, forms, permission buttons, state flows, tests, commands, and acceptance gates.
- MineAdmin fit: The plan keeps backend code untouched and uses MineAdmin route meta auth, user permission store, result envelope, and admin-web conventions.
- API readiness: Each client lists exact methods, endpoints, permissions, success payload category, validation failure handling, and business failure handling.
- PC readiness: Every Foundation page has filters, columns, actions, permission rules, loading, empty, error, success, submit, and reload behavior.
- Mobile fit: F05 has no teacher or guardian page by design and includes a mobile build regression gate.
- Readiness: This plan has exact paths, migration/backend non-change constraints, API client contracts, PC page tasks, mobile rationale, tests, commands, expected outputs, and acceptance gates, so F05 can be marked `ready`.
