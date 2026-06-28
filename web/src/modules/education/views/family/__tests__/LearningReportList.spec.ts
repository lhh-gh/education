import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { describe, expect, it } from 'vitest'
import { guardianVisibleMarker, reportStatusAfterWithdraw } from '../familyRules.ts'

const learningReportListSource = readFileSync(resolve(__dirname, '../LearningReportList.vue'), 'utf8')

describe('learning report list', () => {
  it('asserts_withdrawn_report_status_hides_guardian_visible_marker', () => {
    expect(guardianVisibleMarker({ status: 'published' })).toBe('家长可见')
    expect(guardianVisibleMarker({ status: 'withdrawn' })).toBe('')
    expect(reportStatusAfterWithdraw({ id: 1, status: 'published' })).toBe('已撤回')
  })

  it('guards_create_publish_and_withdraw_actions_with_backend_permissions', () => {
    expect(learningReportListSource).toContain('import hasAuth from \'@/utils/permission/hasAuth.ts\'')
    expect(learningReportListSource).toContain('hasAuth(\'education:family:report:create\')')
    expect(learningReportListSource).toContain('hasAuth(\'education:family:report:publish\')')
    expect(learningReportListSource).toContain('v-if="canCreate"')
    expect(learningReportListSource).toContain('v-if="canPublish"')
  })
})
