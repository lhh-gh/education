export type FinanceTagType = '' | 'success' | 'warning' | 'danger' | 'info'

export interface FinancePermissions {
  offlinePayment: boolean
  paymentChannelSave: boolean
  refundCreate: boolean
  refundApprove: boolean
  receiptIssue: boolean
  reconciliationImport: boolean
}

export const financePageText = {
  dashboard: {
    title: '财务看板',
    refresh: '刷新',
    fields: {
      campus: '校区',
      dateRange: '日期范围',
      start: '开始时间',
      end: '结束时间',
    },
    cards: {
      orders: '订单数',
      payments: '收款笔数',
      paidAmount: '收款金额',
      refundAmount: '退费金额',
    },
  },
  orders: {
    title: '订单管理',
    refresh: '刷新',
    search: '查询',
    reset: '重置',
    empty: '暂无订单',
    fields: {
      campus: '校区',
      status: '状态',
      keyword: '关键字',
    },
    columns: {
      orderNo: '订单号',
      student: '学员',
      total: '应收金额',
      paid: '已收金额',
      status: '状态',
      actions: '操作',
    },
    actions: {
      collect: '线下收款',
      receipt: '开票',
    },
  },
  payments: {
    title: '收款记录',
    refresh: '刷新',
    search: '查询',
    reset: '重置',
    empty: '暂无收款记录',
    fields: {
      channel: '渠道',
      status: '状态',
    },
    columns: {
      paymentNo: '收款单号',
      channel: '渠道',
      tradeNo: '交易流水号',
      amount: '金额',
      status: '状态',
    },
  },
  channels: {
    title: '支付渠道',
    save: '保存',
    saved: '支付渠道已保存',
    empty: '暂无支付渠道',
    fields: {
      code: '渠道编码',
      name: '渠道名称',
      type: '渠道类型',
    },
    columns: {
      code: '渠道编码',
      name: '渠道名称',
      type: '渠道类型',
      status: '状态',
    },
  },
  refunds: {
    title: '退费管理',
    refresh: '刷新',
    empty: '暂无退费申请',
    columns: {
      refundNo: '退费单号',
      order: '订单',
      amount: '退费金额',
      status: '状态',
      actions: '操作',
    },
    actions: {
      approve: '审核',
    },
  },
  receipts: {
    title: '票据管理',
    refresh: '刷新',
    empty: '暂无票据',
    columns: {
      receiptNo: '票据号',
      student: '学员',
      amount: '票据金额',
      status: '状态',
    },
  },
  reconciliation: {
    title: '对账管理',
    import: '导入对账',
    empty: '暂无对账批次',
    columns: {
      batchNo: '批次号',
      channel: '渠道',
      businessDate: '业务日期',
      total: '总笔数',
      matched: '已匹配',
      unmatched: '未匹配',
      status: '状态',
    },
  },
  drawers: {
    offlineCollection: {
      title: '线下收款',
      confirm: '确认收款',
      fields: {
        channel: '收款渠道',
        paymentNo: '收款单号',
        amount: '收款金额',
        payer: '付款人',
        remark: '备注',
      },
    },
    receiptIssue: {
      title: '开具票据',
      issue: '开票',
      fields: {
        amount: '票据金额',
        pdfUrl: '票据文件 URL',
      },
    },
    refundApproval: {
      title: '退费审核',
      approve: '通过',
      fields: {
        refund: '退费单',
        reviewNote: '审核备注',
      },
    },
    reconciliationImport: {
      title: '导入对账',
      import: '导入',
      fields: {
        channel: '渠道',
        businessDate: '业务日期',
        fileUrl: '文件 URL',
      },
    },
  },
} as const

const statusLabels: Record<string, string> = {
  approved: '已通过',
  cancelled: '已取消',
  closed: '已关闭',
  disabled: '停用',
  enabled: '启用',
  exception: '异常',
  failed: '失败',
  imported: '已导入',
  issued: '已开票',
  matched: '已匹配',
  paid: '已收款',
  partially_matched: '部分匹配',
  partial_refunded: '部分退费',
  paying: '收款中',
  pending: '待处理',
  processing: '处理中',
  refunded: '已退费',
  rejected: '已拒绝',
  unmatched: '未匹配',
  voided: '已作废',
}

const channelTypeLabels: Record<string, string> = {
  offline_bank: '银行转账',
  offline_cash: '现金',
  offline_pos: 'POS',
  wechat: '微信支付',
}

export function centsToYuan(cents?: number | null): string {
  return `¥${((cents ?? 0) / 100).toFixed(2)}`
}

export function financeTagType(value?: string | null): FinanceTagType {
  if (!value) {
    return ''
  }
  if (['paid', 'approved', 'issued', 'matched', 'enabled'].includes(value)) {
    return 'success'
  }
  if (['pending', 'paying', 'processing', 'imported', 'partially_matched'].includes(value)) {
    return 'warning'
  }
  if (['failed', 'rejected', 'cancelled', 'voided', 'exception'].includes(value)) {
    return 'danger'
  }
  if (['refunded', 'partial_refunded', 'closed', 'unmatched'].includes(value)) {
    return 'info'
  }

  return ''
}

export function financePermissions(has: (code: string) => boolean): FinancePermissions {
  return {
    offlinePayment: has('education:finance:payment:offline'),
    paymentChannelSave: has('education:finance:payment-channel:save'),
    refundCreate: has('education:finance:refund:create'),
    refundApprove: has('education:finance:refund:approve'),
    receiptIssue: has('education:finance:receipt:issue'),
    reconciliationImport: has('education:finance:reconciliation:import'),
  }
}

export function canShowOfflineCollection(has: (code: string) => boolean): boolean {
  return financePermissions(has).offlinePayment
}

export function validateRefundAmount(amountCents: number, refundableCents: number): string {
  if (amountCents <= 0) {
    return '退费金额必须大于 0'
  }
  if (amountCents > refundableCents) {
    return '退费金额不能超过可退金额'
  }

  return ''
}

export function reconciliationRowState(row: { match_status?: string | null }): 'normal' | 'exception' {
  return row.match_status === 'unmatched' ? 'exception' : 'normal'
}

export function duplicateCallbackText(message?: string): string {
  return message === 'payment already processed' ? '支付已处理，请勿重复回调' : (message ?? '')
}

export function buildFinanceDashboardParams(input: { tenant_id?: number, campus_id?: number, start_at?: string, end_at?: string }): Record<string, unknown> {
  return {
    tenant_id: input.tenant_id,
    campus_id: input.campus_id,
    start_at: input.start_at,
    end_at: input.end_at,
  }
}

export function financeStatusLabel(status?: string | null): string {
  return status ? (statusLabels[status] ?? status) : ''
}

export function paymentChannelTypeLabel(type?: string | null): string {
  return type ? (channelTypeLabels[type] ?? type) : ''
}

export function financeErrorMessage(error: any, fallback: string): string {
  if (error?.message === 'Permission denied' || error?.code === 403) {
    return '暂无操作权限'
  }

  return error?.message || fallback
}
