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
  tab: 'all',
  rows: [] as TeacherSalarySlip[],
})

onLoad((query = {}) => {
  state.salaryMonth = (query as Record<string, string>).salary_month || currentMonth()
  loadRows()
})
onPullDownRefresh(refresh)

async function loadRows(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getTeacherSalarySlips({ salary_month: state.salaryMonth, status: state.tab === 'all' ? undefined : state.tab })
    state.rows = result.list
    state.status = state.rows.length === 0 ? 'empty' : 'success'
    state.message = state.rows.length === 0 ? 'No salary slips' : ''
  }
  catch (error) {
    state.rows = []
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

function switchTab(tab: string): void {
  state.tab = tab
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

function openDetail(row: TeacherSalarySlip): void {
  uni.navigateTo({ url: `/pages/teacher/payroll/slip-detail?salary_month=${state.salaryMonth}&slip_id=${row.id}` })
}

function formatYuan(cents?: number): string {
  return `¥${((Number(cents || 0)) / 100).toFixed(2)}`
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
    <view class="tabs">
      <button class="tab" :class="{ active: state.tab === 'all' }" @tap="switchTab('all')">All</button>
      <button class="tab" :class="{ active: state.tab === 'approved' }" @tap="switchTab('approved')">Approved</button>
      <button class="tab" :class="{ active: state.tab === 'paid' }" @tap="switchTab('paid')">Paid</button>
    </view>
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading salary slips" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="loadRows" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="loadRows" />
    <view v-else class="list">
      <view v-for="row in state.rows" :key="row.id" class="card" @tap="openDetail(row)">
        <view class="line">
          <text class="title">{{ row.salary_month }}</text>
          <text class="badge">{{ row.status }}</text>
        </view>
        <text class="amount">{{ formatYuan(row.payable_amount_cents) }}</text>
        <text class="meta">Gross {{ formatYuan(row.gross_amount_cents) }}</text>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.tabs, .list { display: flex; flex-direction: column; gap: 16rpx; }
.tabs { flex-direction: row; margin-bottom: 18rpx; }
.tab { border-radius: 8rpx; background: #edf1f6; color: #172033; }
.tab.active { background: #2456a6; color: #ffffff; }
.card { display: flex; min-height: 164rpx; flex-direction: column; gap: 10rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.line { display: flex; align-items: center; justify-content: space-between; gap: 12rpx; }
.title { font-size: 30rpx; font-weight: 700; }
.amount { font-size: 36rpx; font-weight: 700; color: #17623a; }
.meta { color: #5f6f86; }
.badge { min-width: 132rpx; padding: 4rpx 12rpx; border-radius: 8rpx; background: #e7f6ee; color: #17623a; text-align: center; }
</style>
