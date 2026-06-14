import type { FoundationStatus, MinePage, MineResult, PageParams } from './types.ts'

export type EducationStatus = FoundationStatus

export interface TenantListItem {
  id: number
  name: string
  code: string
  short_name?: string
  contact_name?: string
  contact_phone?: string
  contact_mobile?: string
  status: FoundationStatus
  created_at?: string
  updated_at?: string
}

export type TenantRecord = TenantListItem
export type TenantDetail = TenantListItem

export interface TenantPageParams extends Partial<PageParams> {
  page?: number
  page_size?: number
  keyword?: string
  status?: FoundationStatus
}

export interface TenantSavePayload {
  name: string
  code: string
  short_name?: string
  contact_name?: string
  contact_phone?: string
  contact_mobile?: string
  status?: FoundationStatus
  settings?: Record<string, unknown>
}

export function pageTenants(params: TenantPageParams): Promise<MineResult<MinePage<TenantListItem>>> {
  return useHttp().get('/admin/education/foundation/tenants/page', { params })
}

export function createTenant(data: TenantSavePayload): Promise<MineResult<TenantDetail>> {
  return useHttp().post('/admin/education/foundation/tenants', data)
}

export function updateTenant(id: number, data: TenantSavePayload): Promise<MineResult<TenantDetail>> {
  return useHttp().put(`/admin/education/foundation/tenants/${id}`, data)
}

export function updateTenantStatus(id: number, status: FoundationStatus): Promise<MineResult<TenantDetail>> {
  return useHttp().put(`/admin/education/foundation/tenants/${id}/status`, { status })
}

export function deleteTenant(id: number): Promise<MineResult<true>> {
  return useHttp().delete(`/admin/education/foundation/tenants/${id}`)
}
