import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { guardianVisibleLabel, materialPublishState, publishFailureNotice } from '../contentRules.ts'

describe('learning material list', () => {
  it('registers_content_pages', () => {
    const content = educationRoutes[0].children?.find(route => route.name === 'EducationContent')
    expect(content?.children?.map(route => route.name)).toEqual([
      'EducationContentLearningMaterialList',
      'EducationContentMaterialVersionList',
      'EducationContentMaterialAttachmentList',
      'EducationContentMaterialRelationEditor',
      'EducationContentStudentWorkList',
      'EducationContentShowcaseList',
      'EducationContentContentReviewList',
      'EducationContentMaterialUsageDashboard',
    ])
  })

  it('shows_guardian_visible_indicator_and_publish_409_review_error', () => {
    expect(guardianVisibleLabel({ guardian_visible: true })).toBe('Guardian visible')
    expect(materialPublishState({ status: 'draft' })).toEqual({ canPublish: true, badge: 'Review required' })
    expect(publishFailureNotice({ code: 409, message: 'material requires approved review before publish' })).toBe('material requires approved review before publish')
  })
})
