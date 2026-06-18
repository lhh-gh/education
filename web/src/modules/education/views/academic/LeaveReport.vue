<script setup lang="ts">
import type { LeaveReportParams, LeaveReportRow, LeaveReportSummary } from '../../api/academic/report.ts'
import { pageLeaveReport } from '../../api/academic/report.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import ReportDateRangeFilter from './components/ReportDateRangeFilter.vue'
import ReportMetricCard from './components/ReportMetricCard.vue'
import ReportStateBlock from './components/ReportStateBlock.vue'
import ReportTableToolbar from './components/ReportTableToolbar.vue'
import { canShowReportDrillLink, quickReportRange, reportHasRows, reportTagType, summaryMetricItems } from './reportRules.ts'

defineOptions({ name: 'EducationLeaveReport' })

const loading = ref(false)
const errorText = ref('')
const rows = ref<LeaveReportRow[]>([])
const total = ref(0)
const summary = ref<LeaveReportSummary | null>(null)
const search = reactive<LeaveReportParams>({ page: 1, pageSize: 20, ...quickReportRange('this_month') })
const state = computed(() => loading.value ? 'loading' : errorText.value ? 'error' : !reportHasRows(total.value, rows.value.length) ? 'empty' : null)
const summaryItems = computed(() => summaryMetricItems(summary.value ?? {}, ['total_count', 'pending_count', 'approved_count', 'rejected_count', 'cancelled_count', 'makeup_scheduled_count', 'guardian_source_count']))
const canDrill = computed(() => hasAuth('education:academic:leave-request:detail'))

async function loadRows() {
  loading.value = true
  try {
    const response = await pageLeaveReport(search)
    rows.value = response.data.list
    total.value = response.data.total
    summary.value = response.data.summary
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Leave report loading failed'
  }
  finally {
    loading.value = false
  }
}

function handleFilter(value: LeaveReportParams) {
  Object.assign(search, value, { page: 1 })
  loadRows()
}

function handleReset() {
  Object.assign(search, { page: 1, pageSize: 20, campus_id: undefined, class_id: undefined, teacher_id: undefined, course_id: undefined, source: undefined, leave_type: undefined, status: undefined, ...quickReportRange('this_month') })
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-report-page pt-3">
    <el-card shadow="never">
      <template #header>Leave Report</template>
      <ReportDateRangeFilter v-model="search" require-range @submit="handleFilter" @reset="handleReset" />
      <el-form :inline="true" :model="search" class="report-extra-filters">
        <el-form-item label="Class ID"><el-input-number v-model="search.class_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="Teacher ID"><el-input-number v-model="search.teacher_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="Course ID"><el-input-number v-model="search.course_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="Source"><el-select v-model="search.source" clearable style="width: 130px;"><el-option label="Staff" value="staff" /><el-option label="Guardian" value="guardian" /><el-option label="Teacher" value="teacher" /></el-select></el-form-item>
        <el-form-item label="Type"><el-select v-model="search.leave_type" clearable style="width: 130px;"><el-option label="Sick" value="sick" /><el-option label="Personal" value="personal" /><el-option label="School" value="school" /><el-option label="Other" value="other" /></el-select></el-form-item>
        <el-form-item label="Status"><el-select v-model="search.status" clearable style="width: 160px;"><el-option label="Pending" value="pending" /><el-option label="Approved" value="approved" /><el-option label="Rejected" value="rejected" /><el-option label="Cancelled" value="cancelled" /><el-option label="Makeup Scheduled" value="makeup_scheduled" /><el-option label="Closed" value="closed" /></el-select></el-form-item>
      </el-form>
      <ReportStateBlock v-if="state" :state="state" :message="errorText" @retry="loadRows" />
      <template v-else>
        <div class="metric-grid"><ReportMetricCard v-for="item in summaryItems" :key="item.title" :title="item.title" :value="item.value" /></div>
        <ReportTableToolbar title="Leave Rows" :total="total" :loading="loading" @refresh="loadRows" />
        <el-table :data="rows" row-key="leave_id">
          <el-table-column prop="leave_no" label="Leave No" width="180" />
          <el-table-column prop="source" label="Source" width="110" />
          <el-table-column prop="leave_type" label="Type" width="110" />
          <el-table-column label="Status" width="150"><template #default="{ row }"><el-tag :type="reportTagType(row.status)">{{ row.status }}</el-tag></template></el-table-column>
          <el-table-column prop="student_name" label="Student" min-width="130" />
          <el-table-column prop="class_name" label="Class" min-width="130" />
          <el-table-column prop="teacher_name" label="Teacher" min-width="130" />
          <el-table-column prop="course_name" label="Course" min-width="130" />
          <el-table-column prop="lesson_title" label="Lesson" min-width="160" />
          <el-table-column prop="requested_at" label="Requested At" width="180" />
          <el-table-column prop="reviewed_at" label="Reviewed At" width="180" />
          <el-table-column label="Makeup" width="100"><template #default="{ row }">{{ row.makeup_required ? 'Yes' : 'No' }}</template></el-table-column>
          <el-table-column label="Actions" width="110" fixed="right">
            <template #default="{ row }">
              <router-link v-if="canShowReportDrillLink(canDrill, row.leave_id)" :to="`/education/academic/leave-requests?leave_id=${row.leave_id}`">Detail</router-link>
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
