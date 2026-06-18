<script setup lang="ts">
import type { GenerationResultRecord, GenerationTaskRecord } from '../../api/ai/generation.ts'
import { createGenerationTask, getGenerationResult, pageGenerationTasks } from '../../api/ai/generation.ts'
import { aiErrorTitle, aiTagType, shouldPollGenerationStatus } from './aiRules.ts'

defineOptions({ name: 'EducationAiGenerationTaskList' })

const loading = ref(false)
const rows = ref<GenerationTaskRecord[]>([])
const total = ref(0)
const errorText = ref('')
const successText = ref('')
const resultVisible = ref(false)
const result = ref<GenerationResultRecord | null>(null)
const search = reactive({ page: 1, pageSize: 20, feature_code: '' })
const form = reactive({ feature_code: 'lesson_comment', business_type: 'lesson_student', business_id: undefined as number | undefined })
let timer: ReturnType<typeof setInterval> | undefined

function stopPolling() {
  if (timer) {
    clearInterval(timer)
    timer = undefined
  }
}

function refreshPolling() {
  stopPolling()
  if (rows.value.some(row => shouldPollGenerationStatus(row.status))) {
    timer = setInterval(loadRows, 5000)
  }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageGenerationTasks(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
    refreshPolling()
  }
  catch (error: any) {
    errorText.value = aiErrorTitle(error?.code) || error?.message || 'Generation tasks loading failed'
  }
  finally {
    loading.value = false
  }
}

async function createTask() {
  await createGenerationTask({ ...form })
  successText.value = 'Queued'
  await loadRows()
}

async function openResult(row: GenerationTaskRecord) {
  const response = await getGenerationResult(row.id)
  result.value = response.data
  resultVisible.value = true
}

onMounted(loadRows)
onBeforeUnmount(stopPolling)
</script>

<template>
  <div class="mine-layout education-ai-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Generation Tasks</span>
          <el-button type="primary" @click="createTask">
            Queue
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" @close="successText = ''" />
      <el-form inline>
        <el-form-item label="Feature">
          <el-input v-model="form.feature_code" />
        </el-form-item>
        <el-form-item label="Business">
          <el-input v-model="form.business_type" />
        </el-form-item>
        <el-form-item label="Business ID">
          <el-input-number v-model="form.business_id" :min="1" :controls="false" />
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="task_no" label="Task No" min-width="190" />
        <el-table-column prop="feature_code" label="Feature" width="150" />
        <el-table-column prop="business_type" label="Business" width="150" />
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="aiTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="120">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="row.status !== 'succeeded'" @click="openResult(row)">
              Result
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No generation tasks" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <el-drawer v-model="resultVisible" title="Generation Result" size="42%">
      <el-alert v-if="result?.safety_status === 'blocked'" type="error" show-icon :closable="false" title="State conflict" />
      <el-input :model-value="result?.result_text" type="textarea" :rows="12" readonly />
    </el-drawer>
  </div>
</template>

<style scoped lang="scss">
.education-ai-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }
  .page-alert { margin-bottom: 12px; }
  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>
