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
  })

  it('shows_published_row_as_immutable', () => {
    expect(versionActionState({ status: 'published' })).toEqual({ canPublish: false, badge: 'Immutable' })
  })
})
