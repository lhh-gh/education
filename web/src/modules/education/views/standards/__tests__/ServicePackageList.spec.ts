import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { versionActionState } from '../standardRules.ts'

describe('standards service packages', () => {
  it('registers_standards_pages', () => {
    const standards = educationRoutes[0].children?.find(route => route.name === 'EducationStandards')
    expect(standards?.children?.map(route => route.name)).toEqual([
      'EducationStandardsServicePackageList',
      'EducationStandardsStageGoalEditor',
      'EducationStandardsAbilityPointList',
      'EducationStandardsTrialStandardEditor',
      'EducationStandardsDeliveryStandardEditor',
      'EducationStandardsServiceTemplateList',
      'EducationStandardsCourseMaterialList',
      'EducationStandardsCourseFeedbackList',
      'EducationStandardsCourseQualityDashboard',
      'EducationStandardsStandardVersionList',
      'EducationStandardsStandardReviewList',
    ])
    expect(standards?.meta?.title).toBe('标准化管理')
    expect(standards?.meta?.auth).toContain('education:standards:quality-dashboard:page')

    expect(standards?.children?.map(route => route.meta?.title)).toEqual([
      '服务包',
      '阶段目标',
      '能力点',
      '试听标准',
      '交付标准',
      '服务模板',
      '课程资料',
      '课程反馈',
      '质量看板',
      '标准版本',
      '标准评审',
    ])

    const qualityDashboard = standards?.children?.find(route => route.name === 'EducationStandardsCourseQualityDashboard')
    expect(qualityDashboard?.meta?.auth).toEqual(['education:standards:quality-dashboard:page'])
  })

  it('shows_published_row_as_immutable', () => {
    expect(versionActionState({ status: 'published' })).toEqual({ canPublish: false, badge: '已发布' })
    expect(versionActionState({ status: 'draft', review_status: 'approved' })).toEqual({ canPublish: true, badge: '已通过' })
  })
})
