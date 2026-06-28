import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { afterEach, describe, expect, it, vi } from 'vitest'
import { saveDeliveryStandard } from '../../../api/standards/delivery-standard.ts'
import { pageCourseMaterials, saveCourseMaterial } from '../../../api/standards/material.ts'
import { createPackageVersion, pageServicePackages, saveServicePackage } from '../../../api/standards/package.ts'
import { getCourseQualityMetrics, pageCourseFeedbackRecords, saveCourseFeedbackRecord } from '../../../api/standards/quality.ts'
import { saveStageGoal, syncGoalAbilityPoints } from '../../../api/standards/stage-goal.ts'
import { saveServiceTemplateSet } from '../../../api/standards/template.ts'
import { saveTrialStandard } from '../../../api/standards/trial-standard.ts'
import { publishStandardVersion, reviewStandardVersion, saveLocalizationOverride, withdrawStandardVersion } from '../../../api/standards/version.ts'

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

describe('education standards api clients', () => {
  it('uses_expected_standards_endpoints_and_scope_headers', () => {
    const calls = stubHttp()

    pageServicePackages({ tenant_id: 1, campus_id: 9 })
    saveServicePackage({ tenant_id: 1, campus_id: 9, package_code: 'ART', package_name: 'Art', course_id: 3 })
    createPackageVersion({ tenant_id: 1, campus_id: 9, service_package_id: 2, package_code: 'ART', package_name: 'Art 2', course_id: 3 })
    saveStageGoal({ tenant_id: 1, campus_id: 9, service_package_id: 2, goal_code: 'S1', goal_name: 'Line', goal_content: 'control', ability_point_ids: [7] })
    syncGoalAbilityPoints({ tenant_id: 1, campus_id: 9, service_package_id: 2, goal_code: 'S1', goal_name: 'Line', goal_content: 'control', ability_point_ids: [7] })
    saveTrialStandard({ tenant_id: 1, campus_id: 9, course_id: 3, standard_code: 'TRIAL', standard_name: 'Trial' })
    saveDeliveryStandard({ tenant_id: 1, campus_id: 9, course_id: 3, standard_code: 'DELIVERY', standard_name: 'Delivery', lesson_type: 'regular', content: 'teach' })
    saveServiceTemplateSet({ tenant_id: 1, campus_id: 9, template_set_code: 'TPL', template_set_name: 'Template' })
    pageCourseMaterials({ tenant_id: 1, campus_id: 9 })
    saveCourseMaterial({ tenant_id: 1, campus_id: 9, material_code: 'MAT', material_name: 'Material', material_type: 'file' })
    pageCourseFeedbackRecords({ tenant_id: 1, campus_id: 9 })
    saveCourseFeedbackRecord({ tenant_id: 1, campus_id: 9, course_id: 3, feedback_type: 'teacher', content: '稳定', score: 90 })
    getCourseQualityMetrics({ tenant_id: 1, campus_id: 9, course_id: 3 })
    publishStandardVersion(5, { tenant_id: 1, campus_id: 9, publish_note: 'ok' })
    withdrawStandardVersion(5, { tenant_id: 1, campus_id: 9 })
    reviewStandardVersion(6, { tenant_id: 1, campus_id: 9, status: 'approved' })
    saveLocalizationOverride(5, { tenant_id: 1, campus_id: 9, override_json: { title: 'Campus' } })

    expect(calls.map(call => [call.method, call.url])).toEqual([
      ['GET', '/admin/education/standards/service-packages'],
      ['POST', '/admin/education/standards/service-packages'],
      ['POST', '/admin/education/standards/service-packages'],
      ['POST', '/admin/education/standards/stage-goals'],
      ['POST', '/admin/education/standards/stage-goals'],
      ['POST', '/admin/education/standards/trial-standards'],
      ['POST', '/admin/education/standards/delivery-standards'],
      ['POST', '/admin/education/standards/service-templates'],
      ['GET', '/admin/education/standards/materials'],
      ['POST', '/admin/education/standards/materials'],
      ['GET', '/admin/education/standards/feedback-records'],
      ['POST', '/admin/education/standards/feedback-records'],
      ['GET', '/admin/education/standards/quality-metrics'],
      ['POST', '/admin/education/standards/versions/5/publish'],
      ['POST', '/admin/education/standards/versions/5/withdraw'],
      ['POST', '/admin/education/standards/reviews/6/review'],
      ['POST', '/admin/education/standards/versions/5/localization-overrides'],
    ])
    expect(calls[0].config.headers).toMatchObject({ 'X-Tenant-Id': '1', 'X-Campus-Id': '9' })
  })

  it('course_material_page_uses_real_page_api_and_permission_guard', () => {
    const source = readFileSync(resolve(__dirname, '../CourseMaterialList.vue'), 'utf8')

    expect(source).toContain('pageCourseMaterials')
    expect(source).toContain('hasAuth(\'education:standards:material:save\')')
    expect(source).toContain('v-loading="loading"')
    expect(source).toContain('v-model:current-page="search.page"')
  })
})
