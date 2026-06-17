import { readFileSync } from 'fs'
import { join } from 'path'

describe('guardian student selector page', () => {
  it('select_student_saves_local_storage', () => {
    const source = readFileSync(join(process.cwd(), 'src/pages/guardian/student/index.vue'), 'utf8')

    expect(source).toContain('getGuardianStudents')
    expect(source).toContain('uni.setStorageSync(selectedStudentKey, student.id)')
    expect(source).toContain('uni.navigateBack()')
    expect(source).toContain('GuardianStateBlock')
    expect(source).toContain('StudentSelector')
  })
})
