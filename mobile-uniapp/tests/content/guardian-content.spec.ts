import { readFileSync } from 'fs'
import { join } from 'path'
import {
  getGuardianContentMaterials,
  getGuardianContentShowcases,
  getGuardianMaterialDetail,
  MobileApiError,
} from '../../src/api/content/guardian'

describe('guardian content api and pages', () => {
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

  it('published_materials_use_bound_student_scope', async () => {
    storageMock.mockImplementation((key: string) => ({
      access_token: 'guardian-token',
      education_tenant_id: 11,
      education_campus_id: 22,
      guardian_selected_student_id: 1201,
    })[key])
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data: { list: [{ material_id: 101, status: 'published' }], total: 1 } } })
    })

    await expect(getGuardianContentMaterials()).resolves.toEqual({ list: [{ material_id: 101, status: 'published' }], total: 1 })
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: {},
      method: 'GET',
      url: '/mobile/education/content/guardian/students/1201/materials',
    }))
  })

  it('read_record_is_called_once_for_material_detail', async () => {
    storageMock.mockImplementation((key: string) => ({
      guardian_selected_student_id: 1201,
    })[key])
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data: { material_id: 101, status: 'published' } } })
    })

    await getGuardianMaterialDetail(101)
    expect(requestMock).toHaveBeenCalledTimes(1)
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: {},
      url: '/mobile/education/content/guardian/students/1201/materials/101',
    }))
  })

  it('unbound_student_returns_403_and_pages_require_selected_student', async () => {
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 403, message: 'student is not bound to current guardian', data: { student_id: 1201 } } })
    })

    let thrown: MobileApiError | undefined
    try {
      await getGuardianContentShowcases({ student_id: 1201 })
    }
    catch (error) {
      thrown = error as MobileApiError
    }

    const source = readFileSync(join(process.cwd(), 'src/pages/guardian/content/showcases.vue'), 'utf8')
    const pagesJson = JSON.parse(readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8'))
    const pages = pagesJson.pages as Array<{ path: string, meta?: { role?: string, requiresSelectedStudent?: boolean } }>
    const contentPages = pages.filter(page => page.path.startsWith('pages/guardian/content/'))

    expect(thrown?.code).toBe(403)
    expect(source).toContain("item.status === 'published'")
    expect(source).not.toContain("status !== 'withdrawn'")
    expect(contentPages.map(page => page.path)).toEqual(expect.arrayContaining([
      'pages/guardian/content/materials',
      'pages/guardian/content/material-detail',
      'pages/guardian/content/showcases',
      'pages/guardian/content/showcase-detail',
    ]))
    expect(contentPages.every(page => page.meta?.role === 'guardian' && page.meta?.requiresSelectedStudent === true)).toBe(true)
  })
})
