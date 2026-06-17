<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { GuardianFinanceOrder } from '@/api/finance/guardian'
import { getGuardianFinanceOrder } from '@/api/finance/guardian'
import GuardianStateBlock from '@/pages/guardian/components/GuardianStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: 'student is not bound',
  studentId: 0,
  orderId: 0,
  order: null as GuardianFinanceOrder | null,
})

onLoad((query = {}) => {
  state.studentId = Number((query as Record<string, string>).student_id || (query as Record<string, string>).studentId || uni.getStorageSync('guardian_selected_student_id') || 0)
  state.orderId = Number((query as Record<string, string>).order_id || (query as Record<string, string>).id || 0)
  loadOrder()
})
onPullDownRefresh(refresh)

async function loadOrder(): Promise<void> {
  if (state.orderId <= 0) {
    state.status = 'empty'
    state.message = 'Finance order not found'
    return
  }

  state.status = 'loading'
  try {
    state.order = await getGuardianFinanceOrder(state.orderId, { student_id: state.studentId })
    state.status = 'success'
    state.message = ''
  }
  catch (error) {
    state.order = null
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function refresh(): Promise<void> {
  try {
    await loadOrder()
  }
  finally {
    uni.stopPullDownRefresh?.()
  }
}

function openReceipts(): void {
  uni.navigateTo({ url: `/pages/guardian/finance/receipts?student_id=${state.studentId}` })
}

function openRefunds(): void {
  uni.navigateTo({ url: `/pages/guardian/finance/refunds?student_id=${state.studentId}` })
}

function formatYuan(cents?: number): string {
  return `¥${((Number(cents || 0)) / 100).toFixed(2)}`
}

function isForbidden(error: unknown): boolean {
  const code = (error as { code?: number })?.code

  return code === 401 || code === 403
}

function errorMessage(error: unknown): string {
  return (error as { message?: string })?.message || 'student is not bound'
}
</script>

<template>
  <view class="page">
    <GuardianStateBlock v-if="state.status === 'loading'" state="loading" message="Loading finance order" />
    <GuardianStateBlock v-else-if="state.status === 'empty'" state="empty" :message="state.message" />
    <GuardianStateBlock v-else-if="state.status === 'forbidden'" state="forbidden" :message="state.message" @retry="loadOrder" />
    <GuardianStateBlock v-else-if="state.status === 'error'" state="error" :message="state.message" @retry="loadOrder" />
    <view v-else-if="state.order" class="content">
      <view class="summary">
        <text class="title">{{ state.order.order_no }}</text>
        <text class="amount">{{ formatYuan(state.order.total_amount_cents) }}</text>
        <text class="badge">{{ state.order.status }}</text>
      </view>
      <view class="section">
        <text class="section-title">Payment</text>
        <text class="meta">Paid {{ formatYuan(state.order.paid_amount_cents) }}</text>
        <text class="meta">Refund {{ formatYuan(state.order.refund_amount_cents) }}</text>
        <text class="meta">Due {{ state.order.due_at || '-' }}</text>
        <text class="meta">Paid at {{ state.order.paid_at || '-' }}</text>
      </view>
      <view class="section">
        <button class="entry-button" @tap="openReceipts">Receipt link</button>
        <button class="entry-button secondary" @tap="openRefunds">Refund status</button>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f7f8f3; color: #172033; }
.content, .summary, .section { display: flex; flex-direction: column; gap: 18rpx; }
.summary, .section { padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.amount { font-size: 40rpx; font-weight: 700; color: #17623a; }
.badge { width: 180rpx; padding: 4rpx 12rpx; border-radius: 8rpx; background: #e7f6ee; color: #17623a; text-align: center; }
.section-title { font-size: 28rpx; font-weight: 700; }
.meta { color: #5f6f86; }
.entry-button { min-height: 76rpx; border-radius: 8rpx; background: #2456a6; color: #ffffff; }
.secondary { background: #17623a; }
</style>
