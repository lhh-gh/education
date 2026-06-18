import { readFileSync } from 'fs'
import { join } from 'path'
import {
  createTeacherLessonMaterialUsage,
  getTeacherMaterials,
  saveTeacherMaterialFavorite,
  saveTeacherStudentWork,
  MobileApiError,
} from '../../src/api/content/teacher'

describe('teacher content api and pages', () => {
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

  it('material_search_respects_course_authorization', async () => {
    storageMock.mockImplementation((key: string) => ({
      access_token: 'teacher-token',
      education_tenant_id: 11,
      education_campus_id: 22,
    })[key])
    requestMock.mockImplementation((options) => {
      options.success({
        data: { code: 403, message: 'teacher is not authorized for this course', data: { course_id: 999001 } },
      })
    })

    let thrown: MobileApiError | undefined
    try {
      await getTeacherMaterials({ course_id: 999001, keyword: 'line' })
    }
    catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.code).toBe(403)
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { course_id: 999001, keyword: 'line' },
      header: { Authorization: 'Bearer teacher-token', 'X-Tenant-Id': '11', 'X-Campus-Id': '22' },
      method: 'GET',
      url: '/mobile/education/content/teacher/materials',
    }))
  })

  it('favorite_toggle_and_usage_are_sent_to_content_endpoints', async () => {
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data: { status: 'saved', lesson_material_usage_id: 7 } } })
    })

    await saveTeacherMaterialFavorite({ material_id: 101, favorited: true })
    await createTeacherLessonMaterialUsage({ lesson_id: 8801, material_id: 101, usage_type: 'pre_class' })

    expect(requestMock).toHaveBeenNthCalledWith(1, expect.objectContaining({
      data: { material_id: 101, favorited: true },
      method: 'POST',
      url: '/mobile/education/content/teacher/material-favorites',
    }))
    expect(requestMock).toHaveBeenNthCalledWith(2, expect.objectContaining({
      data: { lesson_id: 8801, material_id: 101, usage_type: 'pre_class' },
      method: 'POST',
      url: '/mobile/education/content/teacher/lesson-material-usages',
    }))
  })

  it('student_work_submit_preserves_403_and_pages_are_registered', async () => {
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 403, message: 'student is not assigned to current teacher', data: { student_id: 1201 } } })
    })

    let thrown: MobileApiError | undefined
    try {
      await saveTeacherStudentWork({ student_id: 1201, lesson_id: 8801, title: 'Line work', attachment_ids: [9001] })
    }
    catch (error) {
      thrown = error as MobileApiError
    }

    const pagesJson = JSON.parse(readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8'))
    const pages = pagesJson.pages as Array<{ path: string, meta?: { role?: string } }>
    const contentPages = pages.filter(page => page.path.startsWith('pages/teacher/content/'))

    expect(thrown?.code).toBe(403)
    expect(contentPages.map(page => page.path)).toEqual(expect.arrayContaining([
      'pages/teacher/content/materials',
      'pages/teacher/content/material-detail',
      'pages/teacher/content/favorites',
      'pages/teacher/content/lesson-material-usage',
      'pages/teacher/content/student-work-form',
    ]))
    expect(contentPages.every(page => page.meta?.role === 'teacher')).toBe(true)
  })
})
