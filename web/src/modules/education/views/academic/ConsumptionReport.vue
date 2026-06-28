<script setup lang="ts">
import type { ConsumptionReportParams, ConsumptionReportRow, ConsumptionReportSummary } from '../../api/academic/report.ts'
import { pageConsumptionReport } from '../../api/academic/report.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import ReportDateRangeFilter from './components/ReportDateRangeFilter.vue'
import ReportMetricCard from './components/ReportMetricCard.vue'
import ReportStateBlock from './components/ReportStateBlock.vue'
import ReportTableToolbar from './components/ReportTableToolbar.vue'
import { consumptionSourceLabel, ledgerDirectionLabel, ledgerStatusLabel } from './attendanceConsumptionRules.ts'
import { canShowReportDrillLink, quickReportRange, reportHasRows, reportTagType, summaryMetricItems } from './reportRules.ts'

defineOptions({ name: 'EducationConsumptionReport' })

const loading = ref(false)
const errorText = ref('')
const rows = ref<ConsumptionReportRow[]>([])
const total = ref(0)
const summary = ref<ConsumptionReportSummary | null>(null)
const search = reactive<ConsumptionReportParams>({ page: 1, pageSize: 20, ...quickReportRange('this_month') })
const state = computed(() => loading.value ? 'loading' : errorText.value ? 'error' : !reportHasRows(total.value, rows.value.length) ? 'empty' : null)
const summaryItems = computed(() => summaryMetricItems(summary.value ?? {}, ['decrease_units', 'rollback_units', 'net_units', 'active_row_count', 'reversed_row_count']))
const canDrill = computed(() => hasAuth('education:academic:consumption:detail'))

async function loadRows() {
  loading.value = true
  try {
    const response = await pageConsumptionReport(search)
    rows.value = response.data.list
    total.value = response.data.total
    summary.value = response.data.summary
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? '课消报表加载失败'
  }
  finally {
    loading.value = false
  }
}

function handleFilter(value: ConsumptionReportParams) {
  Object.assign(search, value, { page: 1 })
  loadRows()
}

function handleReset() {
  Object.assign(search, { page: 1, pageSize: 20, course_id: undefined, class_id: undefined, student_id: undefined, account_id: undefined, source_type: undefined, status: undefined, group_by: undefined, ...quickReportRange('this_month') })
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-report-page pt-3">
    <el-card shadow="never">
      <template #header>课消报表</template>
      <ReportDateRangeFilter v-model="search" require-range @submit="handleFilter" @reset="handleReset" />
      <el-form :inline="true" :model="search" class="report-extra-filters">
        <el-form-item label="课程ID"><el-input-number v-model="search.course_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="班级ID"><el-input-number v-model="search.class_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="学员ID"><el-input-number v-model="search.student_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="账户ID"><el-input-number v-model="search.account_id" :min="1" :controls="false" /></el-form-item>
      </el-form>
      <ReportStateBlock v-if="state" :state="state" :message="errorText" @retry="loadRows" />
      <template v-else>
        <div class="metric-grid"><ReportMetricCard v-for="item in summaryItems" :key="item.title" :title="item.title" :value="item.value" /></div>
        <ReportTableToolbar title="课消明细" :total="total" :loading="loading" @refresh="loadRows" />
        <el-table :data="rows" row-key="consumption_no">
          <el-table-column prop="date" label="日期" width="130" />
          <el-table-column prop="consumption_no" label="课消编号" width="190" />
          <el-table-column prop="student_name" label="学员" min-width="130" />
          <el-table-column prop="course_name" label="课程" min-width="130" />
          <el-table-column prop="class_name" label="班级" min-width="130" />
          <el-table-column prop="teacher_name" label="教师" min-width="130" />
          <el-table-column prop="lesson_title" label="课次" min-width="160" />
          <el-table-column label="来源" width="120"><template #default="{ row }">{{ consumptionSourceLabel(row.source_type) }}</template></el-table-column>
          <el-table-column label="方向" width="120"><template #default="{ row }">{{ ledgerDirectionLabel(row.direction) }}</template></el-table-column>
          <el-table-column prop="units" label="课时" width="90" />
          <el-table-column prop="before_available_units" label="变更前" width="100" />
          <el-table-column prop="after_available_units" label="变更后" width="100" />
          <el-table-column label="状态" width="110">
            <template #default="{ row }"><el-tag :type="reportTagType(row.status)">{{ ledgerStatusLabel(row.status) }}</el-tag></template>
          </el-table-column>
          <el-table-column label="操作" width="110" fixed="right">
            <template #default="{ row }">
              <router-link v-if="canShowReportDrillLink(canDrill, row.account_id)" :to="`/education/academic/consumptions?account_id=${row.account_id}`">详情</router-link>
            </template>
          </el-table-column>
        </el-table>
        <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
      </template>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin: 16px 0; }
</style>
