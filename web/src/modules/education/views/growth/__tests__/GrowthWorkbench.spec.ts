import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { growthWorkbenchFilterPayload, suggestionActionState } from '../growthRules.ts'

describe('growth workbench', () => {
  it('registers_growth_pages', () => {
    const growth = educationRoutes[0].children?.find(route => route.name === 'EducationGrowth')
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
  })

  it('keeps_owner_scope_and_suggestion_actions_explicit', () => {
    expect(growthWorkbenchFilterPayload({ tenant_id: 1, campus_id: 2, owner_user_id: 9, score_level: 'hot' })).toEqual({
      tenant_id: 1,
      campus_id: 2,
      owner_user_id: 9,
      score_level: 'hot',
    })
    expect(suggestionActionState({ status: 'accepted' })).toEqual({ disabled: true, label: 'Accepted' })
    expect(suggestionActionState({ status: 'ignored' })).toEqual({ disabled: true, label: 'Ignored' })
    expect(suggestionActionState({ status: 'pending' })).toEqual({ disabled: false, label: 'Accept' })
  })
})
