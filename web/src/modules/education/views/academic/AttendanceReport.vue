<script setup lang="ts">
import type { AttendanceReportParams, AttendanceReportRow, AttendanceReportSummary } from '../../api/academic/report.ts'
import { pageAttendanceReport } from '../../api/academic/report.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import ReportDateRangeFilter from './components/ReportDateRangeFilter.vue'
import ReportMetricCard from './components/ReportMetricCard.vue'
import ReportStateBlock from './components/ReportStateBlock.vue'
import ReportTableToolbar from './components/ReportTableToolbar.vue'
import { attendanceStatusLabel, consumePolicyLabel } from './attendanceConsumptionRules.ts'
import { canShowReportDrillLink, quickReportRange, reportHasRows, reportTagType, summaryMetricItems } from './reportRules.ts'

defineOptions({ name: 'EducationAttendanceReport' })

const loading = ref(false)
const errorText = ref('')
const rows = ref<AttendanceReportRow[]>([])
const total = ref(0)
const summary = ref<AttendanceReportSummary | null>(null)
const search = reactive<AttendanceReportParams>({ page: 1, pageSize: 20, ...quickReportRange('this_month') })
const state = computed(() => loading.value ? 'loading' : errorText.value ? 'error' : !reportHasRows(total.value, rows.value.length) ? 'empty' : null)
const summaryItems = computed(() => summaryMetricItems(summary.value ?? {}, ['total_records', 'present_count', 'late_count', 'absent_count', 'leave_count', 'attendance_rate']))
const canDrill = computed(() => hasAuth('education:academic:attendance:detail'))

async function loadRows() {
  loading.value = true
  try {
    const response = await pageAttendanceReport(search)
    rows.value = response.data.list
    total.value = response.data.total
    summary.value = response.data.summary
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? '考勤报表加载失败'
  }
  finally {
    loading.value = false
  }
}

function handleFilter(value: AttendanceReportParams) {
  Object.assign(search, value, { page: 1 })
  loadRows()
}

function handleReset() {
  Object.assign(search, { page: 1, pageSize: 20, class_id: undefined, teacher_id: undefined, course_id: undefined, attendance_status: undefined, group_by: undefined, ...quickReportRange('this_month') })
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-report-page pt-3">
    <el-card shadow="never">
      <template #header>考勤报表</template>
      <ReportDateRangeFilter v-model="search" require-range @submit="handleFilter" @reset="handleReset" />
      <el-form :inline="true" :model="search" class="report-extra-filters">
        <el-form-item label="班级ID"><el-input-number v-model="search.class_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="教师ID"><el-input-number v-model="search.teacher_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="课程ID"><el-input-number v-model="search.course_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.attendance_status" clearable style="width: 140px;">
            <el-option label="出勤" value="present" />
            <el-option label="迟到" value="late" />
            <el-option label="缺勤" value="absent" />
            <el-option label="请假" value="leave" />
          </el-select>
        </el-form-item>
      </el-form>
      <ReportStateBlock v-if="state" :state="state" :message="errorText" @retry="loadRows" />
      <template v-else>
        <div class="metric-grid">
          <ReportMetricCard v-for="item in summaryItems" :key="item.title" :title="item.title" :value="item.value" />
        </div>
        <ReportTableToolbar title="考勤明细" :total="total" :loading="loading" @refresh="loadRows" />
        <el-table :data="rows" row-key="lesson_id">
          <el-table-column prop="date" label="日期" width="130" />
          <el-table-column prop="class_name" label="班级" min-width="150" />
          <el-table-column prop="teacher_name" label="教师" min-width="140" />
          <el-table-column prop="course_name" label="课程" min-width="140" />
          <el-table-column prop="lesson_title" label="课次" min-width="180" />
          <el-table-column prop="student_name" label="学员" min-width="140" />
          <el-table-column label="状态" width="110">
            <template #default="{ row }"><el-tag :type="reportTagType(row.attendance_status)">{{ attendanceStatusLabel(row.attendance_status) }}</el-tag></template>
          </el-table-column>
          <el-table-column label="消课规则" width="120"><template #default="{ row }">{{ consumePolicyLabel(row.consume_policy) }}</template></el-table-column>
          <el-table-column prop="consumed_units" label="课时" width="100" />
          <el-table-column prop="submitted_at" label="提交时间" width="180" />
          <el-table-column label="操作" width="110" fixed="right">
            <template #default="{ row }">
              <router-link v-if="canShowReportDrillLink(canDrill, row.lesson_id)" :to="`/education/academic/attendance-review?lesson_id=${row.lesson_id}`">详情</router-link>
            </template>
          </el-table-column>
        </el-table>
        <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
      </template>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-report-page {
  .metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin: 16px 0; }
}
</style>
