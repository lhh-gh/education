import { MobileApiError } from '../foundation/context'

export { MobileApiError }

export interface PageResult<T> {
  list: T[]
  total: number
  page?: number
  pageSize?: number
}

export interface OperationScopedParams {
  tenant_id?: number
  campus_id?: number
  student_id?: number
  page?: number
  pageSize?: number
}

interface MineAdminResult<T> {
  code: number
  message: string
  data: T
}

export function requestOperation<T>(url: string, method: UniApp.RequestOptions['method'], data?: object): Promise<T> {
  return new Promise((resolve, reject) => {
    uni.request({
      url,
      method,
      data: compact(data),
      header: operationHeaders(data as OperationScopedParams | undefined),
      success: (response) => {
        const result = response.data as MineAdminResult<T>
        if (result?.code === 200) {
          resolve(result.data)
          return
        }

        const error = new MobileApiError(result?.message || 'Request failed')
        error.code = result?.code
        error.data = result?.data
        reject(error)
      },
      fail: (error) => {
        reject(new Error(error.errMsg || 'Network request failed'))
      },
    })
  })
}

export function operationHeaders(params?: OperationScopedParams): Record<string, string> {
  const headers: Record<string, string> = {}
  const token = storageString('access_token', 'token')
  const tenantId = params?.tenant_id || storageNumber('education_tenant_id', 'tenant_id')
  const campusId = params?.campus_id || storageNumber('education_campus_id', 'campus_id')

  if (token) {
    headers.Authorization = `Bearer ${token}`
  }
  if (tenantId) {
    headers['X-Tenant-Id'] = String(tenantId)
  }
  if (campusId) {
    headers['X-Campus-Id'] = String(campusId)
  }

  return headers
}

export function selectedStudentId(params?: { student_id?: number }): number | undefined {
  return params?.student_id || storageNumber('guardian_selected_student_id', 'selected_student_id')
}

function compact(params?: object): Record<string, unknown> {
  return Object.fromEntries(
    Object.entries(params || {}).filter(([, value]) => value !== undefined && value !== null && value !== ''),
  )
}

function storageString(...keys: string[]): string | null {
  for (const key of keys) {
    try {
      const value = uni.getStorageSync(key)
      if (value) {
        return String(value)
      }
    }
    catch {}
  }

  return null
}

function storageNumber(...keys: string[]): number | undefined {
  const value = storageString(...keys)
  const numberValue = Number(value || 0)

  return numberValue > 0 ? numberValue : undefined
}
