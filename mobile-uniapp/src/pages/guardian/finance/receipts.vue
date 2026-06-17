<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { GuardianFinanceReceipt } from '@/api/finance/guardian'
import { getGuardianFinanceReceipts } from '@/api/finance/guardian'
import GuardianStateBlock from '@/pages/guardian/components/GuardianStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  studentId: 0,
  rows: [] as GuardianFinanceReceipt[],
})

onLoad((query = {}) => {
  state.studentId = Number((query as Record<string, string>).student_id || (query as Record<string, string>).studentId || uni.getStorageSync('guardian_selected_student_id') || 0)
  loadRows()
})
onPullDownRefresh(refresh)

async function loadRows(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getGuardianFinanceReceipts({ student_id: state.studentId })
    state.rows = result.list
    state.status = state.rows.length === 0 ? 'empty' : 'success'
    state.message = state.rows.length === 0 ? 'No receipts' : ''
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

function copyReceiptLink(row: GuardianFinanceReceipt): void {
  if (!row.pdf_url) {
    return
  }
  uni.setClipboardData?.({ data: row.pdf_url })
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
    <GuardianStateBlock v-if="state.status === 'loading'" state="loading" message="Loading receipts" />
    <GuardianStateBlock v-else-if="state.status === 'empty'" state="empty" :message="state.message" />
    <GuardianStateBlock v-else-if="state.status === 'forbidden'" state="forbidden" :message="state.message" @retry="loadRows" />
    <GuardianStateBlock v-else-if="state.status === 'error'" state="error" :message="state.message" @retry="loadRows" />
    <view v-else class="list">
      <view v-for="row in state.rows" :key="row.id" class="card">
        <view class="line">
          <text class="title">{{ row.receipt_no }}</text>
          <text class="badge">{{ row.status }}</text>
        </view>
        <text class="amount">{{ formatYuan(row.amount_cents) }}</text>
        <text class="meta">Issued {{ row.issued_at || '-' }}</text>
        <button class="entry-button" :disabled="!row.pdf_url" @tap="copyReceiptLink(row)">Receipt link</button>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f7f8f3; color: #172033; }
.list { display: flex; flex-direction: column; gap: 16rpx; }
.card { display: flex; min-height: 172rpx; flex-direction: column; gap: 10rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.line { display: flex; align-items: center; justify-content: space-between; gap: 12rpx; }
.title { font-size: 30rpx; font-weight: 700; }
.amount { font-size: 34rpx; font-weight: 700; color: #17623a; }
.meta { color: #5f6f86; }
.badge { min-width: 132rpx; padding: 4rpx 12rpx; border-radius: 8rpx; background: #e7f6ee; color: #17623a; text-align: center; }
.entry-button { min-height: 76rpx; border-radius: 8rpx; background: #2456a6; color: #ffffff; }
.entry-button[disabled] { background: #c9d1db; color: #5f6f86; }
</style>
