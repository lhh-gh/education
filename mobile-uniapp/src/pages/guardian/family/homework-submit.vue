<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import { submitGuardianHomework } from '@/api/family/guardian'
import GuardianStateBlock from '@/pages/guardian/components/GuardianStateBlock.vue'

const state = reactive({
  status: 'success' as 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  homeworkTargetId: 0,
  studentId: 0,
  content: '',
  attachmentIdsText: '',
  submitted: false,
})

onLoad((query = {}) => {
  state.homeworkTargetId = Number((query as Record<string, string>).homework_target_id || 0)
  state.studentId = Number((query as Record<string, string>).student_id || uni.getStorageSync('guardian_selected_student_id') || 0)
  if (state.homeworkTargetId <= 0 || state.studentId <= 0) {
    state.status = 'empty'
    state.message = 'Homework and selected student are required'
  }
})

async function submit(): Promise<void> {
  try {
    const attachmentIds = state.attachmentIdsText.split(',').map(item => Number(item.trim())).filter(item => item > 0)
    await submitGuardianHomework({
      homework_target_id: state.homeworkTargetId,
      student_id: state.studentId,
      content: state.content,
      attachment_ids: attachmentIds,
    })
    state.submitted = true
    state.message = 'Homework submitted'
  }
  catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error) || 'Attachment permission denied'
  }
}

function isForbidden(error: unknown): boolean {
  const code = (error as { code?: number })?.code

  return code === 401 || code === 403
}

function errorMessage(error: unknown): string {
  return (error as { message?: string })?.message || 'Attachment permission denied'
}
</script>

<template>
  <view class="page">
    <GuardianStateBlock v-if="state.status === 'empty'" state="empty" :message="state.message" />
    <GuardianStateBlock v-else-if="state.status === 'forbidden'" state="forbidden" :message="state.message" />
    <GuardianStateBlock v-else-if="state.status === 'error'" state="error" :message="state.message" />
    <view v-else class="form">
      <text class="title">Homework {{ state.homeworkTargetId }}</text>
      <textarea v-model="state.content" class="textarea" placeholder="Submission content" />
      <input v-model="state.attachmentIdsText" class="input" placeholder="Attachment IDs, e.g. 9001,9002" />
      <button class="primary" :disabled="state.submitted" @tap="submit">{{ state.submitted ? 'Submitted' : 'Submit' }}</button>
      <text v-if="state.message" class="message">{{ state.message }}</text>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f7f8f3; color: #172033; }
.form { display: flex; flex-direction: column; gap: 18rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.input, .textarea { padding: 16rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; }
.textarea { min-height: 220rpx; }
.primary { background: #2456a6; color: #ffffff; }
.message { color: #17623a; }
</style>
