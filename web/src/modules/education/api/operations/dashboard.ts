import type { MineResult } from '../foundation/types.ts'
import type { OperationScopedParams } from './types.ts'
import { operationGetOptions } from './types.ts'

export interface OperationMetricCard {
  key: string
  title: string
  value: string | number
  unit?: string
}

export interface OperationOverview {
  metrics: Record<string, string | number>
  cards?: OperationMetricCard[]
  pending_reviews?: number
  urgent_renewals?: number
}

export interface OperationTrendRow {
  date: string
  consumed_units: string
  review_count: number
}

export interface RenewalAlertSummary {
  urgent_count: number
  warning_count: number
  normal_count: number
}

export interface DailyOperationMetric {
  date: string
  lesson_change_count: number
  makeup_count: number
  consumption_review_count: number
  renewal_alert_count: number
}

export function getOperationOverview(params: OperationScopedParams): Promise<MineResult<OperationOverview>> {
  return useHttp().get('/admin/education/operations/dashboard/overview', operationGetOptions(params))
}

export function getConsumptionTrend(params: OperationScopedParams): Promise<MineResult<{ list: OperationTrendRow[] }>> {
  return useHttp().get('/admin/education/operations/dashboard/consumption-trend', operationGetOptions(params))
}

export function getRenewalAlertSummary(params: OperationScopedParams): Promise<MineResult<RenewalAlertSummary>> {
  return useHttp().get('/admin/education/operations/dashboard/renewal-alert-summary', operationGetOptions(params))
}

export function getDailyOperationMetrics(params: OperationScopedParams): Promise<MineResult<{ list: DailyOperationMetric[] }>> {
  return useHttp().get('/admin/education/operations/dashboard/daily-metrics', operationGetOptions(params))
}
