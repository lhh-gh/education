import type { ConfigOwnerType } from './dictionary.ts'
import type { FoundationStatus, MinePage, MineResult, PageParams } from './types.ts'

export interface FeatureFlagListItem {
  id: number
  owner_type: ConfigOwnerType
  tenant_id?: number
  owner_key: string
  feature_code: string
  feature_name: string
  description?: string
  enabled: boolean
  config?: Record<string, unknown>
  effective_from?: string
  effective_to?: string
  status: FoundationStatus
  is_locked: boolean
  created_at?: string
  updated_at?: string
}

export type FeatureFlagRecord = FeatureFlagListItem
export type FeatureFlagDetail = FeatureFlagListItem

export interface FeatureFlagPageParams extends Partial<PageParams> {
  page?: number
  page_size?: number
  owner_type?: ConfigOwnerType
  tenant_id?: number
  feature_code?: string
  keyword?: string
  enabled?: boolean
  status?: FoundationStatus
  effective_from?: string
  effective_to?: string
}

export interface FeatureFlagSavePayload {
  owner_type: ConfigOwnerType
  tenant_id?: number
  feature_code: string
  feature_name: string
  description?: string
  enabled: boolean
  config?: Record<string, unknown>
  effective_from?: string
  effective_to?: string
  status?: FoundationStatus
  is_locked?: boolean
}

export interface FeatureFlagResolved {
  feature_code?: string
  enabled: boolean
  owner_key?: string
  config: Record<string, unknown>
  effective_from?: string
  effective_to?: string
}

function tenantConfig(tenantId?: number): { headers: Record<string, string> } | undefined {
  return tenantId && tenantId > 0
    ? { headers: { 'X-Tenant-Id': String(tenantId) } }
    : undefined
}

export function pageFeatureFlags(params: FeatureFlagPageParams): Promise<MineResult<MinePage<FeatureFlagListItem>>> {
  return useHttp().get('/admin/education/foundation/feature-flags/page', {
    params,
    ...tenantConfig(params.tenant_id),
  })
}

export function createFeatureFlag(data: FeatureFlagSavePayload): Promise<MineResult<FeatureFlagDetail>> {
  return useHttp().post('/admin/education/foundation/feature-flags', data, tenantConfig(data.tenant_id))
}

export function updateFeatureFlag(id: number, data: FeatureFlagSavePayload): Promise<MineResult<FeatureFlagDetail>> {
  return useHttp().put(`/admin/education/foundation/feature-flags/${id}`, data, tenantConfig(data.tenant_id))
}

export function updateFeatureFlagStatus(id: number, status: FoundationStatus): Promise<MineResult<FeatureFlagDetail>> {
  return useHttp().put(`/admin/education/foundation/feature-flags/${id}/status`, { status })
}

export function deleteFeatureFlag(id: number): Promise<MineResult<true>> {
  return useHttp().delete(`/admin/education/foundation/feature-flags/${id}`)
}

export function resolveFeatureFlag(featureCode: string, tenantId?: number): Promise<MineResult<FeatureFlagResolved>> {
  return useHttp().get(`/admin/education/foundation/feature-flags/${featureCode}/resolved`, tenantConfig(tenantId))
}
