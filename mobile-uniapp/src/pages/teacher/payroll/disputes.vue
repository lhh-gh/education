<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { TeacherWorkloadDispute } from '@/api/payroll/teacher'
import { getTeacherWorkloadDisputes } from '@/api/payroll/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  filter: 'all',
  rows: [] as TeacherWorkloadDispute[],
})

onLoad(loadRows)
onPullDownRefresh(refresh)

async function loadRows(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getTeacherWorkloadDisputes({ status: state.filter === 'all' ? undefined : state.filter })
    state.rows = result.list
    state.status = state.rows.length === 0 ? 'empty' : 'success'
    state.message = state.rows.length === 0 ? 'No workload disputes' : ''
  }
  catch (error) {
    state.rows = []
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

function switchFilter(status: string): void {
  state.filter = status
  loadRows()
}

async function refresh(): Promise<void> {
  try {
    await loadRows()
  }
  finally {
    uni.stopPullDownRefresh?.()
  }
}

function openForm(): void {
  uni.navigateTo({ url: '/pages/teacher/payroll/dispute-form' })
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
      <button class="tab" :class="{ active: state.filter === 'all' }" @tap="switchFilter('all')">All</button>
      <button class="tab" :class="{ active: state.filter === 'pending' }" @tap="switchFilter('pending')">Pending</button>
      <button class="new-button" @tap="openForm">New</button>
    </view>
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading disputes" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="loadRows" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="loadRows" />
    <view v-else class="list">
      <view v-for="row in state.rows" :key="row.id" class="card">
        <view class="line">
          <text class="title">{{ row.dispute_type }}</text>
          <text class="badge">{{ row.status }}</text>
        </view>
        <text class="meta">{{ row.content }}</text>
        <text v-if="row.review_note" class="meta">Review {{ row.review_note }}</text>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.tabs, .list { display: flex; flex-direction: column; gap: 16rpx; }
.tabs { flex-direction: row; margin-bottom: 18rpx; }
.tab, .new-button { border-radius: 8rpx; background: #edf1f6; color: #172033; }
.tab.active, .new-button { background: #2456a6; color: #ffffff; }
.card { display: flex; min-height: 150rpx; flex-direction: column; gap: 10rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.line { display: flex; align-items: center; justify-content: space-between; gap: 12rpx; }
.title { font-size: 30rpx; font-weight: 700; }
.meta { color: #5f6f86; line-height: 1.5; }
.badge { min-width: 132rpx; padding: 4rpx 12rpx; border-radius: 8rpx; background: #fff4d8; color: #6b4e00; text-align: center; }
</style>
