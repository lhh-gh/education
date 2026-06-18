# MineAdmin Education SaaS F04 Audit Log Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement immutable education-domain audit logs for Foundation write operations, plus MineAdmin admin query APIs and a read-only PC audit log page.

**Architecture:** F04 adds an education audit log table, event, listener, logger, query service, repository, admin controller, schema, and PC page. Foundation write services dispatch `EducationAuditEvent` inside successful write transactions; `EducationAuditListener` calls `AuditLoggerInterface` synchronously so audit rows roll back with failed writes. Query APIs use F02 `EducationUserContext` and campus scope rules.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8 JSON columns, MineAdmin-Vue, Vue3, TypeScript, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. F04 audit log gates have passed.

---

## Scope Check

Included:

- Create immutable `edu_audit_logs` migration and model.
- Create audit actor enum, audit event, listener, logger contract, logger service, context resolver, query service, repository, request classes, schema, and admin controller.
- Register `EducationAuditListener` in Hyperf listener config.
- Add Foundation write audit dispatch to tenant, campus, user profile, campus scope, dictionary, and feature flag services created by F01-F03.
- Add admin APIs for audit log pagination and detail viewing.
- Add a MineAdmin PC read-only audit list page, API client, router entry, and payload detail drawer.
- Add backend tests for migration, logger sanitization, listener, repository filters, service isolation, API contract, permission, and Foundation write integration.
- Add PC tests for list filters, detail drawer, immutable page behavior, and button permission.
- Keep mobile builds verified without adding teacher or guardian audit pages.

Excluded:

- Login, token, OAuth, and MineAdmin platform security logs; MineAdmin native security or operation modules own those events.
- Export, archive, retention policy, SIEM integration, and tamper-evident hash chains; these belong to a later compliance module.
- Teacher and guardian visible audit pages; audit query is admin-only in Foundation.
- Auditing V1-V12 business modules; those modules will dispatch the same `EducationAuditEvent` when implemented.
- Replacing MineAdmin `OperationMiddleware`; F04 adds education-domain business payload audit and keeps operation middleware on write controllers.

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_000400_create_education_audit_logs_table.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/AuditActorType.php
mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationAuditLog.php
mineadmin-education-saas/backend/app/Contract/Education/Foundation/AuditLoggerInterface.php
mineadmin-education-saas/backend/app/Event/Education/Foundation/EducationAuditEvent.php
mineadmin-education-saas/backend/app/Listener/Education/Foundation/EducationAuditListener.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/AuditContextResolver.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/AuditLogger.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/AuditLogService.php
mineadmin-education-saas/backend/app/Repository/Education/Foundation/AuditLogRepository.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/AuditLogPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/AuditLogDetailRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/AuditLogController.php
mineadmin-education-saas/backend/app/Schema/Education/Foundation/AuditLogSchema.php
```

Modify backend:

```text
mineadmin-education-saas/backend/config/autoload/listeners.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/TenantService.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusService.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/UserProfileService.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusScopeService.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/DictionaryService.php
mineadmin-education-saas/backend/app/Service/Education/Foundation/FeatureFlagService.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/AuditLogMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/AuditLoggerTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/AuditContextResolverTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/EducationAuditListenerTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/AuditLogRepositoryTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Foundation/AuditLogServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/FoundationAuditWriteIntegrationTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/AuditLogAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/AuditLogPermissionTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Foundation/AuditLogIsolationTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/foundation/auditLog.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/AuditLogList.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/components/AuditPayloadDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/AuditLogList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/AuditPayloadDrawer.spec.ts
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
mineadmin-education-saas/backend/databases/migrations/2026_06_10_000400_create_education_audit_logs_table.php
```

Table: `edu_audit_logs`

Columns:

| Column | Type | Nullable | Default | Comment |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned primary key | no | auto increment | Audit log id |
| `tenant_id` | bigint unsigned | yes | null | Tenant id; null for platform-wide audit |
| `campus_id` | bigint unsigned | yes | null | Campus id; null for tenant-wide audit |
| `actor_user_id` | bigint unsigned | yes | null | MineAdmin user id that caused the write |
| `actor_type` | varchar(20) | no | `admin` | `admin`, `teacher`, `guardian`, or `system` |
| `actor_role_code` | varchar(80) | yes | null | Education role code resolved from F02 context |
| `module` | varchar(60) | no | none | Product module, for F04 normally `foundation` |
| `resource` | varchar(80) | no | none | Resource name such as `tenant`, `campus`, `dict_type` |
| `action` | varchar(120) | no | none | Full action code |
| `business_type` | varchar(80) | no | none | Business object type |
| `business_id` | varchar(80) | yes | null | Business object id stored as string |
| `request_id` | varchar(80) | yes | null | Request correlation id |
| `ip_address` | varchar(64) | yes | null | Client IP |
| `user_agent` | varchar(512) | yes | null | Client user agent |
| `method` | varchar(10) | yes | null | HTTP method |
| `path` | varchar(255) | yes | null | HTTP path |
| `summary` | varchar(255) | yes | null | Human-readable audit summary |
| `before_snapshot` | json | yes | null | Sanitized data before write |
| `after_snapshot` | json | yes | null | Sanitized data after write |
| `diff` | json | yes | null | Sanitized changed keys |
| `metadata` | json | yes | null | Extra labels, reason, and client metadata |
| `created_at` | timestamp | yes | null | Audit create time |

Indexes:

```text
index idx_edu_audit_logs_tenant_created (tenant_id, created_at)
index idx_edu_audit_logs_tenant_module_created (tenant_id, module, created_at)
index idx_edu_audit_logs_tenant_action_created (tenant_id, action, created_at)
index idx_edu_audit_logs_tenant_actor_created (tenant_id, actor_user_id, created_at)
index idx_edu_audit_logs_campus_created (tenant_id, campus_id, created_at)
index idx_edu_audit_logs_business (business_type, business_id)
index idx_edu_audit_logs_request (request_id)
index idx_edu_audit_logs_created_at (created_at)
```

Foreign-key policy:

```text
No physical foreign keys. Audit rows must remain queryable after tenant, campus, user, or business rows are disabled, soft-deleted, or archived. Services validate tenant, campus, user, and business ids before dispatching audit events.
```

Rollback:

```text
Drop `edu_audit_logs`. No other table is modified by this migration.
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
        Schema::create('edu_audit_logs', static function (Blueprint $table): void {
            $table->comment('Education domain audit logs');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('Tenant id; null for platform-wide audit');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id; null for tenant-wide audit');
            $table->unsignedBigInteger('actor_user_id')->nullable()->comment('MineAdmin user id that caused the write');
            $table->string('actor_type', 20)->default('admin')->comment('admin, teacher, guardian, or system');
            $table->string('actor_role_code', 80)->nullable()->comment('Education role code from user context');
            $table->string('module', 60)->comment('Product module');
            $table->string('resource', 80)->comment('Resource name');
            $table->string('action', 120)->comment('Full action code');
            $table->string('business_type', 80)->comment('Business object type');
            $table->string('business_id', 80)->nullable()->comment('Business object id');
            $table->string('request_id', 80)->nullable()->comment('Request correlation id');
            $table->string('ip_address', 64)->nullable()->comment('Client IP');
            $table->string('user_agent', 512)->nullable()->comment('Client user agent');
            $table->string('method', 10)->nullable()->comment('HTTP method');
            $table->string('path', 255)->nullable()->comment('HTTP path');
            $table->string('summary', 255)->nullable()->comment('Readable audit summary');
            $table->json('before_snapshot')->nullable()->comment('Sanitized data before write');
            $table->json('after_snapshot')->nullable()->comment('Sanitized data after write');
            $table->json('diff')->nullable()->comment('Sanitized changed keys');
            $table->json('metadata')->nullable()->comment('Extra metadata');
            $table->timestamp('created_at')->nullable();

            $table->index(['tenant_id', 'created_at'], 'idx_edu_audit_logs_tenant_created');
            $table->index(['tenant_id', 'module', 'created_at'], 'idx_edu_audit_logs_tenant_module_created');
            $table->index(['tenant_id', 'action', 'created_at'], 'idx_edu_audit_logs_tenant_action_created');
            $table->index(['tenant_id', 'actor_user_id', 'created_at'], 'idx_edu_audit_logs_tenant_actor_created');
            $table->index(['tenant_id', 'campus_id', 'created_at'], 'idx_edu_audit_logs_campus_created');
            $table->index(['business_type', 'business_id'], 'idx_edu_audit_logs_business');
            $table->index('request_id', 'idx_edu_audit_logs_request');
            $table->index('created_at', 'idx_edu_audit_logs_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_audit_logs');
    }
};
```

## MineAdmin Backend Module Design

### Model and Enum

Create `AuditActorType`:

```php
<?php

namespace App\Model\Enums\Education\Foundation;

enum AuditActorType: string
{
    case Admin = 'admin';
    case Teacher = 'teacher';
    case Guardian = 'guardian';
    case System = 'system';
}
```

Create `EducationAuditLog`:

```text
Extends: MineAdmin model base used by generated project.
Table: edu_audit_logs
Timestamps: created_at only; no updated_at and no soft delete.
Fillable: tenant_id, campus_id, actor_user_id, actor_type, actor_role_code, module, resource, action, business_type, business_id, request_id, ip_address, user_agent, method, path, summary, before_snapshot, after_snapshot, diff, metadata, created_at.
Casts: tenant_id integer, campus_id integer, actor_user_id integer, before_snapshot array, after_snapshot array, diff array, metadata array, created_at datetime.
Guard: no update/delete methods are exposed through service or controller.
```

### Audit Event

Create `EducationAuditEvent` with constructor properties:

```php
public function __construct(
    public readonly string $module,
    public readonly string $resource,
    public readonly string $action,
    public readonly string $businessType,
    public readonly int|string|null $businessId,
    public readonly ?EducationUserContext $context,
    public readonly array $beforeSnapshot = [],
    public readonly array $afterSnapshot = [],
    public readonly array $metadata = [],
    public readonly ?string $summary = null,
    public readonly string $actorType = 'admin',
) {
}
```

Rules:

- `module`, `resource`, `action`, and `businessType` are required non-empty strings.
- `businessId` is stored as string when present.
- `context` can be null only for system jobs.
- `actorType` must be one of `admin`, `teacher`, `guardian`, or `system`.
- Event listeners do not mutate event properties.

### Audit Logger Contract

Create `AuditLoggerInterface`:

```php
<?php

namespace App\Contract\Education\Foundation;

use App\Event\Education\Foundation\EducationAuditEvent;
use App\Model\Education\Foundation\EducationAuditLog;

interface AuditLoggerInterface
{
    public function record(EducationAuditEvent $event): EducationAuditLog;
}
```

### Audit Context Resolver

Create `AuditContextResolver` methods:

```php
public function resolveRequestId(): ?string
public function resolveIpAddress(): ?string
public function resolveUserAgent(): ?string
public function resolveMethod(): ?string
public function resolvePath(): ?string
public function resolveActorRoleCode(?EducationUserContext $context): ?string
public function resolveTenantId(?EducationUserContext $context): ?int
public function resolveCampusId(?EducationUserContext $context, array $metadata): ?int
```

Rules:

- Read HTTP request data from Hyperf `ServerRequestInterface` when a request exists.
- Prefer `X-Request-Id`; fallback to request attribute `request_id`; return null when both are missing.
- Use `X-Forwarded-For` first IP when present; fallback to server params.
- Use `metadata['campus_id']` when explicitly provided and allowed by the F02 context.
- Return null campus id for tenant-wide writes.

### Audit Logger Service

Create `AuditLogger` implementing `AuditLoggerInterface`.

Methods:

```php
public function record(EducationAuditEvent $event): EducationAuditLog
private function sanitize(array $payload): array
private function buildDiff(array $before, array $after): array
private function truncateString(string $value, int $maxLength): string
```

Sanitization rules:

```text
Drop keys matching: password, password_hash, token, access_token, refresh_token, authorization, secret, openid, unionid, id_card, bank_card.
Mask keys matching: phone, mobile, email.
Preserve scalar values, arrays, and JSON-serializable objects.
Truncate string values longer than 1000 characters.
```

Diff rules:

```text
For each key in before/after union, include key when values differ.
Diff item shape: {"before": oldValue, "after": newValue}
Do not include dropped sensitive keys.
```

Create log payload:

```php
[
    'tenant_id' => $this->contextResolver->resolveTenantId($event->context),
    'campus_id' => $this->contextResolver->resolveCampusId($event->context, $event->metadata),
    'actor_user_id' => $event->context?->userId,
    'actor_type' => $event->actorType,
    'actor_role_code' => $this->contextResolver->resolveActorRoleCode($event->context),
    'module' => $event->module,
    'resource' => $event->resource,
    'action' => $event->action,
    'business_type' => $event->businessType,
    'business_id' => $event->businessId === null ? null : (string) $event->businessId,
    'request_id' => $this->contextResolver->resolveRequestId(),
    'ip_address' => $this->contextResolver->resolveIpAddress(),
    'user_agent' => $this->contextResolver->resolveUserAgent(),
    'method' => $this->contextResolver->resolveMethod(),
    'path' => $this->contextResolver->resolvePath(),
    'summary' => $event->summary,
    'before_snapshot' => $this->sanitize($event->beforeSnapshot),
    'after_snapshot' => $this->sanitize($event->afterSnapshot),
    'diff' => $this->buildDiff($event->beforeSnapshot, $event->afterSnapshot),
    'metadata' => $this->sanitize($event->metadata),
    'created_at' => date('Y-m-d H:i:s'),
]
```

### Audit Listener

Create `EducationAuditListener`.

Methods:

```php
public function listen(): array
public function process(object $event): void
```

Behavior:

```text
listen() returns [EducationAuditEvent::class].
process() ignores non-EducationAuditEvent objects.
process() calls AuditLoggerInterface::record($event).
Logger exceptions are not swallowed; write transactions must roll back if audit persistence fails.
```

Register listener in:

```text
mineadmin-education-saas/backend/config/autoload/listeners.php
```

Add:

```php
App\Listener\Education\Foundation\EducationAuditListener::class,
```

### Repository

Create `AuditLogRepository`.

Extends:

```text
App\Repository\IRepository
```

Methods:

```php
public function getModel(): string
public function createLog(array $data): EducationAuditLog
public function pageByContext(array $filters, EducationUserContext $context): array
public function findVisibleById(int $id, EducationUserContext $context): ?EducationAuditLog
private function applyContextScope($query, EducationUserContext $context): void
private function applyFilters($query, array $filters): void
```

Context scope:

```text
platform admin: no tenant filter.
tenant admin: tenant_id equals context tenant id.
campus-scoped role: tenant_id equals context tenant id and campus_id is null or in context campus ids.
system context: only used by logger writes; admin query APIs never use system context.
```

Filters:

```text
tenant_id: platform admin only; ignored for tenant roles.
campus_id: allowed only when platform admin or campus id is inside current context.
module: exact match.
resource: exact match.
action: exact match.
business_type: exact match.
business_id: exact string match.
actor_user_id: exact match.
actor_type: exact match.
start_at: created_at >= start_at.
end_at: created_at <= end_at.
keyword: matches action, summary, business_type, business_id, request_id.
```

Sorting:

```text
Default order: created_at desc, id desc.
No custom sort parameter in F04.
```

### Service

Create `AuditLogService`.

Extends:

```text
App\Service\IService
```

Methods:

```php
public function page(array $filters, EducationUserContext $context): array
public function detail(int $id, EducationUserContext $context): array
```

Page response mapping:

```text
Return list items without before_snapshot, after_snapshot, diff, and metadata.
Fields: id, tenant_id, campus_id, actor_user_id, actor_type, actor_role_code, module, resource, action, business_type, business_id, request_id, ip_address, method, path, summary, created_at.
```

Detail response mapping:

```text
Return all page fields plus user_agent, before_snapshot, after_snapshot, diff, metadata.
Throw business exception with code 404 when the log does not exist or is not visible in current context.
```

### Request Classes

Create `AuditLogPageRequest` rules:

```php
[
    'page' => ['required', 'integer', 'min:1'],
    'pageSize' => ['required', 'integer', 'between:1,100'],
    'tenant_id' => ['nullable', 'integer', 'min:1'],
    'campus_id' => ['nullable', 'integer', 'min:1'],
    'module' => ['nullable', 'string', 'max:60'],
    'resource' => ['nullable', 'string', 'max:80'],
    'action' => ['nullable', 'string', 'max:120'],
    'business_type' => ['nullable', 'string', 'max:80'],
    'business_id' => ['nullable', 'string', 'max:80'],
    'actor_user_id' => ['nullable', 'integer', 'min:1'],
    'actor_type' => ['nullable', 'in:admin,teacher,guardian,system'],
    'keyword' => ['nullable', 'string', 'max:120'],
    'start_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
    'end_at' => ['nullable', 'date_format:Y-m-d H:i:s', 'after_or_equal:start_at'],
]
```

Messages:

```text
page.required: page is required
pageSize.between: pageSize must be between 1 and 100
actor_type.in: actor_type must be one of admin, teacher, guardian, system
end_at.after_or_equal: end_at must be greater than or equal to start_at
```

Create `AuditLogDetailRequest` rules:

```php
[
    'id' => ['required', 'integer', 'min:1'],
]
```

### Controller

Create `AuditLogController`.

Path:

```text
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/AuditLogController.php
```

Controller contract:

```php
#[Controller(prefix: 'admin/education/foundation/audit-logs')]
#[Auth]
final class AuditLogController extends AbstractController
{
    public function __construct(
        private readonly AuditLogService $service,
        private readonly EducationUserContextResolver $contextResolver,
    ) {
    }

    #[GetMapping('page')]
    #[Permission('education:foundation:audit-log:page')]
    public function page(AuditLogPageRequest $request): Result
    {
        return $this->success(
            $this->service->page($request->validated(), $this->contextResolver->resolve())
        );
    }

    #[GetMapping('{id}')]
    #[Permission('education:foundation:audit-log:detail')]
    public function detail(int $id, AuditLogDetailRequest $request): Result
    {
        return $this->success(
            $this->service->detail($id, $this->contextResolver->resolve())
        );
    }
}
```

Middleware:

```text
Use MineAdmin Auth and Permission middleware through controller attributes.
Do not add OperationMiddleware on read-only audit endpoints.
Write controllers from F01-F03 keep OperationMiddleware and dispatch audit events in service layer.
```

### Schema

Create `AuditLogSchema` fields:

```text
id: integer
tenant_id: integer nullable
campus_id: integer nullable
actor_user_id: integer nullable
actor_type: string enum admin|teacher|guardian|system
actor_role_code: string nullable
module: string
resource: string
action: string
business_type: string
business_id: string nullable
request_id: string nullable
ip_address: string nullable
user_agent: string nullable
method: string nullable
path: string nullable
summary: string nullable
before_snapshot: object nullable
after_snapshot: object nullable
diff: object nullable
metadata: object nullable
created_at: datetime
```

### Foundation Write Audit Action Matrix

Modify existing Foundation services so these successful write operations dispatch `EducationAuditEvent`:

| File | Method | Action | Business type | Snapshot rule |
| --- | --- | --- | --- | --- |
| `TenantService.php` | `create` | `education.foundation.tenant.created` | `tenant` | after only |
| `TenantService.php` | `update` | `education.foundation.tenant.updated` | `tenant` | before and after |
| `TenantService.php` | `changeStatus` | `education.foundation.tenant.status_changed` | `tenant` | before and after |
| `CampusService.php` | `create` | `education.foundation.campus.created` | `campus` | after only |
| `CampusService.php` | `update` | `education.foundation.campus.updated` | `campus` | before and after |
| `CampusService.php` | `changeStatus` | `education.foundation.campus.status_changed` | `campus` | before and after |
| `UserProfileService.php` | `create` | `education.foundation.user_profile.created` | `user_profile` | after only |
| `UserProfileService.php` | `update` | `education.foundation.user_profile.updated` | `user_profile` | before and after |
| `UserProfileService.php` | `changeStatus` | `education.foundation.user_profile.status_changed` | `user_profile` | before and after |
| `CampusScopeService.php` | `saveScopes` | `education.foundation.campus_scope.saved` | `campus_scope` | before and after |
| `DictionaryService.php` | `createType` | `education.foundation.dict_type.created` | `dict_type` | after only |
| `DictionaryService.php` | `updateType` | `education.foundation.dict_type.updated` | `dict_type` | before and after |
| `DictionaryService.php` | `changeTypeStatus` | `education.foundation.dict_type.status_changed` | `dict_type` | before and after |
| `DictionaryService.php` | `createItem` | `education.foundation.dict_item.created` | `dict_item` | after only |
| `DictionaryService.php` | `updateItem` | `education.foundation.dict_item.updated` | `dict_item` | before and after |
| `DictionaryService.php` | `changeItemStatus` | `education.foundation.dict_item.status_changed` | `dict_item` | before and after |
| `FeatureFlagService.php` | `create` | `education.foundation.feature_flag.created` | `feature_flag` | after only |
| `FeatureFlagService.php` | `update` | `education.foundation.feature_flag.updated` | `feature_flag` | before and after |
| `FeatureFlagService.php` | `changeStatus` | `education.foundation.feature_flag.status_changed` | `feature_flag` | before and after |

Dispatch shape:

```php
$this->eventDispatcher->dispatch(new EducationAuditEvent(
    module: 'foundation',
    resource: 'tenant',
    action: 'education.foundation.tenant.updated',
    businessType: 'tenant',
    businessId: $tenant->id,
    context: $context,
    beforeSnapshot: $before,
    afterSnapshot: $tenant->toArray(),
    metadata: ['tenant_id' => $tenant->id],
    summary: sprintf('Tenant %s updated', $tenant->name),
));
```

## API Contract

### API 1: Audit Log Page

```text
GET /admin/education/foundation/audit-logs/page
Permission: education:foundation:audit-log:page
Caller: platform admin, tenant admin, campus-scoped admin
Isolation: repository applies F02 user context and campus scope
Audit: read operation, no audit log
```

Headers:

```text
Authorization: Bearer test-admin-token
X-Tenant-Id: 1001
X-Campus-Id: 2001
X-Request-Id: req-audit-page-001
```

Query:

```json
{
  "page": 1,
  "pageSize": 20,
  "module": "foundation",
  "resource": "campus",
  "action": "education.foundation.campus.updated",
  "business_type": "campus",
  "business_id": "2001",
  "actor_type": "admin",
  "actor_user_id": 501,
  "keyword": "Campus",
  "start_at": "2026-06-10 00:00:00",
  "end_at": "2026-06-10 23:59:59"
}
```

Success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 9001,
        "tenant_id": 1001,
        "campus_id": 2001,
        "actor_user_id": 501,
        "actor_type": "admin",
        "actor_role_code": "tenant_admin",
        "module": "foundation",
        "resource": "campus",
        "action": "education.foundation.campus.updated",
        "business_type": "campus",
        "business_id": "2001",
        "request_id": "req-audit-page-001",
        "ip_address": "127.0.0.1",
        "method": "PUT",
        "path": "/admin/education/foundation/campuses/2001",
        "summary": "Campus East updated",
        "created_at": "2026-06-10 10:10:00"
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
  "message": "end_at must be greater than or equal to start_at",
  "data": {
    "field": "end_at"
  }
}
```

Business failure:

```json
{
  "code": 403,
  "message": "permission denied",
  "data": {
    "permission": "education:foundation:audit-log:page"
  }
}
```

### API 2: Audit Log Detail

```text
GET /admin/education/foundation/audit-logs/{id}
Permission: education:foundation:audit-log:detail
Caller: platform admin, tenant admin, campus-scoped admin
Isolation: repository applies F02 user context and campus scope to the single row
Audit: read operation, no audit log
```

Headers:

```text
Authorization: Bearer test-admin-token
X-Tenant-Id: 1001
X-Campus-Id: 2001
X-Request-Id: req-audit-detail-001
```

Path:

```json
{
  "id": 9001
}
```

Success:

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 9001,
    "tenant_id": 1001,
    "campus_id": 2001,
    "actor_user_id": 501,
    "actor_type": "admin",
    "actor_role_code": "tenant_admin",
    "module": "foundation",
    "resource": "campus",
    "action": "education.foundation.campus.updated",
    "business_type": "campus",
    "business_id": "2001",
    "request_id": "req-campus-update-001",
    "ip_address": "127.0.0.1",
    "user_agent": "MineAdminTest/1.0",
    "method": "PUT",
    "path": "/admin/education/foundation/campuses/2001",
    "summary": "Campus East updated",
    "before_snapshot": {
      "name": "Campus East",
      "status": "enabled"
    },
    "after_snapshot": {
      "name": "Campus East Plus",
      "status": "enabled"
    },
    "diff": {
      "name": {
        "before": "Campus East",
        "after": "Campus East Plus"
      }
    },
    "metadata": {
      "tenant_id": 1001,
      "campus_id": 2001
    },
    "created_at": "2026-06-10 10:10:00"
  }
}
```

Validation failure:

```json
{
  "code": 422,
  "message": "id must be at least 1",
  "data": {
    "field": "id"
  }
}
```

Business failure:

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

### API Client

Create:

```text
mineadmin-education-saas/admin-web/src/api/education/foundation/auditLog.ts
```

Types:

```ts
export type AuditActorType = 'admin' | 'teacher' | 'guardian' | 'system'

export interface AuditLogListItem {
  id: number
  tenant_id: number | null
  campus_id: number | null
  actor_user_id: number | null
  actor_type: AuditActorType
  actor_role_code: string | null
  module: string
  resource: string
  action: string
  business_type: string
  business_id: string | null
  request_id: string | null
  ip_address: string | null
  method: string | null
  path: string | null
  summary: string | null
  created_at: string
}

export interface AuditLogDetail extends AuditLogListItem {
  user_agent: string | null
  before_snapshot: Record<string, unknown> | null
  after_snapshot: Record<string, unknown> | null
  diff: Record<string, { before: unknown; after: unknown }> | null
  metadata: Record<string, unknown> | null
}

export interface AuditLogPageParams {
  page: number
  pageSize: number
  tenant_id?: number
  campus_id?: number
  module?: string
  resource?: string
  action?: string
  business_type?: string
  business_id?: string
  actor_user_id?: number
  actor_type?: AuditActorType
  keyword?: string
  start_at?: string
  end_at?: string
}
```

Methods:

```ts
export function pageAuditLogs(params: AuditLogPageParams) {
  return request.get<{ list: AuditLogListItem[]; total: number }>({
    url: '/admin/education/foundation/audit-logs/page',
    params,
  })
}

export function getAuditLogDetail(id: number) {
  return request.get<AuditLogDetail>({
    url: `/admin/education/foundation/audit-logs/${id}`,
  })
}
```

### Router and Menu

Modify:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Route:

```ts
{
  path: '/education/foundation/audit-logs',
  name: 'EducationFoundationAuditLogList',
  component: () => import('@/views/education/foundation/AuditLogList.vue'),
  meta: {
    title: 'Audit Logs',
    icon: 'i-lucide-file-clock',
    auth: ['education:foundation:audit-log:page'],
  },
}
```

Permissions:

```text
education:foundation:audit-log:page controls route and list query.
education:foundation:audit-log:detail controls payload drawer button.
No create, update, status, delete, import, or export button is registered in F04.
```

### Audit Log List Page

Create:

```text
mineadmin-education-saas/admin-web/src/views/education/foundation/AuditLogList.vue
```

Filters:

```text
keyword input: action, summary, business type, business id, request id.
module select: foundation initially; leave component capable of later modules.
resource select: tenant, campus, user_profile, campus_scope, dict_type, dict_item, feature_flag.
action input: exact action code.
business_type input.
business_id input.
actor_type select: admin, teacher, guardian, system.
actor_user_id numeric input.
date range picker maps to start_at/end_at.
```

Table columns:

```text
created_at fixed width 170
module width 120
resource width 140
action min width 260
business_type width 140
business_id width 120
actor_type width 110
actor_user_id width 120
campus_id width 120
ip_address width 140
summary min width 220
actions width 100
```

States:

```text
loading: table loading state true while pageAuditLogs request is pending.
empty: show MineAdmin empty state when total is 0.
error: show message.error from API error and keep previous filter values.
success: table rows and pagination update from response.
detailLoading: drawer shows loading while getAuditLogDetail request is pending.
detailError: drawer remains open and displays request error.
```

Actions:

```text
Search: resets page to 1 and calls pageAuditLogs.
Reset: clears filters, sets module to foundation, resets page to 1.
View payload: visible only with education:foundation:audit-log:detail permission; opens AuditPayloadDrawer.
```

### Payload Drawer

Create:

```text
mineadmin-education-saas/admin-web/src/views/education/foundation/components/AuditPayloadDrawer.vue
```

Sections:

```text
Basic info: id, created_at, actor, request, IP, method, path.
Before: formatted JSON from before_snapshot.
After: formatted JSON from after_snapshot.
Diff: formatted JSON from diff.
Metadata: formatted JSON from metadata.
```

Behavior:

```text
Drawer width: 720px on desktop; full width on mobile admin viewport.
JSON sections use monospace pre blocks with wrapping.
Null payload sections show a compact empty state.
No edit controls are rendered.
```

## Teacher / Guardian Mobile Page Tasks

This module has no teacher or guardian page because audit log query is an admin-only compliance and operations function.

Mobile impact:

```text
No mobile route is added.
No mobile API client is added.
No teacher or guardian navigation item is added.
F06 mobile context APIs can later use the same `EducationAuditEvent` for mobile writes.
```

Verification:

```text
Run mobile H5 build after F04 to prove the Foundation audit additions do not break the uni-app workspace.
```

## Test Plan

Backend tests:

| Test file | Case | Assertion |
| --- | --- | --- |
| `AuditLogMigrationTest.php` | `test_audit_log_table_has_required_columns_and_indexes` | table has all columns and indexes from migration design |
| `AuditLoggerTest.php` | `test_record_creates_audit_log_from_event` | row stores module, action, business id, actor, tenant, campus, and request metadata |
| `AuditLoggerTest.php` | `test_sensitive_fields_are_removed_or_masked` | password/token/openid keys are absent and phone/email are masked |
| `AuditLoggerTest.php` | `test_diff_contains_only_changed_safe_fields` | unchanged fields and sensitive fields are excluded from diff |
| `AuditContextResolverTest.php` | `test_request_context_resolves_headers_ip_method_and_path` | request id, IP, user agent, method, and path are extracted |
| `EducationAuditListenerTest.php` | `test_listener_records_education_audit_event` | listener calls logger and row exists |
| `EducationAuditListenerTest.php` | `test_listener_ignores_other_event_objects` | non-audit event creates no row |
| `AuditLogRepositoryTest.php` | `test_platform_admin_can_filter_all_tenants` | platform context returns rows from multiple tenants |
| `AuditLogRepositoryTest.php` | `test_tenant_admin_reads_only_own_tenant` | other tenant rows are excluded |
| `AuditLogRepositoryTest.php` | `test_campus_scoped_role_reads_tenant_wide_and_allowed_campus_rows` | campus_id null and allowed campus rows included; other campus excluded |
| `AuditLogServiceTest.php` | `test_page_hides_payload_fields` | list response excludes before, after, diff, metadata |
| `AuditLogServiceTest.php` | `test_detail_returns_payload_fields` | detail response includes before, after, diff, metadata |
| `AuditLogServiceTest.php` | `test_detail_throws_not_found_for_invisible_row` | tenant/campus scope miss returns 404 business exception |
| `FoundationAuditWriteIntegrationTest.php` | `test_tenant_update_dispatches_audit_log` | tenant update creates `education.foundation.tenant.updated` row |
| `FoundationAuditWriteIntegrationTest.php` | `test_campus_status_change_dispatches_audit_log` | campus status change creates status action row |
| `FoundationAuditWriteIntegrationTest.php` | `test_dictionary_item_update_dispatches_audit_log` | dictionary item update creates dict item action row |
| `FoundationAuditWriteIntegrationTest.php` | `test_feature_flag_update_dispatches_audit_log` | feature flag update creates feature flag action row |
| `AuditLogAdminApiTest.php` | `test_page_returns_mineadmin_result_shape` | response contains code, message, data.list, data.total |
| `AuditLogAdminApiTest.php` | `test_detail_returns_payload` | detail API returns JSON snapshots and diff |
| `AuditLogAdminApiTest.php` | `test_invalid_date_range_returns_422` | validation failure matches API contract |
| `AuditLogPermissionTest.php` | `test_page_requires_permission` | missing `education:foundation:audit-log:page` returns 403 |
| `AuditLogPermissionTest.php` | `test_detail_requires_permission` | missing `education:foundation:audit-log:detail` returns 403 |
| `AuditLogIsolationTest.php` | `test_tenant_admin_cannot_read_other_tenant_detail` | invisible id returns 404 |
| `AuditLogIsolationTest.php` | `test_campus_user_cannot_read_other_campus_detail` | other campus id returns 404 |

PC tests:

| Test file | Case | Assertion |
| --- | --- | --- |
| `AuditLogList.spec.ts` | `loads_audit_logs_with_default_foundation_filter` | API called with page, pageSize, module foundation |
| `AuditLogList.spec.ts` | `search_maps_filters_to_api_params` | action, business id, actor type, date range sent to API |
| `AuditLogList.spec.ts` | `reset_clears_filters_and_reloads` | filters cleared and page reset to 1 |
| `AuditLogList.spec.ts` | `detail_button_hidden_without_permission` | no payload button when detail permission missing |
| `AuditLogList.spec.ts` | `does_not_render_write_buttons` | no create, edit, status, delete, import, or export button exists |
| `AuditPayloadDrawer.spec.ts` | `renders_before_after_diff_and_metadata` | drawer shows formatted JSON sections |
| `AuditPayloadDrawer.spec.ts` | `shows_empty_state_for_null_payload` | null payload displays empty state |

Mobile verification:

| Command | Assertion |
| --- | --- |
| `pnpm build:h5` | mobile workspace still builds with no F04 mobile pages |

## Execution Commands

### Backend Migration Gate

Run:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter AuditLogMigrationTest
php bin/hyperf.php migrate:rollback --step=1
php bin/hyperf.php migrate
```

Expected:

```text
Migrated: 2026_06_10_000400_create_education_audit_logs_table
AuditLogMigrationTest passes.
Rolled back: 2026_06_10_000400_create_education_audit_logs_table
Migrated: 2026_06_10_000400_create_education_audit_logs_table
```

### Backend Unit Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter AuditLoggerTest
composer test -- --filter AuditContextResolverTest
composer test -- --filter EducationAuditListenerTest
composer test -- --filter AuditLogRepositoryTest
composer test -- --filter AuditLogServiceTest
```

Expected:

```text
Audit logger, context resolver, listener, repository, and service tests pass.
```

### Backend Feature Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter FoundationAuditWriteIntegrationTest
composer test -- --filter AuditLogAdminApiTest
composer test -- --filter AuditLogPermissionTest
composer test -- --filter AuditLogIsolationTest
```

Expected:

```text
Foundation write audit integration, admin API, permission, and isolation tests pass.
```

### PC Gate

Run:

```bash
cd mineadmin-education-saas/admin-web
pnpm lint
pnpm test -- AuditLogList
pnpm test -- AuditPayloadDrawer
pnpm build
```

Expected:

```text
PC lint, audit list tests, payload drawer tests, and production build pass.
```

### Mobile Regression Gate

Run:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm build:h5
```

Expected:

```text
Mobile H5 build passes with no F04 mobile route changes.
```

### F04 Final Gate

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter Education\\\\Foundation
composer cs-fix -- --dry-run
composer analyse
cd ../admin-web
pnpm lint
pnpm test -- AuditLog
pnpm build
cd ../mobile-uniapp
pnpm build:h5
```

Expected:

```text
All Education Foundation backend tests pass.
Code style dry run passes.
Static analysis passes.
PC lint, tests, and build pass.
Mobile H5 build passes.
```

## Acceptance Gate

F04 is accepted only when all conditions are true:

```text
- `edu_audit_logs` exists with all documented columns and indexes.
- Audit rows are immutable through the F04 service and controller surface.
- Sensitive fields are dropped or masked before persistence.
- Foundation tenant, campus, user profile, campus scope, dictionary, and feature flag writes create audit rows.
- Audit write failures roll back the business write transaction.
- Platform admins can query all audit logs.
- Tenant admins can query only their tenant audit logs.
- Campus-scoped roles can query tenant-wide rows and rows for allowed campuses only.
- Page API returns MineAdmin result shape and excludes payload snapshots.
- Detail API returns snapshots and diff only for visible rows.
- PC audit page has filters, loading, empty, error, success, and detail drawer states.
- PC page renders no create, edit, status, delete, import, or export actions.
- Teacher and guardian mobile clients have no new audit page and the mobile build still passes.
```

## Task Breakdown

### Task 1: Create Audit Migration, Enum, and Model

**Files:**

- Create: `mineadmin-education-saas/backend/databases/migrations/2026_06_10_000400_create_education_audit_logs_table.php`
- Create: `mineadmin-education-saas/backend/app/Model/Enums/Education/Foundation/AuditActorType.php`
- Create: `mineadmin-education-saas/backend/app/Model/Education/Foundation/EducationAuditLog.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/AuditLogMigrationTest.php`

- [x] **Step 1: Create migration**

Use the full migration code from `Database Migration Design`.

- [x] **Step 2: Create actor enum**

Use the `AuditActorType` enum from `MineAdmin Backend Module Design`.

- [x] **Step 3: Create audit log model**

Use the model table, fillable, casts, and immutable rules from `MineAdmin Backend Module Design`.

- [x] **Step 4: Write migration test**

Create `AuditLogMigrationTest` with assertions from `Test Plan`.

- [x] **Step 5: Run migration gate**

Run the commands from `Backend Migration Gate`.

Expected:

```text
Migration, rollback, re-migration, and migration test pass.
```

### Task 2: Create Audit Event, Logger, Context Resolver, and Listener

**Files:**

- Create: `mineadmin-education-saas/backend/app/Contract/Education/Foundation/AuditLoggerInterface.php`
- Create: `mineadmin-education-saas/backend/app/Event/Education/Foundation/EducationAuditEvent.php`
- Create: `mineadmin-education-saas/backend/app/Listener/Education/Foundation/EducationAuditListener.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Foundation/AuditContextResolver.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Foundation/AuditLogger.php`
- Modify: `mineadmin-education-saas/backend/config/autoload/listeners.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/AuditLoggerTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/AuditContextResolverTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/EducationAuditListenerTest.php`

- [x] **Step 1: Create audit contract and event**

Use `AuditLoggerInterface` and `EducationAuditEvent` from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create request context resolver**

Implement `AuditContextResolver` methods and request extraction rules.

- [x] **Step 3: Create logger**

Implement `record`, `sanitize`, `buildDiff`, and `truncateString` exactly as defined in `MineAdmin Backend Module Design`.

- [x] **Step 4: Create listener and register it**

Implement `EducationAuditListener` and add it to `config/autoload/listeners.php`.

- [x] **Step 5: Write unit tests**

Create logger, context resolver, and listener tests listed in `Test Plan`.

- [x] **Step 6: Run backend unit gate**

Run the commands from `Backend Unit Gate`.

Expected:

```text
Audit event, logger, context resolver, listener, repository-independent tests pass.
```

### Task 3: Create Audit Repository, Service, Requests, Schema, and Controller

**Files:**

- Create: `mineadmin-education-saas/backend/app/Repository/Education/Foundation/AuditLogRepository.php`
- Create: `mineadmin-education-saas/backend/app/Service/Education/Foundation/AuditLogService.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/AuditLogPageRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Foundation/AuditLogDetailRequest.php`
- Create: `mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Foundation/AuditLogController.php`
- Create: `mineadmin-education-saas/backend/app/Schema/Education/Foundation/AuditLogSchema.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/AuditLogRepositoryTest.php`
- Test: `mineadmin-education-saas/backend/tests/Unit/Education/Foundation/AuditLogServiceTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/AuditLogAdminApiTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/AuditLogPermissionTest.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/AuditLogIsolationTest.php`

- [x] **Step 1: Create repository**

Implement repository methods, context scope, filters, and sorting from `MineAdmin Backend Module Design`.

- [x] **Step 2: Create query service**

Implement page/detail response mapping and not-found behavior from `MineAdmin Backend Module Design`.

- [x] **Step 3: Create request classes**

Use `AuditLogPageRequest` and `AuditLogDetailRequest` validation rules and messages.

- [x] **Step 4: Create schema**

Use `AuditLogSchema` fields from `MineAdmin Backend Module Design`.

- [x] **Step 5: Create controller**

Use controller prefix, methods, permissions, context resolver, and result shape from `MineAdmin Backend Module Design`.

- [x] **Step 6: Write repository, service, API, permission, and isolation tests**

Create the test files and cases listed in `Test Plan`.

- [x] **Step 7: Run repository, service, and API tests**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter AuditLogRepositoryTest
composer test -- --filter AuditLogServiceTest
composer test -- --filter AuditLogAdminApiTest
composer test -- --filter AuditLogPermissionTest
composer test -- --filter AuditLogIsolationTest
```

Expected:

```text
Audit query, API contract, permission, and isolation tests pass.
```

### Task 4: Add Foundation Write Audit Dispatch

**Files:**

- Modify: `mineadmin-education-saas/backend/app/Service/Education/Foundation/TenantService.php`
- Modify: `mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusService.php`
- Modify: `mineadmin-education-saas/backend/app/Service/Education/Foundation/UserProfileService.php`
- Modify: `mineadmin-education-saas/backend/app/Service/Education/Foundation/CampusScopeService.php`
- Modify: `mineadmin-education-saas/backend/app/Service/Education/Foundation/DictionaryService.php`
- Modify: `mineadmin-education-saas/backend/app/Service/Education/Foundation/FeatureFlagService.php`
- Test: `mineadmin-education-saas/backend/tests/Feature/Education/Foundation/FoundationAuditWriteIntegrationTest.php`

- [x] **Step 1: Inject event dispatcher into Foundation services**

Add `Psr\EventDispatcher\EventDispatcherInterface` to the constructor of each modified service.

- [x] **Step 2: Capture before and after snapshots**

For update and status methods, read the persisted row before mutation and refresh the row after mutation.

- [x] **Step 3: Dispatch audit events**

Use the action matrix and dispatch shape from `Foundation Write Audit Action Matrix`.

- [x] **Step 4: Keep audit dispatch inside the write transaction**

Place the event dispatch inside the same transaction closure as the business write so audit failures roll back the write.

- [x] **Step 5: Write Foundation write integration tests**

Create cases listed under `FoundationAuditWriteIntegrationTest` in `Test Plan`.

- [x] **Step 6: Run write integration gate**

Run:

```bash
cd mineadmin-education-saas/backend
composer test -- --filter FoundationAuditWriteIntegrationTest
```

Expected:

```text
Tenant, campus, dictionary, and feature flag write operations create expected audit log rows.
```

### Task 5: Create PC Audit Log Page

**Files:**

- Create: `mineadmin-education-saas/admin-web/src/api/education/foundation/auditLog.ts`
- Modify: `mineadmin-education-saas/admin-web/src/router/modules/education.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/AuditLogList.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/components/AuditPayloadDrawer.vue`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/AuditLogList.spec.ts`
- Create: `mineadmin-education-saas/admin-web/src/views/education/foundation/__tests__/AuditPayloadDrawer.spec.ts`

- [x] **Step 1: Create API client**

Use TypeScript types and methods from `PC Admin Page Tasks`.

- [x] **Step 2: Add router entry**

Add the audit log route and menu permission from `Router and Menu`.

- [x] **Step 3: Create list page**

Implement filters, table columns, states, and actions from `Audit Log List Page`.

- [x] **Step 4: Create payload drawer**

Implement drawer sections and behavior from `Payload Drawer`.

- [x] **Step 5: Add PC tests**

Create list and drawer tests listed in `Test Plan`.

- [x] **Step 6: Run PC gate**

Run the commands from `PC Gate`.

Expected:

```text
PC audit log API client, route, list page, drawer, tests, lint, and build pass.
```

### Task 6: Run F04 Final Gate

**Files:**

- Verify: all backend, PC, and mobile paths listed in `File Structure`.

- [x] **Step 1: Run backend final gate**

Run backend commands from `F04 Final Gate`.

- [x] **Step 2: Run PC final gate**

Run PC commands from `F04 Final Gate`.

- [x] **Step 3: Run mobile regression gate**

Run mobile command from `F04 Final Gate`.

- [x] **Step 4: Commit F04**

Run:

```bash
cd mineadmin-education-saas
git add backend admin-web mobile-uniapp
git commit -m "feat: add education foundation audit logs"
```

Expected:

```text
Commit succeeds with F04 backend, PC, and verification changes.
```

## Self-Review

- Spec coverage: F04 covers audit storage, logger, listener, Foundation write dispatch, admin query APIs, PC page, tests, commands, and acceptance gates.
- MineAdmin fit: Paths use MineAdmin 3.x `databases/migrations`, `app/Http/Admin/Controller`, `app/Http/Admin/Request`, `app/Repository/IRepository`, `app/Service/IService`, `app/Schema`, permission attributes, result shape, and listener registration.
- Audit correctness: Audit rows are immutable, write failures roll back with business transactions, and read APIs do not create audit rows.
- Tenant isolation: Repository and detail lookup apply platform, tenant, and campus-scope rules from F02.
- Privacy: Logger drops token/password/openid/secret/id-card/bank-card fields, masks phone/email fields, and truncates long strings.
- API readiness: Page and detail APIs include permissions, headers, request payloads, success responses, validation failures, business failures, isolation rules, and audit rules.
- PC fit: Audit page includes API client, route, list filters, table columns, permission-controlled detail button, payload drawer, loading, empty, error, and success states.
- Mobile fit: F04 has no teacher or guardian visible page by design, and mobile build remains part of the acceptance gate.
- Readiness: This plan has exact paths, full migration design, backend layer tasks, API request/response/failure examples, PC tasks, mobile rationale, tests, commands, expected outputs, and acceptance gates, so F04 can be marked `ready`.
