<script setup lang="ts">
import type { LeaveRequestPageParams, LeaveRequestRecord } from '../../api/academic/lessonChange.ts'
import { cancelLeaveRequest, pageLeaveRequests } from '../../api/academic/lessonChange.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import LeaveRequestForm from './components/LeaveRequestForm.vue'
import LeaveReviewDialog from './components/LeaveReviewDialog.vue'
import { applyLeaveReviewSuccess, canApproveLeave, canCancelLeave, canRejectLeave, leaveSourceLabel, leaveStatusLabel, leaveStatusType, leaveTypeLabel } from './leaveMakeupRescheduleRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { useEducationScope } from '@/composables/education/useEducationScope.ts'

defineOptions({ name: 'EducationAcademicLeaveRequestList' })

const { scope } = useEducationScope()

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
  return { page: 1, pageSize: 20, student_id: undefined, class_id: undefined, lesson_id: undefined, source: undefined, status: undefined, keyword: '' }
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
    errorText.value = error?.message ?? '请假列表加载失败'
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
    const response = await cancelLeaveRequest(row.id, '后台取消', scope.tenant_id)
    rows.value = rows.value.map(item => item.id === row.id ? response.data : item)
    message.success('已取消')
  }
  catch (error: any) {
    message.error(error?.message ?? '请假取消失败')
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-academic-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>请假管理</span>
          <el-button v-if="canCreate" type="primary" @click="formVisible = true">
            新增
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="学员ID">
          <el-input-number v-model="search.student_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable style="width: 180px;">
            <el-option label="待审批" value="pending" />
            <el-option label="已通过" value="approved" />
            <el-option label="已拒绝" value="rejected" />
            <el-option label="已取消" value="cancelled" />
            <el-option label="已安排补课" value="makeup_scheduled" />
            <el-option label="已关闭" value="closed" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键字">
          <el-input v-model="search.keyword" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            查询
          </el-button>
          <el-button @click="handleReset">
            重置
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="leave_no" label="请假编号" width="190" />
        <el-table-column label="来源" width="110"><template #default="{ row }">{{ leaveSourceLabel(row.source) }}</template></el-table-column>
        <el-table-column label="类型" width="110"><template #default="{ row }">{{ leaveTypeLabel(row.leave_type) }}</template></el-table-column>
        <el-table-column prop="student_name" label="学员" min-width="140" />
        <el-table-column prop="lesson_id" label="课次ID" width="110" />
        <el-table-column prop="reason" label="原因" min-width="180" show-overflow-tooltip />
        <el-table-column label="状态" width="150">
          <template #default="{ row }">
            <el-tag :type="leaveStatusType(row.status)">
              {{ leaveStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="makeup_lesson_id" label="补课课次" width="140" />
        <el-table-column prop="requested_at" label="申请时间" width="180" />
        <el-table-column label="操作" fixed="right" width="210">
          <template #default="{ row }">
            <el-button v-if="canApproveLeave(row.status, canApprove)" link type="primary" @click="openReview(row, 'approve')">
              通过
            </el-button>
            <el-button v-if="canRejectLeave(row.status, canReject)" link type="danger" @click="openReview(row, 'reject')">
              拒绝
            </el-button>
            <el-button v-if="canCancelLeave(row.status, canCancel)" link @click="handleCancel(row)">
              取消
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无请假记录" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <LeaveRequestForm v-model="formVisible" :tenant-id="scope.tenant_id" @created="loadRows" />
    <LeaveReviewDialog v-model="reviewVisible" :action="reviewAction" :row="current" :tenant-id="scope.tenant_id" @success="onReviewSuccess" />
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
