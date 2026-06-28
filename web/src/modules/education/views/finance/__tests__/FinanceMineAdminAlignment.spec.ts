import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { describe, expect, it } from 'vitest'
import {
  duplicateCallbackText,
  financeErrorMessage,
  financePageText,
  financePermissions,
  financeStatusLabel,
  paymentChannelTypeLabel,
  validateRefundAmount,
} from '../financeRules.ts'

describe('finance mineadmin alignment', () => {
  it('uses_chinese_copy_for_finance_pages_and_common_states', () => {
    expect(financePageText.dashboard.title).toBe('财务看板')
    expect(financePageText.orders.title).toBe('订单管理')
    expect(financePageText.payments.title).toBe('收款记录')
    expect(financePageText.channels.title).toBe('支付渠道')
    expect(financePageText.refunds.title).toBe('退费管理')
    expect(financePageText.receipts.title).toBe('票据管理')
    expect(financePageText.reconciliation.title).toBe('对账管理')

    expect(financeStatusLabel('paid')).toBe('已收款')
    expect(financeStatusLabel('pending')).toBe('待处理')
    expect(financeStatusLabel('approved')).toBe('已通过')
    expect(financeStatusLabel('voided')).toBe('已作废')
    expect(paymentChannelTypeLabel('offline_cash')).toBe('现金')
    expect(paymentChannelTypeLabel('offline_bank')).toBe('银行转账')
    expect(validateRefundAmount(0, 60000)).toBe('退费金额必须大于 0')
    expect(validateRefundAmount(60001, 60000)).toBe('退费金额不能超过可退金额')
    expect(duplicateCallbackText('payment already processed')).toBe('支付已处理，请勿重复回调')
    expect(financeErrorMessage({ message: 'Permission denied' }, '操作失败')).toBe('暂无操作权限')
  })

  it('derives_finance_actions_from_backend_permissions', () => {
    expect(financePermissions(() => false)).toMatchObject({
      offlinePayment: false,
      paymentChannelSave: false,
      refundCreate: false,
      refundApprove: false,
      receiptIssue: false,
      reconciliationImport: false,
    })

    const permissions = new Set([
      'education:finance:payment:offline',
      'education:finance:payment-channel:save',
      'education:finance:refund:create',
      'education:finance:refund:approve',
      'education:finance:receipt:issue',
      'education:finance:reconciliation:import',
    ])

    expect(financePermissions(code => permissions.has(code))).toMatchObject({
      offlinePayment: true,
      paymentChannelSave: true,
      refundCreate: true,
      refundApprove: true,
      receiptIssue: true,
      reconciliationImport: true,
    })
  })

  it('removes_legacy_english_copy_from_finance_vue_pages', () => {
    const financeViewDir = resolve(process.cwd(), 'src/modules/education/views/finance')
    const vueFiles = [
      'FinanceDashboard.vue',
      'FinanceOrderList.vue',
      'PaymentRecordList.vue',
      'PaymentChannelList.vue',
      'RefundRequestList.vue',
      'ReceiptList.vue',
      'ReconciliationBatchList.vue',
      'components/OfflineCollectionForm.vue',
      'components/ReceiptIssueDrawer.vue',
      'components/RefundApprovalDrawer.vue',
      'components/ReconciliationImportDrawer.vue',
    ]
    const legacyCopies = [
      'Finance Dashboard',
      'Finance Orders',
      'Payment Records',
      'Payment Channels',
      'Refund Requests',
      'Paid Amount',
      'Refund Amount',
      'Order No',
      'Payment No',
      'Trade No',
      'Refund No',
      'Receipt No',
      'Batch No',
      'Date Range',
      'Business Date',
      'Offline Collection',
      'Issue Receipt',
      'Refund Approval',
      'Import Reconciliation',
      'No finance orders',
      'No payment records',
      'No payment channels',
      'No refund requests',
      'No receipts',
      'No reconciliation batches',
      'Refund amount exceeds refundable amount',
      'Refund amount must be positive',
      'payment already processed',
    ]

    for (const file of vueFiles) {
      const content = readFileSync(resolve(financeViewDir, file), 'utf8')

      for (const copy of legacyCopies) {
        expect(content, `${file} still contains [${copy}]`).not.toContain(copy)
      }
    }
  })
})
