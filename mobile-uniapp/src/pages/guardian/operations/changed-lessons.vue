<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { GuardianChangedLesson } from '@/api/operations/guardian'
import { getChangedLessons } from '@/api/operations/guardian'
import GuardianStateBlock from '@/pages/guardian/components/GuardianStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  studentId: 0,
  lessons: [] as GuardianChangedLesson[],
})

onLoad((query = {}) => {
  state.studentId = readStudentId(query as Record<string, string>)
  loadRows()
})
onPullDownRefresh(refresh)

async function loadRows(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getChangedLessons({ student_id: state.studentId })
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

async function refresh(): Promise<void> {
  try {
    await loadRows()
  }
  finally {
    uni.stopPullDownRefresh?.()
  }
}

function readStudentId(query: Record<string, string>): number {
  const queryStudentId = Number(query.student_id || query.studentId || 0)
  if (queryStudentId > 0) {
    return queryStudentId
  }
  try {
    return Number(uni.getStorageSync('guardian_selected_student_id') || 0)
  }
  catch {
    return 0
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
    <GuardianStateBlock v-if="state.status === 'loading'" state="loading" message="Loading changed lessons" />
    <GuardianStateBlock v-else-if="state.status === 'empty'" state="empty" :message="state.message" />
    <GuardianStateBlock v-else-if="state.status === 'forbidden'" state="forbidden" :message="state.message" @retry="loadRows" />
    <GuardianStateBlock v-else-if="state.status === 'error'" state="error" :message="state.message" @retry="loadRows" />
    <view v-else class="list">
      <view v-for="lesson in state.lessons" :key="lesson.id" class="card">
        <text class="title">Lesson {{ lesson.lesson_id }}</text>
        <text class="meta">{{ lesson.change_type }} / {{ lesson.status }}</text>
        <text class="reason">{{ lesson.reason || 'No reason' }}</text>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f7f8f3; color: #172033; }
.list { display: flex; flex-direction: column; gap: 16rpx; }
.card { display: flex; min-height: 148rpx; flex-direction: column; gap: 8rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.meta, .reason { color: #5f6f86; line-height: 1.4; }
</style>
