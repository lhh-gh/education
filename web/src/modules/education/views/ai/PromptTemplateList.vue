<script setup lang="ts">
import type { PromptTemplateRecord } from '../../api/ai/prompt.ts'
import { pagePromptTemplates, publishPromptTemplate, savePromptTemplate } from '../../api/ai/prompt.ts'
import { aiErrorTitle, aiTagType } from './aiRules.ts'

defineOptions({ name: 'EducationAiPromptTemplateList' })

const loading = ref(false)
const rows = ref<PromptTemplateRecord[]>([])
const total = ref(0)
const errorText = ref('')
const successText = ref('')
const search = reactive({ page: 1, pageSize: 20, feature_code: '' })
const form = reactive({ template_code: '', feature_code: 'lesson_comment', template_name: '', version: 1, system_prompt: '', user_prompt: '', status: 'draft' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pagePromptTemplates(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = aiErrorTitle(error?.code) || error?.message || 'Prompt templates loading failed'
  }
  finally {
    loading.value = false
  }
}

async function savePrompt() {
  await savePromptTemplate({ ...form })
  successText.value = 'Saved'
  await loadRows()
}

async function publish(row: PromptTemplateRecord) {
  await publishPromptTemplate(row.template_code, { version: row.version })
  successText.value = 'Published'
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-ai-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Prompt Templates</span>
          <el-button type="primary" @click="savePrompt">
            Save
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" @close="successText = ''" />
      <el-form inline>
        <el-form-item label="Code">
          <el-input v-model="form.template_code" />
        </el-form-item>
        <el-form-item label="Feature">
          <el-input v-model="form.feature_code" />
        </el-form-item>
        <el-form-item label="Name">
          <el-input v-model="form.template_name" />
        </el-form-item>
        <el-form-item label="Version">
          <el-input-number v-model="form.version" :min="1" :controls="false" />
        </el-form-item>
      </el-form>
      <el-form>
        <el-form-item label="System Prompt">
          <el-input v-model="form.system_prompt" type="textarea" :rows="2" />
        </el-form-item>
        <el-form-item label="User Prompt">
          <el-input v-model="form.user_prompt" type="textarea" :rows="2" />
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="template_code" label="Code" width="170" />
        <el-table-column prop="feature_code" label="Feature" width="150" />
        <el-table-column prop="template_name" label="Name" min-width="180" />
        <el-table-column prop="version" label="Version" width="90" />
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="aiTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="120">
          <template #default="{ row }">
            <el-button link type="primary" @click="publish(row)">
              Publish
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No prompt templates" />
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
