import { readFileSync } from 'fs'
import { join } from 'path'

describe('guardian schedule page', () => {
  it('schedule_renders_lessons_and_leave_action', () => {
    const source = readFileSync(join(process.cwd(), 'src/pages/guardian/schedule/index.vue'), 'utf8')

    expect(source).toContain('pageGuardianStudentLessons')
    expect(source).toContain('start_at: monthStart()')
    expect(source).toContain("lesson.lesson_status === 'scheduled'")
    expect(source).toContain('/pages/guardian/leave/create?lessonStudentId=')
    expect(source).toContain('onPullDownRefresh(refresh)')
  })
})
