<script setup lang="ts">
import type { GenerationResultRecord } from '../../api/ai/generation.ts'
import { approveGenerationResult, pageGenerationResults } from '../../api/ai/generation.ts'
import { aiErrorTitle, aiTagType, canApproveAiResult } from './aiRules.ts'

defineOptions({ name: 'EducationAiReviewList' })

const loading = ref(false)
const rows = ref<GenerationResultRecord[]>([])
const total = ref(0)
const errorText = ref('')
const note = ref('')

async function loadRows() {
  loading.value = true
  try {
    const response = await pageGenerationResults({ page: 1, pageSize: 20 })
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = aiErrorTitle(error?.code) || error?.message || 'AI reviews loading failed'
  }
  finally {
    loading.value = false
  }
}

async function approve(row: GenerationResultRecord) {
  await approveGenerationResult(row.id, { review_note: note.value })
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-ai-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>AI Reviews</span>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-input v-model="note" class="page-alert" placeholder="Review note" />
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="generation_task_id" label="Task" width="100" />
        <el-table-column prop="result_text" label="Draft" min-width="260" show-overflow-tooltip />
        <el-table-column label="Safety" width="120">
          <template #default="{ row }">
            <el-tag :type="aiTagType(row.safety_status)">
              {{ row.safety_status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Review" width="120">
          <template #default="{ row }">
            <el-tag :type="aiTagType(row.review_status)">
              {{ row.review_status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="120">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="!canApproveAiResult(row)" @click="approve(row)">
              Approve
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No AI reviews" />
        </template>
      </el-table>
      <el-pagination class="page-pagination" layout="total" :total="total" />
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-ai-page {
  .page-header { font-weight: 600; }
  .page-alert { margin-bottom: 12px; }
  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>
