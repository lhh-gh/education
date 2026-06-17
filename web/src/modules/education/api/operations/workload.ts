import type { MineResult } from '../foundation/types.ts'
import type { OperationPage, OperationScopedParams } from './types.ts'
import { operationGetOptions } from './types.ts'

export type TeacherWorkloadType = 'main' | 'substitute'

export interface TeacherWorkloadRecord {
  id: number
  tenant_id: number
  campus_id?: number | null
  teacher_id: number
  lesson_id: number
  workload_type: TeacherWorkloadType
  lesson_type: 'normal' | 'makeup' | 'trial'
  credits: string
  student_count: number
  present_count: number
  leave_count: number
  absent_count: number
  recorded_at?: string | null
}

export interface TeacherWorkloadPageParams extends OperationScopedParams {
  teacher_id?: number
  workload_type?: TeacherWorkloadType
}

export interface TeacherWorkloadSummary {
  teacher_id?: number
  total_credits: string
  row_count: number
  student_count: number
  present_count: number
}

export function pageTeacherWorkloadRecords(params: TeacherWorkloadPageParams): Promise<MineResult<OperationPage<TeacherWorkloadRecord>>> {
  return useHttp().get('/admin/education/operations/reports/teacher-workloads', operationGetOptions(params))
}

export function getTeacherWorkloadSummary(params: TeacherWorkloadPageParams): Promise<MineResult<TeacherWorkloadSummary>> {
  return useHttp().get('/admin/education/operations/reports/teacher-workloads/summary', operationGetOptions(params))
}
