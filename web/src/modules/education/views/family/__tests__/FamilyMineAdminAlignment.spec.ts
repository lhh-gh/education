import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import type { RouteRecordRaw } from 'vue-router'
import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import {
  familyMetricCards,
  familySenderLabel,
  familyStatusLabel,
  guardianVisibleMarker,
  reportStatusAfterWithdraw,
  targetCountLabel,
} from '../familyRules.ts'

function flattenRoutes(routes: RouteRecordRaw[]): RouteRecordRaw[] {
  return routes.flatMap(route => [route, ...flattenRoutes(route.children ?? [])])
}

function findRoute(name: string): RouteRecordRaw {
  const route = flattenRoutes(educationRoutes).find(item => item.name === name)
  if (!route) {
    throw new Error(`route ${name} not found`)
  }

  return route
}

describe('family MineAdmin alignment', () => {
  it('uses_chinese_route_titles_for_family_menu', () => {
    expect(findRoute('EducationFamily').meta?.title).toBe('家校服务')

    const expectedRoutes = [
      ['EducationFamilyCommentTemplateList', '评语模板'],
      ['EducationFamilyPerformanceTagList', '表现标签'],
      ['EducationFamilyHomeworkAssignmentList', '课后作业'],
      ['EducationFamilyLearningReportList', '学习报告'],
      ['EducationFamilyGrowthRecordList', '成长记录'],
      ['EducationFamilyMessageMonitor', '家校消息'],
      ['EducationFamilyServiceQualityDashboard', '服务质量'],
    ] as const

    for (const [name, title] of expectedRoutes) {
      expect(findRoute(name).meta?.title).toBe(title)
    }
  })

  it('removes_legacy_english_copy_from_family_vue_pages', () => {
    const familyViewDir = resolve(process.cwd(), 'src/modules/education/views/family')
    const vueFiles = [
      'CommentTemplateList.vue',
      'PerformanceTagList.vue',
      'HomeworkAssignmentList.vue',
      'LearningReportList.vue',
      'GrowthRecordList.vue',
      'FamilyMessageMonitor.vue',
      'ServiceQualityDashboard.vue',
      'components/HomeworkAssignmentForm.vue',
      'components/LearningReportEditor.vue',
      'components/FamilyMessageThreadDrawer.vue',
    ]
    const legacyCopies = [
      '<span>Comment Templates</span>',
      '<span>Performance Tags</span>',
      '<span>Homework Assignments</span>',
      '<span>Learning Reports</span>',
      '<span>Growth Records</span>',
      '<span>Family Messages</span>',
      '<span>Service Quality</span>',
      'New Homework',
      'New Report',
      'Homework Assignment',
      'Learning Report',
      'Message Thread',
      '>Refresh<',
      '>Publish<',
      '>Withdraw<',
      '>Open<',
      '>Reply<',
      '>Cancel<',
      '>Save<',
      'No homework',
      'No reports',
      'No growth records',
      'No messages',
      'No quality metrics',
    ]

    for (const file of vueFiles) {
      const content = readFileSync(resolve(familyViewDir, file), 'utf8')

      for (const copy of legacyCopies) {
        expect(content, `${file} still contains [${copy}]`).not.toContain(copy)
      }
    }
  })

  it('maps_family_status_visibility_sender_and_metrics_to_chinese', () => {
    expect(familyStatusLabel('published')).toBe('已发布')
    expect(familyStatusLabel('submitted')).toBe('已提交')
    expect(familyStatusLabel('withdrawn')).toBe('已撤回')
    expect(targetCountLabel({ target_count: 3 })).toBe('3 人')
    expect(guardianVisibleMarker({ status: 'published' })).toBe('家长可见')
    expect(reportStatusAfterWithdraw({ id: 1, status: 'published' })).toBe('已撤回')
    expect(familySenderLabel('guardian')).toBe('家长')
    expect(familyMetricCards({ comment_count: 2, homework_review_count: 4, report_count: 6 }).map(item => item.title)).toEqual(['评语数', '作业点评', '学习报告'])
  })
})
