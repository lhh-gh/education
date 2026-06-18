# MineAdmin Education SaaS F02 User Profile Role Scope Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Bind MineAdmin users to education tenant profiles, role codes, and campus scopes so every later education API can resolve current tenant, platform access, role, and allowed campuses consistently.

**Architecture:** MineAdmin remains the authentication and permission source. F02 adds education-domain profile and campus-scope tables, resolves an `EducationUserContext` from `CurrentUser`, and upgrades F01's tenant context from raw `X-Tenant-Id` trust to current-user profile validation. Admin APIs manage education profiles and campus scopes; F06 consumes the same services for teacher and guardian mobile context.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, Redis 7, MineAdmin-Vue, Vue3, TypeScript, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. F02 user profile, role scope, tenant context, and campus scope gates have passed.

---

## Scope Check

Included:

- Create `edu_user_profiles` and `edu_user_campus_scopes`.
- Add education role-code enum and profile status enum.
- Bind MineAdmin `user.id` to education tenant/profile records.
- Create `EducationUserContext`, `TenantContext` upgrade, and `CampusScopeService`.
- Add `ResolveEducationContextMiddleware` for admin education APIs.
- Add admin APIs for profile page/create/update/status and campus-scope read/save.
- Add PC page for user profile and campus scope assignment.
- Add tests for migration, repository, service, context, API, tenant isolation, campus scope, permission, PC page behavior, and mobile build continuity.

Excluded:

- Creating MineAdmin login accounts; existing MineAdmin user management owns account creation.
- Student, guardian, teacher business records; V1 owns domain records.
- Teacher course authorization; V1 owns teaching authorization.
- Dictionary/feature flag management; F03 owns it.
- Education audit log table and audit query page; F04 owns it.
- Shared PC tenant selector shell and menu consolidation; F05 owns it.
- Teacher/guardian mobile pages and mobile login binding; F06 owns visible mobile context flows.

## File Structure

Modify backend:

```text
mineadmin-education-saas/backend/config/autoload/dependencies.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/TenantContext.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/TenantController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/CampusController.php
```

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_000200_create_education_user_profile_scope_tables.php
mineadmin-education-saas/backend/databases/seeders/EducationFoundationRoleSeeder.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/EducationRoleCode.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/UserProfileStatus.php
mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationUserProfile.php
mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationUserCampusScope.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/EducationUserContext.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusScopeService.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/UserProfileService.php
mineadmin-education-saas/backend/app/Repository/Education/Foundation/UserProfileRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Foundation/UserCampusScopeRepository.php
mineadmin-education-saas/backend/app/Http/Admin/Middleware/Education/Foundation/ResolveEducationContextMiddleware.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/UserProfilePageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/UserProfileSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/UserProfileStatusRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/CampusScopeSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/UserProfileController.php
mineadmin-education-saas/backend/app/Schema/Education/Foundation/UserProfileSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Foundation/UserCampusScopeSchema.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/UserProfileMigrationTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/UserProfileAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/EducationContextMiddlewareTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/CampusScopePermissionTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/TenantContextUpgradeTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/UserProfileRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/UserCampusScopeRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/UserProfileServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/CampusScopeServiceTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/foundation/userProfile.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/UserProfileList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/UserProfileForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/CampusScopeForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/UserProfileList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/CampusScopeForm.spec.ts
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
mineadmin-education-saas/backend/databases/migrations/2026_06_10_000200_create_education_user_profile_scope_tables.php
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
        Schema::create('edu_user_profiles', static function (Blueprint $table): void {
            $table->comment('Education user profiles');
            $table->bigIncrements('id');
            $table->string('profile_key', 120)->comment('Unique profile key, platform:user or tenant:tenant:user');
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('Tenant id, null for platform profiles');
            $table->unsignedBigInteger('user_id')->comment('MineAdmin user id');
            $table->string('role_code', 40)->comment('Education role code');
            $table->string('display_name', 80)->comment('Education display name');
            $table->string('mobile', 30)->nullable()->comment('Education contact mobile');
            $table->string('avatar', 255)->nullable()->comment('Education avatar');
            $table->string('openid', 80)->nullable()->comment('WeChat openid');
            $table->string('unionid', 80)->nullable()->comment('WeChat unionid');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->unsignedBigInteger('current_campus_id')->nullable()->comment('Default campus id');
            $table->json('settings')->nullable()->comment('Education profile settings');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique('profile_key', 'uk_edu_user_profiles_profile_key');
            $table->index(['tenant_id', 'user_id'], 'idx_edu_user_profiles_tenant_user');
            $table->index(['tenant_id', 'role_code'], 'idx_edu_user_profiles_tenant_role');
            $table->index(['tenant_id', 'status'], 'idx_edu_user_profiles_tenant_status');
            $table->index('user_id', 'idx_edu_user_profiles_user');
            $table->index('openid', 'idx_edu_user_profiles_openid');
            $table->index('unionid', 'idx_edu_user_profiles_unionid');
            $table->index('deleted_at', 'idx_edu_user_profiles_deleted_at');
        });

        Schema::create('edu_user_campus_scopes', static function (Blueprint $table): void {
            $table->comment('Education user campus scopes');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('user_profile_id')->comment('Education user profile id');
            $table->unsignedBigInteger('user_id')->comment('MineAdmin user id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();

            $table->unique(['tenant_id', 'user_id', 'campus_id'], 'uk_edu_user_campus_scopes_tenant_user_campus');
            $table->index(['tenant_id', 'user_profile_id'], 'idx_edu_user_campus_scopes_profile');
            $table->index(['tenant_id', 'campus_id'], 'idx_edu_user_campus_scopes_campus');
            $table->index('user_id', 'idx_edu_user_campus_scopes_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_user_campus_scopes');
        Schema::dropIfExists('edu_user_profiles');
    }
};
```

Column rules:

```text
edu_user_profiles.id: bigint unsigned primary key, auto increment, not null.
edu_user_profiles.profile_key: varchar(120), not null, unique. Format is platform:<user_id> for platform profiles and tenant:<tenant_id>:<user_id> for tenant profiles.
edu_user_profiles.tenant_id: bigint unsigned, nullable. Null means platform profile.
edu_user_profiles.user_id: bigint unsigned, not null, service-level reference to MineAdmin user.id.
edu_user_profiles.role_code: varchar(40), not null, one of EducationRoleCode.
edu_user_profiles.display_name: varchar(80), not null.
edu_user_profiles.mobile: varchar(30), nullable.
edu_user_profiles.avatar: varchar(255), nullable.
edu_user_profiles.openid: varchar(80), nullable.
edu_user_profiles.unionid: varchar(80), nullable.
edu_user_profiles.status: varchar(20), not null, default enabled.
edu_user_profiles.current_campus_id: bigint unsigned, nullable, must be inside tenant when present.
edu_user_profiles.settings: json, nullable.
edu_user_profiles.created_by: bigint unsigned, nullable.
edu_user_profiles.updated_by: bigint unsigned, nullable.
edu_user_profiles.created_at: timestamp, nullable.
edu_user_profiles.updated_at: timestamp, nullable.
edu_user_profiles.deleted_at: timestamp, nullable.

edu_user_campus_scopes.id: bigint unsigned primary key, auto increment, not null.
edu_user_campus_scopes.tenant_id: bigint unsigned, not null, service-level reference to edu_tenants.id.
edu_user_campus_scopes.user_profile_id: bigint unsigned, not null, service-level reference to edu_user_profiles.id.
edu_user_campus_scopes.user_id: bigint unsigned, not null, service-level reference to MineAdmin user.id.
edu_user_campus_scopes.campus_id: bigint unsigned, not null, service-level reference to edu_campuses.id.
edu_user_campus_scopes.created_by: bigint unsigned, nullable.
edu_user_campus_scopes.updated_by: bigint unsigned, nullable.
edu_user_campus_scopes.created_at: timestamp, nullable.
edu_user_campus_scopes.updated_at: timestamp, nullable.
```

Tenant/campus isolation fields:

```text
edu_user_profiles.tenant_id is nullable for platform profiles and required by service validation for tenant roles.
edu_user_campus_scopes.tenant_id is required and every query must filter by tenant_id.
edu_user_campus_scopes.campus_id is required and must belong to the same tenant_id.
```

Unique indexes:

```text
uk_edu_user_profiles_profile_key (profile_key)
uk_edu_user_campus_scopes_tenant_user_campus (tenant_id, user_id, campus_id)
```

Ordinary indexes:

```text
idx_edu_user_profiles_tenant_user (tenant_id, user_id)
idx_edu_user_profiles_tenant_role (tenant_id, role_code)
idx_edu_user_profiles_tenant_status (tenant_id, status)
idx_edu_user_profiles_user (user_id)
idx_edu_user_profiles_openid (openid)
idx_edu_user_profiles_unionid (unionid)
idx_edu_user_profiles_deleted_at (deleted_at)
idx_edu_user_campus_scopes_profile (tenant_id, user_profile_id)
idx_edu_user_campus_scopes_campus (tenant_id, campus_id)
idx_edu_user_campus_scopes_user (user_id)
```

Foreign-key policy:

```text
No physical foreign keys in F02. User, tenant, and campus existence are validated in UserProfileService and CampusScopeService.
Reason: MineAdmin foundation tables already use service-level association management, and education data import/recovery needs controlled service validation rather than hard FK failures.
```

Rollback behavior:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate:rollback --step=1
```

Expected:

```text
edu_user_campus_scopes is dropped before edu_user_profiles.
```

## MineAdmin Backend Module Design

### Dependency Binding

Modify `mineadmin-education-saas/backend/config/autoload/dependencies.php`:

```php
use App\Contract\Education\Foundation\TenantContextInterface;
use App\Service\Education\Foundation\TenantContext;

return [
    TenantContextInterface::class => TenantContext::class,
];
```

If the file already returns other bindings, merge this binding into the existing array.

### Enums

Create `mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/EducationRoleCode.php`:

```php
<?php

declare(strict_types=1);

namespace App\Model\Enums\Education\Foundation;

enum EducationRoleCode: string
{
    case PlatformSuperAdmin = 'platform_super_admin';
    case PlatformOperator = 'platform_operator';
    case TenantAdmin = 'tenant_admin';
    case Principal = 'principal';
    case AcademicStaff = 'academic_staff';
    case FrontDesk = 'front_desk';
    case Teacher = 'teacher';
    case Finance = 'finance';
    case Guardian = 'guardian';

    public function isPlatform(): bool
    {
        return in_array($this, [self::PlatformSuperAdmin, self::PlatformOperator], true);
    }

    public function requiresCampusScope(): bool
    {
        return in_array($this, [self::Principal, self::AcademicStaff, self::FrontDesk, self::Teacher, self::Finance], true);
    }
}
```

Create `mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/UserProfileStatus.php`:

```php
<?php

declare(strict_types=1);

namespace App\Model\Enums\Education\Foundation;

enum UserProfileStatus: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
}
```

### Models

Create `EducationUserProfile`:

```text
Path: mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationUserProfile.php
Table: edu_user_profiles
Traits: SoftDeletes
Fillable: every column from migration.
Casts: id integer, tenant_id integer, user_id integer, current_campus_id integer, settings array, created_by integer, updated_by integer, created_at datetime, updated_at datetime, deleted_at datetime.
Relations:
- user belongsTo App\Model\Permission\User by user_id.
- tenant belongsTo EducationTenant by tenant_id.
- currentCampus belongsTo EducationCampus by current_campus_id.
- campusScopes hasMany EducationUserCampusScope by user_profile_id.
```

Create `EducationUserCampusScope`:

```text
Path: mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationUserCampusScope.php
Table: edu_user_campus_scopes
Fillable: every column from migration.
Casts: id integer, tenant_id integer, user_profile_id integer, user_id integer, campus_id integer, created_by integer, updated_by integer, created_at datetime, updated_at datetime.
Relations:
- profile belongsTo EducationUserProfile by user_profile_id.
- campus belongsTo EducationCampus by campus_id.
```

### Context Object

Create `mineadmin-education-saas/backend/app/Service/Education/Foundation/EducationUserContext.php`:

```php
<?php

declare(strict_types=1);

namespace App\Service\Education\Foundation;

use App\Model\Enums\Education\Foundation\EducationRoleCode;

final readonly class EducationUserContext
{
    public function __construct(
        public int $userId,
        public ?int $tenantId,
        public EducationRoleCode $roleCode,
        public bool $platformAccess,
        /** @var int[] */
        public array $campusIds,
        public ?int $currentCampusId
    ) {}

    public function canAccessCampus(int $campusId): bool
    {
        return $this->platformAccess || in_array($campusId, $this->campusIds, true);
    }
}
```

### Repositories

Create `UserProfileRepository`:

```text
Path: mineadmin-education-saas/backend/app/Repository/Education/Foundation/UserProfileRepository.php
Extends: App\Repository\IRepository
Model: EducationUserProfile
Methods:
- handleSearch filters tenant_id, user_id, role_code, status, keyword(display_name/mobile/openid/unionid), and excludes deleted rows through model default.
- findByProfileKey(string $profileKey): ?EducationUserProfile
- findByUserTenant(int $userId, ?int $tenantId): ?EducationUserProfile
- listEnabledByUser(int $userId): Collection
- existsByProfileKey(string $profileKey, ?int $ignoreId = null): bool
```

Create `UserCampusScopeRepository`:

```text
Path: mineadmin-education-saas/backend/app/Repository/Education/Foundation/UserCampusScopeRepository.php
Extends: App\Repository\IRepository
Model: EducationUserCampusScope
Methods:
- campusIdsForUser(int $tenantId, int $userId): array
- campusIdsForProfile(int $tenantId, int $profileId): array
- replaceScopes(int $tenantId, int $profileId, int $userId, array $campusIds, ?int $operatorId): void
- deleteByProfile(int $tenantId, int $profileId): int
Rule:
- every query filters tenant_id.
```

### Services

Create `UserProfileService`:

```text
Path: mineadmin-education-saas/backend/app/Service/Education/Foundation/UserProfileService.php
Dependencies: UserProfileRepository, UserCampusScopeRepository, TenantRepository, CampusRepository, App\Repository\Permission\UserRepository.
Methods:
- page(array $params, int $page, int $pageSize, EducationUserContext $context): array
- createProfile(array $data, ?int $operatorId): EducationUserProfile
- updateProfile(int $id, array $data, ?int $operatorId): EducationUserProfile
- changeStatus(int $id, string $status, ?int $operatorId): EducationUserProfile
- resolveForUser(int $userId, ?int $requestedTenantId): EducationUserContext
- buildProfileKey(?int $tenantId, int $userId): string
```

Business rules:

```text
Platform roles:
- role_code platform_super_admin or platform_operator requires tenant_id null.
- profile_key must be platform:<user_id>.

Tenant roles:
- role_code tenant_admin, principal, academic_staff, front_desk, teacher, finance, guardian requires tenant_id.
- tenant_id must exist in edu_tenants.
- profile_key must be tenant:<tenant_id>:<user_id>.

All profiles:
- user_id must exist in MineAdmin user table.
- status must be enabled or disabled.
- duplicate profile_key throws BusinessException(ResultCode::CONFLICT, 'education user profile already exists', ['profile_key' => <key>]).
- current_campus_id, when present, must belong to tenant_id.
- disabled profile cannot resolve EducationUserContext.

Page visibility:
- platform context can page all tenant and platform profiles.
- tenant context can page only profiles where tenant_id equals context tenant_id.
```

Create `CampusScopeService`:

```text
Path: mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusScopeService.php
Dependencies: UserProfileRepository, UserCampusScopeRepository, CampusRepository.
Methods:
- campusIdsForUser(int $tenantId, int $userId): array
- campusIdsForProfile(int $tenantId, int $profileId): array
- saveScopes(int $profileId, int $tenantId, array $campusIds, ?int $operatorId): array
- assertCampusInScope(EducationUserContext $context, int $campusId): void
```

Business rules:

```text
saveScopes:
- profile must exist.
- profile tenant_id must equal tenantId.
- every campus_id must exist inside tenantId.
- duplicate campus ids are de-duplicated before save.
- role_code tenant_admin and guardian can save empty scope.
- role codes principal, academic_staff, front_desk, teacher, finance require at least one campus id when enabled.
- replaced scopes are stored with tenant_id, user_profile_id, user_id, campus_id.

assertCampusInScope:
- platformAccess true passes.
- tenant_admin passes for any campus inside tenant.
- other tenant roles pass only if campusId is in context.campusIds.
- failure throws BusinessException(ResultCode::FORBIDDEN, 'campus is outside current user scope', ['campus_id' => <campusId>]).
```

### Tenant Context Upgrade

Modify `mineadmin-education-saas/backend/app/Service/Education/Foundation/TenantContext.php`.

Required behavior:

```text
- Read requested tenant id from X-Tenant-Id header.
- Resolve current MineAdmin user id from CurrentUser.
- Resolve EducationUserContext through UserProfileService.
- If profile is platform role and X-Tenant-Id is present, return header tenant id after confirming tenant exists.
- If profile is platform role and X-Tenant-Id is absent, throw 422 only for campus APIs that require tenant id.
- If profile is tenant role and X-Tenant-Id is absent, return profile tenant_id.
- If profile is tenant role and X-Tenant-Id differs from profile tenant_id, throw 403.
- Never trust request body tenant_id.
```

### Middleware

Create `mineadmin-education-saas/backend/app/Http/Admin/Middleware/Education/Foundation/ResolveEducationContextMiddleware.php`.

Behavior:

```text
- Resolve requested tenant id from X-Tenant-Id header.
- Resolve EducationUserContext through UserProfileService.
- Store context in Hyperf Context key education.user_context.
- Throw 403 when profile is missing or disabled.
- Throw 403 when tenant profile tries to request another tenant.
- Continue request when context is valid.
```

Modify F01 controllers:

```text
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/TenantController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/CampusController.php
```

Rules:

```text
- Add ResolveEducationContextMiddleware after PermissionMiddleware and before OperationMiddleware.
- TenantController page/create/update/status/delete require platformAccess true.
- CampusController page/create/update/status/delete require a resolved tenant id and current-user tenant access.
```

### Requests

Create `UserProfilePageRequest` rules:

```php
return [
    'page' => 'sometimes|integer|min:1',
    'page_size' => 'sometimes|integer|min:1|max:200',
    'tenant_id' => 'sometimes|nullable|integer|min:1',
    'keyword' => 'sometimes|string|max:120',
    'role_code' => 'sometimes|in:platform_super_admin,platform_operator,tenant_admin,principal,academic_staff,front_desk,teacher,finance,guardian',
    'status' => 'sometimes|in:enabled,disabled',
];
```

Create `UserProfileSaveRequest` rules:

```php
return [
    'tenant_id' => 'sometimes|nullable|integer|min:1',
    'user_id' => 'required|integer|min:1',
    'role_code' => 'required|in:platform_super_admin,platform_operator,tenant_admin,principal,academic_staff,front_desk,teacher,finance,guardian',
    'display_name' => 'required|string|max:80',
    'mobile' => 'sometimes|nullable|string|max:30',
    'avatar' => 'sometimes|nullable|string|max:255',
    'openid' => 'sometimes|nullable|string|max:80',
    'unionid' => 'sometimes|nullable|string|max:80',
    'status' => 'sometimes|in:enabled,disabled',
    'current_campus_id' => 'sometimes|nullable|integer|min:1',
    'settings' => 'sometimes|nullable|array',
];
```

Create `UserProfileStatusRequest` rules:

```php
return [
    'status' => 'required|in:enabled,disabled',
];
```

Create `CampusScopeSaveRequest` rules:

```php
return [
    'campus_ids' => 'present|array',
    'campus_ids.*' => 'integer|min:1',
];
```

### Controllers

Create `UserProfileController` with MineAdmin admin middleware:

```text
AccessTokenMiddleware priority 100
PermissionMiddleware priority 99
ResolveEducationContextMiddleware priority 98
OperationMiddleware priority 97
```

Endpoints:

```text
GET /admin/education/foundation/user-profiles/page
POST /admin/education/foundation/user-profiles
PUT /admin/education/foundation/user-profiles/{id}
PUT /admin/education/foundation/user-profiles/{id}/status
GET /admin/education/foundation/user-profiles/{id}/campus-scopes
PUT /admin/education/foundation/user-profiles/{id}/campus-scopes
```

Permission codes:

```text
education:foundation:user-profile:page
education:foundation:user-profile:create
education:foundation:user-profile:update
education:foundation:user-profile:status
education:foundation:campus-scope:page
education:foundation:campus-scope:save
```

Response rules:

```text
page returns service->page(request data, current page, page size, context).
create returns created profile id and profile_key.
update returns updated profile id.
status returns profile id and status.
campus-scopes GET returns profile id and campus_ids.
campus-scopes PUT returns profile id and saved campus_ids.
```

### Schemas

Create `UserProfileSchema` fields:

```text
id int
profile_key string
tenant_id int nullable
user_id int
role_code string
display_name string
mobile string nullable
avatar string nullable
openid string nullable
unionid string nullable
status string
current_campus_id int nullable
settings array nullable
created_by int nullable
updated_by int nullable
created_at string nullable
updated_at string nullable
```

Create `UserCampusScopeSchema` fields:

```text
id int
tenant_id int
user_profile_id int
user_id int
campus_id int
created_by int nullable
updated_by int nullable
created_at string nullable
updated_at string nullable
```

### Seeder

Create `mineadmin-education-saas/backend/databases/seeders/EducationFoundationRoleSeeder.php`.

Purpose:

```text
Ensure MineAdmin role.code values exist for education platform and tenant roles.
This seeder does not grant menus automatically; F05 handles menu consolidation and final permission grants.
```

Role rows:

```text
education_platform_operator
education_tenant_admin
education_principal
education_academic_staff
education_front_desk
education_teacher
education_finance
education_guardian
```

Run command:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php db:seed --class=EducationFoundationRoleSeeder
```

Expected:

```text
Role rows exist with status normal and stable role codes.
```

## API Contract

### Profile Page

```text
GET /admin/education/foundation/user-profiles/page
Permission: education:foundation:user-profile:page
Caller: platform admin or tenant admin
Headers: Authorization: Bearer <token>, optional X-Tenant-Id: 1
Isolation: platform context can see all; tenant context sees current tenant only
Audit: read operation, no operation audit row required
```

Request query:

```json
{
  "page": 1,
  "page_size": 20,
  "tenant_id": 1,
  "keyword": "王老师",
  "role_code": "teacher",
  "status": "enabled"
}
```

Success response:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 10,
        "tenant_id": 1,
        "user_id": 1001,
        "role_code": "teacher",
        "display_name": "王老师",
        "mobile": "13800000000",
        "status": "enabled",
        "current_campus_id": 1,
        "campus_scope_count": 2
      }
    ],
    "total": 1
  }
}
```

Validation failure:

```json
{
  "code": 422,
  "message": "role_code is invalid",
  "data": []
}
```

Business failure:

```json
{
  "code": 403,
  "message": "education user profile is missing",
  "data": {
    "user_id": 1001
  }
}
```

### Profile Create

```text
POST /admin/education/foundation/user-profiles
Permission: education:foundation:user-profile:create
Caller: platform admin or tenant admin
Headers: Authorization: Bearer <token>, optional X-Tenant-Id: 1
Isolation: tenant admin can create only current-tenant profiles
Audit: OperationMiddleware records write operation
```

Request body:

```json
{
  "tenant_id": 1,
  "user_id": 1001,
  "role_code": "teacher",
  "display_name": "王老师",
  "mobile": "13800000000",
  "openid": "wx-openid-001",
  "status": "enabled",
  "current_campus_id": 1
}
```

Success response:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 10,
    "profile_key": "tenant:1:1001"
  }
}
```

Business failure:

```json
{
  "code": 409,
  "message": "education user profile already exists",
  "data": {
    "profile_key": "tenant:1:1001"
  }
}
```

### Profile Update

```text
PUT /admin/education/foundation/user-profiles/{id}
Permission: education:foundation:user-profile:update
Caller: platform admin or tenant admin
Headers: Authorization: Bearer <token>, optional X-Tenant-Id: 1
Isolation: tenant admin can update only current-tenant profiles
Audit: OperationMiddleware records write operation
```

Request body:

```json
{
  "tenant_id": 1,
  "user_id": 1001,
  "role_code": "academic_staff",
  "display_name": "王教务",
  "mobile": "13800000000",
  "status": "enabled",
  "current_campus_id": 1
}
```

Success response:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 10
  }
}
```

Cross-tenant failure:

```json
{
  "code": 403,
  "message": "tenant is outside current user scope",
  "data": {
    "tenant_id": 2
  }
}
```

### Profile Status

```text
PUT /admin/education/foundation/user-profiles/{id}/status
Permission: education:foundation:user-profile:status
Caller: platform admin or tenant admin
Headers: Authorization: Bearer <token>, optional X-Tenant-Id: 1
Isolation: tenant admin can change only current-tenant profiles
Audit: OperationMiddleware records write operation
```

Request body:

```json
{
  "status": "disabled"
}
```

Success response:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 10,
    "status": "disabled"
  }
}
```

### Campus Scope Page

```text
GET /admin/education/foundation/user-profiles/{id}/campus-scopes
Permission: education:foundation:campus-scope:page
Caller: platform admin or tenant admin
Headers: Authorization: Bearer <token>, optional X-Tenant-Id: 1
Isolation: profile tenant must be inside current-user tenant scope
Audit: read operation, no operation audit row required
```

Success response:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "user_profile_id": 10,
    "user_id": 1001,
    "tenant_id": 1,
    "campus_ids": [1, 2]
  }
}
```

### Campus Scope Save

```text
PUT /admin/education/foundation/user-profiles/{id}/campus-scopes
Permission: education:foundation:campus-scope:save
Caller: platform admin or tenant admin
Headers: Authorization: Bearer <token>, optional X-Tenant-Id: 1
Isolation: every campus_id must belong to profile tenant_id
Audit: OperationMiddleware records write operation
```

Request body:

```json
{
  "campus_ids": [1, 2]
}
```

Success response:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "user_profile_id": 10,
    "campus_ids": [1, 2]
  }
}
```

Business failure:

```json
{
  "code": 403,
  "message": "campus is outside current tenant",
  "data": {
    "campus_id": 9
  }
}
```

## PC Admin Page Tasks

### User Profile API Client

Create `mineadmin-education-saas/admin-web/src/api/education/foundation/userProfile.ts`.

Types:

```ts
export type EducationRoleCode =
  | 'platform_super_admin'
  | 'platform_operator'
  | 'tenant_admin'
  | 'principal'
  | 'academic_staff'
  | 'front_desk'
  | 'teacher'
  | 'finance'
  | 'guardian'

export interface UserProfileRecord {
  id: number
  profile_key: string
  tenant_id?: number
  user_id: number
  role_code: EducationRoleCode
  display_name: string
  mobile?: string
  avatar?: string
  openid?: string
  unionid?: string
  status: 'enabled' | 'disabled'
  current_campus_id?: number
  campus_scope_count?: number
  created_at?: string
  updated_at?: string
}

export interface UserProfilePageParams {
  page?: number
  page_size?: number
  tenant_id?: number
  keyword?: string
  role_code?: EducationRoleCode
  status?: 'enabled' | 'disabled'
}

export interface UserProfileSavePayload {
  tenant_id?: number
  user_id: number
  role_code: EducationRoleCode
  display_name: string
  mobile?: string
  avatar?: string
  openid?: string
  unionid?: string
  status?: 'enabled' | 'disabled'
  current_campus_id?: number
  settings?: Record<string, unknown>
}
```

Methods:

```text
pageUserProfiles(params: UserProfilePageParams)
createUserProfile(data: UserProfileSavePayload)
updateUserProfile(id: number, data: UserProfileSavePayload)
updateUserProfileStatus(id: number, status: 'enabled' | 'disabled')
getCampusScopes(id: number)
saveCampusScopes(id: number, campus_ids: number[])
```

### Router

Modify `mineadmin-education-saas/admin-web/src/router/modules/education.ts`.

Route:

```text
Route path: /education/foundation/user-profiles
Route name: EducationFoundationUserProfileList
Menu: 教务 SaaS / 基础设置 / 人员权限
Permission: education:foundation:user-profile:page
Component: admin-web/src/views/education/foundation/UserProfileList.vue
```

### User Profile List Page

Create `mineadmin-education-saas/admin-web/src/views/education/foundation/UserProfileList.vue`.

Search fields:

```text
tenant_id: tenant selector, visible for platform profile users
keyword: input, hint text 姓名/手机号/OpenID/UnionID
role_code: select role code
status: select enabled/disabled
```

Table columns:

```text
display_name, mobile, role_code, tenant_id, current_campus_id, campus_scope_count, status, updated_at, actions
```

Actions:

```text
Create button permission: education:foundation:user-profile:create
Edit button permission: education:foundation:user-profile:update
Enable/disable button permission: education:foundation:user-profile:status
Campus scope button permission: education:foundation:campus-scope:save
```

States:

```text
loading: table loading true while pageUserProfiles pending.
empty: show empty state when list length is 0.
error: show API message and keep last successful list.
permission: hide action button when user lacks permission code.
status flow: enabled row shows disable action; disabled row shows enable action.
scope flow: platform roles hide campus scope action; tenant scoped roles show campus scope action.
```

### User Profile Form

Create `mineadmin-education-saas/admin-web/src/views/education/foundation/components/UserProfileForm.vue`.

Fields:

```text
tenant_id: required for tenant roles, disabled for platform roles
user_id: required MineAdmin user selector
role_code: required select
display_name: required input max 80
mobile: optional input max 30
avatar: optional upload or input max 255
openid: optional input max 80
unionid: optional input max 80
status: enabled/disabled, default enabled
current_campus_id: optional campus selector, only for tenant roles
```

Validation:

```text
platform_super_admin and platform_operator require tenant_id empty.
tenant_admin, principal, academic_staff, front_desk, teacher, finance, guardian require tenant_id.
current_campus_id must be empty for platform roles.
```

Submit:

```text
create mode calls createUserProfile.
edit mode calls updateUserProfile.
validation failure keeps modal open and displays field message.
success closes modal and refreshes list.
```

### Campus Scope Form

Create `mineadmin-education-saas/admin-web/src/views/education/foundation/components/CampusScopeForm.vue`.

Fields:

```text
campus_ids: multi-select or tree-select of campuses from the profile tenant
```

States:

```text
loading: true while getCampusScopes pending.
empty: show no campus options when tenant has no campus.
error: show API message and keep selected campus ids unchanged.
permission: form cannot submit without education:foundation:campus-scope:save.
```

Submit:

```text
saveCampusScopes(id, campus_ids) on confirm.
success closes modal and refreshes user profile list.
```

Verification:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- UserProfileList
pnpm test -- CampusScopeForm
pnpm build
```

Expected:

```text
Lint passes.
UserProfileList tests pass.
CampusScopeForm tests pass.
Build succeeds.
```

## Teacher / Guardian Mobile Page Tasks

This module has no visible teacher or guardian page because it only establishes backend user context, role codes, and campus scopes. F06 adds teacher/guardian context APIs and mobile pages using `UserProfileService` and `CampusScopeService`.

Mobile verification:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm build:h5
```

Expected:

```text
H5 build succeeds and F02 adds no teacher or guardian mobile route.
```

Role isolation rule:

```text
Teacher and guardian profiles must not access PC admin profile management APIs unless MineAdmin permissions explicitly grant the relevant education:foundation:* permission codes.
```

## Test Plan

Migration/schema test:

```text
Test file: backend/tests/Feature/Education/Foundation/UserProfileMigrationTest.php
Case: test_profile_and_scope_tables_have_required_columns_and_indexes
Assert:
- edu_user_profiles has profile_key, tenant_id, user_id, role_code, display_name, status, current_campus_id, deleted_at
- edu_user_campus_scopes has tenant_id, user_profile_id, user_id, campus_id
- uk_edu_user_profiles_profile_key exists
- uk_edu_user_campus_scopes_tenant_user_campus exists
```

Repository tests:

```text
Test file: backend/tests/Unit/Education/Foundation/UserProfileRepositoryTest.php
Case: test_find_by_user_tenant_returns_correct_profile
Assert:
- tenant 1 profile is returned for user 1001 and tenant 1
- tenant 2 profile is not returned for tenant 1 lookup

Test file: backend/tests/Unit/Education/Foundation/UserCampusScopeRepositoryTest.php
Case: test_campus_ids_for_user_are_tenant_scoped
Assert:
- tenant 1 scope returns only tenant 1 campus ids
- tenant 2 scope is excluded
```

Service tests:

```text
Test file: backend/tests/Unit/Education/Foundation/UserProfileServiceTest.php
Case: test_platform_role_requires_null_tenant
Assert:
- createProfile with role platform_operator and tenant_id 1 throws 422 BusinessException

Case: test_tenant_role_requires_tenant
Assert:
- createProfile with role teacher and tenant_id null throws 422 BusinessException

Case: test_duplicate_profile_key_returns_conflict
Assert:
- second profile for same tenant_id and user_id throws BusinessException
- response code is 409
- response data.profile_key is tenant:1:1001
```

```text
Test file: backend/tests/Unit/Education/Foundation/CampusScopeServiceTest.php
Case: test_save_scopes_rejects_campus_outside_tenant
Assert:
- saving campus id from tenant 2 for tenant 1 throws BusinessException
- response code is 403
- response data.campus_id is tenant 2 campus id

Case: test_teacher_enabled_profile_requires_scope
Assert:
- saving empty campus_ids for enabled teacher throws BusinessException
- response code is 422
```

Context/middleware tests:

```text
Test file: backend/tests/Feature/Education/Foundation/EducationContextMiddlewareTest.php
Case: test_tenant_profile_resolves_context_without_header
Assert:
- current user profile tenant_id 1 resolves context tenant_id 1
- context role_code is teacher

Case: test_tenant_profile_cannot_request_other_tenant
Assert:
- request with X-Tenant-Id 2 for profile tenant_id 1 returns code 403

Case: test_disabled_profile_is_rejected
Assert:
- disabled profile returns code 403
```

Tenant context upgrade tests:

```text
Test file: backend/tests/Feature/Education/Foundation/TenantContextUpgradeTest.php
Case: test_campus_api_uses_profile_tenant_when_header_missing
Assert:
- tenant admin profile with tenant_id 1 can page tenant 1 campuses without X-Tenant-Id

Case: test_campus_api_rejects_mismatched_header
Assert:
- tenant admin profile with tenant_id 1 and X-Tenant-Id 2 returns code 403

Case: test_platform_profile_can_request_tenant_header
Assert:
- platform profile with X-Tenant-Id 1 can page tenant 1 campuses
```

API tests:

```text
Test file: backend/tests/Feature/Education/Foundation/UserProfileAdminApiTest.php
Case: test_tenant_admin_can_create_teacher_profile
Assert:
- POST /admin/education/foundation/user-profiles returns code 200
- edu_user_profiles contains role_code teacher and tenant_id 1
- profile_key is tenant:1:1001

Case: test_guardian_profile_can_store_openid
Assert:
- profile row contains openid wx-openid-001
- lookup by openid returns profile

Case: test_profile_scope_read_and_save
Assert:
- PUT campus-scopes saves [1,2]
- GET campus-scopes returns [1,2]
```

Permission tests:

```text
Test file: backend/tests/Feature/Education/Foundation/CampusScopePermissionTest.php
Case: test_user_without_profile_permission_cannot_page_profiles
Assert:
- GET /admin/education/foundation/user-profiles/page returns code 403

Case: test_teacher_with_no_scope_cannot_access_campus_data
Assert:
- CampusScopeService::assertCampusInScope throws 403 for unassigned campus

Case: test_write_controllers_include_operation_middleware
Assert:
- UserProfileController class has OperationMiddleware attribute
```

PC tests:

```text
Test file: admin-web/src/views/education/foundation/__tests__/UserProfileList.spec.ts
Case: renders_actions_by_permission
Assert:
- create button renders with education:foundation:user-profile:create
- campus scope button hides without education:foundation:campus-scope:save
- disabled row shows enable action

Test file: admin-web/src/views/education/foundation/__tests__/CampusScopeForm.spec.ts
Case: saves_selected_campuses
Assert:
- getCampusScopes is called when form opens
- saveCampusScopes receives selected campus_ids
- validation prevents submit when required teacher scope is empty
```

Mobile build verification:

```text
Command: cd mineadmin-education-saas/mobile-uniapp && pnpm build:h5
Assert:
- command exits 0
- no F02 teacher/guardian route is added
```

## Execution Commands

Backend:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
php bin/hyperf.php db:seed --class=EducationFoundationRoleSeeder
composer test -- --filter UserProfileMigrationTest
composer test -- --filter UserProfileRepositoryTest
composer test -- --filter UserCampusScopeRepositoryTest
composer test -- --filter UserProfileServiceTest
composer test -- --filter CampusScopeServiceTest
composer test -- --filter EducationContextMiddlewareTest
composer test -- --filter TenantContextUpgradeTest
composer test -- --filter UserProfileAdminApiTest
composer test -- --filter CampusScopePermissionTest
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
Migration creates edu_user_profiles and edu_user_campus_scopes.
Role seeder creates stable education role rows.
Migration tests pass.
Repository tests pass.
Service tests pass.
Context middleware tests pass.
TenantContext upgrade tests pass.
API tests pass.
Permission tests pass.
Code style dry run passes.
Static analysis passes.
```

PC:

```bash
cd mineadmin-education-saas/admin-web
pnpm install --frozen-lockfile
pnpm lint
pnpm test -- UserProfileList
pnpm test -- CampusScopeForm
pnpm build
```

Expected:

```text
Install exits 0.
Lint exits 0.
UserProfileList test exits 0.
CampusScopeForm test exits 0.
Build exits 0 and admin-web/dist/index.html exists.
```

Mobile:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm build:h5
```

Expected:

```text
H5 build exits 0 and mobile-uniapp/dist/build/h5/index.html exists.
```

Full F02 gate:

```bash
cd mineadmin-education-saas
make up
cd backend
php bin/hyperf.php migrate
php bin/hyperf.php db:seed --class=EducationFoundationRoleSeeder
composer test -- --filter Education\\\\Foundation
composer cs-fix -- --dry-run
composer analyse
cd ../admin-web
pnpm lint && pnpm test -- UserProfileList && pnpm test -- CampusScopeForm && pnpm build
cd ../mobile-uniapp
pnpm build:h5
```

Expected:

```text
All commands exit 0.
```

## Acceptance Gate

```text
- edu_user_profiles exists with profile_key, tenant_id, user_id, role_code, status, current_campus_id, and planned indexes.
- edu_user_campus_scopes exists with tenant_id, user_profile_id, user_id, campus_id, and planned indexes.
- EducationRoleCode and UserProfileStatus enums exist.
- UserProfileService rejects platform roles with tenant_id.
- UserProfileService rejects tenant roles without tenant_id.
- Duplicate profile_key returns code 409.
- CampusScopeService rejects campuses outside tenant.
- Teacher, principal, academic_staff, front_desk, and finance enabled profiles require campus scope.
- TenantContext validates current user profile and rejects mismatched tenant headers.
- F01 CampusController is protected by resolved education context.
- UserProfileController exposes page/create/update/status/campus-scope APIs with MineAdmin result shape.
- PC user profile page has API client, route, list, form, permission buttons, status flow, campus-scope form, loading, empty, and error states.
- F02 adds no teacher or guardian mobile page.
- Backend tests pass.
- PC tests and build pass.
- Mobile H5 build still passes.
```

## Tasks

### Task 1: Create Migration, Enums, Models, and Seeder

**Files:**

- Create: `mineadmin-education-saas/backend/databases/migrations/2026_06_10_000200_create_education_user_profile_scope_tables.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/EducationRoleCode.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/UserProfileStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationUserProfile.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationUserCampusScope.php`
- Create: `mineadmin-education-saas/backend/databases/seeders/EducationFoundationRoleSeeder.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/UserProfileMigrationTest.php`

- [x] **Step 1: Create migration**

Use the full migration from `Database Migration Design`.

- [x] **Step 2: Create enums**

Use the enum definitions from `MineAdmin Backend Module Design`.

- [x] **Step 3: Create models**

Create model files with the table, fillable, casts, and relations listed in `MineAdmin Backend Module Design`.

- [x] **Step 4: Create role seeder**

Create `EducationFoundationRoleSeeder` with the role rows listed in `MineAdmin Backend Module Design`.

- [x] **Step 5: Run migration and seeder**

Run:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
php bin/hyperf.php db:seed --class=EducationFoundationRoleSeeder
```

Expected:

```text
edu_user_profiles exists.
edu_user_campus_scopes exists.
education role rows exist.
```

- [x] **Step 6: Write and run migration test**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter UserProfileMigrationTest
```

Expected:

```text
UserProfileMigrationTest passes.
```

### Task 2: Create Repositories and Services

**Files:**

- Create: `mineadmin-education-saas/backend/app/Repository/Education/Foundation/UserProfileRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Foundation/UserCampusScopeRepository.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Foundation/UserProfileService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusScopeService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Foundation/EducationUserContext.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/UserProfileRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/UserCampusScopeRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/UserProfileServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/CampusScopeServiceTest.php`

- [x] **Step 1: Create context object**

Use the full `EducationUserContext` class from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create repositories**

Implement repository methods listed in `MineAdmin Backend Module Design`.

- [x] **Step 3: Create services**

Implement service methods and business rules listed in `MineAdmin Backend Module Design`.

- [x] **Step 4: Write repository and service tests**

Create all unit test files listed in `Test Plan`.

- [x] **Step 5: Run unit tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter UserProfileRepositoryTest
composer test -- --filter UserCampusScopeRepositoryTest
composer test -- --filter UserProfileServiceTest
composer test -- --filter CampusScopeServiceTest
```

Expected:

```text
Repository and service tests pass.
```

### Task 3: Upgrade Tenant Context and Add Middleware

**Files:**

- Modify: `mineadmin-education-saas/backend/config/autoload/dependencies.php`
- Modify: `mineadmin-education-saas/backend/app/Service/Education/Foundation/TenantContext.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Middleware/Education/Foundation/ResolveEducationContextMiddleware.php`
- Modify: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/TenantController.php`
- Modify: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/CampusController.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/EducationContextMiddlewareTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/TenantContextUpgradeTest.php`

- [x] **Step 1: Bind TenantContextInterface**

Add the dependency binding from `MineAdmin Backend Module Design`.

- [x] **Step 2: Upgrade TenantContext**

Implement the tenant context upgrade rules from `MineAdmin Backend Module Design`.

- [x] **Step 3: Create ResolveEducationContextMiddleware**

Implement middleware behavior from `MineAdmin Backend Module Design`.

- [x] **Step 4: Add middleware to F01 controllers**

Add `ResolveEducationContextMiddleware` to TenantController and CampusController as described.

- [x] **Step 5: Write and run context tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter EducationContextMiddlewareTest
composer test -- --filter TenantContextUpgradeTest
```

Expected:

```text
Education context middleware tests pass.
TenantContext upgrade tests pass.
```

### Task 4: Create Requests, Schemas, and User Profile Controller

**Files:**

- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/UserProfilePageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/UserProfileSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/UserProfileStatusRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/CampusScopeSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/UserProfileController.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Foundation/UserProfileSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Foundation/UserCampusScopeSchema.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/UserProfileAdminApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/CampusScopePermissionTest.php`

- [x] **Step 1: Create request classes**

Use the request validation rules from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create schema classes**

Use the schema fields from `MineAdmin Backend Module Design`.

- [x] **Step 3: Create UserProfileController**

Use the endpoints, permissions, middleware, and response rules from `MineAdmin Backend Module Design`.

- [x] **Step 4: Write API and permission tests**

Create feature tests listed in `Test Plan`.

- [x] **Step 5: Run feature tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter UserProfileAdminApiTest
composer test -- --filter CampusScopePermissionTest
```

Expected:

```text
User profile API tests pass.
Campus scope permission tests pass.
```

### Task 5: Create PC Profile and Scope Pages

**Files:**

- Create: `mineadmin-education-saas/admin-web/src/api/education/foundation/userProfile.ts`
- Modify: `mineadmin-education-saas/admin-web/src/router/modules/education.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/UserProfileList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/UserProfileForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/CampusScopeForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/UserProfileList.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/CampusScopeForm.spec.ts`

- [x] **Step 1: Create API client**

Use the TypeScript types and methods from `PC Admin Page Tasks`.

- [x] **Step 2: Add router entry**

Use the route path, route name, menu, permission, and component from `PC Admin Page Tasks`.

- [x] **Step 3: Create list page and profile form**

Implement all search, table, action, state, and form requirements from `PC Admin Page Tasks`.

- [x] **Step 4: Create campus scope form**

Implement campus scope fields, state handling, permission behavior, and submit behavior from `PC Admin Page Tasks`.

- [x] **Step 5: Add PC tests**

Create `UserProfileList.spec.ts` and `CampusScopeForm.spec.ts` with assertions listed in `Test Plan`.

- [x] **Step 6: Run PC verification**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- UserProfileList
pnpm test -- CampusScopeForm
pnpm build
```

Expected:

```text
Lint, tests, and build pass.
```

### Task 6: Run F02 Final Gate

**Files:**

- Verify: all F02 backend, PC, and mobile paths listed in `File Structure`.

- [x] **Step 1: Run backend final gate**

Run:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
php bin/hyperf.php db:seed --class=EducationFoundationRoleSeeder
composer test -- --filter Education\\\\Foundation
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
Migration, seeder, and all Education Foundation tests pass.
Code style dry run passes.
Static analysis passes.
```

- [x] **Step 2: Run PC final gate**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- UserProfileList
pnpm test -- CampusScopeForm
pnpm build
```

Expected:

```text
PC lint, tests, and build pass.
```

- [x] **Step 3: Run mobile final gate**

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm build:h5
```

Expected:

```text
Mobile H5 build still passes.
```

- [x] **Step 4: Commit F02**

Run:

```bash
cd mineadmin-education-saas
git add backend admin-web mobile-uniapp
git commit -m "feat: add education user profile and scope foundation"
```

Expected:

```text
Commit succeeds with F02 backend, PC, and verification changes.
```

## Self-Review

- Spec coverage: F02 covers education user profiles, role codes, campus scopes, context middleware, TenantContext upgrade, admin APIs, PC pages, tests, commands, and acceptance gates.
- MineAdmin fit: The plan uses MineAdmin 3.x `CurrentUser`, `PermissionMiddleware`, `OperationMiddleware`, `app/Http/Admin`, `app/Repository/IRepository`, `app/Service`, `app/Schema`, and `databases/migrations`.
- Boundary fit: MineAdmin user creation remains in base user management; F02 only binds users to education profiles.
- Tenant isolation: Tenant roles cannot request another tenant, and campus scope queries always filter tenant_id.
- Campus scope: scoped roles require campus ids, and campus ids must belong to the profile tenant.
- PC fit: The profile page includes API client, route, list, form, campus-scope form, permission buttons, loading, empty, and error states.
- Mobile fit: F02 exposes backend context services only; F06 owns visible teacher/guardian mobile flows.
- Readiness: This plan has exact paths, full migration design, backend layer tasks, API request/response/failure examples, PC tasks, mobile verification, tests, commands, expected outputs, and acceptance gates, so F02 can be marked `ready`.
