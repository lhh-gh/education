<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import { aiDraftStatusMessage, saveAiDraftAsLessonComment } from '@/api/ai/teacher'
import type { TeacherAiDraftStatus } from '@/api/ai/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'success' as 'success' | 'empty' | 'error' | 'blocked',
  message: '',
  lessonId: 0,
  studentId: 0,
  taskId: 0,
  taskStatus: 'succeeded' as TeacherAiDraftStatus,
  draftText: '',
  saved: false,
})

onLoad((query = {}) => {
  const params = query as Record<string, string>
  state.lessonId = Number(params.lesson_id || 0)
  state.studentId = Number(params.student_id || 0)
  state.taskId = Number(params.task_id || 0)
  state.taskStatus = (params.status || 'succeeded') as TeacherAiDraftStatus
  state.message = aiDraftStatusMessage(state.taskStatus)
  state.draftText = params.draft_text || ''
  if (state.lessonId <= 0 || state.studentId <= 0 || state.taskId <= 0) {
    state.status = 'empty'
    state.message = 'AI draft task is required'
    return
  }
  if (state.taskStatus === 'blocked') {
    state.status = 'blocked'
  }
})

async function saveDraft(): Promise<void> {
  try {
    await saveAiDraftAsLessonComment({
      lesson_id: state.lessonId,
      student_id: state.studentId,
      content: state.draftText,
      publish: false,
    })
    state.saved = true
    state.message = 'Draft saved'
  }
  catch (error) {
    state.status = 'error'
    state.message = (error as { message?: string })?.message || 'Draft save failed'
  }
}
</script>

<template>
  <view class="page">
    <TeacherStateBlock v-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" />
    <view v-else-if="state.status === 'blocked'" class="panel blocked">
      <text class="title">Safety Blocked</text>
      <text>{{ state.message }}</text>
    </view>
    <view v-else class="panel">
      <text class="title">AI Draft {{ state.taskId }}</text>
      <text class="meta">{{ state.message }}</text>
      <textarea v-model="state.draftText" class="textarea" placeholder="Edit draft before saving" />
      <button class="primary" :disabled="state.saved || !state.draftText" @tap="saveDraft">{{ state.saved ? 'Saved' : 'Save Draft' }}</button>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.panel { display: flex; flex-direction: column; gap: 18rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.panel.blocked { border-color: #f0b8b8; background: #fdecec; color: #8a1f1f; }
.title { font-size: 30rpx; font-weight: 700; }
.meta { color: #5f6f86; }
.textarea { min-height: 260rpx; padding: 18rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; }
.primary { background: #2456a6; color: #ffffff; }
</style>
