import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { describe, expect, it } from 'vitest'
import {
  academicActionText,
  academicGenderLabel,
  academicStatusLabel,
  relationLabel,
} from '../actionRules.ts'

const academicDir = resolve(__dirname, '..')

const corePages = [
  'AcademicDashboard.vue',
  'ClassroomList.vue',
  'StudentList.vue',
  'GuardianList.vue',
  'TeacherList.vue',
  'CourseList.vue',
  'LessonPackageList.vue',
  'ClassList.vue',
]

const coreForms = [
  'components/ClassroomForm.vue',
  'components/StudentForm.vue',
  'components/GuardianForm.vue',
  'components/TeacherForm.vue',
  'components/CourseForm.vue',
  'components/LessonPackageForm.vue',
  'components/ClassForm.vue',
]

const legacyEnglishCopy = [
  '<span>Academic Dashboard</span>',
  '<span>Classrooms</span>',
  '<span>Students</span>',
  '<span>Guardians</span>',
  '<span>Teachers</span>',
  '<span>Courses</span>',
  '<span>Lesson Packages</span>',
  '<span>Classes</span>',
  '>New',
  '>Search',
  '>Reset',
  'label="Actions"',
  'description="No classrooms"',
  'description="No students"',
  'description="No guardians"',
  'description="No teachers"',
  'description="No courses"',
  'description="No lesson packages"',
  'description="No classes"',
  'Delete this classroom?',
  'Delete this student?',
  'Delete this guardian?',
  'Delete this teacher?',
  'Delete this course?',
  'Delete this lesson package?',
  'Delete this class?',
  'Campus is required',
  'Code is required',
  'Name is required',
  'Status is required',
  'Student number is required',
  'Gender is required',
  'Teacher number is required',
  'Course is required',
  'Class type is required',
  'Lesson units is required',
  '>Save',
  'label="Campus ID"',
  'label="Student No"',
  'label="Teacher No"',
  'label="Class Type"',
  'label="Unit Minutes"',
]

describe('academic core localization', () => {
  it('translates shared academic labels to Chinese', () => {
    expect(academicStatusLabel('enabled')).toBe('启用')
    expect(academicStatusLabel('disabled')).toBe('停用')
    expect(academicGenderLabel('male')).toBe('男')
    expect(academicGenderLabel('female')).toBe('女')
    expect(academicGenderLabel('unknown')).toBe('未知')
    expect(relationLabel('father')).toBe('父亲')
    expect(relationLabel('mother')).toBe('母亲')
    expect(relationLabel('guardian')).toBe('监护人')
    expect(academicActionText('enabled')).toBe('停用')
    expect(academicActionText('disabled')).toBe('启用')
  })

  it('removes legacy English copy from academic core pages', () => {
    const pageText = [...corePages, ...coreForms]
      .map(file => readFileSync(resolve(academicDir, file), 'utf8'))
      .join('\n')

    for (const copy of legacyEnglishCopy) {
      expect(pageText).not.toContain(copy)
    }
  })
})
