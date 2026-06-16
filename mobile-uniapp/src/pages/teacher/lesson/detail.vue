<script setup lang="ts">
import { reactive } from 'vue'
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import {
  getTeacherLessonDetail,
  type TeacherLessonDetail,
} from '@/api/academic/teacher'
import LessonStatusBadge from '@/pages/teacher/components/LessonStatusBadge.vue'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

type PageStatus = 'loading' | 'success' | 'empty' | 'error' | 'forbidden'

const state = reactive({
  status: 'loading' as PageStatus,
  message: '',
  lessonId: 0,
  lesson: null as TeacherLessonDetail | null,
})

onLoad((query = {}) => {
  state.lessonId = Number((query as Record<string, string>).lesson_id || 0)
  loadDetail()
})
onPullDownRefresh(refresh)

async function loadDetail(): Promise<void> {
  state.status = 'loading'
  state.message = ''
  try {
    if (state.lessonId <= 0) {
      throw new Error('Lesson id is required')
    }
    state.lesson = await getTeacherLessonDetail(state.lessonId)
    state.status = state.lesson.lesson_students.length === 0 ? 'empty' : 'success'
    state.message = state.status === 'empty' ? 'No students in this lesson' : ''
  } catch (error) {
    state.lesson = null
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function retry(): Promise<void> {
  await loadDetail()
}

async function refresh(): Promise<void> {
  try {
    await loadDetail()
  } finally {
    uni.stopPullDownRefresh?.()
  }
}

function openAttendance(): void {
  if (state.lesson === null) {
    return
  }
  uni.navigateTo({ url: `/pages/teacher/lesson/attendance?lesson_id=${state.lesson.id}` })
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
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading lesson" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="retry" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="retry" />

    <view v-else-if="state.lesson" class="content">
      <view class="lesson-head">
        <view class="head-copy">
          <text class="title">{{ state.lesson.title }}</text>
          <text class="time">{{ state.lesson.start_at }} - {{ state.lesson.end_at }}</text>
          <text class="meta">{{ state.lesson.class_name_snapshot || state.lesson.course_name_snapshot || '' }}</text>
        </view>
        <LessonStatusBadge :status="state.lesson.status" />
      </view>

      <button v-if="state.lesson.can_submit_attendance" class="primary-action" @tap="openAttendance">Start attendance</button>

      <view class="section">
        <text class="section-title">Students</text>
        <view v-for="student in state.lesson.lesson_students" :key="student.lesson_student_id" class="student-row">
          <text class="student-name">{{ student.student_name_snapshot }}</text>
          <text class="student-meta">{{ student.student_no_snapshot || '-' }}</text>
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

.content {
  display: flex;
  flex-direction: column;
  gap: 20rpx;
}

.lesson-head,
.section {
  padding: 24rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
}

.lesson-head {
  display: flex;
  justify-content: space-between;
  gap: 20rpx;
}

.head-copy {
  display: flex;
  min-width: 0;
  flex-direction: column;
  gap: 8rpx;
}

.title {
  font-size: 34rpx;
  font-weight: 700;
}

.time,
.meta,
.student-meta {
  color: #5f6f86;
}

.primary-action {
  min-height: 80rpx;
  border-radius: 8rpx;
  background: #2456a6;
  color: #ffffff;
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

.student-row {
  display: flex;
  min-height: 64rpx;
  align-items: center;
  justify-content: space-between;
  border-top: 1rpx solid #edf1f6;
}

.student-name {
  font-weight: 700;
}
</style>
