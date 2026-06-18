<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import { submitTeacherTrialFeedback } from '@/api/admissions/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'form' as 'form' | 'submitting' | 'submitted' | 'error' | 'forbidden',
  message: '',
  trialLessonId: 0,
  score: 5,
  content: '',
  recommendCourseId: undefined as number | undefined,
})

onLoad((query = {}) => {
  state.trialLessonId = Number((query as Record<string, string>).trial_lesson_id || 0)
})

async function submit(): Promise<void> {
  const validation = validateForm()
  if (validation !== '') {
    state.status = 'error'
    state.message = validation
    return
  }

  state.status = 'submitting'
  state.message = ''
  try {
    await submitTeacherTrialFeedback({
      trial_lesson_id: state.trialLessonId,
      score: state.score,
      content: state.content,
      recommend_course_id: state.recommendCourseId,
    })
    state.status = 'submitted'
    state.message = 'Feedback submitted'
  }
  catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

function validateForm(): string {
  if (state.trialLessonId <= 0) {
    return 'Trial lesson is required'
  }
  if (state.content.trim() === '') {
    return 'Feedback content is required'
  }

  return ''
}

function editAgain(): void {
  state.status = 'form'
  state.message = ''
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
    <TeacherStateBlock v-if="state.status === 'submitting'" status="loading" message="Submitting feedback" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="editAgain" />
    <view v-else-if="state.status === 'submitted'" class="panel success">
      <text class="title">Feedback Submitted</text>
      <text class="meta">{{ state.message }}</text>
    </view>
    <view v-else class="panel">
      <text class="title">Trial Feedback</text>
      <text class="meta">Trial lesson {{ state.trialLessonId || '-' }}</text>
      <slider :value="state.score" :min="1" :max="5" show-value @change="state.score = $event.detail.value" />
      <textarea v-model="state.content" class="textarea" placeholder="Feedback content" />
      <input v-model.number="state.recommendCourseId" class="input" placeholder="Recommend course id" type="number" />
      <text v-if="state.message" class="error">{{ state.message }}</text>
      <button class="primary" @tap="submit">Submit</button>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.panel { display: flex; flex-direction: column; gap: 18rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 32rpx; font-weight: 700; }
.meta { color: #5f6f86; }
.input { min-height: 78rpx; padding: 0 18rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #f8fafc; }
.textarea { min-height: 180rpx; padding: 18rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #f8fafc; }
.primary { min-height: 76rpx; border-radius: 8rpx; background: #2456a6; color: #ffffff; }
.error { color: #8a1f1f; line-height: 1.5; }
.success { border-color: #b8e4cc; }
</style>
