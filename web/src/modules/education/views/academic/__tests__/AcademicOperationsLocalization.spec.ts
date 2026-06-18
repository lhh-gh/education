import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { describe, expect, it } from 'vitest'
import { attendanceStatusLabel } from '../attendanceConsumptionRules.ts'
import { lessonStatusLabel } from '../classScheduleRules.ts'
import {
  accountStatusLabel,
  enrollmentStatusLabel,
} from '../courseAccountRules.ts'
import { leaveStatusLabel } from '../leaveMakeupRescheduleRules.ts'
import { noticeStatusLabel } from '../noticeRules.ts'
import { dashboardMetricItems, summaryMetricItems } from '../reportRules.ts'

const academicDir = resolve(__dirname, '..')

const operationPages = [
  'AcademicDashboard.vue',
  'EnrollmentWorkbench.vue',
  'AccountLedgerList.vue',
  'AccountBalanceReport.vue',
  'LessonScheduleCalendar.vue',
  'LessonList.vue',
  'AttendanceReview.vue',
  'ConsumptionLedgerList.vue',
  'AccountAdjustmentList.vue',
  'LeaveRequestList.vue',
  'LessonChangeList.vue',
  'AttendanceReport.vue',
  'ConsumptionReport.vue',
  'LeaveReport.vue',
  'NoticeList.vue',
  'V1AcceptanceReport.vue',
]

const sharedOperationComponents = [
  'components/ReportDateRangeFilter.vue',
  'components/ReportTableToolbar.vue',
  'components/ReportStateBlock.vue',
]

const legacyEnglishCopy = [
  '<span>Academic Dashboard</span>',
  '<span>Enrollments</span>',
  '<span>Course Accounts</span>',
  '<span>Lesson Schedule</span>',
  '<span>Lessons</span>',
  '<span>Attendance Review</span>',
  '<span>Consumption Ledger</span>',
  '<span>Account Adjustments</span>',
  '<span>Leave Requests</span>',
  '<span>Lesson Changes</span>',
  '<span>Notices</span>',
  'Account Balance Report',
  'Attendance Report',
  'Consumption Report',
  'Leave Report',
  'V1 Acceptance',
  '>Search',
  '>Reset',
  '>Refresh',
  '>Retry',
  'label="Tenant ID"',
  'label="Campus ID"',
  'label="Start"',
  'label="End"',
  '>Today',
  '>This Week',
  '>This Month',
  'label="Actions"',
  'Cancel reason',
  'description="No enrollments"',
  'description="No course accounts"',
  'description="No lessons',
  'description="No consumption rows"',
  'description="No account adjustments"',
  'description="No leave requests"',
  'description="No lesson changes"',
  'description="No notices"',
]

describe('academic operations localization', () => {
  it('translates shared operation labels to Chinese', () => {
    expect(dashboardMetricItems({}).map(item => item.title)).toContain('在读学员')
    expect(enrollmentStatusLabel('confirmed')).toBe('已确认')
    expect(accountStatusLabel('active')).toBe('正常')
    expect(lessonStatusLabel('scheduled')).toBe('待上课')
    expect(attendanceStatusLabel('present')).toBe('出勤')
    expect(leaveStatusLabel('pending')).toBe('待审批')
    expect(noticeStatusLabel('published')).toBe('已发布')
    expect(summaryMetricItems({}, ['total_records'])[0].title).toBe('记录数')
  })

  it('removes legacy English copy from academic operation pages', () => {
    const pageText = [...operationPages, ...sharedOperationComponents]
      .map(file => readFileSync(resolve(academicDir, file), 'utf8'))
      .join('\n')

    for (const copy of legacyEnglishCopy) {
      expect(pageText).not.toContain(copy)
    }

    expect(pageText).toContain('<span>教务看板</span>')
    expect(pageText).not.toContain('鏁欏姟')
    expect(pageText).not.toContain('璇炬')
  })
})
