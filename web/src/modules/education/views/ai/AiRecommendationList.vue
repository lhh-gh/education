<script setup lang="ts">
import type { RecommendationTaskRecord } from '../../api/ai/recommendation.ts'
import { markRecommendationHandled, pageRecommendationTasks } from '../../api/ai/recommendation.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { aiErrorMessage, aiStatusLabel, aiTagType } from './aiRules.ts'

defineOptions({ name: 'EducationAiRecommendationList' })

const message = useMessage()
const loading = ref(false)
const rows = ref<RecommendationTaskRecord[]>([])
const total = ref(0)
const errorText = ref('')
const successText = ref('')
const search = reactive({ page: 1, pageSize: 20, status: 'pending' })
const canHandle = computed(() => hasAuth('education:ai:recommendation:handle'))

function handleError(error: any, fallback: string) {
  errorText.value = aiErrorMessage(error, fallback)
  message.error(errorText.value)
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageRecommendationTasks(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    handleError(error, '智能推荐加载失败')
  }
  finally {
    loading.value = false
  }
}

async function handle(row: RecommendationTaskRecord) {
  try {
    await markRecommendationHandled(row.id)
    successText.value = '推荐已采纳'
    message.success(successText.value)
    await loadRows()
  }
  catch (error: any) {
    handleError(error, '智能推荐处理失败')
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-ai-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>智能推荐</span>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" @close="successText = ''" />
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="recommendation_type" label="推荐类型" width="170" />
        <el-table-column prop="target_type" label="对象类型" width="140" />
        <el-table-column prop="assignee_user_id" label="负责人" width="110" />
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="aiTagType(row.status)">
              {{ aiStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="120">
          <template #default="{ row }">
            <el-button v-if="canHandle" link type="primary" :disabled="row.status === 'handled'" @click="handle(row)">
              采纳
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无智能推荐" />
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
