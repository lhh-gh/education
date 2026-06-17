<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import { saveTeacherGrowthRecord } from '@/api/family/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'success' as 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  studentId: 0,
  recordType: 'classroom',
  title: '',
  content: '',
  publish: true,
  submitted: false,
})

onLoad((query = {}) => {
  state.studentId = Number((query as Record<string, string>).student_id || 0)
  if (state.studentId <= 0) {
    state.status = 'empty'
    state.message = 'Assigned student is required'
  }
})

async function submit(): Promise<void> {
  try {
    await saveTeacherGrowthRecord({
      student_id: state.studentId,
      record_type: state.recordType,
      title: state.title,
      content: state.content,
      publish: state.publish,
    })
    state.submitted = true
    state.message = state.publish ? 'Growth record published' : 'Draft saved'
  }
  catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

function setPublish(event: Event): void {
  state.publish = Boolean((event as { detail?: { value?: boolean } }).detail?.value)
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
    <TeacherStateBlock v-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" />
    <view v-else class="form">
      <text class="title">Student {{ state.studentId }}</text>
      <input v-model="state.recordType" class="input" placeholder="Record type" />
      <input v-model="state.title" class="input" placeholder="Title" />
      <textarea v-model="state.content" class="textarea" placeholder="Growth record content" />
      <label class="toggle"><switch :checked="state.publish" @change="setPublish" />Publish now</label>
      <button class="primary" :disabled="state.submitted || !state.title || !state.content" @tap="submit">{{ state.submitted ? 'Submitted' : 'Submit' }}</button>
      <text v-if="state.message" class="message">{{ state.message }}</text>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.form { display: flex; flex-direction: column; gap: 18rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.input, .textarea { padding: 16rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; }
.textarea { min-height: 220rpx; }
.toggle { display: flex; align-items: center; gap: 12rpx; color: #5f6f86; }
.primary { background: #2456a6; color: #ffffff; }
.message { color: #17623a; }
</style>
