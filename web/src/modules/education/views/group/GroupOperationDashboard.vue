<script setup lang="ts">
import { getGroupOperationDashboard, getGroupOperationMetrics } from '../../api/group/metric.ts'
import { metricCards } from './groupRules.ts'

defineOptions({ name: 'EducationGroupOperationDashboard' })

const loading = ref(false)
const metrics = ref<Record<string, number>>({})
const rows = ref<any[]>([])
const search = reactive({ start_date: '', end_date: '' })
const cards = computed(() => metricCards(metrics.value))

async function loadRows() {
  loading.value = true
  try {
    const [dashboard, page] = await Promise.all([getGroupOperationDashboard(search), getGroupOperationMetrics(search)])
    metrics.value = dashboard.data
    rows.value = page.data.list
  }
  finally {
    loading.value = false
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-group-page pt-3">
    <el-alert class="mb-3" type="info" title="Dashboard metrics respect current group data scope" show-icon />
    <div class="metric-grid mb-3">
      <el-card v-for="item in cards" :key="item.title" shadow="never">
        <div class="metric-title">
          {{ item.title }}
        </div>
        <div class="metric-value">
          {{ item.value }}
        </div>
      </el-card>
    </div>
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Operation Metrics</span>
          <el-button :loading="loading" @click="loadRows">
            Refresh
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="metric_date" label="Date" width="140" />
        <el-table-column prop="campus_count" label="Campuses" width="120" />
        <el-table-column prop="student_count" label="Students" width="120" />
        <el-table-column prop="revenue_cents" label="Revenue Cents" width="160" />
        <template #empty>
          <el-empty description="No metrics" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>

<style scoped>
.metric-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
}

.metric-title {
  color: var(--el-text-color-secondary);
  font-size: 13px;
}

.metric-value {
  margin-top: 8px;
  font-size: 24px;
  font-weight: 600;
}
</style>
