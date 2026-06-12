import type { PageList, ResponseStruct } from '#/global'
import type { EducationStatus } from './tenant.ts'

export type EducationRoleCode
  = | 'platform_super_admin'
    | 'platform_operator'
    | 'tenant_admin'
    | 'principal'
    | 'academic_staff'
    | 'front_desk'
    | 'teacher'
    | 'finance'
    | 'guardian'

export interface UserProfileRecord {
  id: number
  profile_key: string
  tenant_id?: number
  user_id: number
  role_code: EducationRoleCode
  display_name: string
  mobile?: string
  avatar?: string
  openid?: string
  unionid?: string
  status: EducationStatus
  current_campus_id?: number
  campus_scope_count?: number
  created_at?: string
  updated_at?: string
}

export interface UserProfilePageParams {
  page?: number
  page_size?: number
  tenant_id?: number
  keyword?: string
  role_code?: EducationRoleCode
  status?: EducationStatus
}

export interface UserProfileSavePayload {
  tenant_id?: number
  user_id: number
  role_code: EducationRoleCode
  display_name: string
  mobile?: string
  avatar?: string
  openid?: string
  unionid?: string
  status?: EducationStatus
  current_campus_id?: number
  settings?: Record<string, unknown>
}

export interface CampusScopeRecord {
  user_profile_id: number
  user_id: number
  tenant_id: number
  campus_ids: number[]
}

export function pageUserProfiles(params: UserProfilePageParams): Promise<ResponseStruct<PageList<UserProfileRecord>>> {
  return useHttp().get('/admin/education/foundation/user-profiles/page', { params })
}

export function createUserProfile(data: UserProfileSavePayload): Promise<ResponseStruct<{ id: number, profile_key: string }>> {
  return useHttp().post('/admin/education/foundation/user-profiles', data)
}

export function updateUserProfile(id: number, data: UserProfileSavePayload): Promise<ResponseStruct<{ id: number }>> {
  return useHttp().put(`/admin/education/foundation/user-profiles/${id}`, data)
}

export function updateUserProfileStatus(id: number, status: EducationStatus): Promise<ResponseStruct<{ id: number, status: EducationStatus }>> {
  return useHttp().put(`/admin/education/foundation/user-profiles/${id}/status`, { status })
}

export function getCampusScopes(id: number): Promise<ResponseStruct<CampusScopeRecord>> {
  return useHttp().get(`/admin/education/foundation/user-profiles/${id}/campus-scopes`)
}

export function saveCampusScopes(id: number, campus_ids: number[]): Promise<ResponseStruct<{ user_profile_id: number, campus_ids: number[] }>> {
  return useHttp().put(`/admin/education/foundation/user-profiles/${id}/campus-scopes`, { campus_ids })
}
