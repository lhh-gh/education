<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { GuardianRenewalAlert } from '@/api/operations/guardian'
import { getRenewalAlerts } from '@/api/operations/guardian'
import GuardianStateBlock from '@/pages/guardian/components/GuardianStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  studentId: 0,
  rows: [] as GuardianRenewalAlert[],
})

onLoad((query = {}) => {
  state.studentId = Number((query as Record<string, string>).student_id || uni.getStorageSync('guardian_selected_student_id') || 0)
  loadRows()
})
onPullDownRefresh(refresh)

async function loadRows(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getRenewalAlerts({ student_id: state.studentId })
    state.rows = result.list
    state.status = state.rows.length === 0 ? 'empty' : 'success'
    state.message = state.rows.length === 0 ? 'No renewal alerts' : ''
  }
  catch (error) {
    state.rows = []
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function refresh(): Promise<void> {
  try {
    await loadRows()
  }
  finally {
    uni.stopPullDownRefresh?.()
  }
}

function levelClass(level: string): string {
  return `level-${level}`
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
    <GuardianStateBlock v-if="state.status === 'loading'" state="loading" message="Loading renewal alerts" />
    <GuardianStateBlock v-else-if="state.status === 'empty'" state="empty" :message="state.message" />
    <GuardianStateBlock v-else-if="state.status === 'forbidden'" state="forbidden" :message="state.message" @retry="loadRows" />
    <GuardianStateBlock v-else-if="state.status === 'error'" state="error" :message="state.message" @retry="loadRows" />
    <view v-else class="list">
      <view v-for="row in state.rows" :key="row.id" class="card" :class="levelClass(row.alert_level)">
        <text class="title">{{ row.alert_type }}</text>
        <text class="meta">Course {{ row.course_id }}</text>
        <text class="meta">Trigger {{ row.trigger_value || '-' }} / Threshold {{ row.threshold_value || '-' }}</text>
        <text class="badge">{{ row.alert_level }}</text>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f7f8f3; color: #172033; }
.list { display: flex; flex-direction: column; gap: 16rpx; }
.card { display: flex; min-height: 166rpx; flex-direction: column; gap: 8rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.meta { color: #5f6f86; }
.badge { width: 150rpx; padding: 4rpx 12rpx; border-radius: 8rpx; text-align: center; }
.level-urgent .badge { background: #ffe3e3; color: #8a1f1f; }
.level-warning .badge { background: #fff2cc; color: #6b4e00; }
.level-normal .badge { background: #e7f6ee; color: #17623a; }
</style>
