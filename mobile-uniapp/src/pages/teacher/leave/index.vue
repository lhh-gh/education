<script setup lang="ts">
import { reactive } from 'vue'
import { onLoad, onPullDownRefresh, onReachBottom } from '@dcloudio/uni-app'
import {
  pageTeacherLeaveRequests,
  type TeacherLeaveCard,
  type TeacherLeaveStatus,
} from '@/api/academic/teacher'
import LeaveStatusBadge from '@/pages/teacher/components/LeaveStatusBadge.vue'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

type PageStatus = 'loading' | 'success' | 'empty' | 'error' | 'forbidden'

const statuses: TeacherLeaveStatus[] = ['pending', 'approved', 'rejected']
const state = reactive({
  status: 'loading' as PageStatus,
  message: '',
  filter: 'pending' as TeacherLeaveStatus,
  page: 1,
  pageSize: 20,
  total: 0,
  list: [] as TeacherLeaveCard[],
  loadingMore: false,
})

onLoad((query = {}) => {
  const status = (query as Record<string, string>).status as TeacherLeaveStatus | undefined
  state.filter = status || 'pending'
  loadFirstPage()
})
onPullDownRefresh(refresh)
onReachBottom(loadMore)

async function loadFirstPage(): Promise<void> {
  state.status = 'loading'
  state.message = ''
  state.page = 1
  try {
    const result = await pageTeacherLeaveRequests({ page: state.page, pageSize: state.pageSize, status: state.filter })
    state.list = result.list
    state.total = result.total
    state.status = state.list.length === 0 ? 'empty' : 'success'
    state.message = state.list.length === 0 ? 'No leave requests' : ''
  } catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function loadMore(): Promise<void> {
  if (state.loadingMore || state.list.length >= state.total) {
    return
  }
  state.loadingMore = true
  try {
    const nextPage = state.page + 1
    const result = await pageTeacherLeaveRequests({ page: nextPage, pageSize: state.pageSize, status: state.filter })
    state.page = nextPage
    state.list = state.list.concat(result.list)
    state.total = result.total
  } finally {
    state.loadingMore = false
  }
}

async function selectStatus(status: TeacherLeaveStatus): Promise<void> {
  state.filter = status
  await loadFirstPage()
}

function openDetail(item: TeacherLeaveCard): void {
  uni.navigateTo({ url: `/pages/teacher/leave/detail?id=${item.id}` })
}

async function retry(): Promise<void> {
  await loadFirstPage()
}

async function refresh(): Promise<void> {
  try {
    await loadFirstPage()
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
    <view class="tabs">
      <button
        v-for="status in statuses"
        :key="status"
        class="tab"
        :class="{ active: state.filter === status }"
        @tap="selectStatus(status)"
      >
        {{ status }}
      </button>
    </view>

    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading leave requests" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="retry" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="retry" />

    <view v-else class="leave-list">
      <view v-for="item in state.list" :key="item.id" class="leave-row" @tap="openDetail(item)">
        <view class="leave-copy">
          <text class="student">{{ item.student_name_snapshot || item.leave_no }}</text>
          <text class="reason">{{ item.reason }}</text>
          <text class="time">{{ item.lesson_start_at || item.requested_at || '' }}</text>
        </view>
        <LeaveStatusBadge :status="item.status" />
      </view>
      <text v-if="state.loadingMore" class="bottom-text">Loading more</text>
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

.tabs {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10rpx;
  margin-bottom: 18rpx;
}

.tab {
  min-height: 64rpx;
  padding: 0;
  border-radius: 8rpx;
  background: #f1f3f7;
  color: #334155;
  font-size: 24rpx;
}

.active {
  background: #2456a6;
  color: #ffffff;
}

.leave-list {
  display: flex;
  flex-direction: column;
  gap: 16rpx;
}

.leave-row {
  display: flex;
  min-height: 136rpx;
  justify-content: space-between;
  gap: 18rpx;
  padding: 22rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
}

.leave-copy {
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
.bottom-text {
  color: #5f6f86;
}

.bottom-text {
  text-align: center;
}
</style>
