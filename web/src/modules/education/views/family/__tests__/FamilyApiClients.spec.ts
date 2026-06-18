import { afterEach, describe, expect, it, vi } from 'vitest'
import { pageCommentTemplates, pagePerformanceTags, saveCommentTemplate, savePerformanceTag } from '../../../api/family/comment.ts'
import { pageHomeworkAssignments, pageHomeworkSubmissions, publishHomeworkAssignment, saveHomeworkAssignment } from '../../../api/family/homework.ts'
import { getFamilyMessageThread, pageFamilyMessages, sendFamilyMessage } from '../../../api/family/message.ts'
import { pageGrowthRecords, pageLearningReports, publishLearningReport, saveLearningReport, withdrawLearningReport } from '../../../api/family/report.ts'
import { getServiceQualityDashboard, getServiceQualityMetrics } from '../../../api/family/quality.ts'

interface HttpCall {
  method: 'GET' | 'POST'
  url: string
  data?: unknown
  config?: unknown
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

afterEach(() => {
  vi.unstubAllGlobals()
})

describe('education family api clients', () => {
  it('uses_expected_family_endpoints', () => {
    const calls = stubHttp()

    pageCommentTemplates({ tenant_id: 1, page: 1, pageSize: 20 })
    saveCommentTemplate({ tenant_id: 1, template_code: 'A', template_name: 'Active', content: 'Good' })
    pagePerformanceTags({ tenant_id: 1, tag_type: 'attitude' })
    savePerformanceTag({ tenant_id: 1, tag_code: 'FOCUS', tag_name: 'Focus', tag_type: 'attitude' })
    pageHomeworkAssignments({ tenant_id: 1, page: 1, pageSize: 20 })
    saveHomeworkAssignment({ tenant_id: 1, title: 'Unit 1', content: 'Practice', student_ids: [2] })
    publishHomeworkAssignment(8, { tenant_id: 1 })
    pageHomeworkSubmissions({ tenant_id: 1, status: 'submitted' })
    pageLearningReports({ tenant_id: 1, page: 1, pageSize: 20 })
    saveLearningReport({ tenant_id: 1, student_id: 2, report_title: 'June', report_period: '2026-06', items: [] })
    publishLearningReport(9, { tenant_id: 1 })
    withdrawLearningReport(9, { tenant_id: 1 })
    pageGrowthRecords({ tenant_id: 1, student_id: 2 })
    pageFamilyMessages({ tenant_id: 1, student_id: 2 })
    getFamilyMessageThread({ tenant_id: 1, student_id: 2, thread_id: 'student-2' })
    sendFamilyMessage({ tenant_id: 1, student_id: 2, content: 'Hello' })
    getServiceQualityMetrics({ tenant_id: 1 })
    getServiceQualityDashboard({ tenant_id: 1 })

    expect(calls.map(call => [call.method, call.url])).toEqual([
      ['GET', '/admin/education/family/comment-templates/page'],
      ['POST', '/admin/education/family/comment-templates'],
      ['GET', '/admin/education/family/performance-tags/page'],
      ['POST', '/admin/education/family/performance-tags'],
      ['GET', '/admin/education/family/homework-assignments/page'],
      ['POST', '/admin/education/family/homework-assignments'],
      ['POST', '/admin/education/family/homework-assignments/8/publish'],
      ['GET', '/admin/education/family/homework-submissions/page'],
      ['GET', '/admin/education/family/learning-reports/page'],
      ['POST', '/admin/education/family/learning-reports'],
      ['POST', '/admin/education/family/learning-reports/9/publish'],
      ['POST', '/admin/education/family/learning-reports/9/withdraw'],
      ['GET', '/admin/education/family/growth-records/page'],
      ['GET', '/admin/education/family/messages/page'],
      ['GET', '/admin/education/family/messages/thread'],
      ['POST', '/admin/education/family/messages'],
      ['GET', '/admin/education/family/service-quality'],
      ['GET', '/admin/education/family/service-quality'],
    ])
  })
})
