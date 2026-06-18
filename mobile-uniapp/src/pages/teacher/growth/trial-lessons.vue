<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { TeacherGrowthTrialLesson } from '@/api/growth/teacher'
import { getTeacherGrowthTrialLessons } from '@/api/growth/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  date: today(),
  lessons: [] as TeacherGrowthTrialLesson[],
})

onLoad((query = {}) => {
  const date = (query as Record<string, string>).date
  state.date = date || state.date
  loadRows()
})
onPullDownRefresh(refresh)

async function loadRows(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getTeacherGrowthTrialLessons({ date: state.date })
    state.lessons = result.list
    state.status = state.lessons.length > 0 ? 'success' : 'empty'
    state.message = state.lessons.length > 0 ? '' : 'No assigned trial lessons'
  }
  catch (error) {
    state.lessons = []
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

function openFeedback(lesson: TeacherGrowthTrialLesson): void {
  uni.navigateTo({ url: `/pages/teacher/growth/trial-feedback?trial_lesson_id=${lesson.id}` })
}

async function refresh(): Promise<void> {
  try {
    await loadRows()
  }
  finally {
    uni.stopPullDownRefresh?.()
  }
}

function today(): string {
  return new Date().toISOString().slice(0, 10)
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
    <view class="toolbar">
      <input v-model="state.date" class="date-input" placeholder="YYYY-MM-DD" />
      <button class="reload" @tap="loadRows">Load</button>
    </view>
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading growth trial lessons" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="loadRows" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="loadRows" />
    <view v-else class="list">
      <view v-for="lesson in state.lessons" :key="lesson.id" class="card" @tap="openFeedback(lesson)">
        <text class="title">{{ lesson.student_name || `Lead ${lesson.lead_id}` }}</text>
        <text class="meta">{{ lesson.start_time }} - {{ lesson.end_time || '-' }}</text>
        <text class="badge">{{ lesson.status }}</text>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.toolbar { display: flex; gap: 12rpx; margin-bottom: 18rpx; }
.date-input { flex: 1; min-height: 76rpx; padding: 0 18rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.reload { width: 160rpx; min-height: 76rpx; border-radius: 8rpx; background: #2456a6; color: #ffffff; }
.list { display: flex; flex-direction: column; gap: 16rpx; }
.card { display: flex; min-height: 148rpx; flex-direction: column; gap: 8rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.meta { color: #5f6f86; line-height: 1.4; }
.badge { width: 170rpx; padding: 4rpx 12rpx; border-radius: 8rpx; background: #e7f6ee; color: #17623a; text-align: center; }
</style>
