<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { TeacherWorkloadSummary } from '@/api/operations/teacher'
import { getWorkloadSummary } from '@/api/operations/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  month: '',
  summary: null as TeacherWorkloadSummary | null,
})

onLoad((query = {}) => {
  state.month = (query as Record<string, string>).month || currentMonth()
  loadSummary()
})
onPullDownRefresh(refresh)

async function loadSummary(): Promise<void> {
  state.status = 'loading'
  try {
    state.summary = await getWorkloadSummary({ month: state.month })
    state.status = state.summary.row_count === 0 ? 'empty' : 'success'
    state.message = state.summary.row_count === 0 ? 'No workload records' : ''
  }
  catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function refresh(): Promise<void> {
  try {
    await loadSummary()
  }
  finally {
    uni.stopPullDownRefresh?.()
  }
}

function currentMonth(): string {
  return new Date().toISOString().slice(0, 7)
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
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading workload" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="loadSummary" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="loadSummary" />
    <view v-else-if="state.summary" class="grid">
      <view class="tile"><text>Month</text><strong>{{ state.month }}</strong></view>
      <view class="tile"><text>Credits</text><strong>{{ state.summary.total_credits }}</strong></view>
      <view class="tile"><text>Rows</text><strong>{{ state.summary.row_count }}</strong></view>
      <view class="tile"><text>Present</text><strong>{{ state.summary.present_count }}</strong></view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16rpx; }
.tile { display: flex; min-height: 142rpx; flex-direction: column; justify-content: center; gap: 8rpx; padding: 22rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.tile text { color: #5f6f86; }
.tile strong { font-size: 34rpx; }
</style>
