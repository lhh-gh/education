<script setup lang="ts">
import type { FinanceOverview } from '../../api/finance/dashboard.ts'
import { getFinanceOverview } from '../../api/finance/dashboard.ts'
import { buildFinanceDashboardParams, centsToYuan, financePageText } from './financeRules.ts'

defineOptions({ name: 'EducationFinanceDashboard' })

const loading = ref(false)
const overview = ref<FinanceOverview | null>(null)
const filter = reactive({ start_at: '', end_at: '' })
const cards = computed(() => [
  { title: financePageText.dashboard.cards.orders, value: overview.value?.order_count ?? 0 },
  { title: financePageText.dashboard.cards.payments, value: overview.value?.payment_count ?? 0 },
  { title: financePageText.dashboard.cards.paidAmount, value: centsToYuan(overview.value?.paid_amount_cents) },
  { title: financePageText.dashboard.cards.refundAmount, value: centsToYuan(overview.value?.refund_amount_cents) },
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
          <span>{{ financePageText.dashboard.title }}</span>
          <el-button type="primary" @click="loadDashboard">
            {{ financePageText.dashboard.refresh }}
          </el-button>
        </div>
      </template>
      <el-form :inline="true" :model="filter" class="search-form">
        <el-form-item :label="financePageText.dashboard.fields.dateRange">
          <el-date-picker v-model="filter.start_at" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" :placeholder="financePageText.dashboard.fields.start" />
          <el-date-picker v-model="filter.end_at" class="ml-2" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" :placeholder="financePageText.dashboard.fields.end" />
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
