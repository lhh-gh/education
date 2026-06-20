import type { GrowthTagType } from '../../api/growth/types.ts'

export const growthText = {
  workbenchTitle: '增长工作台',
  leadScoreTitle: '线索评分',
  aiScriptTitle: 'AI 话术',
  followupStrategyTitle: '跟进策略',
  trialConversionTitle: '试听转化',
  channelRoiTitle: '渠道 ROI',
  consultantMetricTitle: '顾问指标',
  lossReasonTitle: '流失原因',
  refresh: '刷新',
  query: '查询',
  save: '保存',
  generate: '生成',
  confirm: '确认',
  ignore: '忽略',
  recalculate: '重新计算',
  loadConversion: '加载转化链路',
  saveReason: '保存原因',
  markLost: '标记流失',
  noPermission: '暂无操作权限',
  fields: {
    owner: '负责人',
    level: '评分等级',
    lead: '线索',
    leadId: '线索 ID',
    score: '评分',
    summary: '摘要',
    scriptType: '话术类型',
    goal: '目标',
    draftText: '草稿内容',
    code: '编码',
    name: '名称',
    template: '模板',
    status: '状态',
    source: '渠道',
    date: '日期',
    converted: '已转化',
    cost: '成本',
    revenue: '收入',
    consultant: '顾问',
    assigned: '已分配',
    follows: '跟进数',
    trials: '试听数',
    lost: '流失数',
    conversion: '转化率',
    reason: '原因',
    detail: '详情',
    suggestion: '建议',
    dueAt: '截止时间',
    actions: '操作',
  },
  levels: {
    hot: '高意向',
    high: '重点',
  },
  empty: {
    hotLeads: '暂无高意向线索',
    suggestions: '暂无跟进建议',
    scores: '暂无评分记录',
    conversion: '暂无转化链路',
  },
  aiBlocked: '不允许承诺自动优惠',
  scriptPlaceholder: '请输入话术内容',
  generatedFallback: '请在顾问确认后邀请家长参加试听课。',
  conversionFlow: '转化链路',
  ownerPrefix: '负责人',
} as const

export function growthWorkbenchFilterPayload(input: { tenant_id?: number, campus_id?: number, owner_user_id?: number, score_level?: string }) {
  return {
    tenant_id: input.tenant_id,
    campus_id: input.campus_id,
    owner_user_id: input.owner_user_id,
    score_level: input.score_level,
  }
}

export function suggestionActionState(suggestion: { status: string }) {
  if (suggestion.status === 'accepted') {
    return { disabled: true, label: '已采纳' }
  }
  if (suggestion.status === 'ignored') {
    return { disabled: true, label: '已忽略' }
  }

  return { disabled: false, label: '采纳' }
}

export function growthStatusLabel(status: string): string {
  const labels: Record<string, string> = {
    saved: '已保存',
    pending: '待处理',
    accepted: '已采纳',
    ignored: '已忽略',
    hot: '高意向',
    high: '重点',
    warm: '温和',
    cold: '低意向',
  }

  return labels[status] ?? status
}

export function containsBlockedAiPromise(text: string): boolean {
  const normalized = text.toLowerCase()

  return ['guaranteed discount', 'automatic discount', 'promise discount', 'create payment order'].some(phrase => normalized.includes(phrase))
}

export function aiScriptPayload(input: { tenant_id?: number, campus_id?: number, lead_id: number, script_type: string, goal: string, generated_text?: string }) {
  return {
    tenant_id: input.tenant_id,
    campus_id: input.campus_id,
    lead_id: input.lead_id,
    script_type: input.script_type,
    goal: input.goal.trim(),
    generated_text: input.generated_text,
  }
}

export function channelRoiFilterPayload(input: { tenant_id?: number, campus_id?: number, source_id?: number, dateRange?: [string, string] }) {
  return {
    tenant_id: input.tenant_id,
    campus_id: input.campus_id,
    source_id: input.source_id,
    start_date: input.dateRange?.[0],
    end_date: input.dateRange?.[1],
  }
}

export function roiTagType(roi: string | null): GrowthTagType {
  if (roi === null) {
    return 'info'
  }

  return Number.parseFloat(roi) >= 2 ? 'success' : 'warning'
}

export function consultantMetricFilterPayload(input: { tenant_id?: number, campus_id?: number, consultant_user_id?: number, dateRange?: [string, string] }) {
  return {
    tenant_id: input.tenant_id,
    campus_id: input.campus_id,
    consultant_user_id: input.consultant_user_id,
    start_date: input.dateRange?.[0],
    end_date: input.dateRange?.[1],
  }
}

export function consultantConversionRate(metric: { assigned_leads_count: number, converted_count: number }): string {
  if (metric.assigned_leads_count <= 0) {
    return '0.00%'
  }

  return `${((metric.converted_count / metric.assigned_leads_count) * 100).toFixed(2)}%`
}
