import type { MineResult } from '../foundation/types.ts'
import type { FamilyPage, FamilyScopedParams } from './types.ts'
import { familyGetOptions, familyRequestOptions } from './types.ts'

export interface FamilyMessageRecord {
  id: number
  thread_id: string
  student_id: number
  sender_type: string
  sender_user_id: number
  content: string
  status: string
}

export interface FamilyMessagePayload extends FamilyScopedParams {
  thread_id?: string
  student_id: number
  receiver_user_id?: number
  content: string
}

export function pageFamilyMessages(params: FamilyScopedParams): Promise<MineResult<FamilyPage<FamilyMessageRecord>>> {
  return useHttp().get('/admin/education/family/messages/page', familyGetOptions(params))
}

export function getFamilyMessageThread(params: FamilyScopedParams & { student_id: number, thread_id: string }): Promise<MineResult<FamilyMessageRecord[]>> {
  return useHttp().get('/admin/education/family/messages/thread', familyGetOptions(params))
}

export function sendFamilyMessage(payload: FamilyMessagePayload): Promise<MineResult<{ message_id: number, status: string }>> {
  return useHttp().post('/admin/education/family/messages', payload, familyRequestOptions(payload))
}
