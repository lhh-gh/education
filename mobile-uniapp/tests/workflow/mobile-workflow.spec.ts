import { readFileSync } from 'fs'
import { join } from 'path'
import {
  addTeacherWorkflowTaskComment,
  completeTeacherWorkflowTask,
  getTeacherWorkflowTaskDetail,
  getTeacherWorkflowTasks,
  MobileApiError,
} from '../../src/api/workflow/teacher'
import {
  completeOperatorWorkflowTask,
  getOperatorWorkflowTasks,
} from '../../src/api/workflow/operator'

describe('mobile workflow api and pages', () => {
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

  it('teacher_my_tasks_uses_mobile_scope_and_backend_assignee_filter', async () => {
    storageMock.mockImplementation((key: string) => ({
      access_token: 'teacher-token',
      education_tenant_id: 11,
      education_campus_id: 22,
    })[key])
    requestMock.mockImplementation((options) => {
      options.success({
        data: {
          code: 200,
          message: 'success',
          data: { list: [{ task_id: 201, title: 'Renewal follow', status: 'pending' }], total: 1 },
        },
      })
    })

    await expect(getTeacherWorkflowTasks({ status: 'pending' })).resolves.toEqual({
      list: [{ task_id: 201, title: 'Renewal follow', status: 'pending' }],
      total: 1,
    })
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { status: 'pending' },
      header: { Authorization: 'Bearer teacher-token', 'X-Tenant-Id': '11', 'X-Campus-Id': '22' },
      method: 'GET',
      url: '/mobile/education/workflow/tasks/my',
    }))
  })

  it('detail_page_reads_only_tasks_returned_by_my_task_scope', async () => {
    requestMock.mockImplementation((options) => {
      options.success({
        data: {
          code: 200,
          message: 'success',
          data: {
            list: [
              { task_id: 301, title: 'Assigned task', status: 'processing', comments: [{ content: 'Need follow up' }] },
            ],
            total: 1,
          },
        },
      })
    })

    await expect(getTeacherWorkflowTaskDetail(301)).resolves.toEqual({
      task_id: 301,
      title: 'Assigned task',
      status: 'processing',
      comments: [{ content: 'Need follow up' }],
    })
    await expect(getTeacherWorkflowTaskDetail(999)).rejects.toMatchObject({ code: 403 })
  })

  it('comment_append_and_unassigned_complete_preserve_backend_state', async () => {
    requestMock
      .mockImplementationOnce((options) => {
        options.success({ data: { code: 200, message: 'success', data: { task_id: 201, commented: true } } })
      })
      .mockImplementationOnce((options) => {
        options.success({
          data: {
            code: 403,
            message: 'workflow task is not assigned to current user',
            data: { task_id: 202 },
          },
        })
      })

    await expect(addTeacherWorkflowTaskComment(201, { content: 'Called guardian' })).resolves.toEqual({
      task_id: 201,
      commented: true,
    })

    let thrown: MobileApiError | undefined
    try {
      await completeTeacherWorkflowTask(202, { result: 'done', content: 'Completed' })
    }
    catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.code).toBe(403)
    expect(thrown?.data).toEqual({ task_id: 202 })
    expect(requestMock).toHaveBeenNthCalledWith(1, expect.objectContaining({
      data: { content: 'Called guardian' },
      method: 'POST',
      url: '/mobile/education/workflow/tasks/201/comments',
    }))
    expect(requestMock).toHaveBeenNthCalledWith(2, expect.objectContaining({
      data: { result: 'done', content: 'Completed' },
      method: 'POST',
      url: '/mobile/education/workflow/tasks/202/complete',
    }))
  })

  it('operator_uses_same_mobile_workflow_contract_with_operator_pages', async () => {
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data: { list: [], total: 0 } } })
    })

    await expect(getOperatorWorkflowTasks({ status: 'overdue' })).resolves.toEqual({ list: [], total: 0 })
    expect(completeOperatorWorkflowTask).toEqual(expect.any(Function))
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { status: 'overdue' },
      method: 'GET',
      url: '/mobile/education/workflow/tasks/my',
    }))
  })

  it('workflow_pages_are_registered_for_teacher_and_operator_only', () => {
    const pagesJson = JSON.parse(readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8'))
    const pages = pagesJson.pages as Array<{ path: string, meta?: { role?: string, requiresProfile?: string } }>
    const paths = pages.map(page => page.path)

    expect(paths).toEqual(expect.arrayContaining([
      'pages/teacher/workflow/tasks',
      'pages/teacher/workflow/task-detail',
      'pages/operator/workflow/tasks',
      'pages/operator/workflow/task-detail',
    ]))
    expect(paths.some(path => path.includes('guardian/workflow'))).toBe(false)
    expect(pages.filter(page => page.path.startsWith('pages/teacher/workflow/')).every(page => page.meta?.role === 'teacher')).toBe(true)
    expect(pages.filter(page => page.path.startsWith('pages/operator/workflow/')).every(page => page.meta?.role === 'operator')).toBe(true)
  })

  it('task_pages_include_assignee_isolation_empty_error_submitted_states', () => {
    const teacherTasks = readFileSync(join(process.cwd(), 'src/pages/teacher/workflow/tasks.vue'), 'utf8')
    const teacherDetail = readFileSync(join(process.cwd(), 'src/pages/teacher/workflow/task-detail.vue'), 'utf8')
    const operatorTasks = readFileSync(join(process.cwd(), 'src/pages/operator/workflow/tasks.vue'), 'utf8')
    const operatorDetail = readFileSync(join(process.cwd(), 'src/pages/operator/workflow/task-detail.vue'), 'utf8')

    expect(teacherTasks).toContain('getTeacherWorkflowTasks')
    expect(teacherTasks).toContain("state.status = result.list.length > 0 ? 'success' : 'empty'")
    expect(teacherTasks).toContain('Overdue')
    expect(teacherDetail).toContain('getTeacherWorkflowTaskDetail')
    expect(teacherDetail).toContain('addTeacherWorkflowTaskComment')
    expect(teacherDetail).toContain('completeTeacherWorkflowTask')
    expect(teacherDetail).toContain('submitted')
    expect(operatorTasks).toContain('getOperatorWorkflowTasks')
    expect(operatorDetail).toContain('getOperatorWorkflowTaskDetail')
    expect(operatorDetail).toContain('completeOperatorWorkflowTask')
  })
})
