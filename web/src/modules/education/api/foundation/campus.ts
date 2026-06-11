import type { PageList, ResponseStruct } from '#/global'
import type { EducationStatus } from './tenant.ts'

export interface CampusRecord {
  id: number
  tenant_id: number
  name: string
  code: string
  contact_name?: string
  contact_phone?: string
  address?: string
  status: EducationStatus
  created_at?: string
  updated_at?: string
}

export interface CampusPageParams {
  page?: number
  page_size?: number
  keyword?: string
  status?: EducationStatus
}

export interface CampusSavePayload {
  name: string
  code: string
  contact_name?: string
  contact_phone?: string
  address?: string
  status?: EducationStatus
  settings?: Record<string, unknown>
}

function tenantHeaders(tenantId: number): { headers: Record<string, string> } {
  return {
    headers: {
      'X-Tenant-Id': String(tenantId),
    },
  }
}

export function pageCampuses(tenantId: number, params: CampusPageParams): Promise<ResponseStruct<PageList<CampusRecord>>> {
  return useHttp().get('/admin/education/foundation/campuses/page', {
    params,
    ...tenantHeaders(tenantId),
  })
}

export function createCampus(tenantId: number, data: CampusSavePayload): Promise<ResponseStruct<{ id: number, tenant_id: number }>> {
  return useHttp().post('/admin/education/foundation/campuses', data, tenantHeaders(tenantId))
}

export function updateCampus(tenantId: number, id: number, data: CampusSavePayload): Promise<ResponseStruct<{ id: number, tenant_id: number }>> {
  return useHttp().put(`/admin/education/foundation/campuses/${id}`, data, tenantHeaders(tenantId))
}

export function updateCampusStatus(tenantId: number, id: number, status: EducationStatus): Promise<ResponseStruct<{ id: number, tenant_id: number, status: EducationStatus }>> {
  return useHttp().put(`/admin/education/foundation/campuses/${id}/status`, { status }, tenantHeaders(tenantId))
}

export function deleteCampus(tenantId: number, id: number): Promise<ResponseStruct<null>> {
  return useHttp().delete(`/admin/education/foundation/campuses/${id}`, tenantHeaders(tenantId))
}
