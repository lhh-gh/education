import type { EducationRoleCode } from '../foundation/userProfile.ts'
import type { MinePage, MineResult, PageParams } from '../foundation/types.ts'

export type AcademicRecordStatus = 'enabled' | 'disabled'
export type Gender = 'male' | 'female' | 'unknown'
export type GuardianRelation = 'father' | 'mother' | 'grandfather' | 'grandmother' | 'guardian' | 'other'

export interface AcademicPageParams extends Partial<PageParams> {
  page?: number
  page_size?: number
  pageSize?: number
  tenant_id?: number
  campus_id?: number
  keyword?: string
  gender?: Gender
  status?: AcademicRecordStatus
}

export interface ClassroomRecord {
  id: number
  tenant_id: number
  campus_id: number
  code: string
  name: string
  capacity: number
  location?: string
  equipment?: Record<string, unknown>
  status: AcademicRecordStatus
  sort_order?: number
  remark?: string
  updated_at?: string
}

export interface StudentRecord {
  id: number
  tenant_id: number
  campus_id: number
  student_no: string
  name: string
  gender: Gender
  birthday?: string
  mobile?: string
  school?: string
  grade?: string
  source?: string
  avatar?: string
  enrolled_at?: string
  guardian_count?: number
  status: AcademicRecordStatus
  remark?: string
  updated_at?: string
}

export interface GuardianRecord {
  id: number
  tenant_id: number
  name: string
  mobile: string
  gender: Gender
  openid?: string
  unionid?: string
  student_count?: number
  status: AcademicRecordStatus
  remark?: string
  updated_at?: string
}

export interface StudentGuardianRecord {
  id?: number
  tenant_id?: number
  student_id?: number
  guardian_id: number
  guardian_name?: string
  guardian_mobile?: string
  relation: GuardianRelation
  is_primary?: boolean
  can_receive_notice?: boolean
  can_submit_leave?: boolean
  remark?: string
}

export interface TeacherRecord {
  id: number
  tenant_id: number
  campus_id: number
  user_profile_id?: number
  teacher_no: string
  name: string
  mobile?: string
  gender: Gender
  birthday?: string
  title?: string
  hire_date?: string
  avatar?: string
  introduction?: string
  status: AcademicRecordStatus
  remark?: string
  updated_at?: string
}

export type ClassroomPageParams = AcademicPageParams
export type StudentPageParams = AcademicPageParams
export type GuardianPageParams = Omit<AcademicPageParams, 'campus_id'>
export type TeacherPageParams = AcademicPageParams

export type ClassroomSavePayload = Omit<Partial<ClassroomRecord>, 'id' | 'updated_at'> & Pick<ClassroomRecord, 'campus_id' | 'code' | 'name' | 'status'>
export type StudentSavePayload = Omit<Partial<StudentRecord>, 'id' | 'updated_at' | 'guardian_count'> & Pick<StudentRecord, 'campus_id' | 'student_no' | 'name' | 'gender' | 'status'>
export type GuardianSavePayload = Omit<Partial<GuardianRecord>, 'id' | 'updated_at' | 'student_count'> & Pick<GuardianRecord, 'name' | 'mobile' | 'gender' | 'status'>
export type TeacherSavePayload = Omit<Partial<TeacherRecord>, 'id' | 'updated_at'> & Pick<TeacherRecord, 'campus_id' | 'teacher_no' | 'name' | 'gender' | 'status'>
export type StudentGuardianPayload = Pick<StudentGuardianRecord, 'guardian_id' | 'relation'> & Partial<Omit<StudentGuardianRecord, 'guardian_id' | 'relation'>>

function tenantHeaders(tenantId?: number): { headers: Record<string, string> } | undefined {
  return tenantId && tenantId > 0 ? { headers: { 'X-Tenant-Id': String(tenantId) } } : undefined
}

function tenantIdFrom(paramsOrPayload: { tenant_id?: number }): number | undefined {
  return paramsOrPayload.tenant_id
}

export function pageClassrooms(params: ClassroomPageParams): Promise<MineResult<MinePage<ClassroomRecord>>> {
  return useHttp().get('/admin/education/academic/classrooms/page', { params, ...tenantHeaders(tenantIdFrom(params)) })
}

export function createClassroom(data: ClassroomSavePayload): Promise<MineResult<ClassroomRecord>> {
  return useHttp().post('/admin/education/academic/classrooms', data, tenantHeaders(tenantIdFrom(data)))
}

export function updateClassroom(id: number, data: ClassroomSavePayload): Promise<MineResult<ClassroomRecord>> {
  return useHttp().put(`/admin/education/academic/classrooms/${id}`, data, tenantHeaders(tenantIdFrom(data)))
}

export function updateClassroomStatus(id: number, status: AcademicRecordStatus, tenantId?: number): Promise<MineResult<ClassroomRecord>> {
  return useHttp().put(`/admin/education/academic/classrooms/${id}/status`, { status }, tenantHeaders(tenantId))
}

export function deleteClassroom(id: number, tenantId?: number): Promise<MineResult<true>> {
  return useHttp().delete(`/admin/education/academic/classrooms/${id}`, tenantHeaders(tenantId))
}

export function pageStudents(params: StudentPageParams): Promise<MineResult<MinePage<StudentRecord>>> {
  return useHttp().get('/admin/education/academic/students/page', { params, ...tenantHeaders(tenantIdFrom(params)) })
}

export function createStudent(data: StudentSavePayload): Promise<MineResult<StudentRecord>> {
  return useHttp().post('/admin/education/academic/students', data, tenantHeaders(tenantIdFrom(data)))
}

export function updateStudent(id: number, data: StudentSavePayload): Promise<MineResult<StudentRecord>> {
  return useHttp().put(`/admin/education/academic/students/${id}`, data, tenantHeaders(tenantIdFrom(data)))
}

export function updateStudentStatus(id: number, status: AcademicRecordStatus, tenantId?: number): Promise<MineResult<StudentRecord>> {
  return useHttp().put(`/admin/education/academic/students/${id}/status`, { status }, tenantHeaders(tenantId))
}

export function deleteStudent(id: number, tenantId?: number): Promise<MineResult<true>> {
  return useHttp().delete(`/admin/education/academic/students/${id}`, tenantHeaders(tenantId))
}

export function listStudentGuardians(id: number, tenantId?: number): Promise<MineResult<{ list: StudentGuardianRecord[] }>> {
  return useHttp().get(`/admin/education/academic/students/${id}/guardians`, tenantHeaders(tenantId))
}

export function saveStudentGuardians(id: number, relations: StudentGuardianPayload[], tenantId?: number): Promise<MineResult<{ list: StudentGuardianRecord[] }>> {
  return useHttp().put(`/admin/education/academic/students/${id}/guardians`, { relations }, tenantHeaders(tenantId))
}

export function pageGuardians(params: GuardianPageParams): Promise<MineResult<MinePage<GuardianRecord>>> {
  return useHttp().get('/admin/education/academic/guardians/page', { params, ...tenantHeaders(tenantIdFrom(params)) })
}

export function createGuardian(data: GuardianSavePayload): Promise<MineResult<GuardianRecord>> {
  return useHttp().post('/admin/education/academic/guardians', data, tenantHeaders(tenantIdFrom(data)))
}

export function updateGuardian(id: number, data: GuardianSavePayload): Promise<MineResult<GuardianRecord>> {
  return useHttp().put(`/admin/education/academic/guardians/${id}`, data, tenantHeaders(tenantIdFrom(data)))
}

export function updateGuardianStatus(id: number, status: AcademicRecordStatus, tenantId?: number): Promise<MineResult<GuardianRecord>> {
  return useHttp().put(`/admin/education/academic/guardians/${id}/status`, { status }, tenantHeaders(tenantId))
}

export function deleteGuardian(id: number, tenantId?: number): Promise<MineResult<true>> {
  return useHttp().delete(`/admin/education/academic/guardians/${id}`, tenantHeaders(tenantId))
}

export function pageTeachers(params: TeacherPageParams): Promise<MineResult<MinePage<TeacherRecord>>> {
  return useHttp().get('/admin/education/academic/teachers/page', { params, ...tenantHeaders(tenantIdFrom(params)) })
}

export function createTeacher(data: TeacherSavePayload): Promise<MineResult<TeacherRecord>> {
  return useHttp().post('/admin/education/academic/teachers', data, tenantHeaders(tenantIdFrom(data)))
}

export function updateTeacher(id: number, data: TeacherSavePayload): Promise<MineResult<TeacherRecord>> {
  return useHttp().put(`/admin/education/academic/teachers/${id}`, data, tenantHeaders(tenantIdFrom(data)))
}

export function updateTeacherStatus(id: number, status: AcademicRecordStatus, tenantId?: number): Promise<MineResult<TeacherRecord>> {
  return useHttp().put(`/admin/education/academic/teachers/${id}/status`, { status }, tenantHeaders(tenantId))
}

export function deleteTeacher(id: number, tenantId?: number): Promise<MineResult<true>> {
  return useHttp().delete(`/admin/education/academic/teachers/${id}`, tenantHeaders(tenantId))
}

export function teacherProfileSelectorParams(tenantId?: number): { tenant_id?: number, role_code: EducationRoleCode, status: 'enabled' } {
  return { tenant_id: tenantId, role_code: 'teacher', status: 'enabled' }
}
