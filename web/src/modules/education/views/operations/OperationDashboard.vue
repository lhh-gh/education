<script setup lang="ts">
import type { DailyOperationMetric, OperationOverview, OperationTrendRow, RenewalAlertSummary } from '../../api/operations/dashboard.ts'
import { getConsumptionTrend, getDailyOperationMetrics, getOperationOverview, getRenewalAlertSummary } from '../../api/operations/dashboard.ts'
import { buildDashboardChartParams, operationDashboardMetricItems } from './operationRules.ts'

defineOptions({ name: 'EducationOperationDashboard' })

const loading = ref(false)
const errorText = ref('')
const overview = ref<OperationOverview | null>(null)
const trend = ref<OperationTrendRow[]>([])
const renewal = ref<RenewalAlertSummary | null>(null)
const daily = ref<DailyOperationMetric[]>([])
const filter = reactive({ tenant_id: undefined as number | undefined, campus_id: undefined as number | undefined, start_at: '', end_at: '' })
const metrics = computed(() => operationDashboardMetricItems(overview.value?.metrics))

async function loadDashboard() {
  loading.value = true
  try {
    const params = buildDashboardChartParams(filter)
    const [overviewResponse, trendResponse, renewalResponse, dailyResponse] = await Promise.all([
      getOperationOverview(params),
      getConsumptionTrend(params),
      getRenewalAlertSummary(params),
      getDailyOperationMetrics(params),
    ])
    overview.value = overviewResponse.data
    trend.value = trendResponse.data.list
    renewal.value = renewalResponse.data
    daily.value = dailyResponse.data.list
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Operation dashboard loading failed'
  }
  finally {
    loading.value = false
  }
}

onMounted(loadDashboard)
</script>

<template>
  <div class="mine-layout education-operation-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Operation Dashboard</span>
          <el-button type="primary" @click="loadDashboard">
            Refresh
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="filter" class="search-form">
        <el-form-item label="Campus">
          <el-input-number v-model="filter.campus_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Date Range">
          <el-date-picker v-model="filter.start_at" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" placeholder="Start" />
          <el-date-picker v-model="filter.end_at" class="ml-2" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" placeholder="End" />
        </el-form-item>
      </el-form>
      <el-skeleton v-if="loading" :rows="8" animated />
      <template v-else>
        <div class="metric-grid">
          <div v-for="item in metrics" :key="item.title" class="metric-tile">
            <div class="metric-title">
              {{ item.title }}
            </div>
            <div class="metric-value">
              {{ item.value }} <span v-if="item.unit">{{ item.unit }}</span>
            </div>
          </div>
        </div>
        <el-row :gutter="16" class="mt-4">
          <el-col :span="12">
            <el-table :data="trend" row-key="date">
              <el-table-column prop="date" label="Date" />
              <el-table-column prop="consumed_units" label="Consumed" />
              <el-table-column prop="review_count" label="Reviews" />
              <template #empty>
                <el-empty description="No trend data" />
              </template>
            </el-table>
          </el-col>
          <el-col :span="12">
            <el-table :data="daily" row-key="date">
              <el-table-column prop="date" label="Date" />
              <el-table-column prop="lesson_change_count" label="Changes" />
              <el-table-column prop="makeup_count" label="Make-ups" />
              <el-table-column prop="renewal_alert_count" label="Renewals" />
              <template #empty>
                <el-empty description="No metric data" />
              </template>
            </el-table>
          </el-col>
        </el-row>
        <el-alert v-if="renewal" class="mt-4" type="warning" show-icon :closable="false" :title="`Urgent ${renewal.urgent_count}, Warning ${renewal.warning_count}, Normal ${renewal.normal_count}`" />
      </template>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-operation-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }

  .page-alert,
  .search-form { margin-bottom: 12px; }
  .metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; }
  .metric-tile { min-height: 88px; padding: 14px; border: 1px solid var(--el-border-color); border-radius: 6px; }
  .metric-title { font-size: 13px; color: var(--el-text-color-secondary); }
  .metric-value { margin-top: 8px; font-size: 22px; font-weight: 600; }
}
</style>
