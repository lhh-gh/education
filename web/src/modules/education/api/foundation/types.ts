export type FoundationStatus = 'enabled' | 'disabled'

export interface MinePage<T> {
  list: T[]
  total: number
}

export interface MineResult<T> {
  code: number
  message: string
  data: T
}

export interface PageParams {
  page: number
  pageSize: number
}

export interface ApiFailure {
  code: number
  message: string
  data?: Record<string, unknown>
}
