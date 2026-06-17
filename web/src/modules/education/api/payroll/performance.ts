import type { MineResult } from '../foundation/types.ts'
import type { PayrollPage, PayrollScopedParams } from './types.ts'
import { payrollGetOptions } from './types.ts'

export interface TeacherPerformanceRecord {
  id: number
  teacher_id: number
  teacher_name?: string
  metric_month: string
  lesson_count?: number
  workload_units?: string
  attendance_rate?: string
  satisfaction_score?: string
  salary_amount_cents?: number
}

export interface TeacherPerformanceSummary {
  teacher_count: number
  lesson_count: number
  salary_amount_cents: number
  average_satisfaction_score?: string
}

export function pageTeacherPerformance(params: PayrollScopedParams): Promise<MineResult<PayrollPage<TeacherPerformanceRecord>>> {
  return useHttp().get('/admin/education/payroll/teacher-performance/page', payrollGetOptions(params))
}

export function getTeacherPerformanceSummary(params: PayrollScopedParams): Promise<MineResult<TeacherPerformanceSummary>> {
  return useHttp().get('/admin/education/payroll/teacher-performance/summary', payrollGetOptions(params))
}
