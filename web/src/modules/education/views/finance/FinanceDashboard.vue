<script setup lang="ts">
import type { FinanceOverview } from '../../api/finance/dashboard.ts'
import { getFinanceOverview } from '../../api/finance/dashboard.ts'
import { buildFinanceDashboardParams, centsToYuan } from './financeRules.ts'

defineOptions({ name: 'EducationFinanceDashboard' })

const loading = ref(false)
const overview = ref<FinanceOverview | null>(null)
const filter = reactive({ tenant_id: undefined as number | undefined, campus_id: undefined as number | undefined, start_at: '', end_at: '' })
const cards = computed(() => [
  { title: 'Orders', value: overview.value?.order_count ?? 0 },
  { title: 'Payments', value: overview.value?.payment_count ?? 0 },
  { title: 'Paid Amount', value: centsToYuan(overview.value?.paid_amount_cents) },
  { title: 'Refund Amount', value: centsToYuan(overview.value?.refund_amount_cents) },
])

async function loadDashboard() {
  loading.value = true
  try {
    const response = await getFinanceOverview(buildFinanceDashboardParams(filter) as any)
    overview.value = response.data
  }
  finally {
    loading.value = false
  }
}

onMounted(loadDashboard)
</script>

<template>
  <div class="mine-layout education-finance-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Finance Dashboard</span>
          <el-button type="primary" @click="loadDashboard">
            Refresh
          </el-button>
        </div>
      </template>
      <el-form :inline="true" :model="filter" class="search-form">
        <el-form-item label="Campus">
          <el-input-number v-model="filter.campus_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Date Range">
          <el-date-picker v-model="filter.start_at" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" placeholder="Start" />
          <el-date-picker v-model="filter.end_at" class="ml-2" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" placeholder="End" />
        </el-form-item>
      </el-form>
      <el-skeleton v-if="loading" :rows="4" animated />
      <div v-else class="metric-grid">
        <div v-for="card in cards" :key="card.title" class="metric-tile">
          <div class="metric-title">
            {{ card.title }}
          </div>
          <div class="metric-value">
            {{ card.value }}
          </div>
        </div>
      </div>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-finance-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }
  .search-form { margin-bottom: 12px; }
  .metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
  .metric-tile { min-height: 88px; padding: 14px; border: 1px solid var(--el-border-color); border-radius: 6px; }
  .metric-title { font-size: 13px; color: var(--el-text-color-secondary); }
  .metric-value { margin-top: 8px; font-size: 22px; font-weight: 600; }
}
</style>
