import type { FoundationStatus, MinePage, MineResult, PageParams } from './types.ts'

export type { FoundationStatus } from './types.ts'
export type ConfigOwnerType = 'system' | 'tenant'

export interface DictTypeListItem {
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

export interface DictItemListItem {
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

export type DictTypeRecord = DictTypeListItem
export type DictTypeDetail = DictTypeListItem
export type DictItemRecord = DictItemListItem
export type DictItemDetail = DictItemListItem

export interface DictOption {
  label: string
  value: string
  color?: string
  extra?: Record<string, unknown>
}

export interface DictTypePageParams extends Partial<PageParams> {
  page?: number
  page_size?: number
  owner_type?: ConfigOwnerType
  tenant_id?: number
  keyword?: string
  status?: FoundationStatus
}

export interface DictItemPageParams extends Partial<PageParams> {
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

export function pageDictTypes(params: DictTypePageParams): Promise<MineResult<MinePage<DictTypeListItem>>> {
  return useHttp().get('/admin/education/foundation/dict-types/page', {
    params,
    ...tenantConfig(params.tenant_id),
  })
}

export function createDictType(data: DictTypeSavePayload): Promise<MineResult<DictTypeDetail>> {
  return useHttp().post('/admin/education/foundation/dict-types', data, tenantConfig(data.tenant_id))
}

export function updateDictType(id: number, data: DictTypeSavePayload): Promise<MineResult<DictTypeDetail>> {
  return useHttp().put(`/admin/education/foundation/dict-types/${id}`, data, tenantConfig(data.tenant_id))
}

export function updateDictTypeStatus(id: number, status: FoundationStatus): Promise<MineResult<DictTypeDetail>> {
  return useHttp().put(`/admin/education/foundation/dict-types/${id}/status`, { status })
}

export function deleteDictType(id: number): Promise<MineResult<true>> {
  return useHttp().delete(`/admin/education/foundation/dict-types/${id}`)
}

export function pageDictItems(params: DictItemPageParams): Promise<MineResult<MinePage<DictItemListItem>>> {
  return useHttp().get('/admin/education/foundation/dict-items/page', { params })
}

export function createDictItem(data: DictItemSavePayload): Promise<MineResult<DictItemDetail>> {
  return useHttp().post('/admin/education/foundation/dict-items', data)
}

export function updateDictItem(id: number, data: DictItemSavePayload): Promise<MineResult<DictItemDetail>> {
  return useHttp().put(`/admin/education/foundation/dict-items/${id}`, data)
}

export function updateDictItemStatus(id: number, status: FoundationStatus): Promise<MineResult<DictItemDetail>> {
  return useHttp().put(`/admin/education/foundation/dict-items/${id}/status`, { status })
}

export function deleteDictItem(id: number): Promise<MineResult<true>> {
  return useHttp().delete(`/admin/education/foundation/dict-items/${id}`)
}

export function lookupDictItems(code: string, tenantId?: number): Promise<MineResult<DictOption[]>> {
  return useHttp().get(`/admin/education/foundation/dictionaries/${code}/items`, tenantConfig(tenantId))
}
