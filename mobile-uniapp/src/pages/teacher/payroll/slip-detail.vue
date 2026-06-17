<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { TeacherSalarySlip } from '@/api/payroll/teacher'
import { getTeacherSalarySlips } from '@/api/payroll/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  salaryMonth: '',
  slipId: 0,
  row: null as TeacherSalarySlip | null,
})

onLoad((query = {}) => {
  state.salaryMonth = (query as Record<string, string>).salary_month || ''
  state.slipId = Number((query as Record<string, string>).slip_id || 0)
  loadDetail()
})
onPullDownRefresh(refresh)

async function loadDetail(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getTeacherSalarySlips({ salary_month: state.salaryMonth, page: 1, pageSize: 100 })
    state.row = result.list.find(row => row.id === state.slipId) || null
    state.status = state.row ? 'success' : 'empty'
    state.message = state.row ? '' : 'Salary slip not found'
  }
  catch (error) {
    state.row = null
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function refresh(): Promise<void> {
  try {
    await loadDetail()
  }
  finally {
    uni.stopPullDownRefresh?.()
  }
}

function openDispute(): void {
  if (!state.row) {
    return
  }
  uni.navigateTo({ url: `/pages/teacher/payroll/dispute-form?salary_slip_id=${state.row.id}` })
}

function formatYuan(cents?: number): string {
  return `¥${((Number(cents || 0)) / 100).toFixed(2)}`
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
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading salary slip" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="loadDetail" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="loadDetail" />
    <view v-else-if="state.row" class="detail-card">
      <text class="title">{{ state.row.salary_month }}</text>
      <text class="amount">{{ formatYuan(state.row.payable_amount_cents) }}</text>
      <view class="line"><text>Gross</text><strong>{{ formatYuan(state.row.gross_amount_cents) }}</strong></view>
      <view class="line"><text>Deduction</text><strong>{{ formatYuan(state.row.deduction_amount_cents) }}</strong></view>
      <view class="line"><text>Adjustment</text><strong>{{ formatYuan(state.row.adjustment_amount_cents) }}</strong></view>
      <view class="line"><text>Status</text><strong>{{ state.row.status }}</strong></view>
      <button class="entry-button" @tap="openDispute">Submit dispute</button>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.detail-card { display: flex; flex-direction: column; gap: 18rpx; padding: 28rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 32rpx; font-weight: 700; }
.amount { font-size: 44rpx; font-weight: 700; color: #17623a; }
.line { display: flex; align-items: center; justify-content: space-between; gap: 16rpx; color: #5f6f86; }
.line strong { color: #172033; }
.entry-button { min-height: 76rpx; border-radius: 8rpx; background: #2456a6; color: #ffffff; }
</style>
