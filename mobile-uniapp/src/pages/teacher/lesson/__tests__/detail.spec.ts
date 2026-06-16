import { readFileSync } from 'fs'
import { join } from 'path'

describe('teacher lesson detail page contract', () => {
  const source = readFileSync(join(process.cwd(), 'src/pages/teacher/lesson/detail.vue'), 'utf8')

  it('renders_loading_empty_error_forbidden_and_success_states', () => {
    expect(source).toContain('status="loading"')
    expect(source).toContain('status="empty"')
    expect(source).toContain('status="forbidden"')
    expect(source).toContain('status="error"')
    expect(source).toContain('state.lesson.lesson_students')
  })

  it('loads_detail_and_links_attendance_when_allowed', () => {
    expect(source).toContain('getTeacherLessonDetail')
    expect(source).toContain('can_submit_attendance')
    expect(source).toContain('/pages/teacher/lesson/attendance?lesson_id=')
  })
})
