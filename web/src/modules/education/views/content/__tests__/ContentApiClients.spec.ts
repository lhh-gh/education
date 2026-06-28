import { afterEach, describe, expect, it, vi } from 'vitest'
import { pageMaterialAttachments } from '../../../api/content/attachment.ts'
import { pageLearningMaterials, publishLearningMaterial, saveLearningMaterial, withdrawLearningMaterial } from '../../../api/content/material.ts'
import { getMaterialUsageMetrics, getStudentWorkMetrics } from '../../../api/content/metric.ts'
import { pageMaterialRelations, saveMaterialRelations } from '../../../api/content/relation.ts'
import { pageContentReviews, reviewContent } from '../../../api/content/review.ts'
import { pageShowcases, publishShowcase, saveShowcase, withdrawShowcase } from '../../../api/content/showcase.ts'
import { pageStudentWorks, publishStudentWork, withdrawStudentWork } from '../../../api/content/student-work.ts'
import { createMaterialVersion, getMaterialVersionDetail, pageMaterialVersions } from '../../../api/content/version.ts'

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

describe('education content api clients', () => {
  it('uses_expected_content_endpoints_and_scope_headers', () => {
    const calls = stubHttp()
    const scope = { tenant_id: 1, campus_id: 9 }

    pageLearningMaterials(scope)
    saveLearningMaterial({ ...scope, material_code: 'MAT', material_name: 'Material', material_type: 'worksheet' })
    publishLearningMaterial(2, { ...scope, publish_note: 'ok' })
    withdrawLearningMaterial(2, scope)
    pageMaterialVersions({ ...scope, material_id: 2 })
    pageMaterialAttachments({ ...scope, material_version_id: 3 })
    createMaterialVersion(2, { ...scope, title: 'V2' })
    getMaterialVersionDetail(3, scope)
    pageMaterialRelations({ ...scope, material_id: 2 })
    saveMaterialRelations(2, { ...scope, relations: [{ target_type: 'course', target_id: 5 }] })
    pageStudentWorks(scope)
    publishStudentWork(6, scope)
    withdrawStudentWork(6, scope)
    pageShowcases(scope)
    saveShowcase({ ...scope, student_id: 7, title: 'Stage' })
    publishShowcase(8, scope)
    withdrawShowcase(8, scope)
    pageContentReviews(scope)
    reviewContent(9, { ...scope, status: 'approved' })
    getMaterialUsageMetrics(scope)
    getStudentWorkMetrics(scope)

    expect(calls.map(call => [call.method, call.url])).toEqual([
      ['GET', '/admin/education/content/materials'],
      ['POST', '/admin/education/content/materials'],
      ['POST', '/admin/education/content/materials/2/publish'],
      ['POST', '/admin/education/content/materials/2/withdraw'],
      ['GET', '/admin/education/content/material-versions'],
      ['GET', '/admin/education/content/attachments'],
      ['POST', '/admin/education/content/materials/2/versions'],
      ['GET', '/admin/education/content/material-versions/3'],
      ['GET', '/admin/education/content/material-relations'],
      ['POST', '/admin/education/content/materials/2/relations'],
      ['GET', '/admin/education/content/student-works'],
      ['POST', '/admin/education/content/student-works/6/publish'],
      ['POST', '/admin/education/content/student-works/6/withdraw'],
      ['GET', '/admin/education/content/showcases'],
      ['POST', '/admin/education/content/showcases'],
      ['POST', '/admin/education/content/showcases/8/publish'],
      ['POST', '/admin/education/content/showcases/8/withdraw'],
      ['GET', '/admin/education/content/reviews'],
      ['POST', '/admin/education/content/reviews/9/review'],
      ['GET', '/admin/education/content/material-usage-metrics'],
      ['GET', '/admin/education/content/student-work-metrics'],
    ])
    expect(calls[0].config.headers).toMatchObject({ 'X-Tenant-Id': '1', 'X-Campus-Id': '9' })
  })
})
