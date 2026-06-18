<script setup lang="ts">
import { reactive } from 'vue'
import { onLoad, onPullDownRefresh, onReachBottom } from '@dcloudio/uni-app'
import {
  pageGuardianNotices,
  type GuardianNoticeCard,
  type GuardianNoticeStatus,
} from '@/api/academic/guardian'
import GuardianStateBlock from '../components/GuardianStateBlock.vue'
import NoticeStatusBadge from '../components/NoticeStatusBadge.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  filter: 'unread' as GuardianNoticeStatus | 'all',
  notices: [] as GuardianNoticeCard[],
  page: 1,
  pageSize: 20,
  total: 0,
  refreshing: false,
})

onLoad(loadFirstPage)
onPullDownRefresh(refresh)
onReachBottom(loadNextPage)

async function loadFirstPage(): Promise<void> {
  state.page = 1
  await loadNotices(false)
}

async function loadNotices(append: boolean): Promise<void> {
  state.status = append ? state.status : 'loading'
  state.message = ''
  try {
    const result = await pageGuardianNotices({
      page: state.page,
      pageSize: state.pageSize,
      status: state.filter,
    })
    state.notices = append ? state.notices.concat(result.list) : result.list
    state.total = result.total
    state.status = state.notices.length === 0 ? 'empty' : 'success'
    state.message = state.notices.length === 0 ? 'No notices found' : ''
  } catch (error) {
    if (!append) {
      state.notices = []
    }
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function refresh(): Promise<void> {
  state.refreshing = true
  try {
    await loadFirstPage()
  } finally {
    state.refreshing = false
    uni.stopPullDownRefresh?.()
  }
}

async function loadNextPage(): Promise<void> {
  if (state.notices.length >= state.total || state.status !== 'success') {
    return
  }
  state.page += 1
  await loadNotices(true)
}

function setFilter(filter: GuardianNoticeStatus | 'all'): void {
  state.filter = filter
  loadFirstPage()
}

function openDetail(notice: GuardianNoticeCard): void {
  uni.navigateTo({ url: `/pages/guardian/notice/detail?receiptId=${notice.receipt_id}` })
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
      <button class="tab" :class="{ active: state.filter === 'unread' }" @tap="setFilter('unread')">Unread</button>
      <button class="tab" :class="{ active: state.filter === 'read' }" @tap="setFilter('read')">Read</button>
      <button class="tab" :class="{ active: state.filter === 'all' }" @tap="setFilter('all')">All</button>
    </view>

    <GuardianStateBlock v-if="state.status === 'loading'" state="loading" message="Loading notices" />
    <GuardianStateBlock v-else-if="state.status === 'empty'" state="empty" :message="state.message" />
    <GuardianStateBlock v-else-if="state.status === 'forbidden'" state="forbidden" :message="state.message" @retry="loadFirstPage" />
    <GuardianStateBlock v-else-if="state.status === 'error'" state="error" :message="state.message" @retry="loadFirstPage" />

    <view v-else class="notice-list">
      <view v-for="notice in state.notices" :key="notice.receipt_id" class="notice-card" @tap="openDetail(notice)">
        <view class="notice-main">
          <text class="notice-title">{{ notice.title }}</text>
          <text class="meta">{{ notice.student_name_snapshot }} · {{ notice.notice_type }}</text>
          <text class="meta">{{ notice.published_at || '' }}</text>
        </view>
        <NoticeStatusBadge :status="notice.status" :priority="notice.priority" />
      </view>
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

.tabs {
  display: flex;
  gap: 12rpx;
  margin-bottom: 18rpx;
}

.tab {
  border-radius: 8rpx;
  background: #edf1f6;
  color: #172033;
}

.tab.active {
  background: #2456a6;
  color: #ffffff;
}

.notice-list {
  display: flex;
  flex-direction: column;
  gap: 18rpx;
}

.notice-card {
  display: flex;
  justify-content: space-between;
  gap: 18rpx;
  min-height: 132rpx;
  padding: 24rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
}

.notice-main {
  display: flex;
  min-width: 0;
  flex: 1;
  flex-direction: column;
  gap: 8rpx;
}

.notice-title {
  font-size: 30rpx;
  font-weight: 700;
}

.meta {
  color: #5f6f86;
  line-height: 1.4;
}
</style>
