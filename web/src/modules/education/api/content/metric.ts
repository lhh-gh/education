import type { MineResult } from '../foundation/types.ts'
import type { ContentPage, ContentScopedParams, MaterialUsageMetricRow, StudentWorkMetricRow } from './types.ts'
import { contentGetOptions } from './types.ts'

export function getMaterialUsageMetrics(params: ContentScopedParams): Promise<MineResult<ContentPage<MaterialUsageMetricRow>>> {
  return useHttp().get('/admin/education/content/material-usage-metrics', contentGetOptions(params))
}

export function getStudentWorkMetrics(params: ContentScopedParams): Promise<MineResult<ContentPage<StudentWorkMetricRow>>> {
  return useHttp().get('/admin/education/content/student-work-metrics', contentGetOptions(params))
}
