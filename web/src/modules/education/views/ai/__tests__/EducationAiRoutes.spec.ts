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
    })

    const expectedRoutes = [
      ['EducationAiModelConfigList', '/education/ai/model-configs', ['education:ai:model-config:page']],
      ['EducationAiPromptTemplateList', '/education/ai/prompts', ['education:ai:prompt:page']],
      ['EducationAiGenerationTaskList', '/education/ai/generation-tasks', ['education:ai:generation:page']],
      ['EducationAiReviewList', '/education/ai/reviews', ['education:ai:review:page']],
      ['EducationAiRiskScoreList', '/education/ai/risk-scores', ['education:ai:risk-score:page']],
      ['EducationAiDataQuestionWorkbench', '/education/ai/data-questions', ['education:ai:data-question:create']],
      ['EducationAiRecommendationList', '/education/ai/recommendations', ['education:ai:recommendation:page']],
      ['EducationAiUsageDashboard', '/education/ai/usage', ['education:ai:usage:summary']],
      ['EducationAiSafetyEventList', '/education/ai/safety-events', ['education:ai:safety:page']],
    ] as const

    for (const [name, path, auth] of expectedRoutes) {
      const route = findRoute(name)

      expect(route.path).toBe(path)
      expect(route.component).toEqual(expect.any(Function))
      expect(route.meta?.auth).toEqual(auth)
    }
  })
})
