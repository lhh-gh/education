import type { FoundationStatus, MinePage, MineResult, PageParams } from './types.ts'

export interface CampusListItem {
  id: number
  tenant_id: number
  name: string
  code: string
  contact_name?: string
  contact_phone?: string
  contact_mobile?: string
  address?: string
  status: FoundationStatus
  created_at?: string
  updated_at?: string
}

export type CampusRecord = CampusListItem
export type CampusDetail = CampusListItem

export interface CampusPageParams extends Partial<PageParams> {
  page?: number
  page_size?: number
  tenant_id?: number
  keyword?: string
  status?: FoundationStatus
}

export interface CampusSavePayload {
  tenant_id?: number
  name: string
  code: string
  contact_name?: string
  contact_phone?: string
  contact_mobile?: string
  address?: string
  status?: FoundationStatus
  settings?: Record<string, unknown>
}

function tenantHeaders(tenantId?: number): { headers: Record<string, string> } | undefined {
  return tenantId && tenantId > 0
    ? { headers: { 'X-Tenant-Id': String(tenantId) } }
    : undefined
}

export function pageCampuses(params: CampusPageParams): Promise<MineResult<MinePage<CampusListItem>>>
export function pageCampuses(tenantId: number, params: CampusPageParams): Promise<MineResult<MinePage<CampusListItem>>>
export function pageCampuses(
  tenantOrParams: number | CampusPageParams,
  maybeParams?: CampusPageParams,
): Promise<MineResult<MinePage<CampusListItem>>> {
  const params = typeof tenantOrParams === 'number' ? maybeParams ?? {} : tenantOrParams
  const tenantId = typeof tenantOrParams === 'number' ? tenantOrParams : params.tenant_id

  return useHttp().get('/admin/education/foundation/campuses/page', {
    params,
    ...tenantHeaders(tenantId),
  })
}

export function createCampus(data: CampusSavePayload): Promise<MineResult<CampusDetail>>
export function createCampus(tenantId: number, data: CampusSavePayload): Promise<MineResult<CampusDetail>>
export function createCampus(
  tenantOrData: number | CampusSavePayload,
  maybeData?: CampusSavePayload,
): Promise<MineResult<CampusDetail>> {
  const data = typeof tenantOrData === 'number' ? maybeData! : tenantOrData
  const tenantId = typeof tenantOrData === 'number' ? tenantOrData : data.tenant_id

  return useHttp().post('/admin/education/foundation/campuses', data, tenantHeaders(tenantId))
}

export function updateCampus(id: number, data: CampusSavePayload): Promise<MineResult<CampusDetail>>
export function updateCampus(tenantId: number, id: number, data: CampusSavePayload): Promise<MineResult<CampusDetail>>
export function updateCampus(
  tenantOrId: number,
  idOrData: number | CampusSavePayload,
  maybeData?: CampusSavePayload,
): Promise<MineResult<CampusDetail>> {
  const id = typeof idOrData === 'number' ? idOrData : tenantOrId
  const data = typeof idOrData === 'number' ? maybeData! : idOrData
  const tenantId = typeof idOrData === 'number' ? tenantOrId : data.tenant_id

  return useHttp().put(`/admin/education/foundation/campuses/${id}`, data, tenantHeaders(tenantId))
}

export function updateCampusStatus(id: number, status: FoundationStatus): Promise<MineResult<CampusDetail>>
export function updateCampusStatus(tenantId: number, id: number, status: FoundationStatus): Promise<MineResult<CampusDetail>>
export function updateCampusStatus(
  tenantOrId: number,
  idOrStatus: number | FoundationStatus,
  maybeStatus?: FoundationStatus,
): Promise<MineResult<CampusDetail>> {
  const id = typeof idOrStatus === 'number' ? idOrStatus : tenantOrId
  const status = typeof idOrStatus === 'number' ? maybeStatus! : idOrStatus
  const tenantId = typeof idOrStatus === 'number' ? tenantOrId : undefined

  return useHttp().put(`/admin/education/foundation/campuses/${id}/status`, { status }, tenantHeaders(tenantId))
}

export function deleteCampus(id: number): Promise<MineResult<true>>
export function deleteCampus(tenantId: number, id: number): Promise<MineResult<true>>
export function deleteCampus(tenantOrId: number, maybeId?: number): Promise<MineResult<true>> {
  const id = maybeId ?? tenantOrId
  const tenantId = maybeId === undefined ? undefined : tenantOrId

  return useHttp().delete(`/admin/education/foundation/campuses/${id}`, tenantHeaders(tenantId))
}
