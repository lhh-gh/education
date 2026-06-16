<script setup lang="ts">
import { reactive } from 'vue'
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import {
  getTeacherTodayLessons,
  pageTeacherLessons,
  type TeacherLessonCard,
} from '@/api/academic/teacher'
import LessonStatusBadge from '@/pages/teacher/components/LessonStatusBadge.vue'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

type PageStatus = 'loading' | 'success' | 'empty' | 'error' | 'forbidden'

const state = reactive({
  status: 'loading' as PageStatus,
  message: '',
  lessons: [] as TeacherLessonCard[],
  campusId: undefined as number | undefined,
})

onLoad((query = {}) => {
  const campusId = Number((query as Record<string, string>).campus_id || 0)
  state.campusId = campusId > 0 ? campusId : undefined
  loadToday()
})
onPullDownRefresh(refresh)

async function loadToday(): Promise<void> {
  await load(async () => {
    const result = await getTeacherTodayLessons({ campus_id: state.campusId, date: today() })
    return result.list
  })
}

async function loadRange(startAt: string, endAt: string): Promise<void> {
  await load(async () => {
    const result = await pageTeacherLessons({
      campus_id: state.campusId,
      page: 1,
      pageSize: 50,
      start_at: startAt,
      end_at: endAt,
    })
    return result.list
  })
}

async function load(loader: () => Promise<TeacherLessonCard[]>): Promise<void> {
  state.status = 'loading'
  state.message = ''
  try {
    state.lessons = await loader()
    state.status = state.lessons.length === 0 ? 'empty' : 'success'
    state.message = state.lessons.length === 0 ? 'No lessons found' : ''
  } catch (error) {
    state.lessons = []
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function retry(): Promise<void> {
  await loadToday()
}

async function refresh(): Promise<void> {
  try {
    await loadToday()
  } finally {
    uni.stopPullDownRefresh?.()
  }
}

function openDetail(lesson: TeacherLessonCard): void {
  uni.navigateTo({ url: `/pages/teacher/lesson/detail?lesson_id=${lesson.id}` })
}

function openAttendance(lesson: TeacherLessonCard): void {
  uni.navigateTo({ url: `/pages/teacher/lesson/attendance?lesson_id=${lesson.id}` })
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
    <TeacherStateBlock
      v-if="state.status === 'loading'"
      status="loading"
      message="Loading lessons"
    />
    <TeacherStateBlock
      v-else-if="state.status === 'empty'"
      status="empty"
      :message="state.message"
    />
    <TeacherStateBlock
      v-else-if="state.status === 'forbidden'"
      status="forbidden"
      :message="state.message"
      @retry="retry"
    />
    <TeacherStateBlock
      v-else-if="state.status === 'error'"
      status="error"
      :message="state.message"
      @retry="retry"
    />

    <view v-else class="lesson-list">
      <view v-for="lesson in state.lessons" :key="lesson.id" class="lesson-row" @tap="openDetail(lesson)">
        <view class="lesson-main">
          <text class="lesson-title">{{ lesson.title }}</text>
          <text class="lesson-time">{{ lesson.start_at }} - {{ lesson.end_at }}</text>
          <text class="lesson-meta">{{ lesson.class_name_snapshot || lesson.course_name_snapshot || '' }}</text>
        </view>
        <view class="lesson-side">
          <LessonStatusBadge :status="lesson.status" />
          <button v-if="lesson.can_submit_attendance" class="icon-button" @tap.stop="openAttendance(lesson)">+</button>
        </view>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page {
  min-height: 100vh;
  padding: 24rpx;
  background: #f6f8fb;
  color: #172033;
}

.lesson-list {
  display: flex;
  flex-direction: column;
  gap: 18rpx;
}

.lesson-row {
  display: flex;
  min-height: 156rpx;
  justify-content: space-between;
  gap: 20rpx;
  padding: 24rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
}

.lesson-main {
  display: flex;
  min-width: 0;
  flex: 1;
  flex-direction: column;
  gap: 8rpx;
}

.lesson-title {
  font-size: 30rpx;
  font-weight: 700;
}

.lesson-time,
.lesson-meta {
  color: #5f6f86;
  line-height: 1.4;
}

.lesson-side {
  display: flex;
  width: 160rpx;
  flex-direction: column;
  align-items: flex-end;
  gap: 16rpx;
}

.icon-button {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 64rpx;
  height: 64rpx;
  padding: 0;
  border-radius: 8rpx;
  background: #2456a6;
  color: #ffffff;
  font-size: 34rpx;
}
</style>
