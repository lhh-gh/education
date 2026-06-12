import type { PageList, ResponseStruct } from '#/global'

export type ConfigOwnerType = 'system' | 'tenant'
export type FoundationStatus = 'enabled' | 'disabled'

export interface DictTypeRecord {
  id: number
  owner_type: ConfigOwnerType
  tenant_id?: number
  owner_key: string
  code: string
  name: string
  description?: string
  status: FoundationStatus
  is_locked: boolean
  sort_order: number
  item_count?: number
  created_at?: string
  updated_at?: string
}

export interface DictItemRecord {
  id: number
  dict_type_id: number
  owner_key: string
  dict_code: string
  label: string
  value: string
  color?: string
  extra?: Record<string, unknown>
  sort_order: number
  status: FoundationStatus
  is_default: boolean
  created_at?: string
  updated_at?: string
}

export interface DictTypePageParams {
  page?: number
  page_size?: number
  owner_type?: ConfigOwnerType
  tenant_id?: number
  keyword?: string
  status?: FoundationStatus
}

export interface DictItemPageParams {
  page?: number
  page_size?: number
  dict_type_id?: number
  dict_code?: string
  keyword?: string
  status?: FoundationStatus
}

export interface DictTypeSavePayload {
  owner_type: ConfigOwnerType
  tenant_id?: number
  code: string
  name: string
  description?: string
  status?: FoundationStatus
  is_locked?: boolean
  sort_order?: number
}

export interface DictItemSavePayload {
  dict_type_id: number
  label: string
  value: string
  color?: string
  extra?: Record<string, unknown>
  sort_order?: number
  status?: FoundationStatus
  is_default?: boolean
}

export interface DictLookupResult {
  dict_code: string
  items: DictItemRecord[]
}

function tenantConfig(tenantId?: number): { headers: Record<string, string> } | undefined {
  return tenantId && tenantId > 0
    ? { headers: { 'X-Tenant-Id': String(tenantId) } }
    : undefined
}

export function pageDictTypes(params: DictTypePageParams): Promise<ResponseStruct<PageList<DictTypeRecord>>> {
  return useHttp().get('/admin/education/foundation/dict-types/page', {
    params,
    ...tenantConfig(params.tenant_id),
  })
}

export function createDictType(data: DictTypeSavePayload): Promise<ResponseStruct<{ id: number, owner_key: string }>> {
  return useHttp().post('/admin/education/foundation/dict-types', data, tenantConfig(data.tenant_id))
}

export function updateDictType(id: number, data: DictTypeSavePayload): Promise<ResponseStruct<{ id: number }>> {
  return useHttp().put(`/admin/education/foundation/dict-types/${id}`, data, tenantConfig(data.tenant_id))
}

export function updateDictTypeStatus(id: number, status: FoundationStatus): Promise<ResponseStruct<{ id: number, status: FoundationStatus }>> {
  return useHttp().put(`/admin/education/foundation/dict-types/${id}/status`, { status })
}

export function deleteDictType(id: number): Promise<ResponseStruct<null>> {
  return useHttp().delete(`/admin/education/foundation/dict-types/${id}`)
}

export function pageDictItems(params: DictItemPageParams): Promise<ResponseStruct<PageList<DictItemRecord>>> {
  return useHttp().get('/admin/education/foundation/dict-items/page', { params })
}

export function createDictItem(data: DictItemSavePayload): Promise<ResponseStruct<{ id: number, dict_type_id: number }>> {
  return useHttp().post('/admin/education/foundation/dict-items', data)
}

export function updateDictItem(id: number, data: DictItemSavePayload): Promise<ResponseStruct<{ id: number }>> {
  return useHttp().put(`/admin/education/foundation/dict-items/${id}`, data)
}

export function updateDictItemStatus(id: number, status: FoundationStatus): Promise<ResponseStruct<{ id: number, status: FoundationStatus }>> {
  return useHttp().put(`/admin/education/foundation/dict-items/${id}/status`, { status })
}

export function deleteDictItem(id: number): Promise<ResponseStruct<null>> {
  return useHttp().delete(`/admin/education/foundation/dict-items/${id}`)
}

export function lookupDictItems(code: string, tenantId?: number): Promise<ResponseStruct<DictLookupResult>> {
  return useHttp().get(`/admin/education/foundation/dictionaries/${code}/items`, tenantConfig(tenantId))
}
