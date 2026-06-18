import { readFileSync } from 'fs'
import { join } from 'path'

describe('teacher schedule page contract', () => {
  const source = readFileSync(join(process.cwd(), 'src/pages/teacher/schedule/index.vue'), 'utf8')

  it('renders_loading_empty_error_forbidden_and_success_states', () => {
    expect(source).toContain('status="loading"')
    expect(source).toContain('status="empty"')
    expect(source).toContain('status="forbidden"')
    expect(source).toContain('status="error"')
    expect(source).toContain('state.lessons')
  })

  it('loads_today_and_paged_lessons', () => {
    expect(source).toContain('getTeacherTodayLessons')
    expect(source).toContain('pageTeacherLessons')
    expect(source).toContain('/pages/teacher/lesson/detail?lesson_id=')
    expect(source).toContain('/pages/teacher/lesson/attendance?lesson_id=')
  })
})
