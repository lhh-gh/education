import { afterEach, describe, expect, it, vi } from 'vitest'
import { confirmAiTalkScript, generateAiTalkScript } from '../../../api/growth/ai-script.ts'
import { saveGrowthCampaign } from '../../../api/growth/campaign.ts'
import { getChannelRoi, saveChannelCost } from '../../../api/growth/channel-roi.ts'
import { getConsultantMetrics } from '../../../api/growth/consultant-metric.ts'
import { saveFollowupStrategy } from '../../../api/growth/followup.ts'
import { recalculateLeadScore } from '../../../api/growth/lead-score.ts'
import { createLeadLossRecord, saveLossReason } from '../../../api/growth/loss.ts'
import { getGrowthWorkbench } from '../../../api/growth/workbench.ts'

interface HttpCall {
  method: 'GET' | 'POST'
  url: string
  data?: unknown
  config?: any
}

function stubHttp(): HttpCall[] {
  const calls: HttpCall[] = []
  const response = Promise.resolve({ code: 200, message: 'success', data: {} })

  vi.stubGlobal('useHttp', () => ({
    get: (url: string, config?: unknown) => {
      calls.push({ method: 'GET', url, config })
      return response
    },
    post: (url: string, data?: unknown, config?: unknown) => {
      calls.push({ method: 'POST', url, data, config })
      return response
    },
  }))

  return calls
}

afterEach(() => vi.unstubAllGlobals())

describe('education growth api clients', () => {
  it('uses_expected_growth_endpoints_and_scope_headers', () => {
    const calls = stubHttp()

    getGrowthWorkbench({ tenant_id: 1, campus_id: 9, owner_user_id: 3 })
    recalculateLeadScore(11, { tenant_id: 1, campus_id: 9, reason: 'trial feedback' })
    generateAiTalkScript({ tenant_id: 1, campus_id: 9, lead_id: 11, script_type: 'trial_invitation', goal: 'invite trial' })
    confirmAiTalkScript(22, { tenant_id: 1, edited_script: 'hello' })
    saveFollowupStrategy({ tenant_id: 1, strategy_code: 'HOT', strategy_name: 'Hot', lead_stage: 'followed', score_level: 'hot', suggestion_template: 'Invite', next_follow_hours: 4 })
    getChannelRoi({ tenant_id: 1, campus_id: 9, source_id: 7, start_date: '2026-06-01', end_date: '2026-06-10' })
    saveChannelCost({ tenant_id: 1, campus_id: 9, source_id: 7, cost_date: '2026-06-10', amount_cents: 1000 })
    getConsultantMetrics({ tenant_id: 1, campus_id: 9, consultant_user_id: 8, start_date: '2026-06-01', end_date: '2026-06-10' })
    saveLossReason({ tenant_id: 1, reason_code: 'PRICE', reason_name: 'Price', category: 'price' })
    createLeadLossRecord(11, { tenant_id: 1, loss_reason_id: 5, detail: 'price objection' })
    saveGrowthCampaign({ tenant_id: 1, campaign_code: 'SUMMER', campaign_name: 'Summer', channel_type: 'ads', budget_cents: 100000 })

    expect(calls.map(call => [call.method, call.url])).toEqual([
      ['GET', '/admin/education/growth/workbench'],
      ['POST', '/admin/education/growth/leads/11/score/recalculate'],
      ['POST', '/admin/education/growth/ai-talk-scripts/generate'],
      ['POST', '/admin/education/growth/ai-talk-scripts/22/confirm'],
      ['POST', '/admin/education/growth/followup-strategies'],
      ['GET', '/admin/education/growth/channel-roi'],
      ['POST', '/admin/education/growth/channel-costs'],
      ['GET', '/admin/education/growth/consultant-metrics'],
      ['POST', '/admin/education/growth/loss-reasons'],
      ['POST', '/admin/education/growth/leads/11/loss-records'],
      ['POST', '/admin/education/growth/campaigns'],
    ])
    expect(calls[0].config.headers).toMatchObject({ 'X-Tenant-Id': '1', 'X-Campus-Id': '9' })
  })
})
