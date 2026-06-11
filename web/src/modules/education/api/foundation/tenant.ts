import type { PageList, ResponseStruct } from '#/global'

export type EducationStatus = 'enabled' | 'disabled'

export interface TenantRecord {
  id: number
  name: string
  code: string
  short_name?: string
  contact_name?: string
  contact_phone?: string
  status: EducationStatus
  created_at?: string
  updated_at?: string
}

export interface TenantPageParams {
  page?: number
  page_size?: number
  keyword?: string
  status?: EducationStatus
}

export interface TenantSavePayload {
  name: string
  code: string
  short_name?: string
  contact_name?: string
  contact_phone?: string
  status?: EducationStatus
  settings?: Record<string, unknown>
}

export function pageTenants(params: TenantPageParams): Promise<ResponseStruct<PageList<TenantRecord>>> {
  return useHttp().get('/admin/education/foundation/tenants/page', { params })
}

export function createTenant(data: TenantSavePayload): Promise<ResponseStruct<{ id: number }>> {
  return useHttp().post('/admin/education/foundation/tenants', data)
}

export function updateTenant(id: number, data: TenantSavePayload): Promise<ResponseStruct<{ id: number }>> {
  return useHttp().put(`/admin/education/foundation/tenants/${id}`, data)
}

export function updateTenantStatus(id: number, status: EducationStatus): Promise<ResponseStruct<{ id: number, status: EducationStatus }>> {
  return useHttp().put(`/admin/education/foundation/tenants/${id}/status`, { status })
}

export function deleteTenant(id: number): Promise<ResponseStruct<null>> {
  return useHttp().delete(`/admin/education/foundation/tenants/${id}`)
}
