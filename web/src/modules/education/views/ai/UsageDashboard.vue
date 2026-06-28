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
    errorText.value = aiErrorTitle(error?.code) || error?.message || '用量统计加载失败'
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
          <span>用量统计</span>
          <el-button type="primary" @click="loadRows">
            刷新
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form inline>
        <el-form-item label="开始日期">
          <el-date-picker v-model="search.start_date" value-format="YYYY-MM-DD" type="date" />
        </el-form-item>
        <el-form-item label="结束日期">
          <el-date-picker v-model="search.end_date" value-format="YYYY-MM-DD" type="date" />
        </el-form-item>
        <el-form-item label="功能编码">
          <el-input v-model="search.feature_code" />
        </el-form-item>
      </el-form>
      <div class="metric-strip">
        <el-statistic title="Token 总量" :value="summary.total_tokens" />
        <el-statistic title="费用(分)" :value="summary.cost_cents" />
      </div>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="usage_date" label="日期" width="140" />
        <el-table-column prop="provider" label="服务商" width="140" />
        <el-table-column prop="model_name" label="模型名称" min-width="180" />
        <el-table-column prop="total_tokens" label="Token 数" width="120" />
        <el-table-column prop="cost_cents" label="费用" width="120" />
        <template #empty>
          <el-empty description="暂无用量日志" />
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
