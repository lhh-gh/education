import { afterEach, describe, expect, it, vi } from 'vitest'
import { pageAttendanceLessons } from '../../../api/academic/attendanceConsumption.ts'
import { pageClasses, scheduleSingleLesson } from '../../../api/academic/classSchedule.ts'
import { createCourse, pageCourses } from '../../../api/academic/courseAccount.ts'
import { pageLeaveRequests } from '../../../api/academic/lessonChange.ts'
import { createNotice, pageNotices } from '../../../api/academic/notice.ts'
import { getAcademicDashboard } from '../../../api/academic/report.ts'

interface HttpCall {
  method: 'GET' | 'POST'
  url: string
  data?: unknown
  config?: any
}

function stubHttp(): HttpCall[] {
  const calls: HttpCall[] = []
  const response = Promise.resolve({ code: 200, message: 'success', data: {} })

  vi.stubGlobal('useHttp', () => ({
    get: (url: string, config?: unknown) => {
      calls.push({ method: 'GET', url, config })
      return response
    },
    post: (url: string, data?: unknown, config?: unknown) => {
      calls.push({ method: 'POST', url, data, config })
      return response
    },
  }))

  return calls
}

afterEach(() => vi.unstubAllGlobals())

describe('academic api SaaS scope headers', () => {
  it('adds tenant and campus headers across core academic modules', () => {
    const calls = stubHttp()
    const scope = { tenant_id: 1, campus_id: 9 }

    pageCourses(scope)
    createCourse({ ...scope, code: 'C001', name: 'Math', unit_minutes: 45, status: 'enabled' })
    pageClasses(scope)
    scheduleSingleLesson({
      ...scope,
      class_id: 1,
      teacher_id: 2,
      title: 'Math lesson',
      start_at: '2026-06-20 09:00:00',
      end_at: '2026-06-20 10:00:00',
      lesson_units: 1,
    })
    pageAttendanceLessons(scope)
    pageLeaveRequests(scope)
    pageNotices(scope)
    createNotice({
      ...scope,
      notice_type: 'academic',
      target_type: 'campus',
      title: 'Notice',
      content: 'Content',
      priority: 'normal',
    })
    getAcademicDashboard(scope)

    expect(calls.map(call => [call.method, call.url])).toEqual([
      ['GET', '/admin/education/academic/courses/page'],
      ['POST', '/admin/education/academic/courses'],
      ['GET', '/admin/education/academic/classes/page'],
      ['POST', '/admin/education/academic/lesson-schedule/single'],
      ['GET', '/admin/education/academic/attendance/lessons/page'],
      ['GET', '/admin/education/academic/leave-requests/page'],
      ['GET', '/admin/education/academic/notices/page'],
      ['POST', '/admin/education/academic/notices'],
      ['GET', '/admin/education/academic/reports/dashboard'],
    ])

    calls.forEach((call) => {
      expect(call.config.headers).toMatchObject({
        'X-Tenant-Id': '1',
        'X-Campus-Id': '9',
      })
    })
  })
})
