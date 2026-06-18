<script setup lang="ts">
import { reactive } from 'vue'
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import {
  approveTeacherLeaveRequest,
  getTeacherLeaveRequest,
  rejectTeacherLeaveRequest,
  type TeacherLeaveDetail,
} from '@/api/academic/teacher'
import LeaveStatusBadge from '@/pages/teacher/components/LeaveStatusBadge.vue'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

type PageStatus = 'loading' | 'success' | 'empty' | 'error' | 'forbidden' | 'conflict'

const state = reactive({
  status: 'loading' as PageStatus,
  message: '',
  id: 0,
  detail: null as TeacherLeaveDetail | null,
  reviewRemark: '',
  submitting: false,
})

onLoad((query = {}) => {
  state.id = Number((query as Record<string, string>).id || 0)
  loadDetail()
})
onPullDownRefresh(refresh)

async function loadDetail(): Promise<void> {
  state.status = 'loading'
  state.message = ''
  try {
    if (state.id <= 0) {
      throw new Error('Leave id is required')
    }
    state.detail = await getTeacherLeaveRequest(state.id)
    state.status = 'success'
  } catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function approve(): Promise<void> {
  await review('approve')
}

async function reject(): Promise<void> {
  await review('reject')
}

async function review(action: 'approve' | 'reject'): Promise<void> {
  if (state.submitting) {
    return
  }
  if (state.reviewRemark.trim() === '') {
    state.message = 'review_remark is required'
    return
  }

  state.submitting = true
  try {
    state.detail = action === 'approve'
      ? await approveTeacherLeaveRequest(state.id, { review_remark: state.reviewRemark })
      : await rejectTeacherLeaveRequest(state.id, { review_remark: state.reviewRemark })
    state.status = 'success'
    state.message = ''
  } catch (error) {
    state.status = (error as { code?: number })?.code === 409 ? 'conflict' : isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  } finally {
    state.submitting = false
  }
}

async function retry(): Promise<void> {
  await loadDetail()
}

async function refresh(): Promise<void> {
  try {
    await loadDetail()
  } finally {
    uni.stopPullDownRefresh?.()
  }
}

function isForbidden(error: unknown): boolean {
  const code = (error as { code?: number })?.code

  return code === 401 || code === 403
}

function errorMessage(error: unknown): string {
  return (error as { message?: string })?.message || 'Request failed'
}
</script>

<template>
  <view class="page">
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading leave detail" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="retry" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="retry" />

    <view v-else-if="state.detail" class="content">
      <view class="detail-head">
        <view class="head-copy">
          <text class="student">{{ state.detail.student_name_snapshot || state.detail.leave_no }}</text>
          <text class="reason">{{ state.detail.reason }}</text>
          <text class="time">{{ state.detail.lesson_start_at || state.detail.requested_at || '' }}</text>
        </view>
        <LeaveStatusBadge :status="state.detail.status" />
      </view>

      <view v-if="state.status === 'conflict'" class="conflict">
        <text>{{ state.message }}</text>
        <button class="secondary-action" @tap="retry">Refresh</button>
      </view>

      <view v-if="state.detail.status === 'pending'" class="review">
        <textarea v-model="state.reviewRemark" class="remark" maxlength="500" placeholder="Review remark" />
        <text v-if="state.message" class="message">{{ state.message }}</text>
        <view class="actions">
          <button class="primary-action" :disabled="state.submitting" @tap="approve">Approve</button>
          <button class="danger-action" :disabled="state.submitting" @tap="reject">Reject</button>
        </view>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page {
  min-height: 100vh;
  padding: 24rpx;
  background: #f6f8fb;
  color: #172033;
}

.content {
  display: flex;
  flex-direction: column;
  gap: 18rpx;
}

.detail-head,
.review,
.conflict {
  padding: 22rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
}

.detail-head {
  display: flex;
  justify-content: space-between;
  gap: 18rpx;
}

.head-copy {
  display: flex;
  min-width: 0;
  flex-direction: column;
  gap: 8rpx;
}

.student {
  font-size: 30rpx;
  font-weight: 700;
}

.reason,
.time,
.message {
  color: #5f6f86;
}

.review,
.conflict {
  display: flex;
  flex-direction: column;
  gap: 16rpx;
}

.remark {
  width: 100%;
  min-height: 160rpx;
  padding: 16rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
  box-sizing: border-box;
}

.actions {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12rpx;
}

.primary-action,
.danger-action,
.secondary-action {
  min-height: 76rpx;
  border-radius: 8rpx;
  color: #ffffff;
}

.primary-action {
  background: #17623a;
}

.danger-action {
  background: #8a1f1f;
}

.secondary-action {
  width: 220rpx;
  background: #2456a6;
}
</style>
