<script setup lang="ts">
import type { PromptTemplateRecord } from '../../api/ai/prompt.ts'
import { pagePromptTemplates, publishPromptTemplate, savePromptTemplate } from '../../api/ai/prompt.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { aiErrorMessage, aiStatusLabel, aiTagType } from './aiRules.ts'

defineOptions({ name: 'EducationAiPromptTemplateList' })

const message = useMessage()
const loading = ref(false)
const rows = ref<PromptTemplateRecord[]>([])
const total = ref(0)
const errorText = ref('')
const successText = ref('')
const search = reactive({ page: 1, pageSize: 20, feature_code: '' })
const form = reactive({ template_code: '', feature_code: 'lesson_comment', template_name: '', version: 1, system_prompt: '', user_prompt: '', status: 'draft' })
const canSavePrompt = computed(() => hasAuth('education:ai:prompt:save'))
const canPublishPrompt = computed(() => hasAuth('education:ai:prompt:publish'))

function handleError(error: any, fallback: string) {
  errorText.value = aiErrorMessage(error, fallback)
  message.error(errorText.value)
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pagePromptTemplates(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    handleError(error, '提示词模板加载失败')
  }
  finally {
    loading.value = false
  }
}

async function savePrompt() {
  try {
    await savePromptTemplate({ ...form })
    successText.value = '提示词模板已保存'
    message.success(successText.value)
    await loadRows()
  }
  catch (error: any) {
    handleError(error, '提示词模板保存失败')
  }
}

async function publish(row: PromptTemplateRecord) {
  try {
    await publishPromptTemplate(row.template_code, { version: row.version })
    successText.value = '提示词模板已发布'
    message.success(successText.value)
    await loadRows()
  }
  catch (error: any) {
    handleError(error, '提示词模板发布失败')
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-ai-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>提示词模板</span>
          <el-button v-if="canSavePrompt" type="primary" @click="savePrompt">
            保存模板
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" @close="successText = ''" />
      <el-form inline>
        <el-form-item label="模板编码">
          <el-input v-model="form.template_code" />
        </el-form-item>
        <el-form-item label="功能编码">
          <el-input v-model="form.feature_code" />
        </el-form-item>
        <el-form-item label="模板名称">
          <el-input v-model="form.template_name" />
        </el-form-item>
        <el-form-item label="版本">
          <el-input-number v-model="form.version" :min="1" :controls="false" />
        </el-form-item>
      </el-form>
      <el-form>
        <el-form-item label="系统提示词">
          <el-input v-model="form.system_prompt" type="textarea" :rows="2" />
        </el-form-item>
        <el-form-item label="用户提示词">
          <el-input v-model="form.user_prompt" type="textarea" :rows="2" />
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="template_code" label="模板编码" width="170" />
        <el-table-column prop="feature_code" label="功能编码" width="150" />
        <el-table-column prop="template_name" label="模板名称" min-width="180" />
        <el-table-column prop="version" label="版本" width="90" />
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="aiTagType(row.status)">
              {{ aiStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="120">
          <template #default="{ row }">
            <el-button v-if="canPublishPrompt" link type="primary" @click="publish(row)">
              发布
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无提示词模板" />
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
