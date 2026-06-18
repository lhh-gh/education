import type { GrowthTagType } from '../../api/growth/types.ts'

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
    return { disabled: true, label: 'Accepted' }
  }
  if (suggestion.status === 'ignored') {
    return { disabled: true, label: 'Ignored' }
  }

  return { disabled: false, label: 'Accept' }
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
