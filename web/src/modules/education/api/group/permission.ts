import type { MineResult } from '../foundation/types.ts'
import type { GroupPage, GroupScopedParams } from './types.ts'
import { groupGetOptions, groupRequestOptions } from './types.ts'

export interface DataPermissionRecord {
  id: number
  user_id: number
  scope_type: string
  status: string
}

export interface DataPermissionPayload extends GroupScopedParams {
  user_id: number
  scope_type: 'group_all' | 'org_tree' | 'campus_set' | 'self'
  campus_ids?: number[]
  org_unit_ids?: number[]
  status?: string
}

export interface DataScopePreview {
  allowed_campus_ids: number[]
}

export function pageDataPermissions(params: GroupScopedParams): Promise<MineResult<GroupPage<DataPermissionRecord>>> {
  return useHttp().get('/admin/education/group/data-permissions/page', groupGetOptions(params))
}

export function saveUserDataPermission(payload: DataPermissionPayload): Promise<MineResult<DataScopePreview>> {
  return useHttp().post('/admin/education/group/data-permissions', payload, groupRequestOptions(payload))
}

export function getUserDataScopePreview(params: GroupScopedParams = {}): Promise<MineResult<DataScopePreview>> {
  return useHttp().get('/admin/education/group/data-permissions/preview', groupGetOptions(params))
}
