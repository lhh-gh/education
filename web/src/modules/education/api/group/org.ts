import type { MineResult } from '../foundation/types.ts'
import type { GroupScopedParams } from './types.ts'
import { groupGetOptions, groupRequestOptions } from './types.ts'

export interface OrgUnitRecord {
  id: number
  parent_id?: number | null
  code: string
  name: string
  unit_type: string
  status: string
  children?: OrgUnitRecord[]
}

export interface OrgUnitPayload extends GroupScopedParams {
  id?: number
  parent_id?: number
  code: string
  name: string
  unit_type: string
  status?: string
  sort_order?: number
}

export interface CampusOrgRelationPayload extends GroupScopedParams {
  org_unit_id: number
  campus_id: number
  relation_type?: string
}

export function getOrgUnitTree(params: GroupScopedParams = {}): Promise<MineResult<OrgUnitRecord[]>> {
  return useHttp().get('/admin/education/group/org-units/tree', groupGetOptions(params))
}

export function saveOrgUnit(payload: OrgUnitPayload): Promise<MineResult<OrgUnitRecord>> {
  return useHttp().post('/admin/education/group/org-units', payload, groupRequestOptions(payload))
}

export function bindCampusOrgRelation(payload: CampusOrgRelationPayload): Promise<MineResult<Record<string, number>>> {
  return useHttp().post('/admin/education/group/campus-org-relations', payload, groupRequestOptions(payload))
}
