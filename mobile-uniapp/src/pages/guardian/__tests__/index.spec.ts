import { MobileApiError } from '../../../api/foundation/context'
import type { GuardianFoundationContext } from '../../../api/foundation/types'
import { guardianPageOptions } from '../../foundation/pageOptions'
import { createFoundationContextPage } from '../../foundation/useFoundationContextPage'
import { readFileSync } from 'fs'
import { join } from 'path'

describe('guardian entry page state', () => {
  beforeEach(() => {
    ;(global as any).uni = { stopPullDownRefresh: jest.fn() }
  })

  afterEach(() => {
    delete (global as any).uni
  })

  it('renders_empty_student_state', async () => {
    const page = createFoundationContextPage(() => Promise.resolve(guardianContext()), guardianPageOptions)

    await page.load()

    expect(page.state.status).toBe('empty')
    expect(page.state.message).toBe('Student binding will be available in V1')
    expect(page.state.context?.bound_students).toEqual([])
  })

  it('renders_forbidden_without_profile_data', async () => {
    const error = new MobileApiError('guardian profile is not bound')
    error.code = 403
    error.data = { required_action: 'contact_campus' }
    const page = createFoundationContextPage(() => Promise.reject(error), guardianPageOptions)

    await page.load()

    expect(page.state.status).toBe('forbidden')
    expect(page.state.message).toBe('Contact campus to bind guardian profile')
    expect(page.state.context).toBeNull()
  })

  it('entry_disables_student_features_without_bound_student', () => {
    const source = readFileSync(join(process.cwd(), 'src/pages/guardian/index.vue'), 'utf8')

    expect(source).toContain('getGuardianStudents')
    expect(source).toContain('guardian_selected_student_id')
    expect(source).toContain(':disabled="!guardian.selectedStudentId"')
    expect(source).toContain('/pages/guardian/schedule/index')
    expect(source).toContain('/pages/guardian/account/index')
    expect(source).toContain('/pages/guardian/consumption/index')
    expect(source).toContain('/pages/guardian/notice/index')
    expect(source).toContain('/pages/guardian/leave/create')
  })
})

function guardianContext(): GuardianFoundationContext {
  return {
    tenant: {
      id: 1001,
      name: 'Demo Education',
      short_name: 'Demo',
    },
    profile: {
      id: 3002,
      user_id: 502,
      role_code: 'guardian',
      display_name: 'Guardian Li',
      mobile: '13900000000',
      avatar: null,
      current_campus_id: null,
    },
    campus_scopes: [],
    bound_students: [],
    feature_flags: {
      'education.v1.core_academic': true,
    },
    entry: {
      default_path: '/pages/guardian/index',
      tabs: [],
    },
    empty_state: {
      code: 'guardian_students_pending_v1',
      message: 'Student binding will be available in V1',
    },
  }
}
