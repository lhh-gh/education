<script setup lang="ts">
import type { AiModelConfigRecord } from '../../api/ai/config.ts'
import { pageModelConfigs, saveFeatureSetting, saveModelConfig } from '../../api/ai/config.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { aiErrorTitle, aiFeatureSafetyLevelLabel, aiModelConfigText, aiStatusLabel, aiTagType, maskedSecret } from './aiRules.ts'

defineOptions({ name: 'EducationAiModelConfigList' })

const message = useMessage()
const loading = ref(false)
const rows = ref<AiModelConfigRecord[]>([])
const total = ref(0)
const errorText = ref('')
const successText = ref('')
const search = reactive({ page: 1, pageSize: 20, status: '' })
const form = reactive({ config_code: '', provider: 'openai', model_name: '', api_key: '', status: 'enabled' })
const featureForm = reactive({ feature_code: 'lesson_comment', feature_name: '课次评语草稿', model_config_id: 0, enabled: true, review_required: true, safety_level: 'normal' })

const canSaveModel = computed(() => hasAuth('education:ai:model-config:save'))
const canSaveFeature = computed(() => hasAuth('education:ai:feature-setting:save'))

function handleError(error: any, fallback: string) {
  errorText.value = aiErrorTitle(error?.code) || error?.message || fallback
  message.error(errorText.value)
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageModelConfigs(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    handleError(error, aiModelConfigText.loadFailed)
  }
  finally {
    loading.value = false
  }
}

async function saveConfig() {
  try {
    await saveModelConfig({ ...form })
    successText.value = aiModelConfigText.modelSaved
    message.success(successText.value)
    await loadRows()
  }
  catch (error: any) {
    handleError(error, '模型配置保存失败')
  }
}

async function saveFeature() {
  try {
    await saveFeatureSetting({ ...featureForm })
    successText.value = aiModelConfigText.featureSaved
    message.success(successText.value)
  }
  catch (error: any) {
    handleError(error, '功能设置保存失败')
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-ai-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>{{ aiModelConfigText.title }}</span>
          <el-button v-if="canSaveModel" type="primary" @click="saveConfig">
            {{ aiModelConfigText.saveModel }}
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" @close="successText = ''" />
      <el-form inline>
        <el-form-item :label="aiModelConfigText.fields.configCode">
          <el-input v-model="form.config_code" placeholder="如 default_openai" />
        </el-form-item>
        <el-form-item :label="aiModelConfigText.fields.provider">
          <el-input v-model="form.provider" />
        </el-form-item>
        <el-form-item :label="aiModelConfigText.fields.modelName">
          <el-input v-model="form.model_name" />
        </el-form-item>
        <el-form-item :label="aiModelConfigText.fields.apiKey">
          <el-input v-model="form.api_key" type="password" show-password />
        </el-form-item>
        <el-form-item :label="aiModelConfigText.fields.status">
          <el-select v-model="form.status" style="width: 120px;">
            <el-option :label="aiStatusLabel('enabled')" value="enabled" />
            <el-option :label="aiStatusLabel('disabled')" value="disabled" />
          </el-select>
        </el-form-item>
      </el-form>
      <el-form inline>
        <el-form-item :label="aiModelConfigText.fields.featureCode">
          <el-input v-model="featureForm.feature_code" />
        </el-form-item>
        <el-form-item :label="aiModelConfigText.fields.featureName">
          <el-input v-model="featureForm.feature_name" />
        </el-form-item>
        <el-form-item :label="aiModelConfigText.fields.modelConfigId">
          <el-input-number v-model="featureForm.model_config_id" :min="0" :controls="false" />
        </el-form-item>
        <el-form-item>
          <el-checkbox v-model="featureForm.enabled">
            {{ aiModelConfigText.fields.enabled }}
          </el-checkbox>
        </el-form-item>
        <el-form-item>
          <el-checkbox v-model="featureForm.review_required">
            {{ aiModelConfigText.fields.reviewRequired }}
          </el-checkbox>
        </el-form-item>
        <el-form-item :label="aiModelConfigText.fields.safetyLevel">
          <el-select v-model="featureForm.safety_level" style="width: 120px;">
            <el-option :label="aiFeatureSafetyLevelLabel('normal')" value="normal" />
            <el-option :label="aiFeatureSafetyLevelLabel('strict')" value="strict" />
          </el-select>
        </el-form-item>
        <el-form-item v-if="canSaveFeature">
          <el-button @click="saveFeature">
            {{ aiModelConfigText.saveFeature }}
          </el-button>
        </el-form-item>
        <el-form-item v-else>
          <el-tag type="info">
            暂无功能保存权限
          </el-tag>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="config_code" :label="aiModelConfigText.columns.configCode" width="170" />
        <el-table-column prop="provider" :label="aiModelConfigText.columns.provider" width="130" />
        <el-table-column prop="model_name" :label="aiModelConfigText.columns.modelName" min-width="180" />
        <el-table-column :label="aiModelConfigText.columns.secret" width="120">
          <template #default>
            {{ maskedSecret() }}
          </template>
        </el-table-column>
        <el-table-column :label="aiModelConfigText.columns.status" width="120">
          <template #default="{ row }">
            <el-tag :type="aiTagType(row.status)">
              {{ aiStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty :description="aiModelConfigText.empty" />
        </template>
      </el-table>
      <el-pagination
        v-model:current-page="search.page"
        v-model:page-size="search.pageSize"
        class="page-pagination"
        layout="total, sizes, prev, pager, next"
        :total="total"
        @change="loadRows"
      />
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-ai-page {
  .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
  }

  .page-alert {
    margin-bottom: 12px;
  }

  .page-pagination {
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>
