<script setup lang="ts">
import type { GenerationResultRecord } from '../../api/ai/generation.ts'
import { approveGenerationResult, pageGenerationResults } from '../../api/ai/generation.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { aiErrorMessage, aiStatusLabel, aiTagType, canApproveAiResult } from './aiRules.ts'

defineOptions({ name: 'EducationAiReviewList' })

const message = useMessage()
const loading = ref(false)
const rows = ref<GenerationResultRecord[]>([])
const total = ref(0)
const errorText = ref('')
const successText = ref('')
const note = ref('')
const canApprove = computed(() => hasAuth('education:ai:review:approve'))

function handleError(error: any, fallback: string) {
  errorText.value = aiErrorMessage(error, fallback)
  message.error(errorText.value)
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageGenerationResults({ page: 1, pageSize: 20 })
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    handleError(error, 'AI 审核列表加载失败')
  }
  finally {
    loading.value = false
  }
}

async function approve(row: GenerationResultRecord) {
  try {
    await approveGenerationResult(row.id, { review_note: note.value })
    successText.value = '审核已通过'
    message.success(successText.value)
    await loadRows()
  }
  catch (error: any) {
    handleError(error, 'AI 审核处理失败')
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-ai-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>AI 审核</span>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" @close="successText = ''" />
      <el-input v-model="note" class="page-alert" placeholder="审核备注" />
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="generation_task_id" label="任务ID" width="100" />
        <el-table-column prop="result_text" label="草稿内容" min-width="260" show-overflow-tooltip />
        <el-table-column label="安全状态" width="120">
          <template #default="{ row }">
            <el-tag :type="aiTagType(row.safety_status)">
              {{ aiStatusLabel(row.safety_status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="审核状态" width="120">
          <template #default="{ row }">
            <el-tag :type="aiTagType(row.review_status)">
              {{ aiStatusLabel(row.review_status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="120">
          <template #default="{ row }">
            <el-button v-if="canApprove" link type="primary" :disabled="!canApproveAiResult(row)" @click="approve(row)">
              通过
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无 AI 审核记录" />
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
