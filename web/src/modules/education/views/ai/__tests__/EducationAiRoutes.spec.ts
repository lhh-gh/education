import type { RouteRecordRaw } from 'vue-router'
import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'

function flattenRoutes(routes: RouteRecordRaw[]): RouteRecordRaw[] {
  return routes.flatMap(route => [
    route,
    ...flattenRoutes(route.children ?? []),
  ])
}

function findRoute(name: string): RouteRecordRaw {
  const route = flattenRoutes(educationRoutes).find(item => item.name === name)
  if (!route) {
    throw new Error(`route ${name} not found`)
  }

  return route
}

describe('education ai routes', () => {
  it('registers_ai_route_tree', () => {
    expect(findRoute('EducationAi')).toMatchObject({
      path: '/education/ai',
      redirect: '/education/ai/model-configs',
      meta: {
        title: 'AI 助手',
      },
    })

    const expectedRoutes = [
      ['EducationAiModelConfigList', '/education/ai/model-configs', '模型配置', ['education:ai:model-config:page']],
      ['EducationAiPromptTemplateList', '/education/ai/prompts', '提示词模板', ['education:ai:prompt:page']],
      ['EducationAiGenerationTaskList', '/education/ai/generation-tasks', '生成任务', ['education:ai:generation:page']],
      ['EducationAiReviewList', '/education/ai/reviews', 'AI 审核', ['education:ai:review:page']],
      ['EducationAiRiskScoreList', '/education/ai/risk-scores', '风险评分', ['education:ai:risk-score:page']],
      ['EducationAiDataQuestionWorkbench', '/education/ai/data-questions', '数据问答', ['education:ai:data-question:page']],
      ['EducationAiRecommendationList', '/education/ai/recommendations', '智能推荐', ['education:ai:recommendation:page']],
      ['EducationAiUsageDashboard', '/education/ai/usage', '用量统计', ['education:ai:usage:summary']],
      ['EducationAiSafetyEventList', '/education/ai/safety-events', '安全事件', ['education:ai:safety:page']],
    ] as const

    for (const [name, path, title, auth] of expectedRoutes) {
      const route = findRoute(name)

      expect(route.path).toBe(path)
      expect(route.component).toEqual(expect.any(Function))
      expect(route.meta?.title).toBe(title)
      expect(route.meta?.auth).toEqual(auth)
    }
  })
})
