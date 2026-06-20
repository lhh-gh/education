import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { growthWorkbenchFilterPayload, suggestionActionState } from '../growthRules.ts'

describe('growth workbench', () => {
  it('registers_growth_pages', () => {
    const growth = educationRoutes[0].children?.find(route => route.name === 'EducationGrowth')
    expect(growth?.meta?.title).toBe('增长转化')
    expect(growth?.children?.map(route => route.name)).toEqual([
      'EducationGrowthWorkbench',
      'EducationGrowthLeadScoreList',
      'EducationGrowthAiTalkScriptWorkbench',
      'EducationGrowthFollowupStrategyList',
      'EducationGrowthTrialConversionList',
      'EducationGrowthChannelRoiDashboard',
      'EducationGrowthConsultantMetricDashboard',
      'EducationGrowthLossReasonReport',
    ])
    expect(growth?.children?.map(route => route.meta?.title)).toEqual([
      '增长工作台',
      '线索评分',
      'AI 话术',
      '跟进策略',
      '试听转化',
      '渠道 ROI',
      '顾问指标',
      '流失原因',
    ])
  })

  it('keeps_owner_scope_and_suggestion_actions_explicit', () => {
    expect(growthWorkbenchFilterPayload({ tenant_id: 1, campus_id: 2, owner_user_id: 9, score_level: 'hot' })).toEqual({
      tenant_id: 1,
      campus_id: 2,
      owner_user_id: 9,
      score_level: 'hot',
    })
    expect(suggestionActionState({ status: 'accepted' })).toEqual({ disabled: true, label: '已采纳' })
    expect(suggestionActionState({ status: 'ignored' })).toEqual({ disabled: true, label: '已忽略' })
    expect(suggestionActionState({ status: 'pending' })).toEqual({ disabled: false, label: '采纳' })
  })
})
