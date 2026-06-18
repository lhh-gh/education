<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { ChangedLessonStatus, TeacherChangedLesson } from '@/api/operations/teacher'
import { getChangedLessons } from '@/api/operations/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  tab: 'pending_today' as ChangedLessonStatus,
  campusId: undefined as number | undefined,
  lessons: [] as TeacherChangedLesson[],
})

onLoad((query = {}) => {
  const campusId = Number((query as Record<string, string>).campus_id || 0)
  state.campusId = campusId > 0 ? campusId : undefined
  loadRows()
})
onPullDownRefresh(refresh)

async function loadRows(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getChangedLessons({ campus_id: state.campusId, status: state.tab })
    state.lessons = result.list
    state.status = state.lessons.length === 0 ? 'empty' : 'success'
    state.message = state.lessons.length === 0 ? 'No changed lessons' : ''
  }
  catch (error) {
    state.lessons = []
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

function switchTab(tab: ChangedLessonStatus): void {
  state.tab = tab
  loadRows()
}

function openAttendance(row: TeacherChangedLesson): void {
  uni.navigateTo({ url: `/pages/teacher/operations/makeup-attendance?makeup_record_id=${row.id}` })
}

async function refresh(): Promise<void> {
  try {
    await loadRows()
  }
  finally {
    uni.stopPullDownRefresh?.()
  }
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
      <button class="tab" :class="{ active: state.tab === 'pending_today' }" @tap="switchTab('pending_today')">Today</button>
      <button class="tab" :class="{ active: state.tab === 'upcoming' }" @tap="switchTab('upcoming')">Upcoming</button>
      <button class="tab" :class="{ active: state.tab === 'applied' }" @tap="switchTab('applied')">Applied</button>
    </view>
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading changed lessons" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="loadRows" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="loadRows" />
    <view v-else class="list">
      <view v-for="lesson in state.lessons" :key="lesson.id" class="card" @tap="openAttendance(lesson)">
        <text class="title">Lesson {{ lesson.lesson_id }}</text>
        <text class="meta">{{ lesson.change_type }} / {{ lesson.status }}</text>
        <text class="reason">{{ lesson.reason || 'No reason' }}</text>
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
.card { display: flex; min-height: 148rpx; flex-direction: column; gap: 8rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.meta, .reason { color: #5f6f86; line-height: 1.4; }
</style>
