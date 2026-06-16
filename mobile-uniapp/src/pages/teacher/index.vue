<script setup lang="ts">
import { reactive } from 'vue'
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import {
  getTeacherTodayLessons,
  pageTeacherLeaveRequests,
} from '@/api/academic/teacher'
import { getTeacherContext } from '@/api/foundation/context'
import { teacherPageOptions } from '@/pages/foundation/pageOptions'
import { createFoundationContextPage } from '@/pages/foundation/useFoundationContextPage'

const {
  state,
  enabledFeatureCodes,
  currentCampusName,
  load,
} = createFoundationContextPage(() => getTeacherContext(), teacherPageOptions)

const summary = reactive({
  loading: false,
  message: '',
  todayLessonCount: 0,
  pendingLeaveCount: 0,
})

onLoad(loadAll)
onPullDownRefresh(refreshAll)

async function loadAll(): Promise<void> {
  await load()
  if (state.status === 'success' && state.context !== null) {
    await loadSummary()
  }
}

async function retryAll(): Promise<void> {
  await loadAll()
}

async function refreshAll(): Promise<void> {
  try {
    await loadAll()
  } finally {
    uni.stopPullDownRefresh?.()
  }
}

async function loadSummary(): Promise<void> {
  summary.loading = true
  summary.message = ''
  try {
    const campusId = state.context?.profile.current_campus_id || undefined
    const [todayLessons, pendingLeaves] = await Promise.all([
      getTeacherTodayLessons({ campus_id: campusId, date: today() }),
      pageTeacherLeaveRequests({ campus_id: campusId, status: 'pending', page: 1, pageSize: 1 }),
    ])
    summary.todayLessonCount = todayLessons.list.length
    summary.pendingLeaveCount = pendingLeaves.total
  } catch (error) {
    summary.message = (error as { message?: string })?.message || 'Summary request failed'
  } finally {
    summary.loading = false
  }
}

function openSchedule(): void {
  uni.navigateTo({ url: '/pages/teacher/schedule/index' })
}

function openPendingLeaves(): void {
  uni.navigateTo({ url: '/pages/teacher/leave/index?status=pending' })
}

function today(): string {
  return new Date().toISOString().slice(0, 10)
}
</script>

<template>
  <view class="page">
    <view v-if="state.status === 'loading'" class="state">
      <text>Loading...</text>
    </view>

    <view v-else-if="state.status === 'forbidden'" class="state blocked">
      <text class="message">{{ state.message }}</text>
      <button class="retry" @tap="retryAll">Retry</button>
    </view>

    <view v-else-if="state.status === 'error'" class="state">
      <text class="message">{{ state.message }}</text>
      <button class="retry" @tap="retryAll">Retry</button>
    </view>

    <view v-else-if="state.context" class="content">
      <view class="summary">
        <text class="tenant">{{ state.context.tenant.name }}</text>
        <text class="name">{{ state.context.profile.display_name }}</text>
        <text class="meta">{{ currentCampusName || 'No current campus' }}</text>
      </view>

      <view v-if="state.status === 'empty'" class="empty">
        <text>{{ state.message }}</text>
      </view>

      <view class="section">
        <text class="section-title">Today</text>
        <view class="summary-grid">
          <view class="metric">
            <text class="metric-value">{{ summary.todayLessonCount }}</text>
            <text class="metric-label">Lessons</text>
          </view>
          <view class="metric">
            <text class="metric-value">{{ summary.pendingLeaveCount }}</text>
            <text class="metric-label">Pending leave</text>
          </view>
        </view>
        <text v-if="summary.message" class="summary-error">{{ summary.message }}</text>
      </view>

      <view class="section">
        <text class="section-title">Actions</text>
        <button class="entry-button" @tap="openSchedule">Schedule</button>
        <button class="entry-button secondary" @tap="openPendingLeaves">Leave review</button>
      </view>

      <view class="section">
        <text class="section-title">Campuses</text>
        <view v-for="campus in state.context.campus_scopes" :key="campus.campus_id" class="row">
          <text>{{ campus.campus_name }}</text>
          <text v-if="campus.is_current" class="tag">Current</text>
        </view>
      </view>

      <view class="section">
        <text class="section-title">Features</text>
        <view class="chips">
          <text v-for="feature in enabledFeatureCodes" :key="feature" class="chip">{{ feature }}</text>
        </view>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page {
  min-height: 100vh;
  padding: 32rpx;
  background: #f6f8fb;
  color: #172033;
}

.state,
.content {
  display: flex;
  flex-direction: column;
  gap: 24rpx;
}

.state {
  min-height: 60vh;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.blocked {
  color: #8a1f1f;
}

.message {
  max-width: 560rpx;
  line-height: 1.6;
}

.retry {
  width: 220rpx;
  border-radius: 8rpx;
  background: #2456a6;
  color: #ffffff;
}

.summary,
.section,
.empty {
  padding: 24rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
}

.summary {
  display: flex;
  flex-direction: column;
  gap: 8rpx;
}

.tenant {
  font-size: 30rpx;
  font-weight: 700;
}

.name {
  font-size: 38rpx;
  font-weight: 700;
}

.meta {
  color: #5f6f86;
}

.section {
  display: flex;
  flex-direction: column;
  gap: 16rpx;
}

.section-title {
  font-size: 28rpx;
  font-weight: 700;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16rpx;
}

.metric {
  display: flex;
  min-height: 112rpx;
  flex-direction: column;
  justify-content: center;
  padding: 18rpx;
  border: 1rpx solid #edf1f6;
  border-radius: 8rpx;
}

.metric-value {
  font-size: 36rpx;
  font-weight: 700;
}

.metric-label,
.summary-error {
  color: #5f6f86;
}

.summary-error {
  line-height: 1.5;
}

.entry-button {
  min-height: 76rpx;
  border-radius: 8rpx;
  background: #2456a6;
  color: #ffffff;
}

.secondary {
  background: #17623a;
}

.row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 56rpx;
}

.tag,
.chip {
  border-radius: 8rpx;
  background: #e7f6ee;
  color: #17623a;
}

.tag {
  padding: 4rpx 12rpx;
  font-size: 22rpx;
}

.chips {
  display: flex;
  flex-wrap: wrap;
  gap: 12rpx;
}

.chip {
  padding: 8rpx 14rpx;
  font-size: 22rpx;
}

.empty {
  color: #6b4e00;
  background: #fff8df;
}
</style>
