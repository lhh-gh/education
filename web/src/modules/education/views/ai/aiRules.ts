import type { AiTagType } from '../../api/ai/types.ts'
import type { GenerationStatus } from '../../api/ai/generation.ts'

export interface AiReviewState {
  safety_status: string
  review_status: string
}

export interface AiModelConfigActions {
  canSaveModel: boolean
  canSaveFeature: boolean
}

export const aiModelConfigText = {
  title: '模型配置',
  saveModel: '保存模型',
  saveFeature: '保存功能',
  modelSaved: '模型配置已保存',
  featureSaved: '功能设置已保存',
  loadFailed: '模型配置加载失败',
  empty: '暂无模型配置',
  fields: {
    configCode: '配置编码',
    provider: '服务商',
    modelName: '模型名称',
    apiKey: 'API Key',
    status: '状态',
    featureCode: '功能编码',
    featureName: '功能名称',
    modelConfigId: '模型配置ID',
    enabled: '启用',
    reviewRequired: '需要审核',
    safetyLevel: '安全等级',
  },
  columns: {
    configCode: '配置编码',
    provider: '服务商',
    modelName: '模型名称',
    secret: '密钥',
    status: '状态',
  },
} as const

const statusLabels: Record<string, string> = {
  approved: '已通过',
  blocked: '已阻断',
  disabled: '停用',
  draft: '草稿',
  enabled: '启用',
  failed: '失败',
  handled: '已处理',
  high: '高风险',
  normal: '正常',
  pending: '待处理',
  published: '已发布',
  queued: '排队中',
  rejected: '已拒绝',
  running: '运行中',
  succeeded: '已完成',
  warning: '预警',
}

const safetyLevelLabels: Record<string, string> = {
  normal: '普通',
  strict: '严格',
  high: '高',
}

export function aiTagType(status?: string): AiTagType {
  if (status === 'succeeded' || status === 'approved' || status === 'enabled' || status === 'published' || status === 'handled') {
    return 'success'
  }
  if (status === 'queued' || status === 'running' || status === 'pending' || status === 'warning') {
    return 'warning'
  }
  if (status === 'blocked' || status === 'failed' || status === 'rejected' || status === 'high') {
    return 'danger'
  }

  return 'info'
}

export function shouldPollGenerationStatus(status: GenerationStatus): boolean {
  return status === 'queued' || status === 'running' || status === 'pending'
}

export function canApproveAiResult(row: AiReviewState): boolean {
  return row.safety_status !== 'blocked' && row.review_status === 'pending'
}

export function containsRawSql(text: string): boolean {
  return /\b(?:select|insert|update|delete|drop|alter|truncate)\b/i.test(text)
}

export function aiStatusLabel(status?: string): string {
  return status ? (statusLabels[status] ?? status) : ''
}

export function aiFeatureSafetyLevelLabel(level?: string): string {
  return level ? (safetyLevelLabels[level] ?? level) : ''
}

export function aiModelConfigActionsByPermission(permissions: string[]): AiModelConfigActions {
  return {
    canSaveModel: permissions.includes('education:ai:model-config:save') || permissions.includes('*'),
    canSaveFeature: permissions.includes('education:ai:feature-setting:save') || permissions.includes('*'),
  }
}

export function aiErrorTitle(code?: number): string {
  if (code === 403) {
    return '暂无操作权限'
  }
  if (code === 422) {
    return '数据校验失败'
  }
  if (code === 409) {
    return '状态冲突'
  }

  return ''
}

export function maskedSecret(): string {
  return '********'
}
