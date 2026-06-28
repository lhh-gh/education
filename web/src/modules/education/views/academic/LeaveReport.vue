<script setup lang="ts">
import type { LeaveReportParams, LeaveReportRow, LeaveReportSummary } from '../../api/academic/report.ts'
import { pageLeaveReport } from '../../api/academic/report.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import ReportDateRangeFilter from './components/ReportDateRangeFilter.vue'
import ReportMetricCard from './components/ReportMetricCard.vue'
import ReportStateBlock from './components/ReportStateBlock.vue'
import ReportTableToolbar from './components/ReportTableToolbar.vue'
import { leaveSourceLabel, leaveStatusLabel, leaveTypeLabel } from './leaveMakeupRescheduleRules.ts'
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
    errorText.value = error?.message ?? '请假报表加载失败'
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
  Object.assign(search, { page: 1, pageSize: 20, class_id: undefined, teacher_id: undefined, course_id: undefined, source: undefined, leave_type: undefined, status: undefined, ...quickReportRange('this_month') })
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-report-page pt-3">
    <el-card shadow="never">
      <template #header>请假报表</template>
      <ReportDateRangeFilter v-model="search" require-range @submit="handleFilter" @reset="handleReset" />
      <el-form :inline="true" :model="search" class="report-extra-filters">
        <el-form-item label="班级ID"><el-input-number v-model="search.class_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="教师ID"><el-input-number v-model="search.teacher_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="课程ID"><el-input-number v-model="search.course_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="来源"><el-select v-model="search.source" clearable style="width: 130px;"><el-option label="员工" value="staff" /><el-option label="家长" value="guardian" /><el-option label="教师" value="teacher" /></el-select></el-form-item>
        <el-form-item label="类型"><el-select v-model="search.leave_type" clearable style="width: 130px;"><el-option label="病假" value="sick" /><el-option label="事假" value="personal" /><el-option label="校内活动" value="school" /><el-option label="其他" value="other" /></el-select></el-form-item>
        <el-form-item label="状态"><el-select v-model="search.status" clearable style="width: 160px;"><el-option label="待审批" value="pending" /><el-option label="已通过" value="approved" /><el-option label="已拒绝" value="rejected" /><el-option label="已取消" value="cancelled" /><el-option label="已安排补课" value="makeup_scheduled" /><el-option label="已关闭" value="closed" /></el-select></el-form-item>
      </el-form>
      <ReportStateBlock v-if="state" :state="state" :message="errorText" @retry="loadRows" />
      <template v-else>
        <div class="metric-grid"><ReportMetricCard v-for="item in summaryItems" :key="item.title" :title="item.title" :value="item.value" /></div>
        <ReportTableToolbar title="请假明细" :total="total" :loading="loading" @refresh="loadRows" />
        <el-table :data="rows" row-key="leave_id">
          <el-table-column prop="leave_no" label="请假编号" width="180" />
          <el-table-column label="来源" width="110"><template #default="{ row }">{{ leaveSourceLabel(row.source) }}</template></el-table-column>
          <el-table-column label="类型" width="110"><template #default="{ row }">{{ leaveTypeLabel(row.leave_type) }}</template></el-table-column>
          <el-table-column label="状态" width="150"><template #default="{ row }"><el-tag :type="reportTagType(row.status)">{{ leaveStatusLabel(row.status) }}</el-tag></template></el-table-column>
          <el-table-column prop="student_name" label="学员" min-width="130" />
          <el-table-column prop="class_name" label="班级" min-width="130" />
          <el-table-column prop="teacher_name" label="教师" min-width="130" />
          <el-table-column prop="course_name" label="课程" min-width="130" />
          <el-table-column prop="lesson_title" label="课次" min-width="160" />
          <el-table-column prop="requested_at" label="申请时间" width="180" />
          <el-table-column prop="reviewed_at" label="审批时间" width="180" />
          <el-table-column label="补课" width="100"><template #default="{ row }">{{ row.makeup_required ? '是' : '否' }}</template></el-table-column>
          <el-table-column label="操作" width="110" fixed="right">
            <template #default="{ row }">
              <router-link v-if="canShowReportDrillLink(canDrill, row.leave_id)" :to="`/education/academic/leave-requests?leave_id=${row.leave_id}`">详情</router-link>
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
