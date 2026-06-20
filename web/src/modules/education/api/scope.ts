import { getEducationScopeSnapshot } from '@/composables/education/useEducationScope.ts'

export interface EducationScopeInput {
  tenant_id?: number
  campus_id?: number
}

export interface EducationScopeRequestOptions {
  headers?: Record<string, string>
}

export type EducationScopeGetOptions<T> = { params: T } & EducationScopeRequestOptions

function isPositiveNumber(value?: number): value is number {
  return typeof value === 'number' && Number.isFinite(value) && value > 0
}

function resolveEducationScope(input: EducationScopeInput = {}): EducationScopeInput {
  const context = getEducationScopeSnapshot()

  return {
    tenant_id: isPositiveNumber(input.tenant_id) ? input.tenant_id : context.tenant_id,
    campus_id: isPositiveNumber(input.campus_id) ? input.campus_id : context.campus_id,
  }
}

export function educationScopeParams<T extends EducationScopeInput>(params: T): T & EducationScopeInput {
  const scope = resolveEducationScope(params)
  const scopedParams = { ...params } as T & EducationScopeInput

  if (isPositiveNumber(scope.tenant_id)) {
    scopedParams.tenant_id = scope.tenant_id
  }

  if (isPositiveNumber(scope.campus_id)) {
    scopedParams.campus_id = scope.campus_id
  }

  return scopedParams
}

export function educationScopeHeaders(input: EducationScopeInput = {}): Record<string, string> {
  const scope = resolveEducationScope(input)
  const headers: Record<string, string> = {}

  if (isPositiveNumber(scope.tenant_id)) {
    headers['X-Tenant-Id'] = String(scope.tenant_id)
  }

  if (isPositiveNumber(scope.campus_id)) {
    headers['X-Campus-Id'] = String(scope.campus_id)
  }

  return headers
}

export function educationScopeRequestOptions(input: EducationScopeInput = {}): EducationScopeRequestOptions {
  const headers = educationScopeHeaders(input)

  return Object.keys(headers).length > 0 ? { headers } : {}
}

export function educationScopeGetOptions<T extends EducationScopeInput>(params: T): EducationScopeGetOptions<T> {
  return { params: educationScopeParams(params), ...educationScopeRequestOptions(params) }
}
