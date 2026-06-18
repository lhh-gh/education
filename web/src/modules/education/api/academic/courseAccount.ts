import type { MinePage, MineResult, PageParams } from '../foundation/types.ts'

export type AcademicRecordStatus = 'enabled' | 'disabled'
export type EnrollmentStatus = 'pending' | 'confirmed' | 'cancelled'
export type StudentCourseAccountStatus = 'active' | 'frozen' | 'closed'
export type AccountLedgerSourceType = 'enrollment' | 'enrollment_cancel' | 'consumption' | 'adjustment'

export interface AcademicScopedPageParams extends Partial<PageParams> {
  page?: number
  page_size?: number
  pageSize?: number
  tenant_id?: number
  campus_id?: number
  keyword?: string
  status?: string
}

export interface CoursePageParams extends AcademicScopedPageParams {
  status?: AcademicRecordStatus
}

export interface LessonPackagePageParams extends AcademicScopedPageParams {
  course_id?: number
  status?: AcademicRecordStatus
}

export interface EnrollmentPageParams extends AcademicScopedPageParams {
  student_id?: number
  course_id?: number
  status?: EnrollmentStatus
  enrolled_at_start?: string
  enrolled_at_end?: string
}

export interface StudentCourseAccountPageParams extends AcademicScopedPageParams {
  student_id?: number
  course_id?: number
  status?: StudentCourseAccountStatus
}

export interface AccountLedgerPageParams extends Partial<PageParams> {
  page?: number
  page_size?: number
  pageSize?: number
  tenant_id?: number
  source_type?: AccountLedgerSourceType
}

export interface CourseRecord {
  id: number
  tenant_id: number
  campus_id: number
  code: string
  name: string
  category?: string
  subject?: string
  unit_minutes: number
  cover_url?: string
  description?: string
  status: AcademicRecordStatus
  sort_order?: number
  remark?: string
  teacher_count?: number
  package_count?: number
  created_at?: string
  updated_at?: string
}

export interface TeacherCourseRecord {
  id?: number
  tenant_id?: number
  campus_id?: number
  course_id: number
  teacher_id: number
  teacher_name?: string
  teacher_no?: string
  teacher_mobile?: string
  status?: AcademicRecordStatus
  authorized_at?: string
  remark?: string
}

export interface LessonPackageRecord {
  id: number
  tenant_id: number
  campus_id: number
  course_id: number
  course_name?: string
  code: string
  name: string
  lesson_units: string
  bonus_units: string
  total_units: string
  list_price: string
  sale_price: string
  validity_days?: number
  status: AcademicRecordStatus
  sort_order?: number
  remark?: string
  created_at?: string
  updated_at?: string
}

export interface EnrollmentRecord {
  id: number
  tenant_id: number
  campus_id: number
  enrollment_no: string
  student_id: number
  student_name_snapshot: string
  course_id: number
  course_name_snapshot: string
  lesson_package_id: number
  package_name_snapshot: string
  account_id?: number
  package_lesson_units: string
  package_bonus_units: string
  total_units: string
  list_price: string
  deal_amount: string
  status: EnrollmentStatus
  enrolled_at?: string
  confirmed_at?: string
  materialized_at?: string
  cancelled_at?: string
  cancel_reason?: string
  remark?: string
  created_at?: string
}

export interface StudentCourseAccountRecord {
  id: number
  tenant_id: number
  campus_id: number
  student_id: number
  student_name?: string
  student_no?: string
  course_id: number
  course_name?: string
  purchased_units: string
  bonus_units: string
  consumed_units: string
  adjusted_units: string
  refunded_units: string
  frozen_units: string
  available_units: string
  status: StudentCourseAccountStatus
  first_enrollment_id?: number
  last_enrollment_id?: number
  opened_at?: string
  expires_at?: string
  remark?: string
  updated_at?: string
}

export interface AccountLedgerRecord {
  source_type: AccountLedgerSourceType
  source_id?: number
  source_no: string
  occurred_at?: string
  direction?: 'in' | 'out'
  units: string
  before_available_units?: string
  after_available_units?: string
  operator_id?: number
  operator_name?: string
  remark?: string
}

export type CourseSavePayload = Omit<Partial<CourseRecord>, 'id' | 'created_at' | 'updated_at' | 'teacher_count' | 'package_count'> & Pick<CourseRecord, 'campus_id' | 'code' | 'name' | 'unit_minutes' | 'status'>
export type LessonPackageSavePayload = Omit<Partial<LessonPackageRecord>, 'id' | 'created_at' | 'updated_at' | 'course_name' | 'total_units'> & Pick<LessonPackageRecord, 'campus_id' | 'course_id' | 'code' | 'name' | 'lesson_units' | 'bonus_units' | 'list_price' | 'sale_price' | 'status'>
export type EnrollmentCreatePayload = Pick<EnrollmentRecord, 'campus_id' | 'student_id' | 'course_id' | 'lesson_package_id'> & Partial<Pick<EnrollmentRecord, 'deal_amount' | 'enrolled_at' | 'remark' | 'tenant_id'>>
export interface EnrollmentCreateResult {
  enrollment: EnrollmentRecord
  account: StudentCourseAccountRecord | null
}

function tenantHeaders(tenantId?: number): { headers: Record<string, string> } | undefined {
  return tenantId && tenantId > 0 ? { headers: { 'X-Tenant-Id': String(tenantId) } } : undefined
}

function tenantIdFrom(paramsOrPayload: { tenant_id?: number }): number | undefined {
  return paramsOrPayload.tenant_id
}

export function pageCourses(params: CoursePageParams): Promise<MineResult<MinePage<CourseRecord>>> {
  return useHttp().get('/admin/education/academic/courses/page', { params, ...tenantHeaders(tenantIdFrom(params)) })
}

export function createCourse(data: CourseSavePayload): Promise<MineResult<CourseRecord>> {
  return useHttp().post('/admin/education/academic/courses', data, tenantHeaders(tenantIdFrom(data)))
}

export function updateCourse(id: number, data: CourseSavePayload): Promise<MineResult<CourseRecord>> {
  return useHttp().put(`/admin/education/academic/courses/${id}`, data, tenantHeaders(tenantIdFrom(data)))
}

export function changeCourseStatus(id: number, status: AcademicRecordStatus, tenantId?: number): Promise<MineResult<CourseRecord>> {
  return useHttp().put(`/admin/education/academic/courses/${id}/status`, { status }, tenantHeaders(tenantId))
}

export function deleteCourse(id: number, tenantId?: number): Promise<MineResult<true>> {
  return useHttp().delete(`/admin/education/academic/courses/${id}`, tenantHeaders(tenantId))
}

export function getCourseTeachers(id: number, tenantId?: number): Promise<MineResult<{ list: TeacherCourseRecord[] }>> {
  return useHttp().get(`/admin/education/academic/courses/${id}/teachers`, tenantHeaders(tenantId))
}

export function saveCourseTeachers(id: number, teacherIds: number[], tenantId?: number): Promise<MineResult<{ list: TeacherCourseRecord[] }>> {
  return useHttp().put(`/admin/education/academic/courses/${id}/teachers`, { teacher_ids: teacherIds }, tenantHeaders(tenantId))
}

export function pageLessonPackages(params: LessonPackagePageParams): Promise<MineResult<MinePage<LessonPackageRecord>>> {
  return useHttp().get('/admin/education/academic/lesson-packages/page', { params, ...tenantHeaders(tenantIdFrom(params)) })
}

export function createLessonPackage(data: LessonPackageSavePayload): Promise<MineResult<LessonPackageRecord>> {
  return useHttp().post('/admin/education/academic/lesson-packages', data, tenantHeaders(tenantIdFrom(data)))
}

export function updateLessonPackage(id: number, data: LessonPackageSavePayload): Promise<MineResult<LessonPackageRecord>> {
  return useHttp().put(`/admin/education/academic/lesson-packages/${id}`, data, tenantHeaders(tenantIdFrom(data)))
}

export function changeLessonPackageStatus(id: number, status: AcademicRecordStatus, tenantId?: number): Promise<MineResult<LessonPackageRecord>> {
  return useHttp().put(`/admin/education/academic/lesson-packages/${id}/status`, { status }, tenantHeaders(tenantId))
}

export function deleteLessonPackage(id: number, tenantId?: number): Promise<MineResult<true>> {
  return useHttp().delete(`/admin/education/academic/lesson-packages/${id}`, tenantHeaders(tenantId))
}

export function pageEnrollments(params: EnrollmentPageParams): Promise<MineResult<MinePage<EnrollmentRecord>>> {
  return useHttp().get('/admin/education/academic/enrollments/page', { params, ...tenantHeaders(tenantIdFrom(params)) })
}

export function getEnrollment(id: number, tenantId?: number): Promise<MineResult<EnrollmentRecord>> {
  return useHttp().get(`/admin/education/academic/enrollments/${id}`, tenantHeaders(tenantId))
}

export function createEnrollment(data: EnrollmentCreatePayload): Promise<MineResult<EnrollmentCreateResult>> {
  return useHttp().post('/admin/education/academic/enrollments', data, tenantHeaders(tenantIdFrom(data)))
}

export function cancelEnrollment(id: number, cancelReason: string, tenantId?: number): Promise<MineResult<EnrollmentCreateResult>> {
  return useHttp().put(`/admin/education/academic/enrollments/${id}/cancel`, { cancel_reason: cancelReason }, tenantHeaders(tenantId))
}

export function pageStudentCourseAccounts(params: StudentCourseAccountPageParams): Promise<MineResult<MinePage<StudentCourseAccountRecord>>> {
  return useHttp().get('/admin/education/academic/student-course-accounts/page', { params, ...tenantHeaders(tenantIdFrom(params)) })
}

export function getAccountLedger(id: number, params: AccountLedgerPageParams): Promise<MineResult<MinePage<AccountLedgerRecord>>> {
  return useHttp().get(`/admin/education/academic/student-course-accounts/${id}/ledger`, { params, ...tenantHeaders(tenantIdFrom(params)) })
}

export function changeStudentCourseAccountStatus(id: number, status: StudentCourseAccountStatus, tenantId?: number): Promise<MineResult<StudentCourseAccountRecord>> {
  return useHttp().put(`/admin/education/academic/student-course-accounts/${id}/status`, { status }, tenantHeaders(tenantId))
}
