import type {
  GuardianFoundationContext,
  MobileClientType,
  MobileFoundationContext,
} from './types'

interface MineAdminResult<T> {
  code: number
  message: string
  data: T
}

interface RequestParams {
  campus_id?: number
  client_type?: MobileClientType
}

export class MobileApiError extends Error {
  code?: number
  data?: unknown
}

export function getTeacherContext(params?: RequestParams): Promise<MobileFoundationContext> {
  return requestContext('/mobile/education/foundation/teacher/context', params)
}

export function getGuardianContext(params?: { client_type?: MobileClientType }): Promise<GuardianFoundationContext> {
  return requestContext('/mobile/education/foundation/guardian/context', params)
}

export function getOperatorContext(params?: RequestParams): Promise<MobileFoundationContext> {
  return requestContext('/mobile/education/foundation/operator/context', params)
}

function requestContext<T>(url: string, params?: Record<string, unknown>): Promise<T> {
  return new Promise((resolve, reject) => {
    uni.request({
      url,
      method: 'GET',
      data: compact(params),
      header: requestHeaders(),
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

function compact(params?: Record<string, unknown>): Record<string, unknown> {
  return Object.fromEntries(
    Object.entries(params || {}).filter(([, value]) => value !== undefined && value !== null && value !== '')
  )
}

function requestHeaders(): Record<string, string> {
  const token = storageToken()

  return token === null ? {} : { Authorization: `Bearer ${token}` }
}

function storageToken(): string | null {
  try {
    return uni.getStorageSync('access_token') || uni.getStorageSync('token') || null
  } catch {
    return null
  }
}
