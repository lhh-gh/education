<script setup lang="ts">
import type { UsageLogRecord, UsageSummary } from '../../api/ai/usage.ts'
import { getUsageSummary, pageUsageLogs } from '../../api/ai/usage.ts'
import { aiErrorTitle } from './aiRules.ts'

defineOptions({ name: 'EducationAiUsageDashboard' })

const loading = ref(false)
const rows = ref<UsageLogRecord[]>([])
const total = ref(0)
const errorText = ref('')
const summary = ref<UsageSummary>({ total_tokens: 0, cost_cents: 0 })
const search = reactive({ page: 1, pageSize: 20, start_date: '', end_date: '', feature_code: '' })

async function loadRows() {
  loading.value = true
  try {
    const [summaryResponse, logsResponse] = await Promise.all([
      getUsageSummary(search),
      pageUsageLogs(search),
    ])
    summary.value = summaryResponse.data
    rows.value = logsResponse.data.list
    total.value = logsResponse.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = aiErrorTitle(error?.code) || error?.message || 'AI usage loading failed'
  }
  finally {
    loading.value = false
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-ai-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Usage Dashboard</span>
          <el-button type="primary" @click="loadRows">
            Refresh
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form inline>
        <el-form-item label="Start">
          <el-date-picker v-model="search.start_date" value-format="YYYY-MM-DD" type="date" />
        </el-form-item>
        <el-form-item label="End">
          <el-date-picker v-model="search.end_date" value-format="YYYY-MM-DD" type="date" />
        </el-form-item>
        <el-form-item label="Feature">
          <el-input v-model="search.feature_code" />
        </el-form-item>
      </el-form>
      <div class="metric-strip">
        <el-statistic title="Tokens" :value="summary.total_tokens" />
        <el-statistic title="Cost Cents" :value="summary.cost_cents" />
      </div>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="usage_date" label="Date" width="140" />
        <el-table-column prop="provider" label="Provider" width="140" />
        <el-table-column prop="model_name" label="Model" min-width="180" />
        <el-table-column prop="total_tokens" label="Tokens" width="120" />
        <el-table-column prop="cost_cents" label="Cost" width="120" />
        <template #empty>
          <el-empty description="No usage logs" />
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
  .metric-strip { display: grid; grid-template-columns: repeat(2, minmax(160px, 1fr)); gap: 16px; margin-bottom: 12px; }
  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>
