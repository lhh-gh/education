import type { FoundationStatus, MinePage, MineResult, PageParams } from './types.ts'

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

export interface UserProfileListItem {
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
  status: FoundationStatus
  current_campus_id?: number
  campus_scope_count?: number
  created_at?: string
  updated_at?: string
}

export type UserProfileRecord = UserProfileListItem
export type UserProfileDetail = UserProfileListItem

export interface UserProfilePageParams extends Partial<PageParams> {
  page?: number
  page_size?: number
  tenant_id?: number
  keyword?: string
  role_code?: EducationRoleCode
  status?: FoundationStatus
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
  status?: FoundationStatus
  current_campus_id?: number
  settings?: Record<string, unknown>
}

export interface CampusScopeRecord {
  user_profile_id: number
  user_id: number
  tenant_id: number
  campus_ids: number[]
}

export function pageUserProfiles(params: UserProfilePageParams): Promise<MineResult<MinePage<UserProfileListItem>>> {
  return useHttp().get('/admin/education/foundation/user-profiles/page', { params })
}

export function createUserProfile(data: UserProfileSavePayload): Promise<MineResult<UserProfileDetail>> {
  return useHttp().post('/admin/education/foundation/user-profiles', data)
}

export function updateUserProfile(id: number, data: UserProfileSavePayload): Promise<MineResult<UserProfileDetail>> {
  return useHttp().put(`/admin/education/foundation/user-profiles/${id}`, data)
}

export function updateUserProfileStatus(id: number, status: FoundationStatus): Promise<MineResult<UserProfileDetail>> {
  return useHttp().put(`/admin/education/foundation/user-profiles/${id}/status`, { status })
}

export function getCampusScopes(id: number): Promise<MineResult<{ campus_ids: number[] } & Partial<CampusScopeRecord>>> {
  return useHttp().get(`/admin/education/foundation/user-profiles/${id}/campus-scopes`)
}

export function saveCampusScopes(id: number, campus_ids: number[]): Promise<MineResult<{ campus_ids: number[] }>> {
  return useHttp().put(`/admin/education/foundation/user-profiles/${id}/campus-scopes`, { campus_ids })
}
