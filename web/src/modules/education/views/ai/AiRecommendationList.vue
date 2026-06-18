<script setup lang="ts">
import type { RecommendationTaskRecord } from '../../api/ai/recommendation.ts'
import { markRecommendationHandled, pageRecommendationTasks } from '../../api/ai/recommendation.ts'
import { aiErrorTitle, aiTagType } from './aiRules.ts'

defineOptions({ name: 'EducationAiRecommendationList' })

const loading = ref(false)
const rows = ref<RecommendationTaskRecord[]>([])
const total = ref(0)
const errorText = ref('')
const search = reactive({ page: 1, pageSize: 20, status: 'pending' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageRecommendationTasks(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = aiErrorTitle(error?.code) || error?.message || 'Recommendations loading failed'
  }
  finally {
    loading.value = false
  }
}

async function handle(row: RecommendationTaskRecord) {
  await markRecommendationHandled(row.id)
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-ai-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>AI Recommendations</span>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="recommendation_type" label="Type" width="170" />
        <el-table-column prop="target_type" label="Target" width="140" />
        <el-table-column prop="assignee_user_id" label="Assignee" width="110" />
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="aiTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="120">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="row.status === 'handled'" @click="handle(row)">
              Handle
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No AI recommendations" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
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
