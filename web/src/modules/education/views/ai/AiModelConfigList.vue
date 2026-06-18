<script setup lang="ts">
import type { AiModelConfigRecord } from '../../api/ai/config.ts'
import { pageModelConfigs, saveFeatureSetting, saveModelConfig } from '../../api/ai/config.ts'
import { aiErrorTitle, aiTagType, maskedSecret } from './aiRules.ts'

defineOptions({ name: 'EducationAiModelConfigList' })

const loading = ref(false)
const rows = ref<AiModelConfigRecord[]>([])
const total = ref(0)
const errorText = ref('')
const successText = ref('')
const search = reactive({ page: 1, pageSize: 20, status: '' })
const form = reactive({ config_code: '', provider: 'openai', model_name: '', api_key: '', status: 'enabled' })
const featureForm = reactive({ feature_code: 'lesson_comment', feature_name: 'Lesson comment draft', model_config_id: 0, enabled: true, review_required: true, safety_level: 'normal' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageModelConfigs(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = aiErrorTitle(error?.code) || error?.message || 'AI model configs loading failed'
  }
  finally {
    loading.value = false
  }
}

async function saveConfig() {
  await saveModelConfig({ ...form })
  successText.value = 'Saved'
  await loadRows()
}

async function saveFeature() {
  await saveFeatureSetting({ ...featureForm })
  successText.value = 'Feature saved'
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-ai-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>AI Model Configs</span>
          <el-button type="primary" @click="saveConfig">
            Save
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" @close="successText = ''" />
      <el-form inline>
        <el-form-item label="Code">
          <el-input v-model="form.config_code" />
        </el-form-item>
        <el-form-item label="Provider">
          <el-input v-model="form.provider" />
        </el-form-item>
        <el-form-item label="Model">
          <el-input v-model="form.model_name" />
        </el-form-item>
        <el-form-item label="API Key">
          <el-input v-model="form.api_key" type="password" show-password />
        </el-form-item>
      </el-form>
      <el-form inline>
        <el-form-item label="Feature">
          <el-input v-model="featureForm.feature_code" />
        </el-form-item>
        <el-form-item label="Model ID">
          <el-input-number v-model="featureForm.model_config_id" :min="0" :controls="false" />
        </el-form-item>
        <el-form-item>
          <el-checkbox v-model="featureForm.enabled">
            Enabled
          </el-checkbox>
        </el-form-item>
        <el-form-item>
          <el-button @click="saveFeature">
            Save Feature
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="config_code" label="Code" width="170" />
        <el-table-column prop="provider" label="Provider" width="130" />
        <el-table-column prop="model_name" label="Model" min-width="180" />
        <el-table-column label="Secret" width="120">
          <template #default>
            {{ maskedSecret() }}
          </template>
        </el-table-column>
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="aiTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No AI model configs" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-ai-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }
  .page-alert { margin-bottom: 12px; }
  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>
