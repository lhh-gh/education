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
    <el-alert class="mb-3" type="info" title="看板指标按当前集团数据范围统计" show-icon />
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
          <span>运营指标</span>
          <el-button :loading="loading" @click="loadRows">
            刷新
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="metric_date" label="日期" width="140" />
        <el-table-column prop="campus_count" label="校区数" width="120" />
        <el-table-column prop="student_count" label="学员数" width="120" />
        <el-table-column prop="revenue_cents" label="营收金额(分)" width="160" />
        <template #empty>
          <el-empty description="暂无指标" />
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
  font-size: 13px;
  color: var(--el-text-color-secondary);
}

.metric-value {
  margin-top: 8px;
  font-size: 24px;
  font-weight: 600;
}
</style>
