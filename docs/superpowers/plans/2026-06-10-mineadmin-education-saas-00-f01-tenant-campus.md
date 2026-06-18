# MineAdmin Education SaaS F01 Tenant Campus Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement tenant and campus management as the first education-domain foundation module, including MineAdmin-native backend layers, tenant context, admin APIs, PC pages, tests, and verification gates.

**Architecture:** Platform admins manage tenants globally. Tenant admins manage campuses only inside the current tenant context, supplied by `X-Tenant-Id` in F01 and later hardened by F02 user profile and role scope. Backend follows MineAdmin 3.x structure: controllers under `app/Http/Admin/Controller`, requests under `app/Http/Admin/Request`, business code under `app/Service`, repositories under `app/Repository`, models under `app/Model`, schemas under `app/Schema`, and migrations under `databases/migrations`.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, Redis 7, MineAdmin-Vue, Vue3, TypeScript, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. F01 tenant and campus foundation has passed backend, PC, and mobile verification gates.

---

## Scope Check

Included:

- Create `edu_tenants` and `edu_campuses` tables.
- Add `CONFLICT = 409` to MineAdmin result codes for duplicate tenant/campus business failures.
- Create tenant and campus status enums.
- Create tenant and campus models, repositories, services, requests, schemas, and admin controllers.
- Create `TenantContext` and `TenantContextInterface` for F01 campus isolation.
- Create admin APIs for tenant page/create/update/status/delete and campus page/create/update/status/delete.
- Create MineAdmin-Vue API clients, tenant list/form, campus list/form, router entries, button permission wiring, and page state tasks.
- Create migration, repository, service, API, tenant isolation, campus scope, permission, operation audit middleware, PC build, and mobile no-surface verification.

Excluded:

- User profile, user-to-tenant binding, user-to-campus binding, and role scope persistence; F02 owns these.
- Dictionary and feature flag tables; F03 owns these.
- Education-domain audit log table and audit query page; F04 owns these. F01 write APIs must still use MineAdmin `OperationMiddleware`.
- Global PC navigation shell and broader menu foundation; F05 owns final navigation consolidation.
- Teacher and guardian mobile pages; F06 and V1 own mobile contexts and business pages.

## File Structure

Modify:

```text
mineadmin-education-saas/backend/app/Http/Common/ResultCode.php
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_000100_create_education_tenant_campus_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/TenantStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/CampusStatus.php
mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationTenant.php
mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationCampus.php
mineadmin-education-saas/backend/app/Contract/Education/Foundation/TenantContextInterface.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/TenantContext.php
mineadmin-education-saas/backend/app/Repository/Education/Foundation/TenantRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Foundation/CampusRepository.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/TenantService.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusService.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/TenantPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/TenantSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/TenantStatusRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/CampusPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/CampusSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/CampusStatusRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/TenantController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/CampusController.php
mineadmin-education-saas/backend/app/Schema/Education/Foundation/TenantSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Foundation/CampusSchema.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/TenantCampusMigrationTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/TenantAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/CampusAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/TenantCampusIsolationTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/TenantCampusPermissionTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/TenantRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/CampusRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/TenantServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/CampusServiceTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/foundation/tenant.ts
mineadmin-education-saas/admin-web/src/api/education/foundation/campus.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/TenantList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/CampusList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/TenantForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/CampusForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/TenantList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/CampusList.spec.ts
```

Verify mobile:

```text
mineadmin-education-saas/mobile-uniapp/package.json
```

## Database Migration Design

Migration file:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_000100_create_education_tenant_campus_tables.php
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
        Schema::create('edu_tenants', static function (Blueprint $table): void {
            $table->comment('Education tenants');
            $table->bigIncrements('id');
            $table->string('name', 120)->comment('Tenant full name');
            $table->string('code', 64)->comment('Tenant unique code');
            $table->string('short_name', 60)->nullable()->comment('Tenant short name');
            $table->string('contact_name', 60)->nullable()->comment('Primary contact name');
            $table->string('contact_phone', 30)->nullable()->comment('Primary contact phone');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->json('settings')->nullable()->comment('Tenant settings JSON');
            $table->timestamp('enabled_at')->nullable()->comment('Enabled time');
            $table->timestamp('disabled_at')->nullable()->comment('Disabled time');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique('code', 'uk_edu_tenants_code');
            $table->index('status', 'idx_edu_tenants_status');
            $table->index('name', 'idx_edu_tenants_name');
            $table->index('deleted_at', 'idx_edu_tenants_deleted_at');
        });

        Schema::create('edu_campuses', static function (Blueprint $table): void {
            $table->comment('Education campuses');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->string('name', 120)->comment('Campus name');
            $table->string('code', 64)->comment('Campus code inside tenant');
            $table->string('contact_name', 60)->nullable()->comment('Campus contact name');
            $table->string('contact_phone', 30)->nullable()->comment('Campus contact phone');
            $table->string('address', 255)->nullable()->comment('Campus address');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->json('settings')->nullable()->comment('Campus settings JSON');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code'], 'uk_edu_campuses_tenant_code');
            $table->index(['tenant_id', 'status'], 'idx_edu_campuses_tenant_status');
            $table->index(['tenant_id', 'name'], 'idx_edu_campuses_tenant_name');
            $table->index('deleted_at', 'idx_edu_campuses_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_campuses');
        Schema::dropIfExists('edu_tenants');
    }
};
```

Column rules:

```text
edu_tenants.id: bigint unsigned primary key, auto increment, not null.
edu_tenants.name: varchar(120), not null.
edu_tenants.code: varchar(64), not null, globally unique.
edu_tenants.short_name: varchar(60), nullable.
edu_tenants.contact_name: varchar(60), nullable.
edu_tenants.contact_phone: varchar(30), nullable.
edu_tenants.status: varchar(20), not null, default enabled, enum values enabled/disabled in service layer.
edu_tenants.settings: json, nullable.
edu_tenants.enabled_at: timestamp, nullable.
edu_tenants.disabled_at: timestamp, nullable.
edu_tenants.created_by: bigint unsigned, nullable.
edu_tenants.updated_by: bigint unsigned, nullable.
edu_tenants.created_at: timestamp, nullable.
edu_tenants.updated_at: timestamp, nullable.
edu_tenants.deleted_at: timestamp, nullable.

edu_campuses.id: bigint unsigned primary key, auto increment, not null.
edu_campuses.tenant_id: bigint unsigned, not null, service-level reference to edu_tenants.id.
edu_campuses.name: varchar(120), not null.
edu_campuses.code: varchar(64), not null, unique inside tenant_id.
edu_campuses.contact_name: varchar(60), nullable.
edu_campuses.contact_phone: varchar(30), nullable.
edu_campuses.address: varchar(255), nullable.
edu_campuses.status: varchar(20), not null, default enabled, enum values enabled/disabled in service layer.
edu_campuses.settings: json, nullable.
edu_campuses.created_by: bigint unsigned, nullable.
edu_campuses.updated_by: bigint unsigned, nullable.
edu_campuses.created_at: timestamp, nullable.
edu_campuses.updated_at: timestamp, nullable.
edu_campuses.deleted_at: timestamp, nullable.
```

Tenant/campus isolation fields:

```text
edu_tenants is platform-level data and has no tenant_id.
edu_campuses.tenant_id is mandatory and must always be filtered from TenantContext for tenant-admin APIs.
edu_campuses has no campus_id because the campus record itself is the campus scope root.
```

Unique indexes:

```text
uk_edu_tenants_code (code)
uk_edu_campuses_tenant_code (tenant_id, code)
```

Ordinary indexes:

```text
idx_edu_tenants_status (status)
idx_edu_tenants_name (name)
idx_edu_tenants_deleted_at (deleted_at)
idx_edu_campuses_tenant_status (tenant_id, status)
idx_edu_campuses_tenant_name (tenant_id, name)
idx_edu_campuses_deleted_at (deleted_at)
```

Foreign-key policy:

```text
No physical foreign keys in F01. Campus tenant existence is enforced in CampusService before create/update.
Reason: MineAdmin foundation tables use service-layer checks and this keeps tenant recovery and import operations simpler for small institutions.
```

Rollback behavior:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate:rollback --step=1
```

Expected:

```text
edu_campuses is dropped before edu_tenants.
```

## MineAdmin Backend Module Design

### Result Code

Modify `mineadmin-education-saas/backend/app/Http/Common/ResultCode.php`:

```php
#[Message('result.conflict')]
case CONFLICT = 409;
```

Placement:

```text
Add CONFLICT after METHOD_NOT_ALLOWED and before NOT_ACCEPTABLE.
Do not rename existing UNPROCESSABLE_ENTITY even though its message key is result.conflict in the generated MineAdmin code.
```

### Enums

Create `mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/TenantStatus.php`:

```php
<?php

declare(strict_types=1);

namespace App\Model\Enums\Education\Foundation;

enum TenantStatus: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
}
```

Create `mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/CampusStatus.php`:

```php
<?php

declare(strict_types=1);

namespace App\Model\Enums\Education\Foundation;

enum CampusStatus: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
}
```

### Tenant Context

Create `mineadmin-education-saas/backend/app/Contract/Education/Foundation/TenantContextInterface.php`:

```php
<?php

declare(strict_types=1);

namespace App\Contract\Education\Foundation;

interface TenantContextInterface
{
    public function id(): int;
}
```

Create `mineadmin-education-saas/backend/app/Service/Education/Foundation/TenantContext.php`:

```php
<?php

declare(strict_types=1);

namespace App\Service\Education\Foundation;

use App\Contract\Education\Foundation\TenantContextInterface;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use Hyperf\HttpServer\Contract\RequestInterface;

final class TenantContext implements TenantContextInterface
{
    public function __construct(
        private readonly RequestInterface $request
    ) {}

    public function id(): int
    {
        $tenantId = (int) $this->request->header('X-Tenant-Id', 0);
        if ($tenantId <= 0) {
            throw new BusinessException(
                ResultCode::UNPROCESSABLE_ENTITY,
                'X-Tenant-Id header is required',
                ['header' => 'X-Tenant-Id']
            );
        }
        return $tenantId;
    }
}
```

F02 follow-up rule:

```text
F02 must replace raw header trust with current-user profile and role scope validation while keeping TenantContextInterface stable.
```

### Models

Create `mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationTenant.php`:

```php
<?php

declare(strict_types=1);

namespace App\Model\Education\Foundation;

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationTenant extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_tenants';

    protected array $fillable = [
        'id',
        'name',
        'code',
        'short_name',
        'contact_name',
        'contact_phone',
        'status',
        'settings',
        'enabled_at',
        'disabled_at',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'settings' => 'array',
        'enabled_at' => 'datetime',
        'disabled_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
```

Create `mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationCampus.php`:

```php
<?php

declare(strict_types=1);

namespace App\Model\Education\Foundation;

use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationCampus extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_campuses';

    protected array $fillable = [
        'id',
        'tenant_id',
        'name',
        'code',
        'contact_name',
        'contact_phone',
        'address',
        'status',
        'settings',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'settings' => 'array',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(EducationTenant::class, 'tenant_id', 'id');
    }
}
```

### Repositories

Create `mineadmin-education-saas/backend/app/Repository/Education/Foundation/TenantRepository.php`:

```php
<?php

declare(strict_types=1);

namespace App\Repository\Education\Foundation;

use App\Model\Education\Foundation\EducationTenant;
use App\Repository\IRepository;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationTenant>
 */
final class TenantRepository extends IRepository
{
    public function __construct(
        protected readonly EducationTenant $model
    ) {}

    public function handleSearch(Builder $query, array $params): Builder
    {
        return $query
            ->when(isset($params['keyword']) && $params['keyword'] !== '', static function (Builder $query) use ($params): void {
                $query->where(static function (Builder $query) use ($params): void {
                    $keyword = '%' . $params['keyword'] . '%';
                    $query->where('name', 'like', $keyword)
                        ->orWhere('short_name', 'like', $keyword)
                        ->orWhere('code', 'like', $keyword)
                        ->orWhere('contact_phone', 'like', $keyword);
                });
            })
            ->when(isset($params['status']) && $params['status'] !== '', static function (Builder $query) use ($params): void {
                $query->where('status', $params['status']);
            })
            ->orderByDesc('id');
    }

    public function existsByCode(string $code, ?int $ignoreId = null): bool
    {
        return $this->getQuery()
            ->where('code', $code)
            ->when($ignoreId !== null, static fn (Builder $query) => $query->where('id', '<>', $ignoreId))
            ->exists();
    }
}
```

Create `mineadmin-education-saas/backend/app/Repository/Education/Foundation/CampusRepository.php`:

```php
<?php

declare(strict_types=1);

namespace App\Repository\Education\Foundation;

use App\Model\Education\Foundation\EducationCampus;
use App\Repository\IRepository;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationCampus>
 */
final class CampusRepository extends IRepository
{
    public function __construct(
        protected readonly EducationCampus $model
    ) {}

    public function handleSearch(Builder $query, array $params): Builder
    {
        return $query
            ->where('tenant_id', (int) $params['tenant_id'])
            ->when(isset($params['keyword']) && $params['keyword'] !== '', static function (Builder $query) use ($params): void {
                $query->where(static function (Builder $query) use ($params): void {
                    $keyword = '%' . $params['keyword'] . '%';
                    $query->where('name', 'like', $keyword)
                        ->orWhere('code', 'like', $keyword)
                        ->orWhere('contact_phone', 'like', $keyword)
                        ->orWhere('address', 'like', $keyword);
                });
            })
            ->when(isset($params['status']) && $params['status'] !== '', static function (Builder $query) use ($params): void {
                $query->where('status', $params['status']);
            })
            ->orderByDesc('id');
    }

    public function existsByTenantCode(int $tenantId, string $code, ?int $ignoreId = null): bool
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->when($ignoreId !== null, static fn (Builder $query) => $query->where('id', '<>', $ignoreId))
            ->exists();
    }

    public function findInTenant(int $tenantId, int $id): ?EducationCampus
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->whereKey($id)
            ->first();
    }
}
```

### Services

Create `mineadmin-education-saas/backend/app/Service/Education/Foundation/TenantService.php`.

Required methods:

```text
page(array $params, int $page, int $pageSize): array
createTenant(array $data): EducationTenant
updateTenant(int $id, array $data): EducationTenant
changeStatus(int $id, string $status, ?int $operatorId): EducationTenant
deleteTenant(int $id): void
```

Business rules:

```text
createTenant:
- code must be globally unique.
- status defaults to enabled.
- enabled_at is set when status is enabled.
- disabled_at is set when status is disabled.
- duplicate code throws BusinessException(ResultCode::CONFLICT, 'tenant code already exists', ['code' => <code>]).

updateTenant:
- tenant must exist.
- code must be globally unique excluding current tenant id.
- duplicate code throws BusinessException with code 409.

changeStatus:
- status must be enabled or disabled.
- enabled sets enabled_at to current time.
- disabled sets disabled_at to current time.

deleteTenant:
- reject delete when edu_campuses has non-deleted rows for tenant_id.
- rejection message is tenant has campuses.
```

Create `mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusService.php`.

Required methods:

```text
page(array $params, int $page, int $pageSize): array
createCampus(int $tenantId, array $data): EducationCampus
updateCampus(int $tenantId, int $id, array $data): EducationCampus
changeStatus(int $tenantId, int $id, string $status, ?int $operatorId): EducationCampus
deleteCampus(int $tenantId, int $id): void
```

Business rules:

```text
createCampus:
- tenant_id must exist in edu_tenants.
- code must be unique inside tenant_id.
- status defaults to enabled.
- created campus row must always include tenant_id from TenantContext, not request body.

updateCampus:
- campus must exist inside tenant_id from TenantContext.
- code must be unique inside tenant_id excluding current campus id.
- request body tenant_id is ignored.

changeStatus:
- campus must exist inside tenant_id from TenantContext.
- status must be enabled or disabled.

deleteCampus:
- campus must exist inside tenant_id from TenantContext.
- soft delete only.
```

### Requests

All request classes extend `Hyperf\Validation\Request\FormRequest`, use `NoAuthorizeTrait`, and return `true` from `authorize()`.

Create `TenantPageRequest` rules:

```php
return [
    'page' => 'sometimes|integer|min:1',
    'page_size' => 'sometimes|integer|min:1|max:200',
    'keyword' => 'sometimes|string|max:120',
    'status' => 'sometimes|in:enabled,disabled',
];
```

Create `TenantSaveRequest` rules:

```php
return [
    'name' => 'required|string|max:120',
    'code' => ['required', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_\\-]{1,63}$/'],
    'short_name' => 'sometimes|nullable|string|max:60',
    'contact_name' => 'sometimes|nullable|string|max:60',
    'contact_phone' => 'sometimes|nullable|string|max:30',
    'status' => 'sometimes|in:enabled,disabled',
    'settings' => 'sometimes|nullable|array',
];
```

Create `TenantStatusRequest` rules:

```php
return [
    'status' => 'required|in:enabled,disabled',
];
```

Create `CampusPageRequest` rules:

```php
return [
    'page' => 'sometimes|integer|min:1',
    'page_size' => 'sometimes|integer|min:1|max:200',
    'keyword' => 'sometimes|string|max:120',
    'status' => 'sometimes|in:enabled,disabled',
];
```

Create `CampusSaveRequest` rules:

```php
return [
    'name' => 'required|string|max:120',
    'code' => ['required', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_\\-]{1,63}$/'],
    'contact_name' => 'sometimes|nullable|string|max:60',
    'contact_phone' => 'sometimes|nullable|string|max:30',
    'address' => 'sometimes|nullable|string|max:255',
    'status' => 'sometimes|in:enabled,disabled',
    'settings' => 'sometimes|nullable|array',
];
```

Create `CampusStatusRequest` rules:

```php
return [
    'status' => 'required|in:enabled,disabled',
];
```

Validation failure response shape:

```json
{
  "code": 422,
  "message": "status must be one of enabled, disabled",
  "data": []
}
```

### Controllers

Controller common middleware:

```php
#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: OperationMiddleware::class, priority: 98)]
```

Create `TenantController` endpoints:

```text
GET /admin/education/foundation/tenants/page
POST /admin/education/foundation/tenants
PUT /admin/education/foundation/tenants/{id}
PUT /admin/education/foundation/tenants/{id}/status
DELETE /admin/education/foundation/tenants/{id}
```

Permission codes:

```text
education:foundation:tenant:page
education:foundation:tenant:create
education:foundation:tenant:update
education:foundation:tenant:status
education:foundation:tenant:delete
```

Controller response rules:

```text
page returns success(service->page(request all, current page, page size)).
create returns success(created tenant id).
update returns success(updated tenant id).
status returns success(updated tenant status).
delete returns success().
```

Create `CampusController` endpoints:

```text
GET /admin/education/foundation/campuses/page
POST /admin/education/foundation/campuses
PUT /admin/education/foundation/campuses/{id}
PUT /admin/education/foundation/campuses/{id}/status
DELETE /admin/education/foundation/campuses/{id}
```

Permission codes:

```text
education:foundation:campus:page
education:foundation:campus:create
education:foundation:campus:update
education:foundation:campus:status
education:foundation:campus:delete
```

Campus controller tenant rule:

```text
Every campus method calls TenantContextInterface::id().
Campus request body must not be trusted for tenant_id.
```

### Schemas

Create `TenantSchema` fields:

```text
id int
name string
code string
short_name string nullable
contact_name string nullable
contact_phone string nullable
status string
settings array nullable
enabled_at string nullable
disabled_at string nullable
created_by int nullable
updated_by int nullable
created_at string nullable
updated_at string nullable
```

Create `CampusSchema` fields:

```text
id int
tenant_id int
name string
code string
contact_name string nullable
contact_phone string nullable
address string nullable
status string
settings array nullable
created_by int nullable
updated_by int nullable
created_at string nullable
updated_at string nullable
```

## API Contract

### Tenant Page

```text
GET /admin/education/foundation/tenants/page
Permission: education:foundation:tenant:page
Caller: platform admin
Headers: Authorization: Bearer <token>
Isolation: platform-only, no tenant filter
Audit: read operation, no operation audit row required
```

Request query:

```json
{
  "page": 1,
  "page_size": 20,
  "keyword": "demo",
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
        "id": 1,
        "name": "示例培训机构",
        "code": "demo",
        "short_name": "示例机构",
        "contact_name": "王老师",
        "contact_phone": "13800000000",
        "status": "enabled",
        "created_at": "2026-06-10 09:00:00"
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
  "message": "status must be one of enabled, disabled",
  "data": []
}
```

Business failure:

```json
{
  "code": 403,
  "message": "result.forbidden",
  "data": []
}
```

### Tenant Create

```text
POST /admin/education/foundation/tenants
Permission: education:foundation:tenant:create
Caller: platform admin
Headers: Authorization: Bearer <token>
Isolation: platform-only, no tenant filter
Audit: OperationMiddleware records write operation
```

Request body:

```json
{
  "name": "示例培训机构",
  "code": "demo",
  "short_name": "示例机构",
  "contact_name": "王老师",
  "contact_phone": "13800000000",
  "status": "enabled",
  "settings": {
    "timezone": "Asia/Shanghai"
  }
}
```

Success response:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1
  }
}
```

Validation failure:

```json
{
  "code": 422,
  "message": "code format is invalid",
  "data": []
}
```

Business failure:

```json
{
  "code": 409,
  "message": "tenant code already exists",
  "data": {
    "code": "demo"
  }
}
```

### Tenant Update

```text
PUT /admin/education/foundation/tenants/{id}
Permission: education:foundation:tenant:update
Caller: platform admin
Headers: Authorization: Bearer <token>
Isolation: platform-only, no tenant filter
Audit: OperationMiddleware records write operation
```

Request body:

```json
{
  "name": "示例培训机构上海校区总部",
  "code": "demo",
  "short_name": "示例总部",
  "contact_name": "李老师",
  "contact_phone": "13900000000",
  "status": "enabled"
}
```

Success response:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1
  }
}
```

Business failure:

```json
{
  "code": 409,
  "message": "tenant code already exists",
  "data": {
    "code": "demo"
  }
}
```

### Tenant Status

```text
PUT /admin/education/foundation/tenants/{id}/status
Permission: education:foundation:tenant:status
Caller: platform admin
Headers: Authorization: Bearer <token>
Isolation: platform-only, no tenant filter
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
    "id": 1,
    "status": "disabled"
  }
}
```

### Campus Page

```text
GET /admin/education/foundation/campuses/page
Permission: education:foundation:campus:page
Caller: tenant admin
Headers: Authorization: Bearer <token>, X-Tenant-Id: 1
Isolation: always filter edu_campuses.tenant_id from TenantContext
Audit: read operation, no operation audit row required
```

Request query:

```json
{
  "page": 1,
  "page_size": 20,
  "keyword": "main",
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
        "id": 1,
        "tenant_id": 1,
        "name": "默认校区",
        "code": "main",
        "contact_name": "赵老师",
        "contact_phone": "021-00000000",
        "address": "上海市徐汇区示例路 1 号",
        "status": "enabled"
      }
    ],
    "total": 1
  }
}
```

Missing tenant header failure:

```json
{
  "code": 422,
  "message": "X-Tenant-Id header is required",
  "data": {
    "header": "X-Tenant-Id"
  }
}
```

### Campus Create

```text
POST /admin/education/foundation/campuses
Permission: education:foundation:campus:create
Caller: tenant admin
Headers: Authorization: Bearer <token>, X-Tenant-Id: 1
Isolation: created tenant_id is from TenantContext, not request body
Audit: OperationMiddleware records write operation
```

Request body:

```json
{
  "name": "默认校区",
  "code": "main",
  "contact_name": "赵老师",
  "contact_phone": "021-00000000",
  "address": "上海市徐汇区示例路 1 号",
  "status": "enabled"
}
```

Success response:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "tenant_id": 1
  }
}
```

Business failure:

```json
{
  "code": 409,
  "message": "campus code already exists",
  "data": {
    "tenant_id": 1,
    "code": "main"
  }
}
```

### Campus Update

```text
PUT /admin/education/foundation/campuses/{id}
Permission: education:foundation:campus:update
Caller: tenant admin
Headers: Authorization: Bearer <token>, X-Tenant-Id: 1
Isolation: update only when edu_campuses.tenant_id equals TenantContext id
Audit: OperationMiddleware records write operation
```

Request body:

```json
{
  "name": "徐汇校区",
  "code": "main",
  "contact_name": "赵老师",
  "contact_phone": "021-00000000",
  "address": "上海市徐汇区示例路 2 号",
  "status": "enabled"
}
```

Cross-tenant failure:

```json
{
  "code": 404,
  "message": "campus not found in current tenant",
  "data": {
    "id": 2
  }
}
```

### Campus Status

```text
PUT /admin/education/foundation/campuses/{id}/status
Permission: education:foundation:campus:status
Caller: tenant admin
Headers: Authorization: Bearer <token>, X-Tenant-Id: 1
Isolation: status update only inside current tenant
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
    "id": 1,
    "tenant_id": 1,
    "status": "disabled"
  }
}
```

## PC Admin Page Tasks

### Tenant API Client

Create `mineadmin-education-saas/admin-web/src/api/education/foundation/tenant.ts`.

Types:

```ts
export interface TenantRecord {
  id: number
  name: string
  code: string
  short_name?: string
  contact_name?: string
  contact_phone?: string
  status: 'enabled' | 'disabled'
  created_at?: string
  updated_at?: string
}

export interface TenantPageParams {
  page?: number
  page_size?: number
  keyword?: string
  status?: 'enabled' | 'disabled'
}

export interface TenantSavePayload {
  name: string
  code: string
  short_name?: string
  contact_name?: string
  contact_phone?: string
  status?: 'enabled' | 'disabled'
  settings?: Record<string, unknown>
}
```

Methods:

```text
pageTenants(params: TenantPageParams)
createTenant(data: TenantSavePayload)
updateTenant(id: number, data: TenantSavePayload)
updateTenantStatus(id: number, status: 'enabled' | 'disabled')
deleteTenant(id: number)
```

### Campus API Client

Create `mineadmin-education-saas/admin-web/src/api/education/foundation/campus.ts`.

Types:

```ts
export interface CampusRecord {
  id: number
  tenant_id: number
  name: string
  code: string
  contact_name?: string
  contact_phone?: string
  address?: string
  status: 'enabled' | 'disabled'
  created_at?: string
  updated_at?: string
}

export interface CampusPageParams {
  page?: number
  page_size?: number
  keyword?: string
  status?: 'enabled' | 'disabled'
}

export interface CampusSavePayload {
  name: string
  code: string
  contact_name?: string
  contact_phone?: string
  address?: string
  status?: 'enabled' | 'disabled'
  settings?: Record<string, unknown>
}
```

Methods:

```text
pageCampuses(params: CampusPageParams)
createCampus(data: CampusSavePayload)
updateCampus(id: number, data: CampusSavePayload)
updateCampusStatus(id: number, status: 'enabled' | 'disabled')
deleteCampus(id: number)
```

Tenant header rule:

```text
Campus API client reads current tenant id from the shared admin store key introduced in F05.
Until F05 exists, the page passes X-Tenant-Id from a local tenant selector state used only in F01 verification.
```

### Router

Modify `mineadmin-education-saas/admin-web/src/router/modules/education.ts`.

Routes:

```text
Route path: /education/foundation/tenants
Route name: EducationFoundationTenantList
Menu: 教务 SaaS / 基础设置 / 机构
Permission: education:foundation:tenant:page
Component: admin-web/src/views/education/foundation/TenantList.vue

Route path: /education/foundation/campuses
Route name: EducationFoundationCampusList
Menu: 教务 SaaS / 基础设置 / 校区
Permission: education:foundation:campus:page
Component: admin-web/src/views/education/foundation/CampusList.vue
```

### Tenant List Page

Create `mineadmin-education-saas/admin-web/src/views/education/foundation/TenantList.vue`.

Search fields:

```text
keyword: input, hint text 机构名称/编码/手机号
status: select, options enabled=启用, disabled=停用
```

Table columns:

```text
name, code, short_name, contact_name, contact_phone, status, created_at, actions
```

Actions:

```text
Create button permission: education:foundation:tenant:create
Edit button permission: education:foundation:tenant:update
Enable/disable button permission: education:foundation:tenant:status
Delete button permission: education:foundation:tenant:delete
```

States:

```text
loading: table loading true while pageTenants pending.
empty: show MineAdmin empty table state when list length is 0.
error: show MineAdmin message with API message and keep last successful list.
permission: hide action button when user lacks permission code.
status flow: enabled row shows disable action; disabled row shows enable action.
```

### Tenant Form

Create `mineadmin-education-saas/admin-web/src/views/education/foundation/components/TenantForm.vue`.

Fields:

```text
name: required input max 120
code: required input max 64, regex lowercase letter start, lowercase letters/digits/underscore/hyphen
short_name: optional input max 60
contact_name: optional input max 60
contact_phone: optional input max 30
status: segmented/select enabled or disabled, default enabled
```

Submit:

```text
create mode calls createTenant.
edit mode calls updateTenant.
validation failure keeps modal open and displays field message.
success closes modal and refreshes list.
```

### Campus List Page

Create `mineadmin-education-saas/admin-web/src/views/education/foundation/CampusList.vue`.

Search fields:

```text
tenant selector: required in F01 local verification state until F05 shared tenant selector exists
keyword: input, hint text 校区名称/编码/手机号/地址
status: select, options enabled=启用, disabled=停用
```

Table columns:

```text
name, code, contact_name, contact_phone, address, status, created_at, actions
```

Actions:

```text
Create button permission: education:foundation:campus:create
Edit button permission: education:foundation:campus:update
Enable/disable button permission: education:foundation:campus:status
Delete button permission: education:foundation:campus:delete
```

States:

```text
loading: table loading true while pageCampuses pending.
empty: no campuses message when list length is 0.
missing tenant: disable search/create buttons until a tenant id is selected.
error: show MineAdmin message with API message and keep last successful list.
permission: hide action button when user lacks permission code.
status flow: enabled row shows disable action; disabled row shows enable action.
```

### Campus Form

Create `mineadmin-education-saas/admin-web/src/views/education/foundation/components/CampusForm.vue`.

Fields:

```text
name: required input max 120
code: required input max 64, regex lowercase letter start, lowercase letters/digits/underscore/hyphen
contact_name: optional input max 60
contact_phone: optional input max 30
address: optional textarea max 255
status: segmented/select enabled or disabled, default enabled
```

Submit:

```text
create mode calls createCampus with X-Tenant-Id from page tenant selector.
edit mode calls updateCampus with X-Tenant-Id from page tenant selector.
validation failure keeps modal open and displays field message.
success closes modal and refreshes list.
```

Verification:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- TenantList
pnpm test -- CampusList
pnpm build
```

Expected:

```text
Lint passes.
TenantList tests pass.
CampusList tests pass.
Build succeeds.
```

## Teacher / Guardian Mobile Page Tasks

This module has no teacher or guardian page because it is platform and tenant administration only.

Mobile verification:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm build:h5
```

Expected:

```text
H5 build succeeds and no teacher or guardian route is added by F01.
```

Role isolation rule:

```text
Teacher and guardian users must not call F01 admin APIs. TenantCampusPermissionTest asserts teacher-like users receive 403 from tenant and campus admin endpoints.
```

## Test Plan

Migration/schema test:

```text
Test file: backend/tests/Feature/Education/Foundation/TenantCampusMigrationTest.php
Case: test_tenant_and_campus_tables_have_required_columns
Assert:
- edu_tenants has columns id, name, code, status, settings, created_by, updated_by, deleted_at
- edu_campuses has columns id, tenant_id, name, code, status, settings, created_by, updated_by, deleted_at
- uk_edu_tenants_code exists
- uk_edu_campuses_tenant_code exists
```

Model/repository tests:

```text
Test file: backend/tests/Unit/Education/Foundation/TenantRepositoryTest.php
Case: test_exists_by_code_ignores_current_id
Assert:
- existsByCode('demo') is true after creating tenant code demo
- existsByCode('demo', $tenant->id) is false

Test file: backend/tests/Unit/Education/Foundation/CampusRepositoryTest.php
Case: test_find_in_tenant_rejects_other_tenant
Assert:
- findInTenant(tenantA, campusA) returns campus
- findInTenant(tenantB, campusA) returns null
```

Service unit tests:

```text
Test file: backend/tests/Unit/Education/Foundation/TenantServiceTest.php
Case: test_duplicate_tenant_code_throws_conflict
Assert:
- second createTenant with code demo throws BusinessException
- exception response code is 409
- exception response data.code is demo

Case: test_delete_tenant_with_campus_is_rejected
Assert:
- deleteTenant throws BusinessException
- exception message is tenant has campuses

Test file: backend/tests/Unit/Education/Foundation/CampusServiceTest.php
Case: test_create_campus_uses_context_tenant_id
Assert:
- campus tenant_id equals TenantContext id
- request body tenant_id is ignored

Case: test_duplicate_campus_code_is_scoped_by_tenant
Assert:
- same code can exist in different tenant ids
- same code in same tenant throws BusinessException with response code 409
```

Controller/API feature tests:

```text
Test file: backend/tests/Feature/Education/Foundation/TenantAdminApiTest.php
Case: test_platform_admin_can_create_update_status_and_page_tenant
Assert:
- POST /admin/education/foundation/tenants returns code 200
- edu_tenants contains code demo
- PUT /admin/education/foundation/tenants/{id} changes contact_name
- PUT /admin/education/foundation/tenants/{id}/status changes status to disabled
- GET /admin/education/foundation/tenants/page includes code demo

Case: test_duplicate_tenant_code_returns_conflict
Assert:
- response code is 409
- response message is tenant code already exists
- response data.code is demo
```

```text
Test file: backend/tests/Feature/Education/Foundation/CampusAdminApiTest.php
Case: test_tenant_admin_can_create_update_status_and_page_campus
Assert:
- POST /admin/education/foundation/campuses with X-Tenant-Id 1 returns code 200
- edu_campuses row tenant_id is 1
- PUT /admin/education/foundation/campuses/{id} changes address
- PUT /admin/education/foundation/campuses/{id}/status changes status to disabled
- GET /admin/education/foundation/campuses/page includes code main

Case: test_missing_tenant_header_returns_validation_failure
Assert:
- response code is 422
- response message is X-Tenant-Id header is required
```

Tenant isolation test:

```text
Test file: backend/tests/Feature/Education/Foundation/TenantCampusIsolationTest.php
Case: test_tenant_admin_cannot_page_other_tenant_campus
Assert:
- campus for tenant 1 appears when X-Tenant-Id is 1
- campus for tenant 2 does not appear when X-Tenant-Id is 1
- total is 1

Case: test_tenant_admin_cannot_update_other_tenant_campus
Assert:
- update tenant 2 campus with X-Tenant-Id 1 returns code 404
- original tenant 2 campus row remains unchanged
```

Campus scope test:

```text
Test file: backend/tests/Feature/Education/Foundation/TenantCampusIsolationTest.php
Case: test_campus_create_ignores_request_body_tenant_id
Assert:
- request body tenant_id 999 is ignored
- created row tenant_id equals X-Tenant-Id 1
```

Permission test:

```text
Test file: backend/tests/Feature/Education/Foundation/TenantCampusPermissionTest.php
Case: test_user_without_tenant_permission_cannot_access_tenant_page
Assert:
- GET /admin/education/foundation/tenants/page returns code 403

Case: test_user_without_campus_permission_cannot_create_campus
Assert:
- POST /admin/education/foundation/campuses returns code 403
```

Audit middleware test:

```text
Test file: backend/tests/Feature/Education/Foundation/TenantCampusPermissionTest.php
Case: test_write_controllers_include_operation_middleware
Assert:
- TenantController class has OperationMiddleware attribute
- CampusController class has OperationMiddleware attribute
```

PC API/page tests:

```text
Test file: admin-web/src/views/education/foundation/__tests__/TenantList.spec.ts
Case: renders tenant actions_by_permission
Assert:
- create button renders when permission education:foundation:tenant:create exists
- edit button is hidden when permission education:foundation:tenant:update is absent
- disabled status row shows enable action

Test file: admin-web/src/views/education/foundation/__tests__/CampusList.spec.ts
Case: blocks campus actions_without_tenant
Assert:
- create button is disabled when no tenant id selected
- pageCampuses receives X-Tenant-Id when tenant id selected
- enabled status row shows disable action
```

Mobile build verification:

```text
Command: cd mineadmin-education-saas/mobile-uniapp && pnpm build:h5
Assert:
- command exits 0
- no F01 teacher/guardian route is added
```

## Execution Commands

Backend:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter TenantCampusMigrationTest
composer test -- --filter TenantRepositoryTest
composer test -- --filter CampusRepositoryTest
composer test -- --filter TenantServiceTest
composer test -- --filter CampusServiceTest
composer test -- --filter TenantAdminApiTest
composer test -- --filter CampusAdminApiTest
composer test -- --filter TenantCampusIsolationTest
composer test -- --filter TenantCampusPermissionTest
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
Migration creates edu_tenants and edu_campuses.
Migration/schema tests pass.
Repository tests pass.
Service tests pass.
API tests pass.
Tenant isolation and campus scope tests pass.
Permission and operation middleware tests pass.
Code style dry run passes.
Static analysis passes.
```

PC:

```bash
cd mineadmin-education-saas/admin-web
pnpm install --frozen-lockfile
pnpm lint
pnpm test -- TenantList
pnpm test -- CampusList
pnpm build
```

Expected:

```text
Install exits 0.
Lint exits 0.
TenantList test exits 0.
CampusList test exits 0.
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

Full F01 gate:

```bash
cd mineadmin-education-saas
make up
cd backend
php bin/hyperf.php migrate
composer test -- --filter Education\\\\Foundation
composer cs-fix -- --dry-run
composer analyse
cd ../admin-web
pnpm lint && pnpm test -- TenantList && pnpm test -- CampusList && pnpm build
cd ../mobile-uniapp
pnpm build:h5
```

Expected:

```text
All commands exit 0.
```

## Acceptance Gate

```text
- edu_tenants and edu_campuses exist with all planned columns and indexes.
- ResultCode has CONFLICT = 409.
- Tenant APIs return MineAdmin result shape.
- Campus APIs return MineAdmin result shape.
- Duplicate tenant code returns code 409.
- Duplicate campus code inside the same tenant returns code 409.
- Same campus code in two different tenants is allowed.
- Campus page always filters by X-Tenant-Id in F01.
- Campus create ignores tenant_id from request body.
- Tenant admin cannot read or update another tenant's campus.
- Write controllers include OperationMiddleware.
- PC tenant page has API client, route, list, form, permission buttons, loading, empty, and error states.
- PC campus page has API client, route, list, form, permission buttons, tenant selector state, loading, empty, and error states.
- F01 adds no teacher or guardian mobile page.
- Backend tests pass.
- PC tests and build pass.
- Mobile H5 build still passes.
```

## Tasks

### Task 1: Add 409 Result Code

**Files:**

- Modify: `mineadmin-education-saas/backend/app/Http/Common/ResultCode.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/TenantServiceTest.php`

- [x] **Step 1: Add CONFLICT enum case**

Add:

```php
#[Message('result.conflict')]
case CONFLICT = 409;
```

Expected:

```text
ResultCode::CONFLICT->value is 409.
```

- [x] **Step 2: Run syntax check**

Run:

```bash
cd mineadmin-education-saas/backend
php -l app/Http/Common/ResultCode.php
```

Expected:

```text
No syntax errors detected in app/Http/Common/ResultCode.php
```

### Task 2: Create Migration, Enums, and Models

**Files:**

- Create: `mineadmin-education-saas/backend/databases/migrations/2026_06_10_000100_create_education_tenant_campus_tables.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/TenantStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/CampusStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationTenant.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationCampus.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/TenantCampusMigrationTest.php`

- [x] **Step 1: Create migration file**

Use the full migration from `Database Migration Design`.

- [x] **Step 2: Create enums**

Use the enum files from `MineAdmin Backend Module Design`.

- [x] **Step 3: Create models**

Use the model files from `MineAdmin Backend Module Design`.

- [x] **Step 4: Run migration**

Run:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
```

Expected:

```text
Migration table records 2026_06_10_000100_create_education_tenant_campus_tables.
edu_tenants exists.
edu_campuses exists.
```

- [x] **Step 5: Write migration test**

Create `TenantCampusMigrationTest.php` with assertions listed in `Test Plan`.

- [x] **Step 6: Run migration test**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter TenantCampusMigrationTest
```

Expected:

```text
TenantCampusMigrationTest passes.
```

### Task 3: Create Tenant Context

**Files:**

- Create: `mineadmin-education-saas/backend/app/Contract/Education/Foundation/TenantContextInterface.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Foundation/TenantContext.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/CampusAdminApiTest.php`

- [x] **Step 1: Create contract**

Use the full contract from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create context service**

Use the full `TenantContext` from `MineAdmin Backend Module Design`.

- [x] **Step 3: Run syntax checks**

Run:

```bash
cd mineadmin-education-saas/backend
php -l app/Contract/Education/Foundation/TenantContextInterface.php
php -l app/Service/Education/Foundation/TenantContext.php
```

Expected:

```text
No syntax errors detected.
```

### Task 4: Create Repositories and Services

**Files:**

- Create: `mineadmin-education-saas/backend/app/Repository/Education/Foundation/TenantRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Foundation/CampusRepository.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Foundation/TenantService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusService.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/TenantRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/CampusRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/TenantServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/CampusServiceTest.php`

- [x] **Step 1: Create repositories**

Use the repository files from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create services**

Implement all required service methods and business rules listed in `MineAdmin Backend Module Design`.

- [x] **Step 3: Write repository and service tests**

Create all unit test files listed in `Test Plan`.

- [x] **Step 4: Run unit tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter TenantRepositoryTest
composer test -- --filter CampusRepositoryTest
composer test -- --filter TenantServiceTest
composer test -- --filter CampusServiceTest
```

Expected:

```text
Repository and service tests pass.
```

### Task 5: Create Requests, Schemas, and Controllers

**Files:**

- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/TenantPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/TenantSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/TenantStatusRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/CampusPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/CampusSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/CampusStatusRequest.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Foundation/TenantSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Foundation/CampusSchema.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/TenantController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/CampusController.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/TenantAdminApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/CampusAdminApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/TenantCampusIsolationTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/TenantCampusPermissionTest.php`

- [x] **Step 1: Create request classes**

Use the request validation rules from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create schema classes**

Use the schema fields from `MineAdmin Backend Module Design`.

- [x] **Step 3: Create controllers**

Use the controller endpoints, middleware, permission codes, and response rules from `MineAdmin Backend Module Design`.

- [x] **Step 4: Write feature tests**

Create all API, isolation, permission, and audit middleware tests listed in `Test Plan`.

- [x] **Step 5: Run feature tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter TenantAdminApiTest
composer test -- --filter CampusAdminApiTest
composer test -- --filter TenantCampusIsolationTest
composer test -- --filter TenantCampusPermissionTest
```

Expected:

```text
Tenant, campus, isolation, permission, and operation middleware feature tests pass.
```

### Task 6: Create PC API Clients and Pages

**Files:**

- Create: `mineadmin-education-saas/admin-web/src/api/education/foundation/tenant.ts`
- Create: `mineadmin-education-saas/admin-web/src/api/education/foundation/campus.ts`
- Modify: `mineadmin-education-saas/admin-web/src/router/modules/education.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/TenantList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/CampusList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/TenantForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/CampusForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/TenantList.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/CampusList.spec.ts`

- [x] **Step 1: Create API clients**

Use the TypeScript types and methods from `PC Admin Page Tasks`.

- [x] **Step 2: Add router entries**

Use the route paths, names, menus, permissions, and components from `PC Admin Page Tasks`.

- [x] **Step 3: Create tenant page and form**

Implement all tenant search, table, action, state, and form requirements from `PC Admin Page Tasks`.

- [x] **Step 4: Create campus page and form**

Implement all campus search, table, action, state, tenant selector, and form requirements from `PC Admin Page Tasks`.

- [x] **Step 5: Add PC tests**

Create `TenantList.spec.ts` and `CampusList.spec.ts` with assertions listed in `Test Plan`.

- [x] **Step 6: Run PC verification**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- TenantList
pnpm test -- CampusList
pnpm build
```

Expected:

```text
Lint, tests, and build pass.
```

### Task 7: Run F01 Final Gate

**Files:**

- Verify: all F01 backend, PC, and mobile paths listed in `File Structure`.

- [x] **Step 1: Run backend final gate**

Run:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter Education\\\\Foundation
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
Migration and all Education Foundation tests pass.
Code style dry run passes.
Static analysis passes.
```

- [x] **Step 2: Run PC final gate**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- TenantList
pnpm test -- CampusList
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

- [x] **Step 4: Commit F01**

Run:

```bash
cd mineadmin-education-saas
git add backend admin-web mobile-uniapp
git commit -m "feat: add tenant and campus foundation"
```

Expected:

```text
Commit succeeds with F01 backend, PC, and verification changes.
```

## Self-Review

- Spec coverage: F01 covers tenant and campus migration, MineAdmin backend layers, API contracts, PC pages, tests, commands, and acceptance gates.
- MineAdmin fit: Paths use MineAdmin 3.x `databases/migrations`, `app/Http/Admin/Controller`, `app/Http/Admin/Request`, `app/Repository/IRepository`, `app/Service/IService`, `app/Schema`, middleware attributes, and MineAdmin result shape.
- Tenant isolation: Campus reads and writes always use `TenantContextInterface::id()` and ignore request body tenant_id.
- Permission design: Permission codes are declared on every endpoint and tested with forbidden access cases.
- Audit behavior: F01 write controllers use existing MineAdmin `OperationMiddleware`; F04 remains responsible for education-domain audit log tables and audit query UI.
- PC fit: Tenant and campus pages include API client methods, route entries, forms, permission buttons, status flows, loading, empty, and error states.
- Mobile fit: F01 has no teacher or guardian page and only verifies mobile build remains passing.
- Readiness: This plan has exact paths, migration columns and indexes, backend layer tasks, API request/response/failure examples, PC tasks, role isolation tests, commands, expected outputs, and acceptance gates, so F01 can be marked `ready`.
