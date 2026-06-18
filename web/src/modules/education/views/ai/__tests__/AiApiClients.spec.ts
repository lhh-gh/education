import { afterEach, describe, expect, it, vi } from 'vitest'
import { pageModelConfigs, saveFeatureSetting, saveModelConfig } from '../../../api/ai/config.ts'
import { askDataQuestion, pageDataQuestionLogs, pageMetricCatalogs } from '../../../api/ai/data-question.ts'
import { approveGenerationResult, createGenerationTask, getGenerationResult, pageGenerationResults, pageGenerationTasks } from '../../../api/ai/generation.ts'
import { pagePromptTemplates, publishPromptTemplate, savePromptTemplate } from '../../../api/ai/prompt.ts'
import { markRecommendationHandled, pageRecommendationTasks } from '../../../api/ai/recommendation.ts'
import { pageRiskScores } from '../../../api/ai/risk.ts'
import { markSafetyHandled, pageSafetyEvents } from '../../../api/ai/safety.ts'
import { getUsageSummary, pageUsageLogs } from '../../../api/ai/usage.ts'

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

describe('education ai api clients', () => {
  it('uses_expected_ai_endpoints_and_scope_headers', () => {
    const calls = stubHttp()

    pageModelConfigs({ tenant_id: 1, campus_id: 9, page: 1 })
    saveModelConfig({ tenant_id: 1, campus_id: 9, config_code: 'default', provider: 'openai', model_name: 'gpt' })
    saveFeatureSetting({ tenant_id: 1, feature_code: 'lesson_comment', feature_name: 'Comment', model_config_id: 2 })
    pagePromptTemplates({ tenant_id: 1 })
    savePromptTemplate({ tenant_id: 1, template_code: 'comment', feature_code: 'lesson_comment', template_name: 'Comment', system_prompt: 's', user_prompt: 'u' })
    publishPromptTemplate('comment', { tenant_id: 1, version: 1 })
    createGenerationTask({ tenant_id: 1, feature_code: 'lesson_comment', business_type: 'lesson_student' })
    pageGenerationTasks({ tenant_id: 1 })
    getGenerationResult(8, { tenant_id: 1 })
    pageGenerationResults({ tenant_id: 1 })
    approveGenerationResult(9, { tenant_id: 1, review_note: 'ok' })
    pageRiskScores({ tenant_id: 1 })
    askDataQuestion({ tenant_id: 1, question_text: 'renewal alerts', metric_codes: ['renewal_alert_count'] })
    pageDataQuestionLogs({ tenant_id: 1 })
    pageMetricCatalogs({ tenant_id: 1 })
    pageRecommendationTasks({ tenant_id: 1 })
    markRecommendationHandled(10, { tenant_id: 1 })
    getUsageSummary({ tenant_id: 1, start_date: '2026-06-01', end_date: '2026-06-30' })
    pageUsageLogs({ tenant_id: 1 })
    pageSafetyEvents({ tenant_id: 1, risk_level: 'high' })
    markSafetyHandled(11, { tenant_id: 1 })

    expect(calls.map(call => [call.method, call.url])).toEqual([
      ['GET', '/admin/education/ai/model-configs/page'],
      ['POST', '/admin/education/ai/model-configs'],
      ['POST', '/admin/education/ai/feature-settings'],
      ['GET', '/admin/education/ai/prompt-templates/page'],
      ['POST', '/admin/education/ai/prompt-templates'],
      ['POST', '/admin/education/ai/prompt-templates/comment/publish'],
      ['POST', '/admin/education/ai/generation-tasks'],
      ['GET', '/admin/education/ai/generation-tasks/page'],
      ['GET', '/admin/education/ai/generation-results/8/detail'],
      ['GET', '/admin/education/ai/generation-results/page'],
      ['POST', '/admin/education/ai/generation-results/9/approve'],
      ['GET', '/admin/education/ai/risk-scores/page'],
      ['POST', '/admin/education/ai/data-questions'],
      ['GET', '/admin/education/ai/data-questions/page'],
      ['GET', '/admin/education/ai/metric-catalogs/page'],
      ['GET', '/admin/education/ai/recommendation-tasks/page'],
      ['POST', '/admin/education/ai/recommendation-tasks/10/handle'],
      ['GET', '/admin/education/ai/usage/summary'],
      ['GET', '/admin/education/ai/usage/page'],
      ['GET', '/admin/education/ai/safety-events/page'],
      ['POST', '/admin/education/ai/safety-events/11/handle'],
    ])
    expect(calls[0].config.headers).toMatchObject({ 'X-Tenant-Id': '1', 'X-Campus-Id': '9' })
  })
})
