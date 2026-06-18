# MineAdmin Education SaaS F03 Dictionary Feature Flag Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement education dictionaries and feature flags with system defaults, tenant overrides, MineAdmin admin APIs, PC management pages, service-level lookup APIs, and verification tests.

**Architecture:** F03 creates a configuration foundation shared by Foundation and V1-V12. System-owned rows use `owner_key = system`; tenant-owned rows use `owner_key = tenant:<tenant_id>`. Dictionary lookups prefer tenant dictionaries when present and fall back to system defaults; feature flag lookups prefer active tenant flags and fall back to active system flags.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8 JSON columns, Redis-compatible cache services where available, MineAdmin-Vue, Vue3, TypeScript, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. F03 dictionary and feature flag foundation gates have passed.

---

## Scope Check

Included:

- Create dictionary type, dictionary item, and feature flag tables.
- Create system/tenant owner model using `owner_type`, `tenant_id`, and `owner_key`.
- Add seed data for common education dictionaries and V1-V12 feature flags.
- Create enums, models, repositories, services, requests, schemas, and admin controllers.
- Add dictionary lookup service for enabled sorted items.
- Add feature flag lookup service with effective time-window support.
- Add admin APIs for dictionary types, dictionary items, dictionary item lookup, feature flags, and feature flag lookup.
- Add PC pages for dictionary and feature flag management with permission-controlled actions.
- Add tests for migration, seeders, repository filters, service fallback/override rules, API contracts, tenant isolation, permission, operation middleware, PC pages, and mobile build continuity.

Excluded:

- Subscription billing, package pricing, and entitlement charging; V4 or a later commercial module owns billing.
- Education audit log table and audit query page; F04 owns education-domain audit storage and UI. F03 write APIs still use MineAdmin `OperationMiddleware`.
- Shared PC tenant selector shell and menu consolidation; F05 owns final navigation integration.
- Teacher and guardian visible mobile pages; F06 consumes F03 services in mobile context APIs.
- V1-specific business migrations; V1 modules consume dictionaries but do not belong to F03.

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_000300_create_education_dictionary_feature_tables.php
mineadmin-education-saas/backend/databases/seeders/EducationFoundationDictionarySeeder.php
mineadmin-education-saas/backend/databases/seeders/EducationFoundationFeatureFlagSeeder.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/ConfigOwnerType.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/DictionaryStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/FeatureFlagStatus.php
mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationDictType.php
mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationDictItem.php
mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationFeatureFlag.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/ConfigOwnerResolver.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/DictionaryService.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/FeatureFlagService.php
mineadmin-education-saas/backend/app/Repository/Education/Foundation/DictTypeRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Foundation/DictItemRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Foundation/FeatureFlagRepository.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/DictTypePageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/DictTypeSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/DictTypeStatusRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/DictItemPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/DictItemSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/DictItemStatusRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/FeatureFlagPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/FeatureFlagSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/FeatureFlagStatusRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/DictionaryController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/FeatureFlagController.php
mineadmin-education-saas/backend/app/Schema/Education/Foundation/DictTypeSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Foundation/DictItemSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Foundation/FeatureFlagSchema.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/DictionaryFeatureMigrationTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/DictionarySeederTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/FeatureFlagSeederTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/DictionaryAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/FeatureFlagAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/DictionaryFeaturePermissionTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/DictionaryFeatureIsolationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/DictTypeRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/DictItemRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/FeatureFlagRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/DictionaryServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/FeatureFlagServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/ConfigOwnerResolverTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/foundation/dictionary.ts
mineadmin-education-saas/admin-web/src/api/education/foundation/featureFlag.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/DictionaryList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/FeatureFlagList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/DictTypeForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/DictItemForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/FeatureFlagForm.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/DictionaryList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/FeatureFlagList.spec.ts
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
mineadmin-education-saas/backend/databases/migrations/2026_06_10_000300_create_education_dictionary_feature_tables.php
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
        Schema::create('edu_dict_types', static function (Blueprint $table): void {
            $table->comment('Education dictionary types');
            $table->bigIncrements('id');
            $table->string('owner_type', 20)->comment('system or tenant');
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('Tenant id when owner_type is tenant');
            $table->string('owner_key', 80)->comment('system or tenant:<tenant_id>');
            $table->string('code', 80)->comment('Dictionary code');
            $table->string('name', 120)->comment('Dictionary name');
            $table->string('description', 255)->nullable()->comment('Dictionary description');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->boolean('is_locked')->default(false)->comment('Locked system dictionary cannot be deleted');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['owner_key', 'code'], 'uk_edu_dict_types_owner_code');
            $table->index(['tenant_id', 'status'], 'idx_edu_dict_types_tenant_status');
            $table->index(['owner_type', 'status'], 'idx_edu_dict_types_owner_status');
            $table->index('deleted_at', 'idx_edu_dict_types_deleted_at');
        });

        Schema::create('edu_dict_items', static function (Blueprint $table): void {
            $table->comment('Education dictionary items');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dict_type_id')->comment('Dictionary type id');
            $table->string('owner_key', 80)->comment('Copied owner key for lookup');
            $table->string('dict_code', 80)->comment('Copied dictionary code for lookup');
            $table->string('label', 120)->comment('Display label');
            $table->string('value', 120)->comment('Stored value');
            $table->string('color', 40)->nullable()->comment('UI color token');
            $table->json('extra')->nullable()->comment('Extra metadata');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->boolean('is_default')->default(false)->comment('Default item');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['dict_type_id', 'value'], 'uk_edu_dict_items_type_value');
            $table->index(['owner_key', 'dict_code', 'status'], 'idx_edu_dict_items_owner_code_status');
            $table->index(['dict_type_id', 'status', 'sort_order'], 'idx_edu_dict_items_type_status_sort');
            $table->index('deleted_at', 'idx_edu_dict_items_deleted_at');
        });

        Schema::create('edu_feature_flags', static function (Blueprint $table): void {
            $table->comment('Education feature flags');
            $table->bigIncrements('id');
            $table->string('owner_type', 20)->comment('system or tenant');
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('Tenant id when owner_type is tenant');
            $table->string('owner_key', 80)->comment('system or tenant:<tenant_id>');
            $table->string('feature_code', 120)->comment('Feature code');
            $table->string('feature_name', 120)->comment('Feature display name');
            $table->string('description', 255)->nullable()->comment('Feature description');
            $table->boolean('enabled')->default(false)->comment('Feature enabled value');
            $table->json('config')->nullable()->comment('Feature config JSON');
            $table->timestamp('effective_from')->nullable()->comment('Feature effective start time');
            $table->timestamp('effective_to')->nullable()->comment('Feature effective end time');
            $table->string('status', 20)->default('enabled')->comment('row enabled or disabled');
            $table->boolean('is_locked')->default(false)->comment('Locked system flag cannot be deleted');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['owner_key', 'feature_code'], 'uk_edu_feature_flags_owner_feature');
            $table->index(['tenant_id', 'enabled'], 'idx_edu_feature_flags_tenant_enabled');
            $table->index(['owner_type', 'status'], 'idx_edu_feature_flags_owner_status');
            $table->index(['feature_code', 'status'], 'idx_edu_feature_flags_feature_status');
            $table->index('deleted_at', 'idx_edu_feature_flags_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_feature_flags');
        Schema::dropIfExists('edu_dict_items');
        Schema::dropIfExists('edu_dict_types');
    }
};
```

Column rules:

```text
edu_dict_types.id: bigint unsigned primary key, auto increment, not null.
edu_dict_types.owner_type: varchar(20), not null, system or tenant.
edu_dict_types.tenant_id: bigint unsigned, nullable, required when owner_type is tenant.
edu_dict_types.owner_key: varchar(80), not null, system or tenant:<tenant_id>.
edu_dict_types.code: varchar(80), not null.
edu_dict_types.name: varchar(120), not null.
edu_dict_types.description: varchar(255), nullable.
edu_dict_types.status: varchar(20), not null, default enabled.
edu_dict_types.is_locked: boolean, not null, default false.
edu_dict_types.sort_order: int, not null, default 0.
edu_dict_types.created_by: bigint unsigned, nullable.
edu_dict_types.updated_by: bigint unsigned, nullable.
edu_dict_types.created_at: timestamp, nullable.
edu_dict_types.updated_at: timestamp, nullable.
edu_dict_types.deleted_at: timestamp, nullable.

edu_dict_items.id: bigint unsigned primary key, auto increment, not null.
edu_dict_items.dict_type_id: bigint unsigned, not null, service-level reference to edu_dict_types.id.
edu_dict_items.owner_key: varchar(80), not null, copied from dict type.
edu_dict_items.dict_code: varchar(80), not null, copied from dict type.
edu_dict_items.label: varchar(120), not null.
edu_dict_items.value: varchar(120), not null.
edu_dict_items.color: varchar(40), nullable.
edu_dict_items.extra: json, nullable.
edu_dict_items.sort_order: int, not null, default 0.
edu_dict_items.status: varchar(20), not null, default enabled.
edu_dict_items.is_default: boolean, not null, default false.
edu_dict_items.created_by: bigint unsigned, nullable.
edu_dict_items.updated_by: bigint unsigned, nullable.
edu_dict_items.created_at: timestamp, nullable.
edu_dict_items.updated_at: timestamp, nullable.
edu_dict_items.deleted_at: timestamp, nullable.

edu_feature_flags.id: bigint unsigned primary key, auto increment, not null.
edu_feature_flags.owner_type: varchar(20), not null, system or tenant.
edu_feature_flags.tenant_id: bigint unsigned, nullable, required when owner_type is tenant.
edu_feature_flags.owner_key: varchar(80), not null, system or tenant:<tenant_id>.
edu_feature_flags.feature_code: varchar(120), not null.
edu_feature_flags.feature_name: varchar(120), not null.
edu_feature_flags.description: varchar(255), nullable.
edu_feature_flags.enabled: boolean, not null, default false.
edu_feature_flags.config: json, nullable.
edu_feature_flags.effective_from: timestamp, nullable.
edu_feature_flags.effective_to: timestamp, nullable.
edu_feature_flags.status: varchar(20), not null, default enabled.
edu_feature_flags.is_locked: boolean, not null, default false.
edu_feature_flags.created_by: bigint unsigned, nullable.
edu_feature_flags.updated_by: bigint unsigned, nullable.
edu_feature_flags.created_at: timestamp, nullable.
edu_feature_flags.updated_at: timestamp, nullable.
edu_feature_flags.deleted_at: timestamp, nullable.
```

Tenant/campus isolation fields:

```text
System rows have owner_type system, tenant_id null, owner_key system.
Tenant rows have owner_type tenant, tenant_id not null, owner_key tenant:<tenant_id>.
F03 has no campus_id because dictionaries and feature flags are tenant-wide configuration.
Campus-specific options must be added by later business modules only when a real campus-specific use case appears.
```

Unique indexes:

```text
uk_edu_dict_types_owner_code (owner_key, code)
uk_edu_dict_items_type_value (dict_type_id, value)
uk_edu_feature_flags_owner_feature (owner_key, feature_code)
```

Ordinary indexes:

```text
idx_edu_dict_types_tenant_status (tenant_id, status)
idx_edu_dict_types_owner_status (owner_type, status)
idx_edu_dict_types_deleted_at (deleted_at)
idx_edu_dict_items_owner_code_status (owner_key, dict_code, status)
idx_edu_dict_items_type_status_sort (dict_type_id, status, sort_order)
idx_edu_dict_items_deleted_at (deleted_at)
idx_edu_feature_flags_tenant_enabled (tenant_id, enabled)
idx_edu_feature_flags_owner_status (owner_type, status)
idx_edu_feature_flags_feature_status (feature_code, status)
idx_edu_feature_flags_deleted_at (deleted_at)
```

Foreign-key policy:

```text
No physical foreign keys in F03. Tenant existence is validated through TenantRepository. Dictionary item dict_type_id and feature tenant_id references are validated in services.
```

Rollback behavior:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate:rollback --step=1
```

Expected:

```text
edu_feature_flags, edu_dict_items, and edu_dict_types are dropped in that order.
```

## MineAdmin Backend Module Design

### Enums

Create `ConfigOwnerType`:

```php
<?php

declare(strict_types=1);

namespace App\Model\Enums\Education\Foundation;

enum ConfigOwnerType: string
{
    case System = 'system';
    case Tenant = 'tenant';
}
```

Create `DictionaryStatus`:

```php
<?php

declare(strict_types=1);

namespace App\Model\Enums\Education\Foundation;

enum DictionaryStatus: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
}
```

Create `FeatureFlagStatus`:

```php
<?php

declare(strict_types=1);

namespace App\Model\Enums\Education\Foundation;

enum FeatureFlagStatus: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
}
```

### Models

Create `EducationDictType`:

```text
Path: mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationDictType.php
Table: edu_dict_types
Traits: SoftDeletes
Fillable: every column from migration.
Casts: id integer, tenant_id integer, is_locked boolean, sort_order integer, created_by integer, updated_by integer, created_at datetime, updated_at datetime, deleted_at datetime.
Relations:
- items hasMany EducationDictItem by dict_type_id.
- tenant belongsTo EducationTenant by tenant_id.
```

Create `EducationDictItem`:

```text
Path: mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationDictItem.php
Table: edu_dict_items
Traits: SoftDeletes
Fillable: every column from migration.
Casts: id integer, dict_type_id integer, extra array, sort_order integer, is_default boolean, created_by integer, updated_by integer, created_at datetime, updated_at datetime, deleted_at datetime.
Relations:
- type belongsTo EducationDictType by dict_type_id.
```

Create `EducationFeatureFlag`:

```text
Path: mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationFeatureFlag.php
Table: edu_feature_flags
Traits: SoftDeletes
Fillable: every column from migration.
Casts: id integer, tenant_id integer, enabled boolean, config array, effective_from datetime, effective_to datetime, is_locked boolean, created_by integer, updated_by integer, created_at datetime, updated_at datetime, deleted_at datetime.
Relations:
- tenant belongsTo EducationTenant by tenant_id.
```

### Owner Resolver

Create `mineadmin-education-saas/backend/app/Service/Education/Foundation/ConfigOwnerResolver.php`.

Methods:

```text
systemOwnerKey(): string returns system.
tenantOwnerKey(int $tenantId): string returns tenant:<tenantId>.
resolveOwnerKey(string $ownerType, ?int $tenantId): string.
assertCanWriteOwner(EducationUserContext $context, string $ownerType, ?int $tenantId): void.
```

Rules:

```text
owner_type system requires tenant_id null and platformAccess true.
owner_type tenant requires tenant_id and current-user access to that tenant.
tenant context can write only owner_type tenant for its own tenant_id.
platform context can write system rows and any tenant row.
Invalid owner combination throws BusinessException(ResultCode::UNPROCESSABLE_ENTITY).
Forbidden owner write throws BusinessException(ResultCode::FORBIDDEN).
```

### Repositories

Create `DictTypeRepository`:

```text
Path: mineadmin-education-saas/backend/app/Repository/Education/Foundation/DictTypeRepository.php
Extends: App\Repository\IRepository
Model: EducationDictType
Methods:
- handleSearch filters owner_type, tenant_id, owner_key, code, status, keyword(code/name), and sorts by sort_order then id.
- findByOwnerCode(string $ownerKey, string $code): ?EducationDictType
- existsByOwnerCode(string $ownerKey, string $code, ?int $ignoreId = null): bool
```

Create `DictItemRepository`:

```text
Path: mineadmin-education-saas/backend/app/Repository/Education/Foundation/DictItemRepository.php
Extends: App\Repository\IRepository
Model: EducationDictItem
Methods:
- handleSearch filters dict_type_id, owner_key, dict_code, status, keyword(label/value), and sorts by sort_order then id.
- enabledItems(string $ownerKey, string $dictCode): Collection
- existsValue(int $dictTypeId, string $value, ?int $ignoreId = null): bool
```

Create `FeatureFlagRepository`:

```text
Path: mineadmin-education-saas/backend/app/Repository/Education/Foundation/FeatureFlagRepository.php
Extends: App\Repository\IRepository
Model: EducationFeatureFlag
Methods:
- handleSearch filters owner_type, tenant_id, owner_key, feature_code, enabled, status, keyword(feature_code/feature_name), and sorts by feature_code then id.
- findActiveByOwnerFeature(string $ownerKey, string $featureCode, string $now): ?EducationFeatureFlag
- existsByOwnerFeature(string $ownerKey, string $featureCode, ?int $ignoreId = null): bool
```

Active feature-row rule:

```text
status is enabled.
effective_from is null or <= current time.
effective_to is null or >= current time.
```

### Services

Create `DictionaryService`:

```text
Path: mineadmin-education-saas/backend/app/Service/Education/Foundation/DictionaryService.php
Dependencies: DictTypeRepository, DictItemRepository, TenantRepository, ConfigOwnerResolver.
Methods:
- pageTypes(array $params, int $page, int $pageSize, EducationUserContext $context): array
- pageItems(array $params, int $page, int $pageSize, EducationUserContext $context): array
- createType(array $data, EducationUserContext $context, ?int $operatorId): EducationDictType
- updateType(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationDictType
- changeTypeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationDictType
- deleteType(int $id, EducationUserContext $context): void
- createItem(array $data, EducationUserContext $context, ?int $operatorId): EducationDictItem
- updateItem(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationDictItem
- changeItemStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationDictItem
- deleteItem(int $id, EducationUserContext $context): void
- items(string $dictCode, ?int $tenantId): array
```

Dictionary business rules:

```text
createType:
- owner_type system requires platformAccess true and tenant_id null.
- owner_type tenant requires tenant_id and current-user access to tenant.
- code is unique within owner_key.
- duplicate code throws 409 with message dictionary code already exists.

updateType:
- cannot change owner_type, tenant_id, owner_key, or code after creation.
- locked system dictionary cannot be renamed by tenant context.

deleteType:
- locked system dictionary cannot be deleted.
- dictionary type with any item cannot be deleted.

createItem/updateItem:
- dict_type_id must exist and be writable by current context.
- value is unique inside dict_type_id.
- owner_key and dict_code are copied from the dictionary type.
- duplicate value throws 409 with message dictionary item value already exists.

items:
- if tenant_id is present and an enabled tenant dictionary type exists for dictCode, return enabled tenant items.
- otherwise return enabled system items.
- disabled dictionary types return empty list.
- items are sorted by sort_order ascending, then id ascending.
```

Create `FeatureFlagService`:

```text
Path: mineadmin-education-saas/backend/app/Service/Education/Foundation/FeatureFlagService.php
Dependencies: FeatureFlagRepository, TenantRepository, ConfigOwnerResolver.
Methods:
- page(array $params, int $page, int $pageSize, EducationUserContext $context): array
- createFlag(array $data, EducationUserContext $context, ?int $operatorId): EducationFeatureFlag
- updateFlag(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationFeatureFlag
- changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationFeatureFlag
- deleteFlag(int $id, EducationUserContext $context): void
- enabled(string $featureCode, ?int $tenantId, ?string $now = null): bool
- resolved(string $featureCode, ?int $tenantId, ?string $now = null): array
```

Feature flag business rules:

```text
createFlag:
- owner_type system requires platformAccess true and tenant_id null.
- owner_type tenant requires tenant_id and current-user access to tenant.
- feature_code is unique within owner_key.
- duplicate feature_code throws 409 with message feature flag already exists.

updateFlag:
- cannot change owner_type, tenant_id, owner_key, or feature_code after creation.
- effective_to must be null or greater than effective_from.
- config must be JSON object when provided.

deleteFlag:
- locked system flag cannot be deleted.

enabled:
- first check active tenant flag for tenant:<tenant_id>.
- if no active tenant flag exists, check active system flag.
- if no active row exists, return false.
- returned boolean is row.enabled.

resolved:
- returns feature_code, enabled, owner_key, config, effective_from, effective_to.
- tenant active row wins over system active row.
```

### Requests

All request classes extend `Hyperf\Validation\Request\FormRequest`, use `NoAuthorizeTrait`, and return `true` from `authorize()`.

Create `DictTypePageRequest` rules:

```php
return [
    'page' => 'sometimes|integer|min:1',
    'page_size' => 'sometimes|integer|min:1|max:200',
    'owner_type' => 'sometimes|in:system,tenant',
    'tenant_id' => 'sometimes|nullable|integer|min:1',
    'keyword' => 'sometimes|string|max:120',
    'status' => 'sometimes|in:enabled,disabled',
];
```

Create `DictTypeSaveRequest` rules:

```php
return [
    'owner_type' => 'required|in:system,tenant',
    'tenant_id' => 'sometimes|nullable|integer|min:1',
    'code' => ['required', 'string', 'max:80', 'regex:/^[a-z][a-z0-9_\\.\\-]{1,79}$/'],
    'name' => 'required|string|max:120',
    'description' => 'sometimes|nullable|string|max:255',
    'status' => 'sometimes|in:enabled,disabled',
    'is_locked' => 'sometimes|boolean',
    'sort_order' => 'sometimes|integer|min:0|max:999999',
];
```

Create `DictTypeStatusRequest` rules:

```php
return [
    'status' => 'required|in:enabled,disabled',
];
```

Create `DictItemPageRequest` rules:

```php
return [
    'page' => 'sometimes|integer|min:1',
    'page_size' => 'sometimes|integer|min:1|max:200',
    'dict_type_id' => 'sometimes|integer|min:1',
    'dict_code' => 'sometimes|string|max:80',
    'keyword' => 'sometimes|string|max:120',
    'status' => 'sometimes|in:enabled,disabled',
];
```

Create `DictItemSaveRequest` rules:

```php
return [
    'dict_type_id' => 'required|integer|min:1',
    'label' => 'required|string|max:120',
    'value' => ['required', 'string', 'max:120', 'regex:/^[a-zA-Z0-9_\\.\\-]+$/'],
    'color' => 'sometimes|nullable|string|max:40',
    'extra' => 'sometimes|nullable|array',
    'sort_order' => 'sometimes|integer|min:0|max:999999',
    'status' => 'sometimes|in:enabled,disabled',
    'is_default' => 'sometimes|boolean',
];
```

Create `DictItemStatusRequest` rules:

```php
return [
    'status' => 'required|in:enabled,disabled',
];
```

Create `FeatureFlagPageRequest` rules:

```php
return [
    'page' => 'sometimes|integer|min:1',
    'page_size' => 'sometimes|integer|min:1|max:200',
    'owner_type' => 'sometimes|in:system,tenant',
    'tenant_id' => 'sometimes|nullable|integer|min:1',
    'feature_code' => 'sometimes|string|max:120',
    'keyword' => 'sometimes|string|max:120',
    'enabled' => 'sometimes|boolean',
    'status' => 'sometimes|in:enabled,disabled',
];
```

Create `FeatureFlagSaveRequest` rules:

```php
return [
    'owner_type' => 'required|in:system,tenant',
    'tenant_id' => 'sometimes|nullable|integer|min:1',
    'feature_code' => ['required', 'string', 'max:120', 'regex:/^[a-z][a-z0-9_\\.\\-]{1,119}$/'],
    'feature_name' => 'required|string|max:120',
    'description' => 'sometimes|nullable|string|max:255',
    'enabled' => 'required|boolean',
    'config' => 'sometimes|nullable|array',
    'effective_from' => 'sometimes|nullable|date',
    'effective_to' => 'sometimes|nullable|date|after:effective_from',
    'status' => 'sometimes|in:enabled,disabled',
    'is_locked' => 'sometimes|boolean',
];
```

Create `FeatureFlagStatusRequest` rules:

```php
return [
    'status' => 'required|in:enabled,disabled',
];
```

### Controllers

Both controllers use:

```text
AccessTokenMiddleware priority 100
PermissionMiddleware priority 99
ResolveEducationContextMiddleware priority 98
OperationMiddleware priority 97
```

Create `DictionaryController` endpoints:

```text
GET /admin/education/foundation/dict-types/page
POST /admin/education/foundation/dict-types
PUT /admin/education/foundation/dict-types/{id}
PUT /admin/education/foundation/dict-types/{id}/status
DELETE /admin/education/foundation/dict-types/{id}
GET /admin/education/foundation/dict-items/page
POST /admin/education/foundation/dict-items
PUT /admin/education/foundation/dict-items/{id}
PUT /admin/education/foundation/dict-items/{id}/status
DELETE /admin/education/foundation/dict-items/{id}
GET /admin/education/foundation/dictionaries/{code}/items
```

Permission codes:

```text
education:foundation:dictionary:page
education:foundation:dictionary:create
education:foundation:dictionary:update
education:foundation:dictionary:status
education:foundation:dictionary:delete
education:foundation:dictionary-item:page
education:foundation:dictionary-item:create
education:foundation:dictionary-item:update
education:foundation:dictionary-item:status
education:foundation:dictionary-item:delete
education:foundation:dictionary-item:lookup
```

Create `FeatureFlagController` endpoints:

```text
GET /admin/education/foundation/feature-flags/page
POST /admin/education/foundation/feature-flags
PUT /admin/education/foundation/feature-flags/{id}
PUT /admin/education/foundation/feature-flags/{id}/status
DELETE /admin/education/foundation/feature-flags/{id}
GET /admin/education/foundation/feature-flags/{featureCode}/resolved
```

Permission codes:

```text
education:foundation:feature-flag:page
education:foundation:feature-flag:create
education:foundation:feature-flag:update
education:foundation:feature-flag:status
education:foundation:feature-flag:delete
education:foundation:feature-flag:lookup
```

Controller response rules:

```text
page returns service page result.
create returns id and owner_key.
update returns id.
status returns id and status.
delete returns success().
dictionary lookup returns dict_code and items.
feature resolved returns feature_code, enabled, owner_key, config, effective_from, effective_to.
```

### Schemas

Create `DictTypeSchema` fields:

```text
id int
owner_type string
tenant_id int nullable
owner_key string
code string
name string
description string nullable
status string
is_locked bool
sort_order int
created_by int nullable
updated_by int nullable
created_at string nullable
updated_at string nullable
```

Create `DictItemSchema` fields:

```text
id int
dict_type_id int
owner_key string
dict_code string
label string
value string
color string nullable
extra array nullable
sort_order int
status string
is_default bool
created_by int nullable
updated_by int nullable
created_at string nullable
updated_at string nullable
```

Create `FeatureFlagSchema` fields:

```text
id int
owner_type string
tenant_id int nullable
owner_key string
feature_code string
feature_name string
description string nullable
enabled bool
config array nullable
effective_from string nullable
effective_to string nullable
status string
is_locked bool
created_by int nullable
updated_by int nullable
created_at string nullable
updated_at string nullable
```

### Seeders

Create `EducationFoundationDictionarySeeder`.

Seed dictionary types:

```text
common_status: enabled, disabled
gender: unknown, male, female
attendance_status: pending, present, absent, leave
lesson_consumption_status: pending, consumed, skipped, refunded
leave_request_status: submitted, approved, rejected, canceled
payment_status: pending, paid, refunded, canceled
```

Seeder rules:

```text
owner_type system, tenant_id null, owner_key system.
All seeded types are is_locked true.
All seeded items have status enabled.
Seeder is idempotent by owner_key + code and dict_type_id + value.
```

Create `EducationFoundationFeatureFlagSeeder`.

Seed feature flags:

```text
education.v1.core_academic: enabled true
education.v2.academic_operations: enabled false
education.v3.admissions_crm: enabled false
education.v4.finance_payment: enabled false
education.v5.teacher_payroll: enabled false
education.v6.group_management: enabled false
education.v7.family_service: enabled false
education.v8.ai_assistant: enabled false
education.v9.workflow_alerts: enabled false
education.v10.growth_conversion: enabled false
education.v11.course_standards: enabled false
education.v12.learning_content: enabled false
```

Seeder rules:

```text
owner_type system, tenant_id null, owner_key system.
All seeded flags are is_locked true.
Seeder is idempotent by owner_key + feature_code.
```

## API Contract

### Dictionary Type Page

```text
GET /admin/education/foundation/dict-types/page
Permission: education:foundation:dictionary:page
Caller: platform admin or tenant admin
Headers: Authorization: Bearer <token>, optional X-Tenant-Id: 1
Isolation: platform context can see system and tenant rows; tenant context sees system rows and current-tenant rows
Audit: read operation, no operation audit row required
```

Request query:

```json
{
  "page": 1,
  "page_size": 20,
  "owner_type": "tenant",
  "tenant_id": 1,
  "keyword": "status",
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
        "owner_type": "system",
        "tenant_id": null,
        "owner_key": "system",
        "code": "common_status",
        "name": "通用状态",
        "status": "enabled",
        "item_count": 2
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
  "message": "owner_type must be one of system, tenant",
  "data": []
}
```

Business failure:

```json
{
  "code": 403,
  "message": "tenant is outside current user scope",
  "data": {
    "tenant_id": 2
  }
}
```

### Dictionary Type Create

```text
POST /admin/education/foundation/dict-types
Permission: education:foundation:dictionary:create
Caller: platform admin or tenant admin
Headers: Authorization: Bearer <token>, optional X-Tenant-Id: 1
Isolation: tenant admin can create only owner_type tenant for current tenant
Audit: OperationMiddleware records write operation
```

Request body:

```json
{
  "owner_type": "tenant",
  "tenant_id": 1,
  "code": "student_source",
  "name": "学员来源",
  "description": "租户自定义学员来源",
  "status": "enabled",
  "sort_order": 10
}
```

Success response:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 20,
    "owner_key": "tenant:1"
  }
}
```

Business failure:

```json
{
  "code": 409,
  "message": "dictionary code already exists",
  "data": {
    "owner_key": "tenant:1",
    "code": "student_source"
  }
}
```

### Dictionary Item Save

```text
POST /admin/education/foundation/dict-items
Permission: education:foundation:dictionary-item:create
Caller: platform admin or tenant admin
Headers: Authorization: Bearer <token>, optional X-Tenant-Id: 1
Isolation: dict type must be writable by current context
Audit: OperationMiddleware records write operation
```

Request body:

```json
{
  "dict_type_id": 20,
  "label": "转介绍",
  "value": "referral",
  "color": "green",
  "extra": {
    "score": 10
  },
  "sort_order": 10,
  "status": "enabled",
  "is_default": false
}
```

Success response:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 100,
    "dict_type_id": 20
  }
}
```

Business failure:

```json
{
  "code": 409,
  "message": "dictionary item value already exists",
  "data": {
    "dict_type_id": 20,
    "value": "referral"
  }
}
```

### Dictionary Item Lookup

```text
GET /admin/education/foundation/dictionaries/{code}/items
Permission: education:foundation:dictionary-item:lookup
Caller: platform admin, tenant admin, or internal admin page
Headers: Authorization: Bearer <token>, optional X-Tenant-Id: 1
Isolation: tenant lookup prefers current tenant dictionary and falls back to system dictionary
Audit: read operation, no operation audit row required
```

Success response:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "dict_code": "common_status",
    "items": [
      {
        "label": "启用",
        "value": "enabled",
        "color": "green",
        "sort_order": 10
      },
      {
        "label": "停用",
        "value": "disabled",
        "color": "gray",
        "sort_order": 20
      }
    ]
  }
}
```

### Feature Flag Page

```text
GET /admin/education/foundation/feature-flags/page
Permission: education:foundation:feature-flag:page
Caller: platform admin or tenant admin
Headers: Authorization: Bearer <token>, optional X-Tenant-Id: 1
Isolation: platform context can see system and tenant rows; tenant context sees system rows and current-tenant rows
Audit: read operation, no operation audit row required
```

Request query:

```json
{
  "page": 1,
  "page_size": 20,
  "owner_type": "tenant",
  "tenant_id": 1,
  "keyword": "core",
  "enabled": true,
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
        "id": 30,
        "owner_type": "system",
        "tenant_id": null,
        "owner_key": "system",
        "feature_code": "education.v1.core_academic",
        "feature_name": "V1 课消制核心教务",
        "enabled": true,
        "status": "enabled"
      }
    ],
    "total": 1
  }
}
```

### Feature Flag Save

```text
POST /admin/education/foundation/feature-flags
Permission: education:foundation:feature-flag:create
Caller: platform admin or tenant admin
Headers: Authorization: Bearer <token>, optional X-Tenant-Id: 1
Isolation: tenant admin can create only owner_type tenant for current tenant
Audit: OperationMiddleware records write operation
```

Request body:

```json
{
  "owner_type": "tenant",
  "tenant_id": 1,
  "feature_code": "education.v2.academic_operations",
  "feature_name": "V2 教务运营增强",
  "description": "为当前租户开启调课补课等能力",
  "enabled": true,
  "config": {
    "max_makeup_per_month": 2
  },
  "effective_from": "2026-06-10 00:00:00",
  "effective_to": null,
  "status": "enabled"
}
```

Success response:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 31,
    "owner_key": "tenant:1"
  }
}
```

Business failure:

```json
{
  "code": 409,
  "message": "feature flag already exists",
  "data": {
    "owner_key": "tenant:1",
    "feature_code": "education.v2.academic_operations"
  }
}
```

### Feature Flag Resolved

```text
GET /admin/education/foundation/feature-flags/{featureCode}/resolved
Permission: education:foundation:feature-flag:lookup
Caller: platform admin, tenant admin, or internal admin page
Headers: Authorization: Bearer <token>, optional X-Tenant-Id: 1
Isolation: tenant lookup prefers current tenant flag and falls back to system flag
Audit: read operation, no operation audit row required
```

Success response:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "feature_code": "education.v2.academic_operations",
    "enabled": true,
    "owner_key": "tenant:1",
    "config": {
      "max_makeup_per_month": 2
    },
    "effective_from": "2026-06-10 00:00:00",
    "effective_to": null
  }
}
```

## PC Admin Page Tasks

### Dictionary API Client

Create `mineadmin-education-saas/admin-web/src/api/education/foundation/dictionary.ts`.

Types:

```ts
export type ConfigOwnerType = 'system' | 'tenant'
export type FoundationStatus = 'enabled' | 'disabled'

export interface DictTypeRecord {
  id: number
  owner_type: ConfigOwnerType
  tenant_id?: number
  owner_key: string
  code: string
  name: string
  description?: string
  status: FoundationStatus
  is_locked: boolean
  sort_order: number
  item_count?: number
}

export interface DictItemRecord {
  id: number
  dict_type_id: number
  owner_key: string
  dict_code: string
  label: string
  value: string
  color?: string
  extra?: Record<string, unknown>
  sort_order: number
  status: FoundationStatus
  is_default: boolean
}
```

Methods:

```text
pageDictTypes(params)
createDictType(data)
updateDictType(id, data)
updateDictTypeStatus(id, status)
deleteDictType(id)
pageDictItems(params)
createDictItem(data)
updateDictItem(id, data)
updateDictItemStatus(id, status)
deleteDictItem(id)
lookupDictItems(code, tenantId?)
```

### Feature Flag API Client

Create `mineadmin-education-saas/admin-web/src/api/education/foundation/featureFlag.ts`.

Types:

```ts
export interface FeatureFlagRecord {
  id: number
  owner_type: ConfigOwnerType
  tenant_id?: number
  owner_key: string
  feature_code: string
  feature_name: string
  description?: string
  enabled: boolean
  config?: Record<string, unknown>
  effective_from?: string
  effective_to?: string
  status: FoundationStatus
  is_locked: boolean
}
```

Methods:

```text
pageFeatureFlags(params)
createFeatureFlag(data)
updateFeatureFlag(id, data)
updateFeatureFlagStatus(id, status)
deleteFeatureFlag(id)
resolveFeatureFlag(featureCode, tenantId?)
```

### Router

Modify `mineadmin-education-saas/admin-web/src/router/modules/education.ts`.

Routes:

```text
Route path: /education/foundation/dictionaries
Route name: EducationFoundationDictionaryList
Menu: 教务 SaaS / 基础设置 / 字典配置
Permission: education:foundation:dictionary:page
Component: admin-web/src/views/education/foundation/DictionaryList.vue

Route path: /education/foundation/feature-flags
Route name: EducationFoundationFeatureFlagList
Menu: 教务 SaaS / 基础设置 / 功能开关
Permission: education:foundation:feature-flag:page
Component: admin-web/src/views/education/foundation/FeatureFlagList.vue
```

### Dictionary Page

Create `mineadmin-education-saas/admin-web/src/views/education/foundation/DictionaryList.vue`.

Search fields:

```text
owner_type: select system/tenant
tenant_id: tenant selector, visible for platform profile users and owner_type tenant
keyword: input, hint text 字典编码/名称
status: select enabled/disabled
```

Type table columns:

```text
owner_type, tenant_id, code, name, status, item_count, sort_order, is_locked, updated_at, actions
```

Item panel columns:

```text
label, value, color, sort_order, status, is_default, updated_at, actions
```

Actions:

```text
Create type button permission: education:foundation:dictionary:create
Edit type button permission: education:foundation:dictionary:update
Enable/disable type button permission: education:foundation:dictionary:status
Delete type button permission: education:foundation:dictionary:delete
Create item button permission: education:foundation:dictionary-item:create
Edit item button permission: education:foundation:dictionary-item:update
Enable/disable item button permission: education:foundation:dictionary-item:status
Delete item button permission: education:foundation:dictionary-item:delete
```

States:

```text
loading: type table and item panel each show loading while API is pending.
empty: type table and item panel show empty state when no rows.
error: show API message and keep last successful list.
permission: hide action button when user lacks permission code.
locked: hide delete action for is_locked true rows.
tenant context: tenant users cannot select owner_type system.
```

### Dictionary Forms

Create `DictTypeForm.vue`.

Fields:

```text
owner_type: required select system/tenant
tenant_id: required when owner_type tenant
code: required input max 80, disabled in edit mode
name: required input max 120
description: optional input max 255
status: enabled/disabled
sort_order: number min 0
is_locked: boolean, visible only for platform context
```

Create `DictItemForm.vue`.

Fields:

```text
dict_type_id: fixed from selected type
label: required input max 120
value: required input max 120, disabled in edit mode
color: optional color token input
extra: optional JSON editor
sort_order: number min 0
status: enabled/disabled
is_default: boolean
```

### Feature Flag Page

Create `mineadmin-education-saas/admin-web/src/views/education/foundation/FeatureFlagList.vue`.

Search fields:

```text
owner_type: select system/tenant
tenant_id: tenant selector, visible for platform profile users and owner_type tenant
keyword: input, hint text 功能编码/名称
enabled: select true/false
status: select enabled/disabled
```

Table columns:

```text
owner_type, tenant_id, feature_code, feature_name, enabled, effective_from, effective_to, status, is_locked, updated_at, actions
```

Actions:

```text
Create button permission: education:foundation:feature-flag:create
Edit button permission: education:foundation:feature-flag:update
Enable/disable row status button permission: education:foundation:feature-flag:status
Delete button permission: education:foundation:feature-flag:delete
Resolve button permission: education:foundation:feature-flag:lookup
```

States:

```text
loading: table loading while pageFeatureFlags pending.
empty: show empty state when list length is 0.
error: show API message and keep last successful list.
permission: hide action button when user lacks permission code.
locked: hide delete action for is_locked true rows.
enabled flow: enabled true row shows close switch; false row shows open switch.
tenant context: tenant users cannot select owner_type system.
```

Create `FeatureFlagForm.vue`.

Fields:

```text
owner_type: required select system/tenant
tenant_id: required when owner_type tenant
feature_code: required input max 120, disabled in edit mode
feature_name: required input max 120
description: optional input max 255
enabled: boolean switch
config: optional JSON editor
effective_from: optional datetime
effective_to: optional datetime greater than effective_from
status: enabled/disabled
is_locked: boolean, visible only for platform context
```

Verification:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- DictionaryList
pnpm test -- FeatureFlagList
pnpm build
```

Expected:

```text
Lint passes.
DictionaryList tests pass.
FeatureFlagList tests pass.
Build succeeds.
```

## Teacher / Guardian Mobile Page Tasks

This module has no visible teacher or guardian page because it provides configuration services only. F06 consumes `DictionaryService` and `FeatureFlagService` in mobile context APIs.

Mobile verification:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm build:h5
```

Expected:

```text
H5 build succeeds and F03 adds no teacher or guardian mobile route.
```

Role isolation rule:

```text
Teacher and guardian profiles must not access F03 admin APIs unless MineAdmin permissions explicitly grant the relevant education:foundation:* permission codes.
```

## Test Plan

Migration/schema test:

```text
Test file: backend/tests/Feature/Education/Foundation/DictionaryFeatureMigrationTest.php
Case: test_dictionary_and_feature_tables_have_required_columns_and_indexes
Assert:
- edu_dict_types has owner_type, tenant_id, owner_key, code, status, is_locked, deleted_at
- edu_dict_items has dict_type_id, owner_key, dict_code, label, value, status, is_default, deleted_at
- edu_feature_flags has owner_type, tenant_id, owner_key, feature_code, enabled, config, effective_from, effective_to, status, deleted_at
- uk_edu_dict_types_owner_code exists
- uk_edu_dict_items_type_value exists
- uk_edu_feature_flags_owner_feature exists
```

Seeder tests:

```text
Test file: backend/tests/Feature/Education/Foundation/DictionarySeederTest.php
Case: test_dictionary_seeder_is_idempotent
Assert:
- running EducationFoundationDictionarySeeder twice creates one system common_status dictionary
- common_status has values enabled and disabled
- seeded system dictionaries have is_locked true

Test file: backend/tests/Feature/Education/Foundation/FeatureFlagSeederTest.php
Case: test_feature_flag_seeder_creates_v1_to_v12_flags
Assert:
- education.v1.core_academic exists and enabled is true
- education.v12.learning_content exists and enabled is false
- running seeder twice does not duplicate rows
```

Repository tests:

```text
Test file: backend/tests/Unit/Education/Foundation/DictTypeRepositoryTest.php
Case: test_exists_by_owner_code_is_owner_scoped
Assert:
- system common_status exists
- tenant:1 common_status can also exist
- tenant:2 common_status lookup does not return tenant:1 row

Test file: backend/tests/Unit/Education/Foundation/DictItemRepositoryTest.php
Case: test_enabled_items_filters_status_and_sorts
Assert:
- disabled item is excluded
- enabled items are ordered by sort_order then id

Test file: backend/tests/Unit/Education/Foundation/FeatureFlagRepositoryTest.php
Case: test_find_active_by_owner_feature_respects_time_window
Assert:
- flag with future effective_from is not active
- flag with expired effective_to is not active
- active flag is returned
```

Service tests:

```text
Test file: backend/tests/Unit/Education/Foundation/ConfigOwnerResolverTest.php
Case: test_tenant_context_cannot_write_system_owner
Assert:
- tenant context writing owner_type system throws BusinessException
- response code is 403

Case: test_platform_context_can_write_system_owner
Assert:
- platform context resolves owner_key system
```

```text
Test file: backend/tests/Unit/Education/Foundation/DictionaryServiceTest.php
Case: test_items_return_tenant_dictionary_before_system_default
Assert:
- system common_status has enabled/disabled
- tenant:1 common_status has open/closed
- items('common_status', 1) returns open/closed
- items('common_status', 2) returns enabled/disabled

Case: test_duplicate_item_value_returns_conflict
Assert:
- second item value enabled in same dict_type_id throws BusinessException
- response code is 409
- response data.value is enabled
```

```text
Test file: backend/tests/Unit/Education/Foundation/FeatureFlagServiceTest.php
Case: test_tenant_flag_overrides_system_default
Assert:
- system education.v2.academic_operations is false
- tenant:1 education.v2.academic_operations is true
- enabled(feature, 1) returns true
- enabled(feature, 2) returns false

Case: test_effective_window_controls_flag
Assert:
- flag outside effective window is ignored
- fallback system flag is used when tenant flag is inactive
```

API tests:

```text
Test file: backend/tests/Feature/Education/Foundation/DictionaryAdminApiTest.php
Case: test_platform_admin_can_create_type_and_item
Assert:
- POST dict-types returns code 200 and owner_key system
- POST dict-items returns code 200
- GET dictionaries/{code}/items includes saved value

Case: test_tenant_admin_can_create_only_tenant_dictionary
Assert:
- owner_type tenant succeeds for current tenant
- owner_type system returns code 403

Case: test_duplicate_dictionary_code_returns_conflict
Assert:
- response code is 409
- response message is dictionary code already exists
```

```text
Test file: backend/tests/Feature/Education/Foundation/FeatureFlagAdminApiTest.php
Case: test_platform_admin_can_create_update_and_resolve_flag
Assert:
- POST feature-flags returns code 200
- PUT feature-flags/{id} changes enabled value
- GET resolved returns changed value

Case: test_tenant_admin_cannot_write_other_tenant_flag
Assert:
- tenant context tenant_id 1 writing tenant_id 2 returns code 403
```

Permission and isolation tests:

```text
Test file: backend/tests/Feature/Education/Foundation/DictionaryFeaturePermissionTest.php
Case: test_user_without_dictionary_permission_cannot_page
Assert:
- GET dict-types/page returns code 403

Case: test_write_controllers_include_operation_middleware
Assert:
- DictionaryController class has OperationMiddleware attribute
- FeatureFlagController class has OperationMiddleware attribute
```

```text
Test file: backend/tests/Feature/Education/Foundation/DictionaryFeatureIsolationTest.php
Case: test_tenant_context_pages_only_system_and_current_tenant_config
Assert:
- tenant 1 context sees system rows and tenant 1 rows
- tenant 1 context does not see tenant 2 rows
```

PC tests:

```text
Test file: admin-web/src/views/education/foundation/__tests__/DictionaryList.spec.ts
Case: renders_dictionary_actions_by_permission
Assert:
- create type button renders with education:foundation:dictionary:create
- delete button is hidden for is_locked true row
- tenant user cannot choose owner_type system

Test file: admin-web/src/views/education/foundation/__tests__/FeatureFlagList.spec.ts
Case: renders_feature_flag_status_flow
Assert:
- enabled true row shows disable switch action
- enabled false row shows enable switch action
- delete button is hidden for is_locked true row
```

Mobile build verification:

```text
Command: cd mineadmin-education-saas/mobile-uniapp && pnpm build:h5
Assert:
- command exits 0
- no F03 teacher/guardian route is added
```

## Execution Commands

Backend:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
php bin/hyperf.php db:seed --class=EducationFoundationDictionarySeeder
php bin/hyperf.php db:seed --class=EducationFoundationFeatureFlagSeeder
composer test -- --filter DictionaryFeatureMigrationTest
composer test -- --filter DictionarySeederTest
composer test -- --filter FeatureFlagSeederTest
composer test -- --filter DictTypeRepositoryTest
composer test -- --filter DictItemRepositoryTest
composer test -- --filter FeatureFlagRepositoryTest
composer test -- --filter ConfigOwnerResolverTest
composer test -- --filter DictionaryServiceTest
composer test -- --filter FeatureFlagServiceTest
composer test -- --filter DictionaryAdminApiTest
composer test -- --filter FeatureFlagAdminApiTest
composer test -- --filter DictionaryFeaturePermissionTest
composer test -- --filter DictionaryFeatureIsolationTest
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
Migration creates edu_dict_types, edu_dict_items, and edu_feature_flags.
Dictionary seeder passes and is idempotent.
Feature flag seeder passes and is idempotent.
Repository tests pass.
Service tests pass.
API tests pass.
Permission and isolation tests pass.
Code style dry run passes.
Static analysis passes.
```

PC:

```bash
cd mineadmin-education-saas/admin-web
pnpm install --frozen-lockfile
pnpm lint
pnpm test -- DictionaryList
pnpm test -- FeatureFlagList
pnpm build
```

Expected:

```text
Install exits 0.
Lint exits 0.
DictionaryList test exits 0.
FeatureFlagList test exits 0.
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

Full F03 gate:

```bash
cd mineadmin-education-saas
make up
cd backend
php bin/hyperf.php migrate
php bin/hyperf.php db:seed --class=EducationFoundationDictionarySeeder
php bin/hyperf.php db:seed --class=EducationFoundationFeatureFlagSeeder
composer test -- --filter Education\\\\Foundation
composer cs-fix -- --dry-run
composer analyse
cd ../admin-web
pnpm lint && pnpm test -- DictionaryList && pnpm test -- FeatureFlagList && pnpm build
cd ../mobile-uniapp
pnpm build:h5
```

Expected:

```text
All commands exit 0.
```

## Acceptance Gate

```text
- edu_dict_types, edu_dict_items, and edu_feature_flags exist with all planned columns and indexes.
- System owner rows use owner_key system.
- Tenant owner rows use owner_key tenant:<tenant_id>.
- Dictionary code uniqueness is scoped by owner_key.
- Dictionary item value uniqueness is scoped by dict_type_id.
- Feature flag uniqueness is scoped by owner_key and feature_code.
- Dictionary lookup prefers tenant dictionary and falls back to system dictionary.
- Feature flag lookup prefers active tenant flag and falls back to active system flag.
- Feature flag effective_from and effective_to are respected.
- Tenant context cannot write system configuration.
- Tenant context cannot read or write another tenant's configuration.
- Write controllers include OperationMiddleware.
- Dictionary PC page has API client, route, type table, item panel, forms, permission buttons, locked-row handling, loading, empty, and error states.
- Feature flag PC page has API client, route, table, form, permission buttons, enabled flow, locked-row handling, loading, empty, and error states.
- F03 adds no teacher or guardian mobile page.
- Backend tests pass.
- PC tests and build pass.
- Mobile H5 build still passes.
```

## Tasks

### Task 1: Create Migration, Enums, Models, and Seeders

**Files:**

- Create: `mineadmin-education-saas/backend/databases/migrations/2026_06_10_000300_create_education_dictionary_feature_tables.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/ConfigOwnerType.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/DictionaryStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/FeatureFlagStatus.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationDictType.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationDictItem.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationFeatureFlag.php`
- Create: `mineadmin-education-saas/backend/databases/seeders/EducationFoundationDictionarySeeder.php`
- Create: `mineadmin-education-saas/backend/databases/seeders/EducationFoundationFeatureFlagSeeder.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/DictionaryFeatureMigrationTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/DictionarySeederTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/FeatureFlagSeederTest.php`

- [x] **Step 1: Create migration**

Use the full migration from `Database Migration Design`.

- [x] **Step 2: Create enums**

Use the enum definitions from `MineAdmin Backend Module Design`.

- [x] **Step 3: Create models**

Create model files with the table, fillable, casts, and relations listed in `MineAdmin Backend Module Design`.

- [x] **Step 4: Create seeders**

Create both seeders using the seed rows and idempotency rules from `MineAdmin Backend Module Design`.

- [x] **Step 5: Run migration and seeders**

Run:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
php bin/hyperf.php db:seed --class=EducationFoundationDictionarySeeder
php bin/hyperf.php db:seed --class=EducationFoundationFeatureFlagSeeder
```

Expected:

```text
edu_dict_types exists.
edu_dict_items exists.
edu_feature_flags exists.
Seeded system dictionaries exist.
Seeded system feature flags exist.
```

- [x] **Step 6: Write and run migration/seeder tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter DictionaryFeatureMigrationTest
composer test -- --filter DictionarySeederTest
composer test -- --filter FeatureFlagSeederTest
```

Expected:

```text
Migration and seeder tests pass.
```

### Task 2: Create Repositories and Services

**Files:**

- Create: `mineadmin-education-saas/backend/app/Service/Education/Foundation/ConfigOwnerResolver.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Foundation/DictionaryService.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Foundation/FeatureFlagService.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Foundation/DictTypeRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Foundation/DictItemRepository.php`
- Create: `mineadmin-education-saas/backend/app/Repository/Education/Foundation/FeatureFlagRepository.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/DictTypeRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/DictItemRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/FeatureFlagRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/DictionaryServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/FeatureFlagServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/ConfigOwnerResolverTest.php`

- [x] **Step 1: Create owner resolver**

Implement methods and rules from `MineAdmin Backend Module Design`.

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
composer test -- --filter DictTypeRepositoryTest
composer test -- --filter DictItemRepositoryTest
composer test -- --filter FeatureFlagRepositoryTest
composer test -- --filter ConfigOwnerResolverTest
composer test -- --filter DictionaryServiceTest
composer test -- --filter FeatureFlagServiceTest
```

Expected:

```text
Repository, owner resolver, and service tests pass.
```

### Task 3: Create Requests, Schemas, and Controllers

**Files:**

- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/DictTypePageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/DictTypeSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/DictTypeStatusRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/DictItemPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/DictItemSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/DictItemStatusRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/FeatureFlagPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/FeatureFlagSaveRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/FeatureFlagStatusRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/DictionaryController.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/FeatureFlagController.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Foundation/DictTypeSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Foundation/DictItemSchema.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Foundation/FeatureFlagSchema.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/DictionaryAdminApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/FeatureFlagAdminApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/DictionaryFeaturePermissionTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/DictionaryFeatureIsolationTest.php`

- [x] **Step 1: Create request classes**

Use the request validation rules from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create schema classes**

Use the schema fields from `MineAdmin Backend Module Design`.

- [x] **Step 3: Create controllers**

Use endpoints, permissions, middleware, and response rules from `MineAdmin Backend Module Design`.

- [x] **Step 4: Write API, permission, and isolation tests**

Create feature tests listed in `Test Plan`.

- [x] **Step 5: Run feature tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter DictionaryAdminApiTest
composer test -- --filter FeatureFlagAdminApiTest
composer test -- --filter DictionaryFeaturePermissionTest
composer test -- --filter DictionaryFeatureIsolationTest
```

Expected:

```text
Dictionary API, feature flag API, permission, and isolation tests pass.
```

### Task 4: Create PC Dictionary and Feature Flag Pages

**Files:**

- Create: `mineadmin-education-saas/admin-web/src/api/education/foundation/dictionary.ts`
- Create: `mineadmin-education-saas/admin-web/src/api/education/foundation/featureFlag.ts`
- Modify: `mineadmin-education-saas/admin-web/src/router/modules/education.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/DictionaryList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/FeatureFlagList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/DictTypeForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/DictItemForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/FeatureFlagForm.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/DictionaryList.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/FeatureFlagList.spec.ts`

- [x] **Step 1: Create API clients**

Use the TypeScript types and methods from `PC Admin Page Tasks`.

- [x] **Step 2: Add router entries**

Use the route paths, names, menus, permissions, and components from `PC Admin Page Tasks`.

- [x] **Step 3: Create dictionary page and forms**

Implement dictionary type table, item panel, actions, forms, state handling, and permission behavior from `PC Admin Page Tasks`.

- [x] **Step 4: Create feature flag page and form**

Implement feature flag table, actions, form, state handling, enabled flow, and permission behavior from `PC Admin Page Tasks`.

- [x] **Step 5: Add PC tests**

Create `DictionaryList.spec.ts` and `FeatureFlagList.spec.ts` with assertions listed in `Test Plan`.

- [x] **Step 6: Run PC verification**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- DictionaryList
pnpm test -- FeatureFlagList
pnpm build
```

Expected:

```text
Lint, tests, and build pass.
```

### Task 5: Run F03 Final Gate

**Files:**

- Verify: all F03 backend, PC, and mobile paths listed in `File Structure`.

- [x] **Step 1: Run backend final gate**

Run:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
php bin/hyperf.php db:seed --class=EducationFoundationDictionarySeeder
php bin/hyperf.php db:seed --class=EducationFoundationFeatureFlagSeeder
composer test -- --filter Education\\\\Foundation
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
Migration, seeders, and all Education Foundation tests pass.
Code style dry run passes.
Static analysis passes.
```

- [x] **Step 2: Run PC final gate**

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- DictionaryList
pnpm test -- FeatureFlagList
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

- [x] **Step 4: Commit F03**

Run:

```bash
cd mineadmin-education-saas
git add backend admin-web mobile-uniapp
git commit -m "feat: add education dictionary and feature flag foundation"
```

Expected:

```text
Commit succeeds with F03 backend, PC, and verification changes.
```

## Self-Review

- Spec coverage: F03 covers dictionary types, dictionary items, feature flags, system defaults, tenant overrides, seeders, APIs, PC pages, tests, commands, and acceptance gates.
- MineAdmin fit: The plan uses MineAdmin 3.x admin controllers, request classes, repositories, services, schemas, middleware stack, result shape, and `databases/migrations`.
- Tenant isolation: Tenant configuration is scoped by owner_key and tenant context; tenant users cannot write system rows or another tenant's rows.
- Dictionary behavior: Tenant dictionary overrides system dictionary at the type level; enabled items are sorted and disabled items are excluded.
- Feature behavior: Tenant flag overrides system flag only when active in the effective time window.
- PC fit: Dictionary and feature flag pages include API clients, routes, forms, permission buttons, locked-row handling, loading, empty, and error states.
- Mobile fit: F03 exposes services only; F06 owns visible teacher/guardian mobile flows.
- Readiness: This plan has exact paths, full migration design, backend layer tasks, API request/response/failure examples, PC tasks, mobile verification, tests, commands, expected outputs, and acceptance gates, so F03 can be marked `ready`.
