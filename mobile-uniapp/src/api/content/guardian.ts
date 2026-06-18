import type { OperationScopedParams, PageResult } from '../operations/shared'
import { requestOperation, selectedStudentId } from '../operations/shared'

export { MobileApiError } from '../operations/shared'

export interface GuardianContentParams extends OperationScopedParams {
  keyword?: string
  status?: string
}

export interface GuardianMaterial {
  material_id: number
  id?: number
  material_name?: string
  material_type?: string
  status: string
  summary?: string
}

export interface GuardianShowcase {
  showcase_id: number
  id?: number
  title: string
  summary?: string
  status: string
}

export function getGuardianContentMaterials(params?: GuardianContentParams): Promise<PageResult<GuardianMaterial>> {
  const studentId = requireStudentId(params)

  return requestOperation(`/mobile/education/content/guardian/students/${studentId}/materials`, 'GET', withoutStudent(params))
}

export function getGuardianMaterialDetail(materialId: number, params?: GuardianContentParams): Promise<GuardianMaterial> {
  const studentId = requireStudentId(params)

  return requestOperation(`/mobile/education/content/guardian/students/${studentId}/materials/${materialId}`, 'GET', withoutStudent(params))
}

export function getGuardianContentShowcases(params?: GuardianContentParams): Promise<PageResult<GuardianShowcase>> {
  const studentId = requireStudentId(params)

  return requestOperation(`/mobile/education/content/guardian/students/${studentId}/showcases`, 'GET', withoutStudent(params))
}

export function getGuardianShowcaseDetail(showcaseId: number, params?: GuardianContentParams): Promise<GuardianShowcase> {
  const studentId = requireStudentId(params)

  return requestOperation(`/mobile/education/content/guardian/students/${studentId}/showcases/${showcaseId}`, 'GET', withoutStudent(params))
}

function withoutStudent<T extends OperationScopedParams>(params?: T): Omit<T, 'student_id'> {
  const { student_id: _studentId, ...rest } = params || {} as T

  return rest
}

function requireStudentId(params?: OperationScopedParams): number {
  return selectedStudentId(params) || 0
}
