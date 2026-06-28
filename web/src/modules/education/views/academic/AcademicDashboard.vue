<script setup lang="ts">
import type { AcademicDashboardResult, DashboardReportParams } from '../../api/academic/report.ts'
import { getAcademicDashboard } from '../../api/academic/report.ts'
import ReportDateRangeFilter from './components/ReportDateRangeFilter.vue'
import ReportMetricCard from './components/ReportMetricCard.vue'
import ReportStateBlock from './components/ReportStateBlock.vue'
import { dashboardMetricItems, quickReportRange, reportLevelLabel, reportTagType } from './reportRules.ts'

defineOptions({ name: 'EducationAcademicDashboard' })

const loading = ref(false)
const errorText = ref('')
const dashboard = ref<AcademicDashboardResult | null>(null)
const filter = reactive<DashboardReportParams>({
  ...quickReportRange('this_month'),
})
const state = computed(() => loading.value ? 'loading' : errorText.value ? 'error' : !dashboard.value ? 'empty' : null)
const metrics = computed(() => dashboardMetricItems(dashboard.value?.metrics))

async function loadDashboard() {
  loading.value = true
  try {
    const response = await getAcademicDashboard(filter)
    dashboard.value = response.data
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? '教务看板加载失败'
  }
  finally {
    loading.value = false
  }
}

function handleFilter(value: DashboardReportParams) {
  Object.assign(filter, value)
  loadDashboard()
}

function handleReset() {
  Object.assign(filter, quickReportRange('this_month'))
  loadDashboard()
}

onMounted(loadDashboard)
</script>

<template>
  <div class="mine-layout education-report-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>教务看板</span>
          <el-button type="primary" :loading="loading" @click="loadDashboard">
            刷新
          </el-button>
        </div>
      </template>
      <ReportDateRangeFilter v-model="filter" :require-range="false" @submit="handleFilter" @reset="handleReset" />
      <ReportStateBlock v-if="state" :state="state" :message="errorText" @retry="loadDashboard" />
      <template v-else-if="dashboard">
        <div class="metric-grid">
          <ReportMetricCard v-for="item in metrics" :key="item.title" :title="item.title" :value="item.value" :unit="item.unit" />
        </div>
        <el-row :gutter="16" class="mt-4">
          <el-col :span="16">
            <el-table :data="dashboard.trends" row-key="date">
              <el-table-column prop="date" label="日期" width="140" />
              <el-table-column prop="scheduled_lesson_count" label="计划课次" />
              <el-table-column prop="completed_lesson_count" label="完成课次" />
              <el-table-column prop="consumed_units" label="课消课时" />
              <template #empty>
                <el-empty description="暂无趋势数据" />
              </template>
            </el-table>
          </el-col>
          <el-col :span="8">
            <el-table :data="dashboard.alerts" row-key="type">
              <el-table-column prop="title" label="提醒事项" />
              <el-table-column label="级别" width="110">
                <template #default="{ row }">
                  <el-tag :type="reportTagType(row.level)">
                    {{ reportLevelLabel(row.level) }}
                  </el-tag>
                </template>
              </el-table-column>
              <el-table-column prop="count" label="数量" width="90" />
              <template #empty>
                <el-empty description="暂无提醒事项" />
              </template>
            </el-table>
          </el-col>
        </el-row>
      </template>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-report-page {
  .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
  }

  .metric-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin-top: 16px;
  }
}
</style>
