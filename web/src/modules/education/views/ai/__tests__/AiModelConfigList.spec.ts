import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { describe, expect, it } from 'vitest'
import {
  aiErrorMessage,
  aiErrorTitle,
  aiFeatureSafetyLevelLabel,
  aiModelConfigActionsByPermission,
  aiModelConfigText,
  aiStatusLabel,
} from '../aiRules.ts'

describe('ai model config list', () => {
  it('keeps_backend_button_permissions_aligned_with_frontend_actions', () => {
    const projectRoot = resolve(process.cwd(), '..')
    const modelConfigController = readFileSync(resolve(projectRoot, 'app/Http/Admin/Controller/Education/Ai/AiModelConfigController.php'), 'utf8')
    const promptController = readFileSync(resolve(projectRoot, 'app/Http/Admin/Controller/Education/Ai/PromptTemplateController.php'), 'utf8')
    const recommendationController = readFileSync(resolve(projectRoot, 'app/Http/Admin/Controller/Education/Ai/AiRecommendationController.php'), 'utf8')

    expect(modelConfigController).toContain('#[Permission(code: \'education:ai:feature-setting:save\')]')
    expect(promptController).toContain('#[Permission(code: \'education:ai:prompt:publish\')]')
    expect(recommendationController).toContain('#[Permission(code: \'education:ai:recommendation:handle\')]')
  })

  it('uses_chinese_copy_for_model_config_page', () => {
    expect(aiModelConfigText.title).toBe('模型配置')
    expect(aiModelConfigText.saveModel).toBe('保存模型')
    expect(aiModelConfigText.saveFeature).toBe('保存功能')
    expect(aiModelConfigText.empty).toBe('暂无模型配置')
    expect(aiModelConfigText.columns).toMatchObject({
      configCode: '配置编码',
      provider: '服务商',
      modelName: '模型名称',
      secret: '密钥',
      status: '状态',
    })
  })

  it('maps_ai_status_and_permission_errors_to_chinese', () => {
    expect(aiStatusLabel('enabled')).toBe('启用')
    expect(aiStatusLabel('disabled')).toBe('停用')
    expect(aiStatusLabel('pending')).toBe('待处理')
    expect(aiFeatureSafetyLevelLabel('normal')).toBe('普通')
    expect(aiFeatureSafetyLevelLabel('strict')).toBe('严格')
    expect(aiErrorTitle(403)).toBe('暂无操作权限')
    expect(aiErrorTitle(422)).toBe('数据校验失败')
    expect(aiErrorTitle(409)).toBe('状态冲突')
    expect(aiErrorMessage({ message: 'Permission denied' }, '保存失败')).toBe('暂无操作权限')
    expect(aiErrorMessage({}, '保存失败')).toBe('保存失败')
  })

  it('derives_save_actions_from_ai_permissions', () => {
    expect(aiModelConfigActionsByPermission([])).toMatchObject({
      canSaveModel: false,
      canSaveFeature: false,
    })
    expect(aiModelConfigActionsByPermission(['education:ai:model-config:save'])).toMatchObject({
      canSaveModel: true,
      canSaveFeature: false,
    })
    expect(aiModelConfigActionsByPermission(['education:ai:feature-setting:save'])).toMatchObject({
      canSaveModel: false,
      canSaveFeature: true,
    })
  })

  it('removes_legacy_english_copy_from_ai_vue_pages', () => {
    const aiViewDir = resolve(process.cwd(), 'src/modules/education/views/ai')
    const legacyCopies = [
      'AI Model Configs',
      'Prompt Templates',
      'Generation Tasks',
      'AI Reviews',
      'Risk Scores',
      'Data Q&A',
      'AI Recommendations',
      'Usage Dashboard',
      'Safety Events',
      'Save Feature',
      'No AI model configs',
      'No prompt templates',
      'No generation tasks',
      'No AI reviews',
      'No risk scores',
      'No data questions',
      'No AI recommendations',
      'No usage logs',
      'No safety events',
      'Permission denied',
    ]

    const vueFiles = [
      'AiModelConfigList.vue',
      'PromptTemplateList.vue',
      'GenerationTaskList.vue',
      'AiReviewList.vue',
      'RiskScoreList.vue',
      'DataQuestionWorkbench.vue',
      'AiRecommendationList.vue',
      'UsageDashboard.vue',
      'SafetyEventList.vue',
    ]

    for (const file of vueFiles) {
      const content = readFileSync(resolve(aiViewDir, file), 'utf8')

      for (const copy of legacyCopies) {
        expect(content, `${file} still contains [${copy}]`).not.toContain(copy)
      }
    }
  })
})
