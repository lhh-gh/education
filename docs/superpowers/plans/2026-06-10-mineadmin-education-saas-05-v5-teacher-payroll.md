# MineAdmin Education SaaS V5 Teacher Payroll Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V5 教师薪酬与绩效结算 for salary rules, rule items, calculation batches, salary slips, salary items, adjustments, review workflow, payment marking, workload disputes, and teacher performance metrics.

**Architecture:** V5 consumes V2 teacher workload records as immutable source data and creates payroll snapshots. Approved payroll batches and slips cannot be silently recalculated; any correction is an adjustment item and audit log.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.1+, MySQL 8, Redis queue, MineAdmin-Vue, Vue3, TypeScript, uni-app, pnpm, PHPUnit/co-phpunit.

**Status:** accepted

**Completion:** implemented / accepted. V5 teacher payroll gates have passed.

---

## Scope Check

Included:

- Teacher salary rules and rule items by campus, course, class type, workload type, and teacher grade.
- Salary batch calculation, rebuild while draft, submit review, approve, reject, and lock.
- Salary slips, salary items, manual adjustment items, and payment marking.
- Teacher workload dispute submit/review workflow.
- Teacher performance metrics from V1/V2 attendance, workload, family service, and payroll sources.
- PC payroll rule, batch, slip, review, payment, dispute, and performance pages.
- Teacher mobile salary slip view and workload dispute submit/status pages.

Excluded:

- Bank transfer integration, personal tax filing, and external payroll provider sync.
- Finance payment ledgers for salary disbursement; only payment marking is included.
- Recalculation of approved historical payroll.

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_050000_create_v5_payroll_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Payroll/SalaryRuleStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Payroll/SalaryBatchStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Payroll/SalarySlipStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Payroll/SalaryItemType.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Payroll/DisputeStatus.php
mineadmin-education-saas/backend/app/Model/Education/Payroll/EducationTeacherSalaryRule.php
mineadmin-education-saas/backend/app/Model/Education/Payroll/EducationTeacherSalaryRuleItem.php
mineadmin-education-saas/backend/app/Model/Education/Payroll/EducationTeacherSalaryBatch.php
mineadmin-education-saas/backend/app/Model/Education/Payroll/EducationTeacherSalarySlip.php
mineadmin-education-saas/backend/app/Model/Education/Payroll/EducationTeacherSalaryItem.php
mineadmin-education-saas/backend/app/Model/Education/Payroll/EducationTeacherSalaryAdjustment.php
mineadmin-education-saas/backend/app/Model/Education/Payroll/EducationTeacherSalaryReview.php
mineadmin-education-saas/backend/app/Model/Education/Payroll/EducationTeacherSalaryPayment.php
mineadmin-education-saas/backend/app/Model/Education/Payroll/EducationTeacherWorkloadDispute.php
mineadmin-education-saas/backend/app/Model/Education/Payroll/EducationTeacherPerformanceMetric.php
mineadmin-education-saas/backend/app/Repository/Education/Payroll/SalaryRuleRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Payroll/SalaryBatchRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Payroll/SalarySlipRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Payroll/SalaryItemRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Payroll/SalaryReviewRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Payroll/SalaryPaymentRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Payroll/WorkloadDisputeRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Payroll/TeacherPerformanceRepository.php
mineadmin-education-saas/backend/app/Service/Education/Payroll/SalaryRuleService.php
mineadmin-education-saas/backend/app/Service/Education/Payroll/SalaryCalculationService.php
mineadmin-education-saas/backend/app/Service/Education/Payroll/SalaryBatchService.php
mineadmin-education-saas/backend/app/Service/Education/Payroll/SalaryReviewService.php
mineadmin-education-saas/backend/app/Service/Education/Payroll/SalaryPaymentService.php
mineadmin-education-saas/backend/app/Service/Education/Payroll/WorkloadDisputeService.php
mineadmin-education-saas/backend/app/Service/Education/Payroll/TeacherPerformanceService.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Payroll/SalaryRuleSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Payroll/SalaryBatchCalculateRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Payroll/SalaryBatchPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Payroll/SalaryReviewRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Payroll/SalaryAdjustmentRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Payroll/SalaryPaymentMarkRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Payroll/WorkloadDisputeReviewRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Payroll/TeacherPerformancePageRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Payroll/TeacherSalarySlipPageRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Payroll/TeacherWorkloadDisputeRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Payroll/SalaryRuleController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Payroll/SalaryBatchController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Payroll/SalarySlipController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Payroll/SalaryReviewController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Payroll/SalaryPaymentController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Payroll/WorkloadDisputeController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Payroll/TeacherPerformanceController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Payroll/TeacherPayrollController.php
mineadmin-education-saas/backend/app/Schema/Education/Payroll/SalaryRuleSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Payroll/SalaryBatchSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Payroll/SalarySlipSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Payroll/SalaryReviewSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Payroll/SalaryPaymentSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Payroll/WorkloadDisputeSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Payroll/TeacherPerformanceSchema.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Payroll/PayrollMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Payroll/SalaryRuleServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Payroll/SalaryCalculationServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Payroll/SalaryReviewServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Payroll/WorkloadDisputeServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Payroll/SalaryRuleAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Payroll/SalaryBatchAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Payroll/SalaryPaymentAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Payroll/TeacherPayrollMobileApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Payroll/PayrollPermissionIsolationAuditTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/payroll/rule.ts
mineadmin-education-saas/admin-web/src/api/education/payroll/batch.ts
mineadmin-education-saas/admin-web/src/api/education/payroll/slip.ts
mineadmin-education-saas/admin-web/src/api/education/payroll/payment.ts
mineadmin-education-saas/admin-web/src/api/education/payroll/dispute.ts
mineadmin-education-saas/admin-web/src/api/education/payroll/performance.ts
mineadmin-education-saas/admin-web/src/views/education/payroll/SalaryRuleList.vue
mineadmin-education-saas/admin-web/src/views/education/payroll/SalaryBatchList.vue
mineadmin-education-saas/admin-web/src/views/education/payroll/SalarySlipList.vue
mineadmin-education-saas/admin-web/src/views/education/payroll/SalaryReviewList.vue
mineadmin-education-saas/admin-web/src/views/education/payroll/SalaryPaymentList.vue
mineadmin-education-saas/admin-web/src/views/education/payroll/WorkloadDisputeList.vue
mineadmin-education-saas/admin-web/src/views/education/payroll/TeacherPerformanceDashboard.vue
mineadmin-education-saas/admin-web/src/views/education/payroll/components/SalaryRuleForm.vue
mineadmin-education-saas/admin-web/src/views/education/payroll/components/SalaryBatchCalculateForm.vue
mineadmin-education-saas/admin-web/src/views/education/payroll/components/SalarySlipDetailDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/payroll/components/SalaryAdjustmentForm.vue
mineadmin-education-saas/admin-web/src/views/education/payroll/components/WorkloadDisputeReviewDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/payroll/__tests__/SalaryRuleList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/payroll/__tests__/SalaryBatchList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/payroll/__tests__/SalarySlipList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/payroll/__tests__/WorkloadDisputeList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/payroll/__tests__/TeacherPerformanceDashboard.spec.ts
```

Modify PC:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Create mobile:

```text
mineadmin-education-saas/mobile-uniapp/src/api/payroll/teacher.ts
mineadmin-education-saas/mobile-uniapp/pages/teacher/payroll/slips.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/payroll/slip-detail.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/payroll/disputes.vue
mineadmin-education-saas/mobile-uniapp/pages/teacher/payroll/dispute-form.vue
mineadmin-education-saas/mobile-uniapp/tests/payroll/teacher-payroll.spec.ts
```

Modify mobile:

```text
mineadmin-education-saas/mobile-uniapp/pages.json
```

## Database Migration Design

Migration file:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_050000_create_v5_payroll_tables.php
```

Money policy:

```text
All salary amount fields use bigint integer cents: base_amount_cents, unit_amount_cents, amount_cents, adjustment_amount_cents, payable_amount_cents, paid_amount_cents.
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
deleted_at timestamp null for mutable config/request records
```

Foreign-key policy:

```text
Use service-level validation for teacher, course, class, lesson, workload, and user references. Do not create physical foreign keys.
```

Rollback behavior:

```text
Drop tables in reverse order: edu_teacher_performance_metrics, edu_teacher_workload_disputes, edu_teacher_salary_payments, edu_teacher_salary_reviews, edu_teacher_salary_adjustments, edu_teacher_salary_items, edu_teacher_salary_slips, edu_teacher_salary_batches, edu_teacher_salary_rule_items, edu_teacher_salary_rules.
```

Table catalog:

| Table | Business columns | Indexes |
| --- | --- | --- |
| `edu_teacher_salary_rules` | `rule_code varchar(64) not null`, `rule_name varchar(120) not null`, `campus_scope_json json null`, `teacher_grade varchar(40) null`, `status varchar(20) not null default enabled`, `effective_start date not null`, `effective_end date null`, `priority int not null default 0`, `remark varchar(500) null` | `unique uk_edu_teacher_salary_rules_tenant_code (tenant_id, rule_code)`, `index idx_edu_teacher_salary_rules_status (tenant_id, campus_id, status, effective_start)` |
| `edu_teacher_salary_rule_items` | `rule_id bigint unsigned not null`, `item_type varchar(40) not null`, `workload_type varchar(40) null`, `course_id bigint unsigned null`, `class_type varchar(40) null`, `calculation_method varchar(40) not null`, `unit_amount_cents bigint unsigned not null default 0`, `rate decimal(8,4) null`, `condition_json json null`, `sort_order int not null default 0` | `index idx_edu_teacher_salary_rule_items_rule (tenant_id, rule_id, item_type)`, `index idx_edu_teacher_salary_rule_items_course (tenant_id, course_id)` |
| `edu_teacher_salary_batches` | `batch_no varchar(64) not null`, `salary_month char(7) not null`, `status varchar(20) not null default draft`, `source_start date not null`, `source_end date not null`, `teacher_count int unsigned not null default 0`, `total_amount_cents bigint unsigned not null default 0`, `calculated_by bigint unsigned null`, `calculated_at timestamp null`, `submitted_at timestamp null`, `approved_at timestamp null` | `unique uk_edu_teacher_salary_batches_tenant_no (tenant_id, batch_no)`, `unique uk_edu_teacher_salary_batches_month_campus (tenant_id, campus_id, salary_month)`, `index idx_edu_teacher_salary_batches_status (tenant_id, status)` |
| `edu_teacher_salary_slips` | `batch_id bigint unsigned not null`, `teacher_id bigint unsigned not null`, `salary_month char(7) not null`, `status varchar(20) not null default draft`, `workload_snapshot_json json not null`, `gross_amount_cents bigint unsigned not null default 0`, `adjustment_amount_cents bigint not null default 0`, `payable_amount_cents bigint unsigned not null default 0`, `paid_amount_cents bigint unsigned not null default 0`, `approved_at timestamp null`, `paid_at timestamp null` | `unique uk_edu_teacher_salary_slips_batch_teacher (tenant_id, batch_id, teacher_id)`, `index idx_edu_teacher_salary_slips_teacher_month (tenant_id, teacher_id, salary_month)`, `index idx_edu_teacher_salary_slips_status (tenant_id, campus_id, status)` |
| `edu_teacher_salary_items` | `salary_slip_id bigint unsigned not null`, `teacher_id bigint unsigned not null`, `source_workload_id bigint unsigned null`, `item_type varchar(40) not null`, `item_name varchar(160) not null`, `quantity decimal(10,2) not null default 0.00`, `unit_amount_cents bigint unsigned not null default 0`, `amount_cents bigint not null default 0`, `rule_item_id bigint unsigned null`, `snapshot_json json not null` | `index idx_edu_teacher_salary_items_slip (tenant_id, salary_slip_id)`, `index idx_edu_teacher_salary_items_workload (tenant_id, source_workload_id)` |
| `edu_teacher_salary_adjustments` | `salary_slip_id bigint unsigned not null`, `teacher_id bigint unsigned not null`, `adjustment_type varchar(40) not null`, `amount_cents bigint not null`, `reason varchar(500) not null`, `operator_id bigint unsigned not null`, `approved_by bigint unsigned null`, `approved_at timestamp null` | `index idx_edu_teacher_salary_adjustments_slip (tenant_id, salary_slip_id)`, `index idx_edu_teacher_salary_adjustments_teacher (tenant_id, teacher_id, created_at)` |
| `edu_teacher_salary_reviews` | `batch_id bigint unsigned not null`, `salary_slip_id bigint unsigned null`, `review_level int unsigned not null default 1`, `reviewer_id bigint unsigned not null`, `status varchar(20) not null default pending`, `review_note varchar(500) null`, `reviewed_at timestamp null` | `index idx_edu_teacher_salary_reviews_reviewer (tenant_id, reviewer_id, status)`, `index idx_edu_teacher_salary_reviews_batch (tenant_id, batch_id, status)` |
| `edu_teacher_salary_payments` | `salary_slip_id bigint unsigned not null`, `teacher_id bigint unsigned not null`, `payment_no varchar(64) not null`, `paid_amount_cents bigint unsigned not null`, `payment_method varchar(40) not null`, `paid_at timestamp not null`, `operator_id bigint unsigned not null`, `remark varchar(500) null` | `unique uk_edu_teacher_salary_payments_tenant_no (tenant_id, payment_no)`, `index idx_edu_teacher_salary_payments_slip (tenant_id, salary_slip_id)`, `index idx_edu_teacher_salary_payments_teacher (tenant_id, teacher_id, paid_at)` |
| `edu_teacher_workload_disputes` | `teacher_id bigint unsigned not null`, `source_workload_id bigint unsigned not null`, `salary_slip_id bigint unsigned null`, `dispute_type varchar(40) not null`, `content text not null`, `status varchar(20) not null default pending`, `reviewed_by bigint unsigned null`, `reviewed_at timestamp null`, `review_note varchar(500) null` | `index idx_edu_teacher_workload_disputes_teacher_status (tenant_id, teacher_id, status)`, `index idx_edu_teacher_workload_disputes_workload (tenant_id, source_workload_id)` |
| `edu_teacher_performance_metrics` | `metric_month char(7) not null`, `teacher_id bigint unsigned not null`, `lesson_count int unsigned not null default 0`, `workload_credits decimal(10,2) not null default 0.00`, `student_count int unsigned not null default 0`, `attendance_rate decimal(6,4) null`, `family_service_count int unsigned not null default 0`, `dispute_count int unsigned not null default 0`, `salary_amount_cents bigint unsigned not null default 0` | `unique uk_edu_teacher_performance_metrics_month_teacher (tenant_id, campus_id, metric_month, teacher_id)`, `index idx_edu_teacher_performance_metrics_teacher (tenant_id, teacher_id, metric_month)` |

## MineAdmin Backend Module Design

Enums:

```text
SalaryRuleStatus: enabled, disabled
SalaryBatchStatus: draft, calculated, submitted, approved, rejected, paid, closed
SalarySlipStatus: draft, pending_review, approved, rejected, paid, disputed
SalaryItemType: workload, bonus, deduction, adjustment, subsidy
DisputeStatus: pending, approved, rejected, closed
```

Layer tasks:

| Layer | Required implementation |
| --- | --- |
| Model | table names, fillable, integer money casts, JSON snapshot casts, enum casts, soft deletes for rules/disputes |
| Repository | salary rule matching, batch locks, slip pagination, item aggregation, dispute lookup, performance summaries |
| Service | rule CRUD, batch calculation, draft rebuild, submit review, approve/reject, adjustment item creation, payment marking, dispute review, performance metric aggregation |
| Request | validate salary month, integer cents, rule conditions, review action, payment method, dispute content |
| Controller | MineAdmin annotations, permissions, request objects, Result envelope, audit writes for all payroll mutations |
| Schema | document rule, batch, slip, item, review, payment, dispute, performance, and teacher mobile responses |

Calculation rules:

```text
SalaryCalculationService reads V2 teacher workload records for the salary month, snapshots each source workload into salary items, applies the highest-priority matching salary rule, writes salary slips and items, and never changes V2 workload rows.
Draft batches can be rebuilt after deleting their draft slips/items inside the same transaction.
Submitted or approved batches cannot be rebuilt.
Approved slips can be corrected only with salary adjustment rows and new salary items.
```

Audit rules:

```text
Write audit actions: education.payroll.rule.saved, batch.calculated, batch.submitted, batch.approved, batch.rejected, slip.adjusted, payment.marked, dispute.submitted, dispute.approved, dispute.rejected.
```

## API Contract

Common headers:

```text
Authorization: Bearer <token>
X-Tenant-Id: <tenant id>
X-Campus-Id: <campus id>
```

Endpoint matrix:

| API | Permission | Caller | Isolation | Audit |
| --- | --- | --- | --- | --- |
| `POST /admin/education/payroll/salary-rules` | `education:payroll:rule:create` | payroll admin | tenant + campus | yes |
| `GET /admin/education/payroll/salary-rules/page` | `education:payroll:rule:page` | payroll admin | tenant + campus | no |
| `POST /admin/education/payroll/salary-batches/calculate` | `education:payroll:batch:calculate` | payroll admin | tenant + campus | yes |
| `POST /admin/education/payroll/salary-batches/{id}/submit-review` | `education:payroll:batch:submit` | payroll admin | tenant + campus | yes |
| `POST /admin/education/payroll/salary-batches/{id}/approve` | `education:payroll:batch:approve` | payroll supervisor | tenant + campus | yes |
| `POST /admin/education/payroll/salary-slips/{id}/adjustments` | `education:payroll:slip:adjust` | payroll admin | tenant + campus | yes |
| `POST /admin/education/payroll/salary-payments` | `education:payroll:payment:mark` | payroll admin | tenant + campus | yes |
| `GET /admin/education/payroll/workload-disputes/page` | `education:payroll:dispute:page` | payroll admin | tenant + campus | no |
| `POST /admin/education/payroll/workload-disputes/{id}/review` | `education:payroll:dispute:review` | payroll admin | tenant + campus | yes |
| `GET /mobile/education/payroll/teacher/slips` | mobile teacher | teacher | current teacher only | no |
| `POST /mobile/education/payroll/teacher/disputes` | mobile teacher | teacher | current teacher only | yes |

Endpoint-level request/response/failure catalog:

```json
[
  {
    "api": "POST /admin/education/payroll/salary-rules",
    "request": {"rule_code": "MAIN-001", "rule_name": "主讲课时费", "effective_start": "2026-06-01", "items": [{"item_type": "workload", "workload_type": "main", "calculation_method": "per_credit", "unit_amount_cents": 12000}]},
    "success": {"code": 200, "message": "success", "data": {"id": 101, "status": "enabled"}},
    "validation_failure": {"code": 422, "message": "rule_code is required", "data": {"field": "rule_code"}},
    "business_failure": {"code": 409, "message": "salary rule code already exists", "data": {"rule_code": "MAIN-001"}}
  },
  {
    "api": "POST /admin/education/payroll/salary-batches/calculate",
    "request": {"salary_month": "2026-06", "campus_id": 2001, "teacher_ids": [301, 302]},
    "success": {"code": 200, "message": "success", "data": {"batch_id": 201, "status": "calculated", "teacher_count": 2}},
    "validation_failure": {"code": 422, "message": "salary_month must be YYYY-MM", "data": {"field": "salary_month"}},
    "business_failure": {"code": 409, "message": "approved salary batch already exists for month", "data": {"salary_month": "2026-06"}}
  },
  {
    "api": "POST /admin/education/payroll/salary-batches/{id}/approve",
    "request": {"review_note": "approved"},
    "success": {"code": 200, "message": "success", "data": {"batch_id": 201, "status": "approved"}},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 409, "message": "salary batch is not submitted", "data": {"batch_id": 201, "status": "calculated"}}
  },
  {
    "api": "POST /admin/education/payroll/salary-slips/{id}/adjustments",
    "request": {"adjustment_type": "bonus", "amount_cents": 5000, "reason": "excellent service"},
    "success": {"code": 200, "message": "success", "data": {"adjustment_id": 301, "payable_amount_cents": 125000}},
    "validation_failure": {"code": 422, "message": "amount_cents must not be zero", "data": {"field": "amount_cents"}},
    "business_failure": {"code": 409, "message": "paid salary slip cannot be adjusted", "data": {"salary_slip_id": 301}}
  },
  {
    "api": "POST /admin/education/payroll/salary-payments",
    "request": {"salary_slip_id": 301, "payment_no": "SP202606100001", "paid_amount_cents": 125000, "payment_method": "bank_transfer", "paid_at": "2026-07-05 10:00:00"},
    "success": {"code": 200, "message": "success", "data": {"payment_id": 401, "slip_status": "paid"}},
    "validation_failure": {"code": 422, "message": "payment_no is required", "data": {"field": "payment_no"}},
    "business_failure": {"code": 409, "message": "salary slip is not approved", "data": {"salary_slip_id": 301}}
  },
  {
    "api": "GET /mobile/education/payroll/teacher/slips",
    "request": {"salary_month": "2026-06"},
    "success": {"code": 200, "message": "success", "data": {"list": [{"salary_slip_id": 301, "salary_month": "2026-06", "payable_amount_cents": 125000, "status": "approved"}]}},
    "validation_failure": {"code": 422, "message": "salary_month must be YYYY-MM", "data": {"field": "salary_month"}},
    "business_failure": {"code": 403, "message": "current user has no teacher profile", "data": {}}
  },
  {
    "api": "POST /mobile/education/payroll/teacher/disputes",
    "request": {"source_workload_id": 801, "salary_slip_id": 301, "dispute_type": "missing_workload", "content": "substitute class is missing"},
    "success": {"code": 200, "message": "success", "data": {"dispute_id": 501, "status": "pending"}},
    "validation_failure": {"code": 422, "message": "content is required", "data": {"field": "content"}},
    "business_failure": {"code": 403, "message": "workload does not belong to current teacher", "data": {"source_workload_id": 801}}
  }
]
```

## PC Admin Page Tasks

API clients:

```text
rule.ts: pageSalaryRules, saveSalaryRule, updateSalaryRuleStatus
batch.ts: pageSalaryBatches, calculateSalaryBatch, rebuildSalaryBatch, submitSalaryBatch, approveSalaryBatch, rejectSalaryBatch
slip.ts: pageSalarySlips, getSalarySlipDetail, createSalaryAdjustment
payment.ts: pageSalaryPayments, markSalaryPayment
dispute.ts: pageWorkloadDisputes, reviewWorkloadDispute
performance.ts: getTeacherPerformanceSummary, pageTeacherPerformanceMetrics
```

Routes and pages:

| Route | Route name | Page | Permission | Key UI work |
| --- | --- | --- | --- | --- |
| `/education/payroll/rules` | `EducationPayrollSalaryRuleList` | `SalaryRuleList.vue` | `education:payroll:rule:page` | rule CRUD, item editor, status switch |
| `/education/payroll/batches` | `EducationPayrollSalaryBatchList` | `SalaryBatchList.vue` | `education:payroll:batch:page` | calculate/rebuild/submit/approve/reject buttons |
| `/education/payroll/slips` | `EducationPayrollSalarySlipList` | `SalarySlipList.vue` | `education:payroll:slip:page` | slip detail, item list, adjustment form |
| `/education/payroll/reviews` | `EducationPayrollSalaryReviewList` | `SalaryReviewList.vue` | `education:payroll:review:page` | review state list |
| `/education/payroll/payments` | `EducationPayrollSalaryPaymentList` | `SalaryPaymentList.vue` | `education:payroll:payment:page` | payment marking |
| `/education/payroll/disputes` | `EducationPayrollWorkloadDisputeList` | `WorkloadDisputeList.vue` | `education:payroll:dispute:page` | dispute review drawer |
| `/education/payroll/performance` | `EducationPayrollTeacherPerformanceDashboard` | `TeacherPerformanceDashboard.vue` | `education:payroll:performance:page` | teacher/month/campus metrics |

Required states:

```text
All payroll pages implement loading, empty, 403 permission, 422 validation, 409 immutable-state conflict, success refresh, and amount cents-to-yuan display.
```

## Teacher / Guardian Mobile Page Tasks

Teacher:

```text
API: mobile-uniapp/src/api/payroll/teacher.ts
Pages: slips.vue, slip-detail.vue, disputes.vue, dispute-form.vue
State: month selector, slip list, slip item detail, payment status, dispute list, dispute form, submitted state, 403 no teacher profile
Isolation: teacher can view only salary slips, workload rows, and disputes belonging to the current teacher profile.
```

Guardian:

```text
This module has no guardian page because teacher salary and disputes are internal employment data.
```

`pages.json`:

```text
Register teacher payroll pages under teacher role and require teacher profile context.
```

## Test Plan

Backend tests:

| Test file | Case | Assert |
| --- | --- | --- |
| `PayrollMigrationTest.php` | `test_payroll_tables_and_integer_money_columns_exist` | all tables, money columns, unique keys, tenant/campus fields exist |
| `SalaryRuleServiceTest.php` | `test_rule_matching_uses_priority_and_effective_date` | correct rule item applies to workload |
| `SalaryCalculationServiceTest.php` | `test_batch_creates_immutable_workload_snapshot` | salary item snapshot equals V2 workload source at calculation time |
| `SalaryCalculationServiceTest.php` | `test_approved_batch_cannot_be_rebuilt` | 409 state error |
| `SalaryReviewServiceTest.php` | `test_adjustment_creates_separate_item` | adjustment row and salary item exist; source workload unchanged |
| `WorkloadDisputeServiceTest.php` | `test_teacher_can_dispute_own_workload_only` | own workload returns 200; other teacher workload returns 403 |
| `SalaryBatchAdminApiTest.php` | `test_calculation_and_review_failures_match_catalog` | documented 422 and 409 payloads |
| `TeacherPayrollMobileApiTest.php` | `test_teacher_reads_only_own_slips` | own slip visible; other teacher slip forbidden |
| `PayrollPermissionIsolationAuditTest.php` | `test_payroll_mutations_require_permission_and_write_audit` | denied without permission; audit exists after allowed write |

PC tests:

```text
SalaryRuleList.spec.ts asserts disabled rule cannot be selected for calculation preview.
SalaryBatchList.spec.ts asserts rebuild button hides after submitted status and 409 state displays.
SalarySlipList.spec.ts asserts adjustment form keeps open on validation failure and updates payable amount after success.
WorkloadDisputeList.spec.ts asserts review drawer records approve/reject note.
TeacherPerformanceDashboard.spec.ts asserts campus/month filters are sent to metrics APIs.
```

Mobile tests:

```text
teacher-payroll.spec.ts asserts teacher slip list uses current teacher context, dispute submit handles 403 workload ownership, and paid status renders read-only.
```

## Execution Commands

Backend:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter Education\\\\Payroll
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
V5 migrations run successfully.
All Education\\Payroll tests pass.
Code style and static analysis pass.
```

PC:

```bash
cd mineadmin-education-saas/admin-web
pnpm install
pnpm lint
pnpm test -- payroll
pnpm build
```

Expected:

```text
Payroll PC lint, tests, and build pass.
```

Mobile:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm install
pnpm lint
pnpm test -- payroll
pnpm build:h5
```

Expected:

```text
Teacher payroll mobile lint, tests, and H5 build pass.
```

## Acceptance Gate

V5 is accepted only when:

```text
- Payroll rules can be configured and matched deterministically.
- Batch calculation snapshots V2 workload and never changes workload or consumption records.
- Draft batches can be rebuilt; submitted/approved batches cannot.
- Approved slips can be corrected only through adjustment rows.
- Salary payments can be marked only for approved slips.
- Teacher can view only own slips and submit disputes only for own workload.
- PC pages enforce status transitions and button permissions.
- All payroll write operations are audited.
```

## Task Breakdown

### Task 1: Migration, Enums, and Models

- [x] Create migration with table catalog, integer cents fields, indexes, and reverse rollback.
- [x] Create enums and models listed in `File Structure`.
- [x] Write `PayrollMigrationTest.php`.
- [x] Run `php bin/hyperf.php migrate`; expected output is successful creation of all V5 payroll tables.

### Task 2: Repositories and Services

- [x] Create repositories with tenant/campus filters, salary month filters, and row lock methods.
- [x] Create services for rule, calculation, batch, review, payment, dispute, and performance.
- [x] Write unit tests for rule matching, snapshot calculation, immutable states, adjustment, and dispute ownership.
- [x] Run `composer test -- --filter Education\\\\Payroll.*ServiceTest`; expected output is all payroll service tests passing.

### Task 3: Requests, Schemas, Controllers, and API Tests

- [x] Create request classes with salary month, money, status, review, payment, and dispute validation.
- [x] Create schemas matching the API catalog.
- [x] Create admin and teacher mobile controllers with permissions, Result envelope, and audit logging.
- [x] Write API, permission, isolation, and audit tests.
- [x] Run `composer test -- --filter Education\\\\Payroll`; expected output is all V5 backend tests passing.

### Task 4: PC Admin

- [x] Create typed API clients.
- [x] Register payroll routes and menus in `education.ts`.
- [x] Create rule, batch, slip, review, payment, dispute, and performance pages.
- [x] Write PC tests listed in `Test Plan`.
- [x] Run `pnpm lint && pnpm test -- payroll && pnpm build`; expected output is all PC gates passing.

### Task 5: Teacher Mobile

- [x] Create teacher payroll API client.
- [x] Register teacher payroll pages in `pages.json`.
- [x] Implement slip list/detail and dispute list/form with teacher isolation.
- [x] Write `teacher-payroll.spec.ts`.
- [x] Run `pnpm lint && pnpm test -- payroll && pnpm build:h5`; expected output is all mobile gates passing.

### Task 6: V5 Final Gate

- [x] Run all backend, PC, and mobile commands in `Execution Commands`.
- [x] Confirm acceptance gate behavior with seeded workloads, rules, batch, slips, payments, and disputes.
- [x] Update status index only after all gates pass during implementation.

## Self-Review

- Spec coverage: Covers salary rules, matching, batches, slips, items, adjustments, reviews, payments, disputes, performance metrics, PC admin, and teacher mobile.
- MineAdmin fit: Uses MineAdmin 3.x backend paths, PC route/API/page conventions, and uni-app teacher pages.
- Code-level readiness: Migration fields, indexes, rollback, API failures, backend layer tasks, PC/mobile states, tests, commands, and acceptance gates are specified.
- Implementation status: No code has been implemented; this is a ready implementation plan only.
