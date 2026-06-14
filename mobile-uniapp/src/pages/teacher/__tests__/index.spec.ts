import { createFoundationContextPage } from '../../foundation/useFoundationContextPage'
import { teacherPageOptions } from '../../foundation/pageOptions'
import type { MobileFoundationContext } from '../../../api/foundation/types'

describe('teacher entry page state', () => {
  beforeEach(() => {
    ;(global as any).uni = { stopPullDownRefresh: jest.fn() }
  })

  afterEach(() => {
    delete (global as any).uni
  })

  it('renders_loading_then_teacher_context', async () => {
    const loader = jest.fn().mockResolvedValue(teacherContext())
    const page = createFoundationContextPage(loader, teacherPageOptions)

    expect(page.state.status).toBe('loading')
    await page.load()

    expect(page.state.status).toBe('success')
    expect(page.state.context?.tenant.name).toBe('Demo Education')
    expect(page.state.context?.profile.display_name).toBe('Teacher Wang')
    expect(page.currentCampusName.value).toBe('East Campus')
    expect(page.enabledFeatureCodes.value).toEqual(['education.v1.core_academic'])
  })

  it('renders_no_campus_empty_state', async () => {
    const context = teacherContext({ campus_scopes: [] })
    const page = createFoundationContextPage(() => Promise.resolve(context), teacherPageOptions)

    await page.load()

    expect(page.state.status).toBe('empty')
    expect(page.state.message).toBe('No campus scope assigned')
  })

  it('retry_calls_context_api_again', async () => {
    const loader = jest.fn()
      .mockRejectedValueOnce(new Error('Network down'))
      .mockResolvedValueOnce(teacherContext())
    const page = createFoundationContextPage(loader, teacherPageOptions)

    await page.load()
    expect(page.state.status).toBe('error')

    await page.retry()
    expect(loader).toHaveBeenCalledTimes(2)
    expect(page.state.status).toBe('success')
  })
})

function teacherContext(overrides: Partial<MobileFoundationContext> = {}): MobileFoundationContext {
  return {
    tenant: {
      id: 1001,
      name: 'Demo Education',
      short_name: 'Demo',
    },
    profile: {
      id: 3001,
      user_id: 501,
      role_code: 'teacher',
      display_name: 'Teacher Wang',
      mobile: '13800000000',
      avatar: null,
      current_campus_id: 2001,
    },
    campus_scopes: [
      {
        campus_id: 2001,
        campus_name: 'East Campus',
        is_current: true,
      },
    ],
    feature_flags: {
      'education.v1.core_academic': true,
      'education.v7.family_service': false,
    },
    entry: {
      default_path: '/pages/teacher/index',
      tabs: [],
    },
    empty_state: null,
    ...overrides,
  }
}
