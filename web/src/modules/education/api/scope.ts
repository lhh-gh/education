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

export function educationScopeHeaders(input: EducationScopeInput = {}): Record<string, string> {
  const headers: Record<string, string> = {}

  if (isPositiveNumber(input.tenant_id)) {
    headers['X-Tenant-Id'] = String(input.tenant_id)
  }

  if (isPositiveNumber(input.campus_id)) {
    headers['X-Campus-Id'] = String(input.campus_id)
  }

  return headers
}

export function educationScopeRequestOptions(input: EducationScopeInput = {}): EducationScopeRequestOptions {
  const headers = educationScopeHeaders(input)

  return Object.keys(headers).length > 0 ? { headers } : {}
}

export function educationScopeGetOptions<T extends EducationScopeInput>(params: T): EducationScopeGetOptions<T> {
  return { params, ...educationScopeRequestOptions(params) }
}
