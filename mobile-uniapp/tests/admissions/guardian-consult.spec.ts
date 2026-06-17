import { readFileSync } from 'fs'
import { join } from 'path'
import {
  createGuardianConsultation,
  MobileApiError,
} from '../../src/api/admissions/guardian'

describe('guardian admissions consultation', () => {
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

  it('submits_consultation_with_mobile_context_headers', async () => {
    storageMock.mockImplementation((key: string) => ({ access_token: 'guardian-token', education_tenant_id: 11, education_campus_id: 22 })[key])
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data: { lead_id: 102, stage: 'new' } } })
    })

    await expect(createGuardianConsultation({
      contact_name: 'Mrs Li',
      contact_mobile: '13900000000',
      student_name: 'Li',
      interested_course: 'Art',
    })).resolves.toEqual({ lead_id: 102, stage: 'new' })

    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: {
        contact_name: 'Mrs Li',
        contact_mobile: '13900000000',
        student_name: 'Li',
        interested_course: 'Art',
      },
      header: { Authorization: 'Bearer guardian-token', 'X-Tenant-Id': '11', 'X-Campus-Id': '22' },
      method: 'POST',
      url: '/mobile/education/admissions/guardian/consultations',
    }))
  })

  it('preserves_duplicate_mobile_response_for_page_display', async () => {
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 409, message: 'consultation mobile already exists', data: { lead_id: 102 } } })
    })

    let thrown: MobileApiError | undefined
    try {
      await createGuardianConsultation({
        contact_name: 'Mrs Li',
        contact_mobile: '13900000000',
        student_name: 'Li',
      })
    }
    catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.code).toBe(409)
    expect(thrown?.data).toEqual({ lead_id: 102 })
  })

  it('consult_page_validates_required_mobile_and_is_registered', () => {
    const source = readFileSync(join(process.cwd(), 'src/pages/guardian/admissions/consult.vue'), 'utf8')
    const pagesJson = JSON.parse(readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8'))
    const paths = pagesJson.pages.map((page: { path: string }) => page.path)

    expect(source).toContain('Contact mobile is required')
    expect(source).toContain("state.status = isConflict(error) ? 'conflict' : 'error'")
    expect(paths).toContain('pages/guardian/admissions/consult')
  })
})
