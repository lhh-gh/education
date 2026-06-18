<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import { aiDraftStatusMessage, isActiveDraftStatus, requestTeacherLessonCommentDraft } from '@/api/ai/teacher'
import type { TeacherAiDraftStatus } from '@/api/ai/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'success' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  lessonId: 0,
  studentId: 0,
  keywordsText: '',
  taskId: 0,
  taskStatus: 'queued' as TeacherAiDraftStatus,
})

onLoad((query = {}) => {
  state.lessonId = Number((query as Record<string, string>).lesson_id || 0)
  state.studentId = Number((query as Record<string, string>).student_id || 0)
  if (state.lessonId <= 0 || state.studentId <= 0) {
    state.status = 'empty'
    state.message = 'Select an assigned lesson and student'
  }
})

async function requestDraft(): Promise<void> {
  if (state.lessonId <= 0 || state.studentId <= 0) {
    state.status = 'empty'
    state.message = 'Assigned lesson and student are required'
    return
  }
  state.status = 'loading'
  try {
    const keywords = state.keywordsText.split(/[,\n]/).map(item => item.trim()).filter(Boolean)
    const result = await requestTeacherLessonCommentDraft({ lesson_id: state.lessonId, student_id: state.studentId, keywords })
    state.taskId = result.task_id
    state.taskStatus = result.status
    state.status = 'success'
    state.message = aiDraftStatusMessage(result.status)
  }
  catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

function openDetail(): void {
  uni.navigateTo({
    url: `/pages/teacher/ai/comment-draft-detail?lesson_id=${state.lessonId}&student_id=${state.studentId}&task_id=${state.taskId}&status=${state.taskStatus}`,
  })
}

function isForbidden(error: unknown): boolean {
  const code = (error as { code?: number })?.code

  return code === 401 || code === 403
}

function errorMessage(error: unknown): string {
  return (error as { message?: string })?.message || 'AI draft request failed'
}
</script>

<template>
  <view class="page">
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Requesting AI draft" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="requestDraft" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="requestDraft" />
    <view v-else class="panel">
      <text class="title">Lesson {{ state.lessonId }}</text>
      <text class="meta">Student {{ state.studentId }}</text>
      <textarea v-model="state.keywordsText" class="textarea" placeholder="Keywords, one per line" />
      <button class="primary" @tap="requestDraft">Request Draft</button>
      <view v-if="state.taskId" class="status-box" :class="state.taskStatus">
        <text>{{ state.message }}</text>
        <button class="secondary" :disabled="isActiveDraftStatus(state.taskStatus)" @tap="openDetail">Open Draft</button>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.panel { display: flex; flex-direction: column; gap: 18rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.meta { color: #5f6f86; }
.textarea { min-height: 180rpx; padding: 18rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; }
.primary { background: #2456a6; color: #ffffff; }
.secondary { background: #edf1f6; color: #172033; }
.status-box { display: flex; flex-direction: column; gap: 12rpx; padding: 18rpx; border-radius: 8rpx; background: #edf1f6; }
.status-box.blocked,
.status-box.failed { background: #fdecec; color: #8a1f1f; }
.status-box.succeeded { background: #eaf7ef; color: #17623a; }
</style>
