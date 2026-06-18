import type { MineResult } from '../foundation/types.ts'
import type { StandardsPage, StandardsScopedParams } from './types.ts'
import { standardsGetOptions, standardsRequestOptions } from './types.ts'

export interface ServicePackagePayload extends StandardsScopedParams {
  service_package_id?: number
  package_code: string
  package_name: string
  course_id: number
  guardian_visible?: boolean
  description?: string
}

export interface ServicePackageRow extends ServicePackagePayload {
  id: number
  version_no: number
  status: string
}

export function pageServicePackages(params: StandardsScopedParams): Promise<MineResult<StandardsPage<ServicePackageRow>>> {
  return useHttp().get('/admin/education/standards/service-packages', standardsGetOptions(params))
}

export function saveServicePackage(payload: ServicePackagePayload): Promise<MineResult<{ service_package_id: number, version_no: number, status: string }>> {
  return useHttp().post('/admin/education/standards/service-packages', payload, standardsRequestOptions(payload))
}

export function createPackageVersion(payload: ServicePackagePayload): Promise<MineResult<{ service_package_id: number, version_no: number, status: string }>> {
  return saveServicePackage(payload)
}
