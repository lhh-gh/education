import type { MineResult } from '../foundation/types.ts'
import type { StandardsScopedParams } from './types.ts'
import { standardsGetOptions, standardsRequestOptions } from './types.ts'

export interface StageGoalPayload extends StandardsScopedParams {
  service_package_id: number
  goal_code: string
  goal_name: string
  goal_content: string
  ability_point_ids?: number[]
}

export interface AbilityPointPayload extends StandardsScopedParams {
  ability_code: string
  ability_name: string
  ability_group: string
  description?: string
}

export function pageStageGoals(params: StandardsScopedParams): Promise<MineResult<any>> {
  return useHttp().get('/admin/education/standards/stage-goals', standardsGetOptions(params))
}

export function saveStageGoal(payload: StageGoalPayload): Promise<MineResult<{ stage_goal_id: number }>> {
  return useHttp().post('/admin/education/standards/stage-goals', payload, standardsRequestOptions(payload))
}

export function pageAbilityPoints(params: StandardsScopedParams): Promise<MineResult<any>> {
  return useHttp().get('/admin/education/standards/ability-points', standardsGetOptions(params))
}

export function saveAbilityPoint(payload: AbilityPointPayload): Promise<MineResult<{ ability_point_id: number }>> {
  return useHttp().post('/admin/education/standards/ability-points', payload, standardsRequestOptions(payload))
}

export function syncGoalAbilityPoints(payload: StageGoalPayload): Promise<MineResult<{ stage_goal_id: number }>> {
  return saveStageGoal(payload)
}
