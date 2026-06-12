import type { PageList, ResponseStruct } from '#/global'
import type { ConfigOwnerType, FoundationStatus } from './dictionary.ts'

export interface FeatureFlagRecord {
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

export interface FeatureFlagPageParams {
  page?: number
  page_size?: number
  owner_type?: ConfigOwnerType
  tenant_id?: number
  feature_code?: string
  keyword?: string
  enabled?: boolean
  status?: FoundationStatus
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
  feature_code: string
  enabled: boolean
  owner_key?: string
  config?: Record<string, unknown>
  effective_from?: string
  effective_to?: string
}

function tenantConfig(tenantId?: number): { headers: Record<string, string> } | undefined {
  return tenantId && tenantId > 0
    ? { headers: { 'X-Tenant-Id': String(tenantId) } }
    : undefined
}

export function pageFeatureFlags(params: FeatureFlagPageParams): Promise<ResponseStruct<PageList<FeatureFlagRecord>>> {
  return useHttp().get('/admin/education/foundation/feature-flags/page', {
    params,
    ...tenantConfig(params.tenant_id),
  })
}

export function createFeatureFlag(data: FeatureFlagSavePayload): Promise<ResponseStruct<{ id: number, owner_key: string }>> {
  return useHttp().post('/admin/education/foundation/feature-flags', data, tenantConfig(data.tenant_id))
}

export function updateFeatureFlag(id: number, data: FeatureFlagSavePayload): Promise<ResponseStruct<{ id: number }>> {
  return useHttp().put(`/admin/education/foundation/feature-flags/${id}`, data, tenantConfig(data.tenant_id))
}

export function updateFeatureFlagStatus(id: number, status: FoundationStatus): Promise<ResponseStruct<{ id: number, status: FoundationStatus }>> {
  return useHttp().put(`/admin/education/foundation/feature-flags/${id}/status`, { status })
}

export function deleteFeatureFlag(id: number): Promise<ResponseStruct<null>> {
  return useHttp().delete(`/admin/education/foundation/feature-flags/${id}`)
}

export function resolveFeatureFlag(featureCode: string, tenantId?: number): Promise<ResponseStruct<FeatureFlagResolved>> {
  return useHttp().get(`/admin/education/foundation/feature-flags/${featureCode}/resolved`, tenantConfig(tenantId))
}
