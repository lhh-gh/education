import { afterEach, describe, expect, it, vi } from 'vitest'
import {
  createCampus,
  deleteCampus,
  pageCampuses,
  updateCampus,
  updateCampusStatus,
} from '../../../api/foundation/campus.ts'
import {
  createDictItem,
  createDictType,
  deleteDictItem,
  deleteDictType,
  lookupDictItems,
  pageDictItems,
  pageDictTypes,
  updateDictItem,
  updateDictItemStatus,
  updateDictType,
  updateDictTypeStatus,
} from '../../../api/foundation/dictionary.ts'
import {
  createFeatureFlag,
  deleteFeatureFlag,
  pageFeatureFlags,
  resolveFeatureFlag,
  updateFeatureFlag,
  updateFeatureFlagStatus,
} from '../../../api/foundation/featureFlag.ts'
import {
  createTenant,
  deleteTenant,
  pageTenants,
  updateTenant,
  updateTenantStatus,
} from '../../../api/foundation/tenant.ts'
import {
  createUserProfile,
  getCampusScopes,
  pageUserProfiles,
  saveCampusScopes,
  updateUserProfile,
  updateUserProfileStatus,
} from '../../../api/foundation/userProfile.ts'
import { getAuditLogDetail, pageAuditLogs } from '../../../api/foundation/auditLog.ts'

interface HttpCall {
  method: 'GET' | 'POST' | 'PUT' | 'DELETE'
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
    put: (url: string, data?: unknown, config?: unknown) => {
      calls.push({ method: 'PUT', url, data, config })
      return response
    },
    delete: (url: string, config?: unknown) => {
      calls.push({ method: 'DELETE', url, config })
      return response
    },
  }))

  return calls
}

afterEach(() => {
  vi.unstubAllGlobals()
})

describe('education foundation api clients', () => {
  it('tenant_client_uses_expected_endpoints', () => {
    const calls = stubHttp()

    pageTenants({ page: 1, page_size: 20 })
    createTenant({ name: 'Demo', code: 'demo' })
    updateTenant(1001, { name: 'Demo', code: 'demo' })
    updateTenantStatus(1001, 'enabled')
    deleteTenant(1001)

    expect(calls.map(call => [call.method, call.url])).toEqual([
      ['GET', '/admin/education/foundation/tenants/page'],
      ['POST', '/admin/education/foundation/tenants'],
      ['PUT', '/admin/education/foundation/tenants/1001'],
      ['PUT', '/admin/education/foundation/tenants/1001/status'],
      ['DELETE', '/admin/education/foundation/tenants/1001'],
    ])
  })

  it('campus_client_uses_expected_endpoints', () => {
    const calls = stubHttp()

    pageCampuses({ page: 1, page_size: 20, tenant_id: 1001 })
    createCampus({ tenant_id: 1001, name: 'East', code: 'east' })
    updateCampus(2001, { tenant_id: 1001, name: 'East', code: 'east' })
    updateCampusStatus(2001, 'disabled')
    deleteCampus(2001)

    expect(calls.map(call => [call.method, call.url])).toEqual([
      ['GET', '/admin/education/foundation/campuses/page'],
      ['POST', '/admin/education/foundation/campuses'],
      ['PUT', '/admin/education/foundation/campuses/2001'],
      ['PUT', '/admin/education/foundation/campuses/2001/status'],
      ['DELETE', '/admin/education/foundation/campuses/2001'],
    ])
  })

  it('user_profile_client_uses_expected_endpoints', () => {
    const calls = stubHttp()

    pageUserProfiles({ page: 1, page_size: 20 })
    createUserProfile({ user_id: 501, role_code: 'tenant_admin', display_name: 'Admin' })
    updateUserProfile(3001, { user_id: 501, role_code: 'tenant_admin', display_name: 'Admin' })
    updateUserProfileStatus(3001, 'disabled')
    getCampusScopes(3001)
    saveCampusScopes(3001, [2001])

    expect(calls.map(call => [call.method, call.url])).toEqual([
      ['GET', '/admin/education/foundation/user-profiles/page'],
      ['POST', '/admin/education/foundation/user-profiles'],
      ['PUT', '/admin/education/foundation/user-profiles/3001'],
      ['PUT', '/admin/education/foundation/user-profiles/3001/status'],
      ['GET', '/admin/education/foundation/user-profiles/3001/campus-scopes'],
      ['PUT', '/admin/education/foundation/user-profiles/3001/campus-scopes'],
    ])
  })

  it('dictionary_client_uses_expected_endpoints', () => {
    const calls = stubHttp()

    pageDictTypes({ page: 1, page_size: 20 })
    createDictType({ owner_type: 'system', code: 'status', name: 'Status' })
    updateDictType(4001, { owner_type: 'system', code: 'status', name: 'Status' })
    updateDictTypeStatus(4001, 'disabled')
    deleteDictType(4001)
    pageDictItems({ page: 1, page_size: 20, dict_type_id: 4001 })
    createDictItem({ dict_type_id: 4001, label: 'Enabled', value: 'enabled' })
    updateDictItem(4101, { dict_type_id: 4001, label: 'Enabled', value: 'enabled' })
    updateDictItemStatus(4101, 'disabled')
    deleteDictItem(4101)
    lookupDictItems('status')

    expect(calls.map(call => [call.method, call.url])).toEqual([
      ['GET', '/admin/education/foundation/dict-types/page'],
      ['POST', '/admin/education/foundation/dict-types'],
      ['PUT', '/admin/education/foundation/dict-types/4001'],
      ['PUT', '/admin/education/foundation/dict-types/4001/status'],
      ['DELETE', '/admin/education/foundation/dict-types/4001'],
      ['GET', '/admin/education/foundation/dict-items/page'],
      ['POST', '/admin/education/foundation/dict-items'],
      ['PUT', '/admin/education/foundation/dict-items/4101'],
      ['PUT', '/admin/education/foundation/dict-items/4101/status'],
      ['DELETE', '/admin/education/foundation/dict-items/4101'],
      ['GET', '/admin/education/foundation/dictionaries/status/items'],
    ])
  })

  it('feature_flag_client_uses_expected_endpoints', () => {
    const calls = stubHttp()

    pageFeatureFlags({ page: 1, page_size: 20 })
    createFeatureFlag({ owner_type: 'system', feature_code: 'demo', feature_name: 'Demo', enabled: true })
    updateFeatureFlag(5001, { owner_type: 'system', feature_code: 'demo', feature_name: 'Demo', enabled: true })
    updateFeatureFlagStatus(5001, 'disabled')
    deleteFeatureFlag(5001)
    resolveFeatureFlag('demo')

    expect(calls.map(call => [call.method, call.url])).toEqual([
      ['GET', '/admin/education/foundation/feature-flags/page'],
      ['POST', '/admin/education/foundation/feature-flags'],
      ['PUT', '/admin/education/foundation/feature-flags/5001'],
      ['PUT', '/admin/education/foundation/feature-flags/5001/status'],
      ['DELETE', '/admin/education/foundation/feature-flags/5001'],
      ['GET', '/admin/education/foundation/feature-flags/demo/resolved'],
    ])
  })

  it('audit_log_client_uses_expected_endpoints', () => {
    const calls = stubHttp()

    pageAuditLogs({ page: 1, pageSize: 20, module: 'foundation' })
    getAuditLogDetail(9001)

    expect(calls.map(call => [call.method, call.url])).toEqual([
      ['GET', '/admin/education/foundation/audit-logs/page'],
      ['GET', '/admin/education/foundation/audit-logs/9001'],
    ])
  })
})
