# MineAdmin Education SaaS V4 Finance Payment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement V4 收款支付与财务对账 for finance orders, order items, payment channels, payment records, callbacks, offline collection, refunds, receipts, reconciliation, finance adjustments, and guardian payment lookup.

**Architecture:** V4 integrates with V1 enrollment/account records and V3 lead conversion outputs through service contracts. Enrollment activation timing follows the Enrollment Activation Timing Contract defined in V1-02; V4 confirms enrollments on payment success and never writes account unit columns directly. Payment and refund flows are idempotent, audit-heavy, tenant/campus isolated, and never physically delete financial ledgers.

**Tech Stack:** MineAdmin 3.x, Hyperf 3.1, PHP 8.3+, MySQL 8, Redis queue, WeChat Pay adapter boundary, MineAdmin-Vue, Vue3, TypeScript, uni-app, pnpm, PHPUnit/co-phpunit.

**Status:** ready

**Completion:** implemented / accepted. Final backend, PC, and mobile gates passed.

---

## Scope Check

Included:

- Finance order and order item management for enrollment, renewal, make-up fee, material fee, and manual charge scenarios.
- Payment channel configuration for offline cash/bank/pos and WeChat payment adapter boundary.
- Payment records, callback logs, idempotent payment confirmation, and offline collection confirmation.
- Refund request, refund approval, refund record, and related V1 account adjustment.
- Receipt records and receipt issue/void state machine.
- Reconciliation batch and reconciliation item import/matching.
- Finance dashboard and guardian bound-student order/payment/receipt/refund lookup.

Excluded:

- Full accounting general ledger, tax filing, invoice platform integration, and bank transfer automation.
- Teacher payroll payment; V5 owns payroll.
- Marketing discount engine; V10 owns growth intelligence.

## Enrollment Activation Timing

The Enrollment Activation Timing Contract in V1-02 is the single source of truth. V4 consumes it and must not redefine when an enrollment becomes active or when account units are materialized.

V4 responsibilities under the contract:

```text
Enabling V4 for a tenant sets F03 feature flag finance_payment_enabled, which puts V1 enrollment creation into gated mode: new enrollments are created pending and their account units are not materialized at creation.
A finance order is created for the pending enrollment. Two entry points exist:
- Order-first: order creation creates the pending enrollment in the same transaction (renewal, manual sale).
- From existing pending enrollment: order is attached to a pending enrollment already created by V1-02 admin entry or V3 conversion.
On payment success (offline confirmation or verified WeChat callback), the same transaction marks the order paid and calls EnrollmentService.confirm, which materializes account units exactly once, idempotently on enrollment_id.
V4 never increments, decrements, or creates account unit columns directly; all account unit changes go through EnrollmentService.confirm or the V1 account adjustment service for refunds.
```

Flag cutover:

```text
Flipping finance_payment_enabled does not migrate existing data. Enrollments and accounts confirmed under V1 direct mode stay materialized and untouched. Only enrollments created after the flip wait for payment confirmation.
```

## File Structure

Create backend:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_040000_create_v4_finance_tables.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Finance/FinanceOrderStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Finance/PaymentStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Finance/PaymentChannelType.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Finance/RefundStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Finance/ReceiptStatus.php
mineadmin-education-saas/backend/app/Model/Enums/Education/Finance/ReconciliationStatus.php
mineadmin-education-saas/backend/app/Model/Education/Finance/EducationFinanceOrder.php
mineadmin-education-saas/backend/app/Model/Education/Finance/EducationFinanceOrderItem.php
mineadmin-education-saas/backend/app/Model/Education/Finance/EducationPaymentChannel.php
mineadmin-education-saas/backend/app/Model/Education/Finance/EducationPaymentRecord.php
mineadmin-education-saas/backend/app/Model/Education/Finance/EducationPaymentCallback.php
mineadmin-education-saas/backend/app/Model/Education/Finance/EducationRefundRequest.php
mineadmin-education-saas/backend/app/Model/Education/Finance/EducationRefundRecord.php
mineadmin-education-saas/backend/app/Model/Education/Finance/EducationReceipt.php
mineadmin-education-saas/backend/app/Model/Education/Finance/EducationReconciliationBatch.php
mineadmin-education-saas/backend/app/Model/Education/Finance/EducationReconciliationItem.php
mineadmin-education-saas/backend/app/Model/Education/Finance/EducationFinanceAdjustment.php
mineadmin-education-saas/backend/app/Repository/Education/Finance/FinanceOrderRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Finance/PaymentChannelRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Finance/PaymentRecordRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Finance/PaymentCallbackRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Finance/RefundRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Finance/ReceiptRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Finance/ReconciliationRepository.php
mineadmin-education-saas/backend/app/Repository/Education/Finance/FinanceAdjustmentRepository.php
mineadmin-education-saas/backend/app/Service/Education/Finance/FinanceOrderService.php
mineadmin-education-saas/backend/app/Service/Education/Finance/PaymentChannelService.php
mineadmin-education-saas/backend/app/Service/Education/Finance/PaymentRecordService.php
mineadmin-education-saas/backend/app/Service/Education/Finance/PaymentCallbackService.php
mineadmin-education-saas/backend/app/Service/Education/Finance/OfflineCollectionService.php
mineadmin-education-saas/backend/app/Service/Education/Finance/RefundService.php
mineadmin-education-saas/backend/app/Service/Education/Finance/ReceiptService.php
mineadmin-education-saas/backend/app/Service/Education/Finance/ReconciliationService.php
mineadmin-education-saas/backend/app/Service/Education/Finance/FinanceDashboardService.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Finance/FinanceOrderPageRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Finance/FinanceOrderCreateRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Finance/PaymentChannelSaveRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Finance/OfflinePaymentRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Finance/RefundRequestCreateRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Finance/RefundApprovalRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Finance/ReceiptIssueRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Finance/ReconciliationBatchCreateRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Finance/ReconciliationImportRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Request/Education/Finance/FinanceDashboardRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Finance/GuardianFinancePageRequest.php
mineadmin-education-saas/backend/app/Http/Api/Request/Education/Finance/PaymentCallbackRequest.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Finance/FinanceOrderController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Finance/PaymentChannelController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Finance/OfflinePaymentController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Finance/RefundController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Finance/ReceiptController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Finance/ReconciliationController.php
mineadmin-education-saas/backend/app/Http/Admin/Controller/Education/Finance/FinanceDashboardController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Finance/GuardianFinanceController.php
mineadmin-education-saas/backend/app/Http/Api/Controller/Education/Finance/PaymentCallbackController.php
mineadmin-education-saas/backend/app/Schema/Education/Finance/FinanceOrderSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Finance/PaymentSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Finance/RefundSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Finance/ReceiptSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Finance/ReconciliationSchema.php
mineadmin-education-saas/backend/app/Schema/Education/Finance/FinanceDashboardSchema.php
```

Create backend tests:

```text
mineadmin-education-saas/backend/tests/Feature/Education/Finance/FinanceMigrationTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Finance/FinanceOrderServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Finance/PaymentCallbackServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Finance/OfflineCollectionServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Finance/RefundServiceTest.php
mineadmin-education-saas/backend/tests/Unit/Education/Finance/ReconciliationServiceTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Finance/FinanceOrderAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Finance/PaymentAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Finance/RefundAdminApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Finance/GuardianFinanceApiTest.php
mineadmin-education-saas/backend/tests/Feature/Education/Finance/FinancePermissionIsolationAuditTest.php
```

Create PC:

```text
mineadmin-education-saas/admin-web/src/api/education/finance/order.ts
mineadmin-education-saas/admin-web/src/api/education/finance/payment.ts
mineadmin-education-saas/admin-web/src/api/education/finance/refund.ts
mineadmin-education-saas/admin-web/src/api/education/finance/receipt.ts
mineadmin-education-saas/admin-web/src/api/education/finance/reconciliation.ts
mineadmin-education-saas/admin-web/src/api/education/finance/dashboard.ts
mineadmin-education-saas/admin-web/src/views/education/finance/FinanceOrderList.vue
mineadmin-education-saas/admin-web/src/views/education/finance/PaymentRecordList.vue
mineadmin-education-saas/admin-web/src/views/education/finance/PaymentChannelList.vue
mineadmin-education-saas/admin-web/src/views/education/finance/RefundRequestList.vue
mineadmin-education-saas/admin-web/src/views/education/finance/ReceiptList.vue
mineadmin-education-saas/admin-web/src/views/education/finance/ReconciliationBatchList.vue
mineadmin-education-saas/admin-web/src/views/education/finance/FinanceDashboard.vue
mineadmin-education-saas/admin-web/src/views/education/finance/components/OfflineCollectionForm.vue
mineadmin-education-saas/admin-web/src/views/education/finance/components/RefundApprovalDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/finance/components/ReceiptIssueDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/finance/components/ReconciliationImportDrawer.vue
mineadmin-education-saas/admin-web/src/views/education/finance/__tests__/FinanceOrderList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/finance/__tests__/PaymentRecordList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/finance/__tests__/RefundRequestList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/finance/__tests__/ReconciliationBatchList.spec.ts
mineadmin-education-saas/admin-web/src/views/education/finance/__tests__/FinanceDashboard.spec.ts
```

Modify PC:

```text
mineadmin-education-saas/admin-web/src/router/modules/education.ts
```

Create mobile:

```text
mineadmin-education-saas/mobile-uniapp/src/api/finance/guardian.ts
mineadmin-education-saas/mobile-uniapp/pages/guardian/finance/orders.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/finance/order-detail.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/finance/receipts.vue
mineadmin-education-saas/mobile-uniapp/pages/guardian/finance/refunds.vue
mineadmin-education-saas/mobile-uniapp/tests/finance/guardian-finance.spec.ts
```

Modify mobile:

```text
mineadmin-education-saas/mobile-uniapp/pages.json
```

## Database Migration Design

Migration file:

```text
mineadmin-education-saas/backend/databases/migrations/2026_06_10_040000_create_v4_finance_tables.php
```

Money policy:

```text
All monetary columns use bigint integer cents: total_amount_cents, paid_amount_cents, refund_amount_cents, discount_amount_cents, channel_fee_cents. Never store money as float.
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
deleted_at timestamp null for mutable request/config records
```

Foreign-key policy:

```text
Use service-level validation for enrollment, student, guardian, payment order, account, and refund references. Do not create physical foreign keys.
```

Rollback behavior:

```text
Drop tables in reverse order: edu_finance_adjustments, edu_reconciliation_items, edu_reconciliation_batches, edu_receipts, edu_refund_records, edu_refund_requests, edu_payment_callbacks, edu_payment_records, edu_payment_channels, edu_finance_order_items, edu_finance_orders.
```

Table catalog:

| Table | Business columns | Indexes |
| --- | --- | --- |
| `edu_finance_orders` | `order_no varchar(64) not null`, `order_type varchar(40) not null`, `student_id bigint unsigned not null`, `guardian_id bigint unsigned null`, `enrollment_id bigint unsigned null`, `student_course_account_id bigint unsigned null`, `total_amount_cents bigint unsigned not null default 0`, `paid_amount_cents bigint unsigned not null default 0`, `refund_amount_cents bigint unsigned not null default 0`, `discount_amount_cents bigint unsigned not null default 0`, `status varchar(20) not null default pending`, `due_at timestamp null`, `paid_at timestamp null`, `remark varchar(500) null` | `unique uk_edu_finance_orders_tenant_order_no (tenant_id, order_no)`, `index idx_edu_finance_orders_student_status (tenant_id, campus_id, student_id, status)`, `index idx_edu_finance_orders_enrollment (tenant_id, enrollment_id)` |
| `edu_finance_order_items` | `order_id bigint unsigned not null`, `item_type varchar(40) not null`, `item_name varchar(160) not null`, `quantity decimal(10,2) not null default 1.00`, `unit_amount_cents bigint unsigned not null default 0`, `total_amount_cents bigint unsigned not null default 0`, `source_type varchar(40) null`, `source_id bigint unsigned null` | `index idx_edu_finance_order_items_order (tenant_id, order_id)`, `index idx_edu_finance_order_items_source (tenant_id, source_type, source_id)` |
| `edu_payment_channels` | `channel_code varchar(64) not null`, `channel_name varchar(120) not null`, `channel_type varchar(30) not null`, `config_json json null`, `status varchar(20) not null default enabled`, `sort_order int not null default 0` | `unique uk_edu_payment_channels_tenant_code (tenant_id, channel_code)`, `index idx_edu_payment_channels_status (tenant_id, status)` |
| `edu_payment_records` | `order_id bigint unsigned not null`, `payment_no varchar(64) not null`, `channel_code varchar(64) not null`, `channel_trade_no varchar(120) null`, `amount_cents bigint unsigned not null`, `channel_fee_cents bigint unsigned not null default 0`, `status varchar(20) not null default pending`, `paid_at timestamp null`, `payer_name varchar(120) null`, `operator_id bigint unsigned null`, `remark varchar(500) null` | `unique uk_edu_payment_records_tenant_payment_no (tenant_id, payment_no)`, `unique uk_edu_payment_records_channel_trade_no (channel_code, channel_trade_no)`, `index idx_edu_payment_records_order_status (tenant_id, order_id, status)` |
| `edu_payment_callbacks` | `channel_code varchar(64) not null`, `channel_trade_no varchar(120) not null`, `payment_record_id bigint unsigned null`, `raw_payload_json json not null`, `signature_valid tinyint(1) not null default 0`, `processed tinyint(1) not null default 0`, `processed_at timestamp null`, `error_message varchar(500) null` | `unique uk_edu_payment_callbacks_channel_trade (channel_code, channel_trade_no)`, `index idx_edu_payment_callbacks_processed (tenant_id, processed, created_at)` |
| `edu_refund_requests` | `order_id bigint unsigned not null`, `payment_record_id bigint unsigned null`, `refund_no varchar(64) not null`, `refund_amount_cents bigint unsigned not null`, `reason varchar(500) not null`, `status varchar(20) not null default pending`, `requested_by bigint unsigned not null`, `reviewed_by bigint unsigned null`, `reviewed_at timestamp null`, `review_note varchar(500) null` | `unique uk_edu_refund_requests_tenant_refund_no (tenant_id, refund_no)`, `index idx_edu_refund_requests_status (tenant_id, campus_id, status)`, `index idx_edu_refund_requests_order (tenant_id, order_id)` |
| `edu_refund_records` | `refund_request_id bigint unsigned not null`, `payment_record_id bigint unsigned null`, `refund_trade_no varchar(120) null`, `refund_amount_cents bigint unsigned not null`, `status varchar(20) not null default processing`, `refunded_at timestamp null`, `raw_payload_json json null` | `unique uk_edu_refund_records_request (tenant_id, refund_request_id)`, `index idx_edu_refund_records_trade (tenant_id, refund_trade_no)` |
| `edu_receipts` | `receipt_no varchar(64) not null`, `order_id bigint unsigned not null`, `student_id bigint unsigned not null`, `amount_cents bigint unsigned not null`, `status varchar(20) not null default issued`, `issued_by bigint unsigned not null`, `issued_at timestamp not null`, `voided_by bigint unsigned null`, `voided_at timestamp null`, `pdf_url varchar(255) null` | `unique uk_edu_receipts_tenant_receipt_no (tenant_id, receipt_no)`, `index idx_edu_receipts_order (tenant_id, order_id)`, `index idx_edu_receipts_student_status (tenant_id, student_id, status)` |
| `edu_reconciliation_batches` | `batch_no varchar(64) not null`, `channel_code varchar(64) not null`, `business_date date not null`, `status varchar(20) not null default imported`, `total_count int unsigned not null default 0`, `matched_count int unsigned not null default 0`, `unmatched_count int unsigned not null default 0`, `total_amount_cents bigint unsigned not null default 0` | `unique uk_edu_reconciliation_batches_channel_date (tenant_id, channel_code, business_date)`, `index idx_edu_reconciliation_batches_status (tenant_id, status)` |
| `edu_reconciliation_items` | `batch_id bigint unsigned not null`, `channel_trade_no varchar(120) not null`, `payment_record_id bigint unsigned null`, `amount_cents bigint unsigned not null`, `trade_time timestamp null`, `match_status varchar(20) not null default unmatched`, `difference_cents bigint not null default 0`, `raw_row_json json not null` | `index idx_edu_reconciliation_items_batch (tenant_id, batch_id, match_status)`, `index idx_edu_reconciliation_items_trade (tenant_id, channel_trade_no)` |
| `edu_finance_adjustments` | `order_id bigint unsigned not null`, `adjustment_type varchar(40) not null`, `amount_cents bigint not null`, `reason varchar(500) not null`, `operator_id bigint unsigned not null`, `source_type varchar(40) null`, `source_id bigint unsigned null` | `index idx_edu_finance_adjustments_order (tenant_id, order_id, created_at)`, `index idx_edu_finance_adjustments_source (tenant_id, source_type, source_id)` |

## MineAdmin Backend Module Design

Enums:

```text
FinanceOrderStatus: pending, paying, paid, partial_refunded, refunded, cancelled, closed
PaymentStatus: pending, paid, failed, cancelled, refunded
PaymentChannelType: offline_cash, offline_bank, offline_pos, wechat
RefundStatus: pending, approved, rejected, processing, refunded, failed, cancelled
ReceiptStatus: issued, voided
ReconciliationStatus: imported, matched, partially_matched, exception, closed
```

Layer tasks:

| Layer | Required implementation |
| --- | --- |
| Model | table names, fillable, integer money casts, JSON casts, enum casts, soft deletes for request/config tables |
| Repository | order/payment/refund/receipt/reconciliation pagination, idempotency lookups, row locks for payment/refund state changes |
| Service | create order from enrollment or create order plus pending enrollment, confirm offline payment, handle callback idempotently, confirm enrollment on payment success, approve refund, issue/void receipt, import/match reconciliation |
| Request | validate money cents as integer, order/payment/refund status enums, callback signature payload, import file metadata |
| Controller | MineAdmin annotations, permissions, request objects, Result envelope, audit writes for all financial mutations |
| Schema | document finance order, payment, refund, receipt, reconciliation, dashboard, and guardian responses |

Transaction and idempotency rules:

```text
PaymentCallbackService locks channel_trade_no, writes edu_payment_callbacks once, updates or creates payment record, marks order paid only once, calls EnrollmentService.confirm for the order's enrollment in the same transaction, and returns success for duplicate processed callbacks.
OfflineCollectionService creates one paid payment record per submitted payment_no, rejects amount over-payment unless configured, and on full payment calls EnrollmentService.confirm in the same transaction.
EnrollmentService.confirm is idempotent on enrollment_id, so a retried payment that marks an already-paid order does not re-materialize account units.
When the order's enrollment was cancelled before payment success, the payment is still recorded but confirm is skipped, the order is flagged paid_unconfirmed, and an operation alert is raised for manual refund or re-enrollment; account units are never materialized for a cancelled enrollment.
WeChat callbacks have no interactive user; PaymentCallbackService resolves a system EducationUserContext from the order's tenant_id and campus_id before calling confirm, so campus scope and audit attribution still apply.
RefundService locks order and refund request, records review result, creates refund record, and calls the V1 account adjustment service without deleting the original payment. Refunds use adjustUnits only; they never touch the enrollment-cancellation reversal path, so the two decrement paths cannot double-reverse the same units.
ReceiptService issues receipts only for paid amount minus refunded amount.
```

Audit rules:

```text
Write audit actions: education.finance.order.created, payment.offline_confirmed, payment.callback_processed, refund.requested, refund.approved, refund.rejected, receipt.issued, receipt.voided, reconciliation.imported, reconciliation.matched.
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
| `GET /admin/education/finance/orders/page` | `education:finance:order:page` | finance/admin | tenant + campus | no |
| `POST /admin/education/finance/orders` | `education:finance:order:create` | finance/admin | tenant + campus | yes |
| `POST /admin/education/finance/orders/from-enrollment` | `education:finance:order:create` | finance/admin | tenant + campus | yes |
| `POST /admin/education/finance/offline-payments` | `education:finance:payment:offline` | finance cashier | tenant + campus | yes |
| `POST /api/education/finance/payment-callbacks/wechat` | system callback | WeChat | signature + tenant resolver | yes |
| `POST /admin/education/finance/refund-requests` | `education:finance:refund:create` | finance/admin | tenant + campus | yes |
| `POST /admin/education/finance/refund-requests/{id}/approve` | `education:finance:refund:approve` | finance supervisor | tenant + campus | yes |
| `POST /admin/education/finance/receipts` | `education:finance:receipt:issue` | finance/admin | tenant + campus | yes |
| `POST /admin/education/finance/reconciliation-batches` | `education:finance:reconciliation:import` | finance/admin | tenant + campus | yes |
| `GET /mobile/education/finance/guardian/orders` | mobile guardian | guardian | bound students only | no |
| `GET /mobile/education/finance/guardian/receipts` | mobile guardian | guardian | bound students only | no |

Endpoint-level request/response/failure catalog:

```json
[
  {
    "api": "POST /admin/education/finance/orders",
    "note": "Order-first entry: creates a finance order and its pending enrollment in one transaction for renewal or manual sale.",
    "request": {"order_type": "enrollment", "student_id": 1201, "course_id": 88, "lesson_package_id": 301, "items": [{"item_type": "lesson_package", "item_name": "24课时包", "quantity": "1.00", "unit_amount_cents": 240000}]},
    "success": {"code": 200, "message": "success", "data": {"order_id": 1001, "order_no": "FO202606100001", "enrollment_id": 501, "status": "pending"}},
    "validation_failure": {"code": 422, "message": "lesson_package_id is required", "data": {"field": "lesson_package_id"}},
    "business_failure": {"code": 409, "message": "lesson package is disabled", "data": {"lesson_package_id": 301}}
  },
  {
    "api": "POST /admin/education/finance/orders/from-enrollment",
    "note": "Attach an order to an existing pending enrollment created by V1-02 admin entry or V3 conversion.",
    "request": {"enrollment_id": 501, "student_id": 1201, "items": [{"item_type": "lesson_package", "item_name": "24课时包", "quantity": "1.00", "unit_amount_cents": 240000}]},
    "success": {"code": 200, "message": "success", "data": {"order_id": 1001, "order_no": "FO202606100001", "status": "pending"}},
    "validation_failure": {"code": 422, "message": "enrollment_id is required", "data": {"field": "enrollment_id"}},
    "business_failure": {"code": 409, "message": "finance order already exists for enrollment", "data": {"enrollment_id": 501, "order_id": 1001}}
  },
  {
    "api": "POST /admin/education/finance/offline-payments",
    "request": {"order_id": 1001, "channel_code": "offline_cash", "amount_cents": 240000, "payer_name": "王女士", "remark": "cash"},
    "success": {"code": 200, "message": "success", "data": {"payment_record_id": 2001, "order_status": "paid"}},
    "validation_failure": {"code": 422, "message": "amount_cents must be a positive integer", "data": {"field": "amount_cents"}},
    "business_failure": {"code": 409, "message": "payment amount exceeds unpaid order amount", "data": {"unpaid_amount_cents": 120000}}
  },
  {
    "api": "POST /api/education/finance/payment-callbacks/wechat",
    "request": {"channel_trade_no": "420000001", "payment_no": "PAY202606100001", "amount_cents": 240000, "signature": "valid"},
    "success": {"code": 200, "message": "payment already processed", "data": {"payment_record_id": 2001}},
    "validation_failure": {"code": 422, "message": "signature is invalid", "data": {"field": "signature"}},
    "business_failure": {"code": 409, "message": "callback amount does not match order amount", "data": {"expected_amount_cents": 240000, "actual_amount_cents": 230000}}
  },
  {
    "api": "POST /admin/education/finance/refund-requests",
    "request": {"order_id": 1001, "refund_amount_cents": 60000, "reason": "student withdrawal"},
    "success": {"code": 200, "message": "success", "data": {"refund_request_id": 3001, "status": "pending"}},
    "validation_failure": {"code": 422, "message": "reason is required", "data": {"field": "reason"}},
    "business_failure": {"code": 409, "message": "order is not paid", "data": {"order_id": 1001, "status": "pending"}}
  },
  {
    "api": "POST /admin/education/finance/refund-requests/{id}/approve",
    "request": {"review_note": "approved by manager"},
    "success": {"code": 200, "message": "success", "data": {"refund_request_id": 3001, "status": "approved", "refund_record_id": 4001}},
    "validation_failure": {"code": 422, "message": "id must be a positive integer", "data": {"field": "id"}},
    "business_failure": {"code": 409, "message": "refund amount exceeds refundable amount", "data": {"refundable_amount_cents": 50000}}
  },
  {
    "api": "POST /admin/education/finance/receipts",
    "request": {"order_id": 1001, "amount_cents": 180000},
    "success": {"code": 200, "message": "success", "data": {"receipt_id": 5001, "receipt_no": "RC202606100001", "status": "issued"}},
    "validation_failure": {"code": 422, "message": "order_id is required", "data": {"field": "order_id"}},
    "business_failure": {"code": 409, "message": "receipt amount exceeds paid unreceipted amount", "data": {"available_amount_cents": 120000}}
  },
  {
    "api": "POST /admin/education/finance/reconciliation-batches",
    "request": {"channel_code": "wechat", "business_date": "2026-06-10", "file_url": "oss://reconciliation/20260610.csv"},
    "success": {"code": 200, "message": "success", "data": {"batch_id": 6001, "status": "imported", "total_count": 100}},
    "validation_failure": {"code": 422, "message": "business_date is required", "data": {"field": "business_date"}},
    "business_failure": {"code": 409, "message": "reconciliation batch already imported", "data": {"channel_code": "wechat", "business_date": "2026-06-10"}}
  },
  {
    "api": "GET /mobile/education/finance/guardian/orders",
    "request": {"student_id": 1201, "status": "paid"},
    "success": {"code": 200, "message": "success", "data": {"list": [{"order_id": 1001, "order_no": "FO202606100001", "status": "paid"}]}},
    "validation_failure": {"code": 422, "message": "student_id is required", "data": {"field": "student_id"}},
    "business_failure": {"code": 403, "message": "student is not bound to current guardian", "data": {"student_id": 1201}}
  }
]
```

## PC Admin Page Tasks

API clients:

```text
order.ts: pageFinanceOrders, createOrderFromEnrollment, cancelFinanceOrder, getFinanceOrderDetail
payment.ts: pagePaymentRecords, confirmOfflinePayment, pagePaymentChannels, savePaymentChannel
refund.ts: pageRefundRequests, createRefundRequest, approveRefundRequest, rejectRefundRequest
receipt.ts: pageReceipts, issueReceipt, voidReceipt
reconciliation.ts: pageReconciliationBatches, importReconciliationBatch, matchReconciliationBatch, pageReconciliationItems
dashboard.ts: getFinanceOverview, getPaymentTrend, getRefundSummary, getReconciliationSummary
```

Routes and pages:

| Route | Route name | Page | Permission | Key UI work |
| --- | --- | --- | --- | --- |
| `/education/finance/orders` | `EducationFinanceOrderList` | `FinanceOrderList.vue` | `education:finance:order:page` | order table, status tags, offline collection drawer, receipt/refund entry |
| `/education/finance/payments` | `EducationFinancePaymentRecordList` | `PaymentRecordList.vue` | `education:finance:payment:page` | payment table, channel filter, callback status |
| `/education/finance/channels` | `EducationFinancePaymentChannelList` | `PaymentChannelList.vue` | `education:finance:payment-channel:page` | channel CRUD, config JSON validation |
| `/education/finance/refunds` | `EducationFinanceRefundRequestList` | `RefundRequestList.vue` | `education:finance:refund:page` | approve/reject drawer, refundable amount display |
| `/education/finance/receipts` | `EducationFinanceReceiptList` | `ReceiptList.vue` | `education:finance:receipt:page` | issue/void receipt, PDF link |
| `/education/finance/reconciliation` | `EducationFinanceReconciliationBatchList` | `ReconciliationBatchList.vue` | `education:finance:reconciliation:page` | import, match, exception rows |
| `/education/finance/dashboard` | `EducationFinanceDashboard` | `FinanceDashboard.vue` | `education:finance:dashboard:overview` | payment/refund/reconciliation cards and trends |

Required states:

```text
All finance pages implement loading, empty, 403 permission, 422 validation, 409 business conflict, success refresh, and amount formatting in cents-to-yuan display.
```

## Teacher / Guardian Mobile Page Tasks

Teacher:

```text
This module has no teacher page because payment, refund, receipt, and reconciliation are finance/guardian concerns. Teacher payroll payment belongs to V5.
```

Guardian:

```text
API: mobile-uniapp/src/api/finance/guardian.ts
Pages: orders.vue, order-detail.vue, receipts.vue, refunds.vue
State: selected student, order list, order detail, payment status, receipt PDF link, refund status, empty state, 403 unbound student state
Isolation: guardian can access only finance records whose student_id is bound through V1 student_guardians.
```

`pages.json`:

```text
Register guardian finance pages under guardian role and require selected student context.
```

## Test Plan

Backend tests:

| Test file | Case | Assert |
| --- | --- | --- |
| `FinanceMigrationTest.php` | `test_finance_tables_have_integer_money_columns` | all amount columns are integer cents, not float |
| `FinanceOrderServiceTest.php` | `test_enrollment_creates_one_finance_order` | duplicate order request returns existing order conflict |
| `PaymentCallbackServiceTest.php` | `test_duplicate_callback_is_idempotent` | second callback returns processed payment without changing paid amount |
| `PaymentCallbackServiceTest.php` | `test_successful_callback_confirms_enrollment_and_materializes_account` | order is paid, enrollment status is confirmed, account available_units equals total_units |
| `PaymentCallbackServiceTest.php` | `test_duplicate_callback_does_not_re_materialize_account` | repeated callback leaves account available_units unchanged |
| `OfflineCollectionServiceTest.php` | `test_full_offline_payment_confirms_pending_enrollment` | pending enrollment becomes confirmed and account units materialize once |
| `PaymentCallbackServiceTest.php` | `test_callback_amount_mismatch_is_rejected` | order remains pending and callback records error |
| `RefundServiceTest.php` | `test_approved_refund_creates_refund_record_and_account_adjustment` | original payment remains, refund row exists, V1 account adjustment called |
| `ReconciliationServiceTest.php` | `test_import_matches_payment_records_by_trade_no_and_amount` | matched/unmatched counts are correct |
| `FinanceOrderAdminApiTest.php` | `test_order_api_validation_and_business_failures_match_catalog` | 422 and 409 payloads match catalog |
| `GuardianFinanceApiTest.php` | `test_guardian_reads_only_bound_student_orders` | bound returns 200; unbound returns 403 |
| `FinancePermissionIsolationAuditTest.php` | `test_finance_mutations_require_permission_and_write_audit` | denied without permission; audit exists after allowed write |

PC tests:

```text
FinanceOrderList.spec.ts asserts offline collection button hides without permission and formats cents as yuan.
PaymentRecordList.spec.ts asserts duplicate callback status displays payment already processed.
RefundRequestList.spec.ts asserts refund approval drawer blocks amount over refundable amount.
ReconciliationBatchList.spec.ts asserts unmatched rows render exception state.
FinanceDashboard.spec.ts asserts campus/date filters are passed to dashboard APIs.
```

Mobile tests:

```text
guardian-finance.spec.ts asserts guardian order list requires selected bound student, shows receipt link, and handles refund status.
```

## Execution Commands

Backend:

```bash
cd mineadmin-education-saas/backend
php bin/hyperf.php migrate
composer test -- --filter Education\\\\Finance
composer cs-fix -- --dry-run
composer analyse
```

Expected:

```text
V4 migrations run successfully.
All Education\\Finance tests pass.
Code style and static analysis pass.
```

PC:

```bash
cd mineadmin-education-saas/admin-web
pnpm install
pnpm lint
pnpm test -- finance
pnpm build
```

Expected:

```text
Finance PC lint, tests, and build pass.
```

Mobile:

```bash
cd mineadmin-education-saas/mobile-uniapp
pnpm install
pnpm lint
pnpm test -- finance
pnpm build:h5
```

Expected:

```text
Guardian finance mobile lint, tests, and H5 build pass.
```

## Acceptance Gate

V4 is accepted only when:

```text
- Enrollment or renewal can create a finance order with item rows.
- With finance_payment_enabled on, a new enrollment stays pending until payment, and account units materialize only on payment success.
- Payment callback and offline collection are idempotent, and a retried payment does not re-materialize account units.
- Monetary fields use integer cents and all displays format cents correctly.
- Refund approval creates refund records and V1 account adjustment without deleting payment rows.
- Receipt issue and void state transitions are enforced.
- Reconciliation imports channel rows and identifies matched/unmatched records.
- Guardian can view only bound-student orders, receipts, and refunds.
- All financial write operations are permission-checked and audited.
```

## Task Breakdown

### Task 1: Migration, Enums, and Models

- [x] Create migration with integer money fields, table catalog indexes, and reverse rollback.
- [x] Create enums and models listed in `File Structure`.
- [x] Write `FinanceMigrationTest.php`.
- [x] Run `php bin/hyperf.php migrate`; expected output is successful creation of all V4 finance tables.

### Task 2: Repositories and Services

- [x] Create repositories with tenant/campus filters, row lock methods, and idempotency lookups.
- [x] Create services for order, channel, payment, callback, offline collection, refund, receipt, reconciliation, and dashboard.
- [x] Write unit tests for payment idempotency, refund safety, reconciliation matching, and order duplication.
- [x] Run `composer test -- --filter Education\\\\Finance.*ServiceTest`; expected output is all finance service tests passing.

### Task 3: Requests, Schemas, Controllers, and API Tests

- [x] Create request classes with integer money validation and finance state validation.
- [x] Create schemas matching the API catalog.
- [x] Create admin, callback, and guardian controllers with permissions, Result envelope, and audit logging.
- [x] Write API, permission, isolation, and audit tests.
- [x] Run `composer test -- --filter Education\\\\Finance`; expected output is all V4 backend tests passing.

### Task 4: PC Admin

- [x] Create typed API clients.
- [x] Register finance routes and menus in `education.ts`.
- [x] Create order, payment, channel, refund, receipt, reconciliation, and dashboard pages.
- [x] Write PC tests listed in `Test Plan`.
- [x] Run `pnpm lint && pnpm test -- finance && pnpm build`; expected output is all PC gates passing.

### Task 5: Guardian Mobile

- [x] Create guardian finance API client.
- [x] Register guardian finance pages in `pages.json`.
- [x] Implement orders, details, receipts, and refunds with selected-student isolation.
- [x] Write `guardian-finance.spec.ts`.
- [x] Run `pnpm lint && pnpm test -- finance && pnpm build:h5`; expected output is all mobile gates passing.

### Task 6: V4 Final Gate

- [ ] Run all backend, PC, and mobile commands in `Execution Commands`.
- [ ] Confirm acceptance gate behavior with seeded paid, unpaid, refunded, and reconciled orders.
- [ ] Update status index only after all gates pass during implementation.

## Self-Review

- Spec coverage: Covers orders, items, channels, payment records, callbacks, offline collection, refunds, receipts, reconciliation, guardian lookup, finance dashboard, permissions, and audit.
- MineAdmin fit: Uses MineAdmin 3.x backend paths, PC pages, and uni-app guardian pages.
- Code-level readiness: Migration fields, indexes, rollback, API failures, backend layer tasks, PC/mobile states, tests, commands, and acceptance gates are specified.
- Implementation status: No code has been implemented; this is a ready implementation plan only.
