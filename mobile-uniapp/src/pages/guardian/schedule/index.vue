<script setup lang="ts">
import { reactive } from 'vue'
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import {
  pageGuardianStudentLessons,
  type GuardianLessonRecord,
  type GuardianLessonStatus,
} from '@/api/academic/guardian'
import GuardianStateBlock from '../components/GuardianStateBlock.vue'

const selectedStudentKey = 'guardian_selected_student_id'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  studentId: 0,
  statusFilter: '' as '' | GuardianLessonStatus,
  lessons: [] as GuardianLessonRecord[],
  page: 1,
  total: 0,
})

onLoad((query = {}) => {
  state.studentId = readStudentId(query as Record<string, string>)
  loadLessons()
})
onPullDownRefresh(refresh)

async function loadLessons(): Promise<void> {
  if (state.studentId <= 0) {
    state.status = 'empty'
    state.message = 'Select a student first'
    return
  }

  state.status = 'loading'
  state.message = ''
  try {
    const result = await pageGuardianStudentLessons(state.studentId, {
      page: state.page,
      pageSize: 20,
      start_at: monthStart(),
      end_at: monthEnd(),
      status: state.statusFilter || undefined,
    })
    state.lessons = result.list
    state.total = result.total
    state.status = state.lessons.length === 0 ? 'empty' : 'success'
    state.message = state.lessons.length === 0 ? 'No lessons found' : ''
  } catch (error) {
    state.lessons = []
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function refresh(): Promise<void> {
  try {
    state.page = 1
    await loadLessons()
  } finally {
    uni.stopPullDownRefresh?.()
  }
}

function setStatusFilter(status: '' | GuardianLessonStatus): void {
  state.statusFilter = status
  state.page = 1
  loadLessons()
}

function openLeave(lesson: GuardianLessonRecord): void {
  uni.navigateTo({
    url: `/pages/guardian/leave/create?lessonStudentId=${lesson.lesson_student_id}&studentId=${state.studentId}`,
  })
}

function readStudentId(query: Record<string, string>): number {
  const queryStudentId = Number(query.studentId || query.student_id || 0)
  if (queryStudentId > 0) {
    return queryStudentId
  }
  try {
    return Number(uni.getStorageSync(selectedStudentKey) || 0)
  } catch {
    return 0
  }
}

function monthStart(): string {
  const now = new Date()

  return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-01 00:00:00`
}

function monthEnd(): string {
  const now = new Date()
  const end = new Date(now.getFullYear(), now.getMonth() + 1, 0)

  return `${end.getFullYear()}-${pad(end.getMonth() + 1)}-${pad(end.getDate())} 23:59:59`
}

function pad(value: number): string {
  return String(value).padStart(2, '0')
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
      <button class="tab" :class="{ active: state.statusFilter === '' }" @tap="setStatusFilter('')">All</button>
      <button class="tab" :class="{ active: state.statusFilter === 'scheduled' }" @tap="setStatusFilter('scheduled')">Scheduled</button>
      <button class="tab" :class="{ active: state.statusFilter === 'completed' }" @tap="setStatusFilter('completed')">Completed</button>
    </view>

    <GuardianStateBlock v-if="state.status === 'loading'" state="loading" message="Loading schedule" />
    <GuardianStateBlock v-else-if="state.status === 'empty'" state="empty" :message="state.message" />
    <GuardianStateBlock v-else-if="state.status === 'forbidden'" state="forbidden" :message="state.message" @retry="loadLessons" />
    <GuardianStateBlock v-else-if="state.status === 'error'" state="error" :message="state.message" @retry="loadLessons" />

    <view v-else class="lesson-list">
      <view v-for="lesson in state.lessons" :key="lesson.lesson_student_id" class="lesson-row">
        <view class="lesson-main">
          <text class="lesson-title">{{ lesson.title }}</text>
          <text class="lesson-meta">{{ lesson.start_at }} - {{ lesson.end_at }}</text>
          <text class="lesson-meta">{{ lesson.course_name_snapshot || '' }} · {{ lesson.teacher_name_snapshot || '' }}</text>
          <text class="lesson-meta">{{ lesson.classroom_name_snapshot || 'No classroom' }}</text>
        </view>
        <view class="lesson-side">
          <text class="badge">{{ lesson.lesson_status }}</text>
          <button v-if="lesson.lesson_status === 'scheduled'" class="leave-button" @tap="openLeave(lesson)">Leave</button>
        </view>
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

.tab,
.leave-button {
  border-radius: 8rpx;
  background: #edf1f6;
  color: #172033;
}

.tab.active,
.leave-button {
  background: #2456a6;
  color: #ffffff;
}

.lesson-list {
  display: flex;
  flex-direction: column;
  gap: 18rpx;
}

.lesson-row {
  display: flex;
  justify-content: space-between;
  gap: 20rpx;
  min-height: 166rpx;
  padding: 24rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
}

.lesson-main,
.lesson-side {
  display: flex;
  flex-direction: column;
  gap: 8rpx;
}

.lesson-main {
  min-width: 0;
  flex: 1;
}

.lesson-title {
  font-size: 30rpx;
  font-weight: 700;
}

.lesson-meta {
  color: #5f6f86;
  line-height: 1.4;
}

.badge {
  padding: 4rpx 12rpx;
  border-radius: 8rpx;
  background: #e7f6ee;
  color: #17623a;
  text-align: center;
  font-size: 22rpx;
}
</style>
