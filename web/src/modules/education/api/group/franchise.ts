import type { MineResult } from '../foundation/types.ts'
import type { GroupPage, GroupScopedParams } from './types.ts'
import { groupGetOptions, groupRequestOptions } from './types.ts'

export interface FranchiseRecord {
  id: number
  franchise_code: string
  franchise_name: string
  contact_name?: string
  contact_mobile?: string
  region?: string
  status: string
}

export interface FranchisePayload extends GroupScopedParams {
  franchise_code: string
  franchise_name: string
  contact_name?: string
  contact_mobile?: string
  region?: string
  status?: string
  remark?: string
}

export function pageFranchiseRecords(params: GroupScopedParams): Promise<MineResult<GroupPage<FranchiseRecord>>> {
  return useHttp().get('/admin/education/group/franchises/page', groupGetOptions(params))
}

export function saveFranchiseRecord(payload: FranchisePayload): Promise<MineResult<{ id: number, status: string }>> {
  return useHttp().post('/admin/education/group/franchises', payload, groupRequestOptions(payload))
}
