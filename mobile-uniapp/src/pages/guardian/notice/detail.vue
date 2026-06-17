<script setup lang="ts">
import { reactive } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import {
  getGuardianNotice,
  readGuardianNotice,
  type GuardianNoticeDetail,
} from '@/api/academic/guardian'
import GuardianStateBlock from '../components/GuardianStateBlock.vue'
import NoticeStatusBadge from '../components/NoticeStatusBadge.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'error' | 'forbidden' | 'not_found',
  message: '',
  receiptId: 0,
  notice: null as GuardianNoticeDetail | null,
  readMessage: '',
})

onLoad((query = {}) => {
  state.receiptId = Number((query as Record<string, string>).receiptId || (query as Record<string, string>).receipt_id || 0)
  loadDetail()
})

async function loadDetail(): Promise<void> {
  if (state.receiptId <= 0) {
    state.status = 'not_found'
    state.message = 'Notice receipt not found'
    return
  }

  state.status = 'loading'
  state.message = ''
  state.readMessage = ''
  try {
    state.notice = await getGuardianNotice(state.receiptId)
    state.status = 'success'
    if (state.notice.status === 'unread') {
      await markAsRead()
    }
  } catch (error) {
    state.notice = null
    state.status = isNotFound(error) ? 'not_found' : isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function markAsRead(): Promise<void> {
  try {
    const result = await readGuardianNotice(state.receiptId)
    if (state.notice) {
      state.notice.status = result.status
      state.notice.read_at = result.read_at
    }
    state.readMessage = 'Read successfully'
  } catch (error) {
    state.status = isNotFound(error) ? 'not_found' : isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

function isForbidden(error: unknown): boolean {
  const code = (error as { code?: number })?.code

  return code === 401 || code === 403
}

function isNotFound(error: unknown): boolean {
  return (error as { code?: number })?.code === 404
}

function errorMessage(error: unknown): string {
  return (error as { message?: string })?.message || 'Request failed'
}
</script>

<template>
  <view class="page">
    <GuardianStateBlock v-if="state.status === 'loading'" state="loading" message="Loading notice" />
    <GuardianStateBlock v-else-if="state.status === 'not_found'" state="not_found" :message="state.message" @retry="loadDetail" />
    <GuardianStateBlock v-else-if="state.status === 'forbidden'" state="forbidden" :message="state.message" @retry="loadDetail" />
    <GuardianStateBlock v-else-if="state.status === 'error'" state="error" :message="state.message" @retry="loadDetail" />

    <view v-else-if="state.notice" class="detail">
      <view class="head">
        <text class="title">{{ state.notice.title }}</text>
        <NoticeStatusBadge :status="state.notice.status" :priority="state.notice.priority" />
      </view>
      <text class="meta">{{ state.notice.student_name_snapshot }} · {{ state.notice.notice_type }}</text>
      <text class="meta">{{ state.notice.published_at || '' }}</text>
      <text class="content">{{ state.notice.content }}</text>
      <text v-if="state.readMessage" class="read-message">{{ state.readMessage }}</text>
    </view>
  </view>
</template>

<style scoped>
.page {
  min-height: 100vh;
  padding: 24rpx;
  background: #f7f8f3;
  color: #172033;
}

.detail {
  display: flex;
  flex-direction: column;
  gap: 18rpx;
  padding: 24rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
}

.head {
  display: flex;
  justify-content: space-between;
  gap: 18rpx;
}

.title {
  min-width: 0;
  flex: 1;
  font-size: 34rpx;
  font-weight: 700;
}

.meta {
  color: #5f6f86;
  line-height: 1.4;
}

.content {
  line-height: 1.7;
}

.read-message {
  color: #17623a;
}
</style>
