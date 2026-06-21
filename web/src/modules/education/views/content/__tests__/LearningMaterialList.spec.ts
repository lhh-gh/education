import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { guardianVisibleLabel, materialPublishState, publishFailureNotice } from '../contentRules.ts'

describe('learning material list', () => {
  it('registers_content_pages', () => {
    const content = educationRoutes[0].children?.find(route => route.name === 'EducationContent')
    expect(content?.meta?.title).toBe('内容教研')
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
    expect(content?.children?.map(route => route.meta?.title)).toEqual([
      '学习资料',
      '资料版本',
      '资料附件',
      '资料关联',
      '学生作品',
      '成果展陈',
      '内容审核',
      '使用看板',
    ])

    const relation = content?.children?.find(route => route.name === 'EducationContentMaterialRelationEditor')
    expect(relation?.meta?.auth).toEqual(['education:content:relation:page'])
  })

  it('shows_guardian_visible_indicator_and_publish_errors_in_chinese', () => {
    expect(guardianVisibleLabel({ guardian_visible: true })).toBe('家长可见')
    expect(guardianVisibleLabel({ guardian_visible: false })).toBe('内部使用')
    expect(materialPublishState({ status: 'draft' })).toEqual({ canPublish: true, badge: '待审核' })
    expect(materialPublishState({ status: 'published' })).toEqual({ canPublish: false, badge: '已发布' })
    expect(publishFailureNotice({ code: 409, message: 'material requires approved review before publish' })).toBe('material requires approved review before publish')
    expect(publishFailureNotice({ code: 403 })).toBe('暂无操作权限')
  })
})
