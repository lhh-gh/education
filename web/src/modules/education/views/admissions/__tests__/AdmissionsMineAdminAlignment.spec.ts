import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import type { RouteRecordRaw } from 'vue-router'
import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { admissionErrorText, admissionStageLabel, admissionStatusLabel } from '../admissionRules.ts'

function flattenRoutes(routes: RouteRecordRaw[]): RouteRecordRaw[] {
  return routes.flatMap(route => [route, ...flattenRoutes(route.children ?? [])])
}

function findRoute(name: string): RouteRecordRaw {
  const route = flattenRoutes(educationRoutes).find(item => item.name === name)
  if (!route) {
    throw new Error(`route ${name} not found`)
  }

  return route
}

describe('admissions MineAdmin alignment', () => {
  it('uses_chinese_route_titles_for_admissions_menu', () => {
    expect(findRoute('EducationAdmissions').meta?.title).toBe('招生获客')

    const expectedRoutes = [
      ['EducationAdmissionLeadSourceList', '线索来源'],
      ['EducationAdmissionLeadPool', '线索池'],
      ['EducationAdmissionLeadDetail', '线索详情'],
      ['EducationAdmissionTrialCalendar', '试听日历'],
      ['EducationAdmissionTrialFeedbackList', '试听反馈'],
      ['EducationAdmissionConversionWorkbench', '线索转化'],
      ['EducationAdmissionTaskList', '招生任务'],
      ['EducationAdmissionDashboard', '招生看板'],
    ] as const

    for (const [name, title] of expectedRoutes) {
      expect(findRoute(name).meta?.title).toBe(title)
    }
  })

  it('removes_legacy_english_copy_from_admissions_vue_pages', () => {
    const admissionsViewDir = resolve(process.cwd(), 'src/modules/education/views/admissions')
    const vueFiles = [
      'AdmissionDashboard.vue',
      'AdmissionTaskList.vue',
      'LeadConversionWorkbench.vue',
      'LeadDetail.vue',
      'LeadPool.vue',
      'LeadSourceList.vue',
      'TrialFeedbackList.vue',
      'TrialLessonCalendar.vue',
    ]
    const legacyCopies = [
      'Admissions Dashboard',
      'Admission Tasks',
      'Lead Pool',
      'Lead Sources',
      'Lead Detail',
      'Trial Feedback',
      'Trial Lessons',
      'Conversion Workbench',
      'New Lead',
      'Save Source',
      'Schedule Trial',
      'Save Follow',
      'Save Feedback',
      'Search',
      'Refresh',
      'Actions',
      'No leads',
      'No lead sources',
      'No trial lessons',
      'No admission tasks',
      'No dashboard data',
      'Lead saved',
      'Lead assigned',
      'Permission denied',
    ]

    for (const file of vueFiles) {
      const content = readFileSync(resolve(admissionsViewDir, file), 'utf8')

      for (const copy of legacyCopies) {
        expect(content, `${file} still contains [${copy}]`).not.toContain(copy)
      }
    }
  })

  it('maps_admission_status_and_errors_to_chinese', () => {
    expect(admissionStageLabel('new')).toBe('新线索')
    expect(admissionStageLabel('trial_scheduled')).toBe('已预约试听')
    expect(admissionStatusLabel('converted')).toBe('已转化')
    expect(admissionErrorText({ code: 409, data: { lead_id: 99 } })).toBe('线索已存在：#99')
    expect(admissionErrorText({ code: 409, data: { conflict_lesson_id: 88 } })).toBe('试听时间冲突：#88')
    expect(admissionErrorText({ message: 'Permission denied' })).toBe('暂无操作权限')
    expect(admissionErrorText({})).toBe('招生操作失败')
  })
})
