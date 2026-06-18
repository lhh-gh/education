import type { OperationScopedParams } from '../operations/shared'
import { requestOperation } from '../operations/shared'

export { MobileApiError } from '../operations/shared'

export interface GuardianConsultationPayload extends OperationScopedParams {
  contact_name: string
  contact_mobile: string
  student_name: string
  student_age?: number
  interested_course?: string
}

export interface GuardianConsultationResult {
  id?: number
  lead_id?: number
  stage?: string
  contact_mobile?: string
}

export function createGuardianConsultation(payload: GuardianConsultationPayload): Promise<GuardianConsultationResult> {
  return requestOperation('/mobile/education/admissions/guardian/consultations', 'POST', payload)
}
