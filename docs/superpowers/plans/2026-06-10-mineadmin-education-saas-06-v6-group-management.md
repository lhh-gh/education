# MineAdmin Education SaaS V6 Group Management Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V6 企业增强与多校区集团管理 for organization units, campus hierarchy, cross-campus data permissions, lightweight approvals, contracts, group metrics, franchise reservation fields, and risk audit events.

**Architecture:** V6 extends Foundation campus scope with group organization units and explicit data permission scopes. Approval and contract modules use simple state machines and audit logs; this is not a BPMN workflow engine.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, Redis queue, MineAdmin-Vue, Vue3, TypeScript, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. V6 group management gates have passed.

---

## Scope Check

Included:

- Organization unit tree and campus-org relation management.
- Data permission scopes and user data permissions for group, org, campus, and self scopes.
- Lightweight approval templates, nodes, instances, tasks, and logs.
- Contract records, parties, attachments, renewals, expiry reminders, and state changes.
- Group operation metrics, franchise reservation records, and risk audit events.
- PC admin pages for group governance, data permissions, approvals, contracts, dashboards, and audit.

Excluded:

- Full BPMN workflow engine and visual process designer.
- Legal e-signature and external contract signing integration.
- Teacher and guardian mobile pages; V6 is governance/admin-only.

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_060000_create_v6_group_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Group/OrgUnitStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Group/DataScopeType.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Group/ApprovalStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Group/ContractStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Group/RiskLevel.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationOrgUnit.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationCampusOrgRelation.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationDataPermissionScope.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationUserDataPermission.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationApprovalTemplate.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationApprovalNode.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationApprovalInstance.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationApprovalTask.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationApprovalLog.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationContract.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationContractParty.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationContractAttachment.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationContractRenewal.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationGroupOperationMetric.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationFranchiseRecord.php
mineadmin-education-saas/backend/app/Model/Education/Group/EducationRiskAuditEvent.php
mineadmin-education-saas/backend/app/Repository/Education/Group/OrgUnitRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Group/DataPermissionRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Group/ApprovalTemplateRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Group/ApprovalInstanceRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Group/ContractRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Group/ContractRenewalRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Group/GroupMetricRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Group/FranchiseRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Group/RiskAuditRepository.php
mineadmin-education-saas/backend/app/Service/Education/Group/OrgUnitService.php
mineadmin-education-saas/backend/app/Service/Education/Group/DataPermissionService.php
mineadmin-education-saas/backend/app/Service/Education/Group/ApprovalTemplateService.php
mineadmin-education-saas/backend/app/Service/Education/Group/ApprovalInstanceService.php
mineadmin-education-saas/backend/app/Service/Education/Group/ContractService.php
mineadmin-education-saas/backend/app/Service/Education/Group/ContractRenewalService.php
mineadmin-education-saas/backend/app/Service/Education/Group/GroupMetricService.php
mineadmin-education-saas/backend/app/Service/Education/Group/FranchiseService.php
mineadmin-education-saas/backend/app/Service/Education/Group/RiskAuditService.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Group/OrgUnitSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Group/CampusOrgRelationSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Group/DataPermissionSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Group/ApprovalTemplateSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Group/ApprovalInstanceCreateRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Group/ApprovalTaskCompleteRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Group/ContractSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Group/ContractRenewalSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Group/GroupMetricPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Group/FranchiseRecordSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Group/RiskAuditPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Group/OrgUnitController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Group/DataPermissionController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Group/ApprovalTemplateController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Group/ApprovalInstanceController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Group/ContractController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Group/ContractRenewalController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Group/GroupMetricController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Group/FranchiseController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Group/RiskAuditController.php
mineadmin-education-saas/backend/app/Schema/Education/Group/OrgUnitSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Group/DataPermissionSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Group/ApprovalSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Group/ContractSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Group/GroupMetricSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Group/FranchiseSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Group/RiskAuditSchema.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Group/GroupMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Group/OrgUnitServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Group/DataPermissionServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Group/ApprovalInstanceServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Group/ContractServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Group/RiskAuditServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Group/OrgUnitAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Group/DataPermissionAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Group/ApprovalAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Group/ContractAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Group/GroupPermissionIsolationAuditTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/group/org.ts
mineadmin-education-saas/admin-web/src/api/education/group/permission.ts
mineadmin-education-saas/admin-web/src/api/education/group/approval.ts
mineadmin-education-saas/admin-web/src/api/education/group/contract.ts
mineadmin-education-saas/admin-web/src/api/education/group/metric.ts
mineadmin-education-saas/admin-web/src/api/education/group/franchise.ts
mineadmin-education-saas/admin-web/src/api/education/group/risk-audit.ts
mineadmin-education-saas/admin-web/src/views/education/group/OrgUnitTree.vue
mineadmin-education-saas/admin-web/src/views/education/group/DataPermissionList.vue
mineadmin-education-saas/admin-web/src/views/education/group/ApprovalTemplateList.vue
mineadmin-education-saas/admin-web/src/views/education/group/ApprovalTaskList.vue
mineadmin-education-saas/admin-web/src/views/education/group/ContractList.vue
mineadmin-education-saas/admin-web/src/views/education/group/ContractRenewalList.vue
mineadmin-education-saas/admin-web/src/views/education/group/GroupOperationDashboard.vue
mineadmin-education-saas/admin-web/src/views/education/group/FranchiseRecordList.vue
mineadmin-education-saas/admin-web/src/views/education/group/RiskAuditEventList.vue
mineadmin-education-saas/admin-web/src/views/education/group/components/OrgUnitForm.vue
mineadmin-education-saas/admin-web/src/views/education/group/components/DataPermissionForm.vue
mineadmin-education-saas/admin-web/src/views/education/group/components/ApprovalTemplateEditor.vue
mineadmin-education-saas/admin-web/src/views/education/group/components/ContractForm.vue
mineadmin-education-saas/admin-web/src/views/education/group/components/ContractRenewalDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/group/__tests__/OrgUnitTree.spec.ts
mineadmin-education-saas/admin-web/src/views/education/group/__tests__/DataPermissionList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/group/__tests__/ApprovalTaskList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/group/__tests__/ContractList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/group/__tests__/RiskAuditEventList.spec.ts
```

Modify PC:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

## Database Migration Design

Migration file:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_060000_create_v6_group_tables.php
```

Shared columns:

```text
id bigint unsigned primary key auto increment
tenant_id bigint unsigned not null
campus_id bigint unsigned null
created_by bigint unsigned null
updated_by bigint unsigned null
created_at timestamp null
updated_at timestamp null
deleted_at timestamp null for mutable business/config records
```

Foreign-key policy:

```text
Use service-level validation for campus, user, org, approval, contract, and attachment references. Do not create physical foreign keys.
```

Rollback behavior:

```text
Drop tables in reverse order: edu_risk_audit_events, edu_franchise_records, edu_group_operation_metrics, edu_contract_renewals, edu_contract_attachments, edu_contract_parties, edu_contracts, edu_approval_logs, edu_approval_tasks, edu_approval_instances, edu_approval_nodes, edu_approval_templates, edu_user_data_permissions, edu_data_permission_scopes, edu_campus_org_relations, edu_org_units.
```

Table catalog:

| Table | Business columns | Indexes |
| --- | --- | --- |
| `edu_org_units` | `parent_id bigint unsigned null`, `code varchar(64) not null`, `name varchar(120) not null`, `unit_type varchar(40) not null`, `path varchar(500) not null`, `level int unsigned not null default 1`, `status varchar(20) not null default enabled`, `sort_order int not null default 0` | `unique uk_edu_org_units_tenant_code (tenant_id, code)`, `index idx_edu_org_units_parent (tenant_id, parent_id)`, `index idx_edu_org_units_path (tenant_id, path)` |
| `edu_campus_org_relations` | `org_unit_id bigint unsigned not null`, `campus_id bigint unsigned not null`, `relation_type varchar(40) not null default owned`, `effective_start date null`, `effective_end date null` | `unique uk_edu_campus_org_relations_org_campus (tenant_id, org_unit_id, campus_id)`, `index idx_edu_campus_org_relations_campus (tenant_id, campus_id)` |
| `edu_data_permission_scopes` | `scope_code varchar(64) not null`, `scope_name varchar(120) not null`, `scope_type varchar(40) not null`, `scope_value_json json not null`, `status varchar(20) not null default enabled` | `unique uk_edu_data_permission_scopes_code (tenant_id, scope_code)`, `index idx_edu_data_permission_scopes_type (tenant_id, scope_type, status)` |
| `edu_user_data_permissions` | `user_id bigint unsigned not null`, `scope_id bigint unsigned not null`, `scope_type varchar(40) not null`, `effective_start date null`, `effective_end date null`, `status varchar(20) not null default enabled` | `unique uk_edu_user_data_permissions_user_scope (tenant_id, user_id, scope_id)`, `index idx_edu_user_data_permissions_user_type (tenant_id, user_id, scope_type, status)` |
| `edu_approval_templates` | `template_code varchar(64) not null`, `template_name varchar(120) not null`, `business_type varchar(60) not null`, `status varchar(20) not null default enabled`, `version int unsigned not null default 1`, `config_json json null` | `unique uk_edu_approval_templates_code_version (tenant_id, template_code, version)`, `index idx_edu_approval_templates_business (tenant_id, business_type, status)` |
| `edu_approval_nodes` | `template_id bigint unsigned not null`, `node_code varchar(64) not null`, `node_name varchar(120) not null`, `sort_order int unsigned not null`, `assignee_type varchar(40) not null`, `assignee_value_json json not null` | `unique uk_edu_approval_nodes_template_node (tenant_id, template_id, node_code)`, `index idx_edu_approval_nodes_template_order (tenant_id, template_id, sort_order)` |
| `edu_approval_instances` | `template_id bigint unsigned not null`, `business_type varchar(60) not null`, `business_id bigint unsigned not null`, `status varchar(20) not null default pending`, `current_node_id bigint unsigned null`, `initiator_id bigint unsigned not null`, `payload_json json not null`, `completed_at timestamp null` | `unique uk_edu_approval_instances_business (tenant_id, business_type, business_id)`, `index idx_edu_approval_instances_status (tenant_id, campus_id, status)` |
| `edu_approval_tasks` | `approval_instance_id bigint unsigned not null`, `node_id bigint unsigned not null`, `assignee_user_id bigint unsigned not null`, `status varchar(20) not null default pending`, `due_at timestamp null`, `completed_at timestamp null`, `result varchar(20) null`, `comment varchar(500) null` | `index idx_edu_approval_tasks_assignee_status (tenant_id, assignee_user_id, status)`, `index idx_edu_approval_tasks_instance (tenant_id, approval_instance_id, status)` |
| `edu_approval_logs` | `approval_instance_id bigint unsigned not null`, `task_id bigint unsigned null`, `operator_id bigint unsigned not null`, `action varchar(40) not null`, `before_status varchar(20) null`, `after_status varchar(20) not null`, `comment varchar(500) null` | `index idx_edu_approval_logs_instance (tenant_id, approval_instance_id, created_at)`, `index idx_edu_approval_logs_operator (tenant_id, operator_id, created_at)` |
| `edu_contracts` | `contract_no varchar(64) not null`, `contract_type varchar(40) not null`, `title varchar(160) not null`, `counterparty_name varchar(160) not null`, `amount_cents bigint unsigned not null default 0`, `status varchar(20) not null default draft`, `start_date date null`, `end_date date null`, `owner_user_id bigint unsigned null`, `risk_level varchar(20) not null default normal` | `unique uk_edu_contracts_tenant_no (tenant_id, contract_no)`, `index idx_edu_contracts_status_expire (tenant_id, campus_id, status, end_date)`, `index idx_edu_contracts_owner (tenant_id, owner_user_id)` |
| `edu_contract_parties` | `contract_id bigint unsigned not null`, `party_type varchar(40) not null`, `party_name varchar(160) not null`, `contact_name varchar(120) null`, `contact_mobile varchar(30) null`, `identity_no varchar(120) null` | `index idx_edu_contract_parties_contract (tenant_id, contract_id)`, `index idx_edu_contract_parties_name (tenant_id, party_name)` |
| `edu_contract_attachments` | `contract_id bigint unsigned not null`, `file_name varchar(160) not null`, `file_url varchar(255) not null`, `file_size bigint unsigned not null default 0`, `uploaded_by bigint unsigned not null` | `index idx_edu_contract_attachments_contract (tenant_id, contract_id)` |
| `edu_contract_renewals` | `contract_id bigint unsigned not null`, `renewal_type varchar(40) not null`, `status varchar(20) not null default pending`, `due_date date not null`, `handled_by bigint unsigned null`, `handled_at timestamp null`, `result varchar(500) null` | `index idx_edu_contract_renewals_due (tenant_id, campus_id, due_date, status)`, `index idx_edu_contract_renewals_contract (tenant_id, contract_id)` |
| `edu_group_operation_metrics` | `metric_date date not null`, `org_unit_id bigint unsigned null`, `campus_count int unsigned not null default 0`, `student_count int unsigned not null default 0`, `revenue_cents bigint unsigned not null default 0`, `consumed_credits decimal(12,2) not null default 0.00`, `renewal_alert_count int unsigned not null default 0` | `unique uk_edu_group_operation_metrics_scope_date (tenant_id, org_unit_id, campus_id, metric_date)`, `index idx_edu_group_operation_metrics_date (tenant_id, metric_date)` |
| `edu_franchise_records` | `franchise_code varchar(64) not null`, `franchise_name varchar(160) not null`, `contact_name varchar(120) null`, `contact_mobile varchar(30) null`, `region varchar(120) null`, `status varchar(20) not null default potential`, `signed_contract_id bigint unsigned null`, `remark varchar(500) null` | `unique uk_edu_franchise_records_code (tenant_id, franchise_code)`, `index idx_edu_franchise_records_status (tenant_id, status)` |
| `edu_risk_audit_events` | `event_type varchar(60) not null`, `risk_level varchar(20) not null`, `business_type varchar(60) not null`, `business_id bigint unsigned null`, `operator_id bigint unsigned null`, `summary varchar(300) not null`, `payload_json json not null`, `handled tinyint(1) not null default 0`, `handled_by bigint unsigned null`, `handled_at timestamp null` | `index idx_edu_risk_audit_events_level (tenant_id, risk_level, created_at)`, `index idx_edu_risk_audit_events_business (tenant_id, business_type, business_id)` |

## MineAdmin Backend Module Design

Enums:

```text
OrgUnitStatus: enabled, disabled
DataScopeType: group_all, org_tree, campus_set, self
ApprovalStatus: pending, approved, rejected, cancelled
ContractStatus: draft, reviewing, active, expired, terminated, archived
RiskLevel: normal, warning, high, critical
```

Layer tasks:

| Layer | Required implementation |
| --- | --- |
| Model | table names, fillable, money casts for contract amount, JSON casts for scopes/templates/payloads, soft deletes for mutable records |
| Repository | org tree queries, cycle checks, data scope lookup, approval task lookup, contract expiry queries, risk audit pagination |
| Service | org unit CRUD, data permission calculation, approval template and instance state machine, contract state and renewal, metrics aggregation, franchise records, risk audit logging |
| Request | validate org parent, scope values, approval nodes, task result, contract dates, amount cents, risk level |
| Controller | MineAdmin annotations, permissions, request objects, Result envelope, admin-only access, audit writes |
| Schema | document org, data permission, approval, contract, metrics, franchise, and risk audit API payloads |

Data permission rule:

```text
DataPermissionService calculates allowed campus ids by user data permission first. If a group scope exists, it overrides default F02 campus scope for PC admin queries; teacher and guardian mobile isolation never expands from group permissions.
```

Risk controls:

```text
Org tree cannot create circular parent relation.
Approval task can be completed only by assigned user or a supervisor with override permission.
Contract amount/date/status changes create risk audit events when the contract is active.
Attachment access must pass contract permission and tenant/campus scope.
```

## API Contract

Common headers:

```text
Authorization: Bearer <token>
X-Tenant-Id: <tenant id>
X-Campus-Id: <campus id, optional for group admins with wider scope>
```

Endpoint matrix:

| API | Permission | Caller | Isolation | Audit |
| --- | --- | --- | --- | --- |
| `GET /admin/education/group/org-units/tree` | `education:group:org:tree` | group admin | user data scope | no |
| `POST /admin/education/group/org-units` | `education:group:org:create` | group admin | tenant | yes |
| `POST /admin/education/group/data-permissions` | `education:group:data-permission:save` | group admin | tenant | yes |
| `POST /admin/education/group/approval-instances` | `education:group:approval:create` | admin | user data scope | yes |
| `POST /admin/education/group/approval-tasks/{id}/complete` | `education:group:approval:complete` | assignee | task assignee or override | yes |
| `POST /admin/education/group/contracts` | `education:group:contract:create` | contract admin | user data scope | yes |
| `POST /admin/education/group/contracts/{id}/submit-review` | `education:group:contract:submit-review` | contract admin | user data scope | yes |
| `GET /admin/education/group/operation-metrics` | `education:group:metric:page` | group admin | user data scope | no |
| `GET /admin/education/group/risk-audit-events/page` | `education:group:risk-audit:page` | risk admin | user data scope | no |

Endpoint-level request/response/failure catalog:

```json
[
  {
    "api": "POST /admin/education/group/org-units",
    "request": {"parent_id": 1, "code": "EAST", "name": "East Region", "unit_type": "region"},
    "success": {"code": 200, "message": "success", "data": {"id": 2, "path": "1/2", "status": "enabled"}},
    "validation_failure": {"code": 422, "message": "code is required", "data": {"field": "code"}},
    "business_failure": {"code": 409, "message": "org unit parent creates cycle", "data": {"org_unit_id": 1, "parent_id": 3}}
  },
  {
    "api": "POST /admin/education/group/data-permissions",
    "request": {"user_id": 88, "scope_type": "campus_set", "campus_ids": [2001, 2002]},
    "success": {"code": 200, "message": "success", "data": {"user_id": 88, "scope_type": "campus_set"}},
    "validation_failure": {"code": 422, "message": "scope_type has an invalid value", "data": {"field": "scope_type"}},
    "business_failure": {"code": 403, "message": "campus is outside operator data scope", "data": {"campus_id": 2002}}
  },
  {
    "api": "POST /admin/education/group/approval-instances",
    "request": {"template_id": 10, "business_type": "contract", "business_id": 1001, "payload_json": {"amount_cents": 500000}},
    "success": {"code": 200, "message": "success", "data": {"approval_instance_id": 301, "status": "pending"}},
    "validation_failure": {"code": 422, "message": "business_type is required", "data": {"field": "business_type"}},
    "business_failure": {"code": 409, "message": "approval instance already exists for business", "data": {"business_type": "contract", "business_id": 1001}}
  },
  {
    "api": "POST /admin/education/group/approval-tasks/{id}/complete",
    "request": {"result": "approved", "comment": "ok"},
    "success": {"code": 200, "message": "success", "data": {"task_id": 401, "instance_status": "approved"}},
    "validation_failure": {"code": 422, "message": "result has an invalid value", "data": {"field": "result"}},
    "business_failure": {"code": 403, "message": "approval task is assigned to another user", "data": {"task_id": 401}}
  },
  {
    "api": "POST /admin/education/group/contracts",
    "request": {"contract_no": "CT202606100001", "contract_type": "lease", "title": "Campus Lease", "counterparty_name": "Landlord", "amount_cents": 12000000, "start_date": "2026-06-10", "end_date": "2027-06-09"},
    "success": {"code": 200, "message": "success", "data": {"contract_id": 1001, "status": "draft"}},
    "validation_failure": {"code": 422, "message": "end_date must be after start_date", "data": {"field": "end_date"}},
    "business_failure": {"code": 409, "message": "contract no already exists", "data": {"contract_no": "CT202606100001"}}
  },
  {
    "api": "GET /admin/education/group/operation-metrics",
    "request": {"start_date": "2026-06-01", "end_date": "2026-06-10", "org_unit_id": 2},
    "success": {"code": 200, "message": "success", "data": {"list": [{"metric_date": "2026-06-10", "student_count": 500}], "total": 1}},
    "validation_failure": {"code": 422, "message": "start_date is required", "data": {"field": "start_date"}},
    "business_failure": {"code": 403, "message": "org unit is outside current data scope", "data": {"org_unit_id": 2}}
  },
  {
    "api": "GET /admin/education/group/risk-audit-events/page",
    "request": {"page": 1, "pageSize": 20, "risk_level": "high"},
    "success": {"code": 200, "message": "success", "data": {"list": [{"id": 9001, "risk_level": "high", "event_type": "contract_amount_changed"}], "total": 1}},
    "validation_failure": {"code": 422, "message": "risk_level has an invalid value", "data": {"field": "risk_level"}},
    "business_failure": {"code": 403, "message": "risk audit access denied", "data": {}}
  }
]
```

## PC Admin Page Tasks

API clients:

```text
org.ts: getOrgUnitTree, saveOrgUnit, bindCampusOrgRelation
permission.ts: pageDataPermissions, saveUserDataPermission, getUserDataScopePreview
approval.ts: pageApprovalTemplates, saveApprovalTemplate, createApprovalInstance, completeApprovalTask, pageApprovalTasks
contract.ts: pageContracts, saveContract, submitContractReview, uploadContractAttachment, pageContractRenewals
metric.ts: getGroupOperationMetrics, getGroupOperationDashboard
franchise.ts: pageFranchiseRecords, saveFranchiseRecord
risk-audit.ts: pageRiskAuditEvents, markRiskAuditHandled
```

Routes and pages:

| Route | Route name | Page | Permission | Key UI work |
| --- | --- | --- | --- | --- |
| `/education/group/org-units` | `EducationGroupOrgUnitTree` | `OrgUnitTree.vue` | `education:group:org:tree` | org tree, campus binding, cycle error display |
| `/education/group/data-permissions` | `EducationGroupDataPermissionList` | `DataPermissionList.vue` | `education:group:data-permission:page` | scope editor, preview allowed campus ids |
| `/education/group/approval-templates` | `EducationGroupApprovalTemplateList` | `ApprovalTemplateList.vue` | `education:group:approval-template:page` | template/node editor |
| `/education/group/approval-tasks` | `EducationGroupApprovalTaskList` | `ApprovalTaskList.vue` | `education:group:approval-task:page` | pending task list, complete action |
| `/education/group/contracts` | `EducationGroupContractList` | `ContractList.vue` | `education:group:contract:page` | contract CRUD, attachments, submit review |
| `/education/group/contract-renewals` | `EducationGroupContractRenewalList` | `ContractRenewalList.vue` | `education:group:contract-renewal:page` | renewal reminders and handling |
| `/education/group/dashboard` | `EducationGroupOperationDashboard` | `GroupOperationDashboard.vue` | `education:group:metric:page` | cross-campus metrics by org scope |
| `/education/group/franchises` | `EducationGroupFranchiseRecordList` | `FranchiseRecordList.vue` | `education:group:franchise:page` | franchise reservation fields |
| `/education/group/risk-audits` | `EducationGroupRiskAuditEventList` | `RiskAuditEventList.vue` | `education:group:risk-audit:page` | risk level filters and handled marker |

Required states:

```text
All group pages implement loading, empty, 403 permission, 422 validation, 409 state/cycle conflict, success refresh, and current user data scope indicator.
```

## Teacher / Guardian Mobile Page Tasks

This module has no teacher or guardian page because V6 is PC-admin-only group governance. Teacher data access remains limited by teacher profile and assigned lessons; guardian data access remains limited by bound students. V6 group data permission must not expand teacher or guardian mobile permissions.

Mobile regression tests:

```text
Existing V1/V2 teacher and guardian mobile isolation tests must still pass after DataPermissionService is introduced.
```

## Test Plan

Backend tests:

| Test file | Case | Assert |
| --- | --- | --- |
| `GroupMigrationTest.php` | `test_group_tables_indexes_and_json_columns_exist` | all V6 tables, indexes, JSON fields, and tenant/campus fields exist |
| `OrgUnitServiceTest.php` | `test_org_tree_rejects_cycle` | service returns documented 409 |
| `DataPermissionServiceTest.php` | `test_group_admin_gets_authorized_campus_set` | allowed campus ids match assigned scope |
| `DataPermissionServiceTest.php` | `test_teacher_mobile_scope_is_not_expanded_by_group_permission` | teacher still sees only assigned lessons |
| `ApprovalInstanceServiceTest.php` | `test_assigned_user_completes_task_and_advances_state` | task status and instance status update correctly |
| `ContractServiceTest.php` | `test_active_contract_amount_change_writes_risk_event` | risk audit event exists |
| `RiskAuditServiceTest.php` | `test_risk_audit_query_respects_data_scope` | out-of-scope event is hidden |
| `DataPermissionAdminApiTest.php` | `test_permission_api_validation_and_business_failures_match_catalog` | 422 and 403 payloads match catalog |
| `GroupPermissionIsolationAuditTest.php` | `test_group_mutations_require_permission_and_write_audit` | denied without permission; audit exists after allowed write |

PC tests:

```text
OrgUnitTree.spec.ts asserts cycle error is shown and tree does not mutate.
DataPermissionList.spec.ts asserts scope preview displays campus ids returned by API.
ApprovalTaskList.spec.ts asserts only assigned/override users see complete button.
ContractList.spec.ts asserts active contract high-risk change shows risk warning.
RiskAuditEventList.spec.ts asserts risk level and handled filters are sent to API.
```

Mobile regression tests:

```text
Run existing teacher and guardian isolation suites to confirm V6 data permissions do not alter mobile access rules.
```

## Execution Commands

Backend:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter Education\\\\Group
composer test -- --filter Education\\\\Academic.*Mobile
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
V6 migrations run successfully.
All Education\\Group tests pass.
Teacher/guardian mobile isolation regression tests pass.
Code style and static analysis pass.
```

PC:

```bash
cd mineadmin-education-saas/admin-web
pnpm install
pnpm lint
pnpm test -- group
pnpm build
```

Expected:

```text
Group PC lint, tests, and build pass.
```

Mobile:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm install
pnpm lint
pnpm test -- teacher
pnpm test -- guardian
pnpm build:h5
```

Expected:

```text
Existing teacher and guardian mobile regression tests pass and H5 build succeeds.
```

## Acceptance Gate

V6 is accepted only when:

```text
- Org tree can be maintained and circular parent relations are rejected.
- Campus-org bindings and user data permissions filter PC admin data by group/org/campus scope.
- Teacher and guardian mobile isolation remains unchanged.
- Approval templates can create instances and assigned users can complete tasks.
- Contracts can be created, reviewed, renewed, and risk-audited.
- Group dashboard respects data permission scope.
- Risk audit events are queryable and handled state is tracked.
- All group governance write operations are permission-checked and audited.
```

## Task Breakdown

### Task 1: Migration, Enums, and Models

- [x] Create migration with table catalog, indexes, JSON fields, and reverse rollback.
- [x] Create enums and models listed in `File Structure`.
- [x] Write `GroupMigrationTest.php`.
- [x] Run `php bin/hyperf.php migrate`; expected output is successful creation of all V6 group tables.

### Task 2: Repositories and Services

- [x] Create repositories for org tree, data permissions, approvals, contracts, metrics, franchise, and risk audit.
- [x] Create services with cycle checks, scope calculation, approval state machine, contract risk events, and metric aggregation.
- [x] Write unit tests listed in `Test Plan`.
- [x] Run `composer test -- --filter Education\\\\Group.*ServiceTest`; expected output is all group service tests passing.

### Task 3: Requests, Schemas, Controllers, and API Tests

- [x] Create request classes with validation for org, scope, approval, contract, metrics, franchise, and risk audit actions.
- [x] Create schemas matching the API catalog.
- [x] Create admin controllers with permissions, Result envelope, data scope enforcement, and audit logging.
- [x] Write API, permission, isolation, and audit tests.
- [x] Run `composer test -- --filter Education\\\\Group`; expected output is all V6 backend tests passing.

### Task 4: PC Admin

- [x] Create typed API clients.
- [x] Register group routes and menus in `education.ts`.
- [x] Create org, permission, approval, contract, renewal, dashboard, franchise, and risk audit pages.
- [x] Write PC tests listed in `Test Plan`.
- [x] Run `pnpm lint && pnpm test -- group && pnpm build`; expected output is all PC gates passing.

### Task 5: Mobile Regression

- [x] Run backend teacher/guardian mobile isolation tests.
- [x] Run mobile teacher and guardian test suites.
- [x] Confirm DataPermissionService has no effect on teacher/guardian mobile access.

### Task 6: V6 Final Gate

- [x] Run all backend, PC, and mobile commands in `Execution Commands`.
- [x] Confirm acceptance gate behavior with seeded group admin, campus admin, teacher, guardian, approval, and contract data.
- [x] Update status index only after all gates pass during implementation.

## Self-Review

- Spec coverage: Covers org units, campus hierarchy, data permissions, approvals, contracts, group metrics, franchise records, and risk audits.
- MineAdmin fit: Uses MineAdmin 3.x backend paths and PC admin conventions; mobile section explicitly states why no new teacher/guardian pages are added.
- Code-level readiness: Migration fields, indexes, rollback, backend layer tasks, API failures, PC states, regression tests, commands, and acceptance gates are specified.
- Acceptance status: V6 group management has been implemented and verified.
