import type { MineResult } from '../foundation/types.ts'
import type { FamilyPage, FamilyScopedParams } from './types.ts'
import { familyGetOptions, familyRequestOptions } from './types.ts'

export interface LearningReportItem {
  item_type: string
  title: string
  content: string
  sort_order?: number
}

export interface LearningReportRecord {
  id: number
  student_id: number
  report_title: string
  report_period: string
  status: string
  summary?: string
}

export interface LearningReportPayload extends FamilyScopedParams {
  id?: number
  student_id: number
  report_title: string
  report_period: string
  summary?: string
  items: LearningReportItem[]
}

export interface GrowthRecordRecord {
  id: number
  student_id: number
  title: string
  record_type: string
  status: string
}

export function pageLearningReports(params: FamilyScopedParams): Promise<MineResult<FamilyPage<LearningReportRecord>>> {
  return useHttp().get('/admin/education/family/learning-reports/page', familyGetOptions(params))
}

export function saveLearningReport(payload: LearningReportPayload): Promise<MineResult<{ learning_report_id: number, status: string }>> {
  return useHttp().post('/admin/education/family/learning-reports', payload, familyRequestOptions(payload))
}

export function publishLearningReport(id: number, params: FamilyScopedParams = {}): Promise<MineResult<{ learning_report_id: number, status: string }>> {
  return useHttp().post(`/admin/education/family/learning-reports/${id}/publish`, {}, familyRequestOptions(params))
}

export function withdrawLearningReport(id: number, params: FamilyScopedParams = {}): Promise<MineResult<{ learning_report_id: number, status: string }>> {
  return useHttp().post(`/admin/education/family/learning-reports/${id}/withdraw`, {}, familyRequestOptions(params))
}

export function pageGrowthRecords(params: FamilyScopedParams): Promise<MineResult<FamilyPage<GrowthRecordRecord>>> {
  return useHttp().get('/admin/education/family/growth-records/page', familyGetOptions(params))
}
