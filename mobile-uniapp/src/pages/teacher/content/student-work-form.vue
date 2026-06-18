<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import { saveTeacherStudentWork } from '@/api/content/teacher'

const state = reactive({
  studentId: 0,
  lessonId: 0,
  title: '',
  description: '',
  attachmentIds: '',
  message: '',
  status: 'idle' as 'idle' | 'submitted' | 'forbidden' | 'error',
})

onLoad((query = {}) => {
  state.studentId = Number((query as Record<string, string>).student_id || 0)
  state.lessonId = Number((query as Record<string, string>).lesson_id || 0)
})

async function submitWork(): Promise<void> {
  try {
    await saveTeacherStudentWork({
      student_id: state.studentId,
      lesson_id: state.lessonId || undefined,
      title: state.title,
      description: state.description,
      attachment_ids: state.attachmentIds.split(',').map(id => Number(id.trim())).filter(id => id > 0),
    })
    state.status = 'submitted'
    state.message = 'Student work submitted'
  }
  catch (error) {
    state.status = (error as { code?: number })?.code === 403 ? 'forbidden' : 'error'
    state.message = (error as { message?: string })?.message || 'Request failed'
  }
}
</script>

<template>
  <view class="page">
    <view class="card">
      <text class="title">Student Work</text>
      <input v-model.number="state.studentId" class="input" type="number" placeholder="Student ID" />
      <input v-model.number="state.lessonId" class="input" type="number" placeholder="Lesson ID" />
      <input v-model="state.title" class="input" placeholder="Title" />
      <textarea v-model="state.description" class="textarea" placeholder="Description" />
      <input v-model="state.attachmentIds" class="input" placeholder="Attachment IDs, comma separated" />
      <button class="primary" @tap="submitWork">Submit</button>
      <text v-if="state.message" class="meta">{{ state.message }}</text>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.card { display: flex; flex-direction: column; gap: 16rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.input, .textarea { padding: 16rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.textarea { min-height: 160rpx; }
.primary { background: #2456a6; color: #ffffff; }
.meta { color: #5f6f86; }
</style>
