<script setup lang="ts">
import type { LeaveRequestPageParams, LeaveRequestRecord } from '../../api/academic/lessonChange.ts'
import { cancelLeaveRequest, pageLeaveRequests } from '../../api/academic/lessonChange.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import LeaveRequestForm from './components/LeaveRequestForm.vue'
import LeaveReviewDialog from './components/LeaveReviewDialog.vue'
import { applyLeaveReviewSuccess, canApproveLeave, canCancelLeave, canRejectLeave, leaveStatusType } from './leaveMakeupRescheduleRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'

defineOptions({ name: 'EducationAcademicLeaveRequestList' })

const message = useMessage()
const loading = ref(false)
const formVisible = ref(false)
const reviewVisible = ref(false)
const reviewAction = ref<'approve' | 'reject'>('approve')
const current = ref<LeaveRequestRecord | null>(null)
const rows = ref<LeaveRequestRecord[]>([])
const total = ref(0)
const errorText = ref('')
const search = reactive<LeaveRequestPageParams>(defaultSearch())

const canCreate = computed(() => hasAuth('education:academic:leave-request:create'))
const canApprove = computed(() => hasAuth('education:academic:leave-request:approve'))
const canReject = computed(() => hasAuth('education:academic:leave-request:reject'))
const canCancel = computed(() => hasAuth('education:academic:leave-request:cancel'))

function defaultSearch(): LeaveRequestPageParams {
  return { page: 1, pageSize: 20, tenant_id: undefined, campus_id: undefined, student_id: undefined, class_id: undefined, lesson_id: undefined, source: undefined, status: undefined, keyword: '' }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageLeaveRequests(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Leave request list loading failed'
  }
  finally {
    loading.value = false
  }
}

function handleSearch() {
  search.page = 1
  loadRows()
}

function handleReset() {
  Object.assign(search, defaultSearch())
  loadRows()
}

function openReview(row: LeaveRequestRecord, action: 'approve' | 'reject') {
  current.value = row
  reviewAction.value = action
  reviewVisible.value = true
}

function onReviewSuccess(row: LeaveRequestRecord) {
  rows.value = rows.value.map(item => item.id === row.id ? applyLeaveReviewSuccess(item, row.status, row.review_remark ?? '') : item)
  loadRows()
}

async function handleCancel(row: LeaveRequestRecord) {
  try {
    const response = await cancelLeaveRequest(row.id, 'Cancelled from PC', search.tenant_id)
    rows.value = rows.value.map(item => item.id === row.id ? response.data : item)
    message.success('Cancelled')
  }
  catch (error: any) {
    message.error(error?.message ?? 'Leave cancellation failed')
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-academic-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Leave Requests</span>
          <el-button v-if="canCreate" type="primary" @click="formVisible = true">
            Create
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="Tenant ID">
          <el-input-number v-model="search.tenant_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Campus ID">
          <el-input-number v-model="search.campus_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Student ID">
          <el-input-number v-model="search.student_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Status">
          <el-select v-model="search.status" clearable style="width: 180px;">
            <el-option label="Pending" value="pending" />
            <el-option label="Approved" value="approved" />
            <el-option label="Rejected" value="rejected" />
            <el-option label="Cancelled" value="cancelled" />
            <el-option label="Make-up Scheduled" value="makeup_scheduled" />
            <el-option label="Closed" value="closed" />
          </el-select>
        </el-form-item>
        <el-form-item label="Keyword">
          <el-input v-model="search.keyword" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            Search
          </el-button>
          <el-button @click="handleReset">
            Reset
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="leave_no" label="Leave No" width="190" />
        <el-table-column prop="source" label="Source" width="110" />
        <el-table-column prop="leave_type" label="Type" width="110" />
        <el-table-column prop="student_name" label="Student" min-width="140" />
        <el-table-column prop="lesson_id" label="Lesson ID" width="110" />
        <el-table-column prop="reason" label="Reason" min-width="180" show-overflow-tooltip />
        <el-table-column label="Status" width="150">
          <template #default="{ row }">
            <el-tag :type="leaveStatusType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="makeup_lesson_id" label="Make-up Lesson" width="140" />
        <el-table-column prop="requested_at" label="Requested At" width="180" />
        <el-table-column label="Actions" fixed="right" width="210">
          <template #default="{ row }">
            <el-button v-if="canApproveLeave(row.status, canApprove)" link type="primary" @click="openReview(row, 'approve')">
              Approve
            </el-button>
            <el-button v-if="canRejectLeave(row.status, canReject)" link type="danger" @click="openReview(row, 'reject')">
              Reject
            </el-button>
            <el-button v-if="canCancelLeave(row.status, canCancel)" link @click="handleCancel(row)">
              Cancel
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No leave requests" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <LeaveRequestForm v-model="formVisible" :tenant-id="search.tenant_id" @created="loadRows" />
    <LeaveReviewDialog v-model="reviewVisible" :action="reviewAction" :row="current" :tenant-id="search.tenant_id" @success="onReviewSuccess" />
  </div>
</template>

<style scoped lang="scss">
.education-academic-page {
  .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .page-alert,
  .search-form {
    margin-bottom: 12px;
  }

  .page-pagination {
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>
