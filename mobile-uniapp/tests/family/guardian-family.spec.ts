import { readFileSync } from 'fs'
import { join } from 'path'
import {
  getGuardianLearningReports,
  markGuardianFamilyRead,
  submitGuardianHomework,
  MobileApiError,
} from '../../src/api/family/guardian'

describe('guardian family api and pages', () => {
  const requestMock = jest.fn()
  const storageMock = jest.fn()

  beforeEach(() => {
    requestMock.mockReset()
    storageMock.mockReset()
    ;(global as any).uni = {
      getStorageSync: storageMock,
      request: requestMock,
    }
  })

  afterEach(() => {
    delete (global as any).uni
  })

  it('uses_selected_student_headers_and_report_path_scope', async () => {
    storageMock.mockImplementation((key: string) => ({
      access_token: 'guardian-token',
      education_tenant_id: 11,
      education_campus_id: 22,
      guardian_selected_student_id: 1201,
    })[key])
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data: { list: [], total: 0 } } })
    })

    await expect(getGuardianLearningReports()).resolves.toEqual({ list: [], total: 0 })
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: {},
      header: { Authorization: 'Bearer guardian-token', 'X-Tenant-Id': '11', 'X-Campus-Id': '22' },
      method: 'GET',
      url: '/mobile/education/family/guardian/students/1201/learning-reports',
    }))
  })

  it('homework_submit_preserves_attachment_permission_failure', async () => {
    requestMock.mockImplementation((options) => {
      options.success({
        data: { code: 403, message: 'attachment permission denied', data: { attachment_id: 9001 } },
      })
    })

    let thrown: MobileApiError | undefined
    try {
      await submitGuardianHomework({ homework_target_id: 301, student_id: 1201, content: 'submitted', attachment_ids: [9001] })
    }
    catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.code).toBe(403)
    expect(thrown?.message).toBe('attachment permission denied')
    expect(thrown?.data).toEqual({ attachment_id: 9001 })
  })

  it('read_receipt_api_is_called_once_for_visible_report', async () => {
    storageMock.mockImplementation((key: string) => ({
      access_token: 'guardian-token',
      education_tenant_id: 11,
      education_campus_id: 22,
      guardian_selected_student_id: 1201,
    })[key])
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data: { read_receipt_id: 1 } } })
    })

    await markGuardianFamilyRead({ business_type: 'learning_report', business_id: 601 })
    expect(requestMock).toHaveBeenCalledTimes(1)
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { business_type: 'learning_report', business_id: 601, student_id: 1201 },
      url: '/mobile/education/family/guardian/read-receipts',
    }))
  })

  it('withdrawn_reports_do_not_render_and_pages_require_selected_student', () => {
    const source = readFileSync(join(process.cwd(), 'src/pages/guardian/family/learning-reports.vue'), 'utf8')
    const submitSource = readFileSync(join(process.cwd(), 'src/pages/guardian/family/homework-submit.vue'), 'utf8')
    const pagesJson = JSON.parse(readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8'))
    const pages = pagesJson.pages as Array<{ path: string, meta?: { role?: string, requiresSelectedStudent?: boolean } }>
    const familyPages = pages.filter(page => page.path.startsWith('pages/guardian/family/'))

    expect(source).toContain("report.status === 'published'")
    expect(source).not.toContain("report.status !== 'withdrawn'")
    expect(submitSource).toContain('Attachment permission denied')
    expect(familyPages.map(page => page.path)).toEqual(expect.arrayContaining([
      'pages/guardian/family/lesson-comments',
      'pages/guardian/family/homework',
      'pages/guardian/family/homework-submit',
      'pages/guardian/family/learning-reports',
      'pages/guardian/family/growth-records',
      'pages/guardian/family/messages',
    ]))
    expect(familyPages.every(page => page.meta?.role === 'guardian' && page.meta?.requiresSelectedStudent === true)).toBe(true)
  })
})
