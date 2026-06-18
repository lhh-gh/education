<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import { submitTeacherGrowthTrialFeedback } from '@/api/growth/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'form' as 'form' | 'submitting' | 'submitted' | 'error' | 'forbidden',
  message: '',
  trialLessonId: 0,
  classroomPerformance: '',
  courseRecommendation: '',
  teacherNote: '',
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
    await submitTeacherGrowthTrialFeedback({
      trial_lesson_id: state.trialLessonId,
      classroom_performance: state.classroomPerformance,
      course_recommendation: state.courseRecommendation,
      teacher_note: state.teacherNote,
    })
    state.status = 'submitted'
    state.message = 'Growth feedback submitted'
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
  if (state.classroomPerformance.trim() === '') {
    return 'Classroom performance is required'
  }
  if (state.courseRecommendation.trim() === '') {
    return 'Course recommendation is required'
  }
  if (state.teacherNote.trim() === '') {
    return 'Teacher note is required'
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
    <TeacherStateBlock v-if="state.status === 'submitting'" status="loading" message="Submitting growth feedback" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="editAgain" />
    <view v-else-if="state.status === 'submitted'" class="panel success">
      <text class="title">Feedback Submitted</text>
      <text class="meta">{{ state.message }}</text>
    </view>
    <view v-else class="panel">
      <text class="title">Growth Trial Feedback</text>
      <text class="meta">Trial lesson {{ state.trialLessonId || '-' }}</text>
      <textarea v-model="state.classroomPerformance" class="textarea" placeholder="Classroom performance" />
      <textarea v-model="state.courseRecommendation" class="textarea" placeholder="Course recommendation" />
      <textarea v-model="state.teacherNote" class="textarea" placeholder="Teacher note" />
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
.textarea { min-height: 160rpx; padding: 18rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #f8fafc; }
.primary { min-height: 76rpx; border-radius: 8rpx; background: #2456a6; color: #ffffff; }
.error { color: #8a1f1f; line-height: 1.5; }
.success { border-color: #b8e4cc; }
</style>
