<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { TeacherFamilyMessage } from '@/api/family/teacher'
import { getTeacherFamilyMessages, sendTeacherFamilyMessage } from '@/api/family/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  studentId: 0,
  threadId: '',
  rows: [] as TeacherFamilyMessage[],
  content: '',
})

onLoad((query = {}) => {
  state.studentId = Number((query as Record<string, string>).student_id || 0)
  state.threadId = (query as Record<string, string>).thread_id || `student-${state.studentId}`
  loadRows()
})
onPullDownRefresh(refresh)

async function loadRows(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getTeacherFamilyMessages({ student_id: state.studentId })
    state.rows = result.list
    state.status = state.rows.length === 0 ? 'empty' : 'success'
    state.message = state.rows.length === 0 ? 'No messages' : ''
  }
  catch (error) {
    state.rows = []
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function send(): Promise<void> {
  try {
    await sendTeacherFamilyMessage({ student_id: state.studentId, thread_id: state.threadId, content: state.content })
    state.content = ''
    await loadRows()
  }
  catch (error) {
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
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading messages" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="loadRows" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="loadRows" />
    <view v-else class="thread">
      <text v-if="state.status === 'empty'" class="empty">No messages</text>
      <view v-for="row in state.rows" :key="row.id" class="bubble" :class="row.sender_type">
        <text>{{ row.content }}</text>
      </view>
      <textarea v-model="state.content" class="textarea" placeholder="Reply to family" />
      <button class="primary" :disabled="!state.content" @tap="send">Send</button>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.thread { display: flex; flex-direction: column; gap: 16rpx; }
.bubble { padding: 18rpx; border-radius: 8rpx; background: #ffffff; color: #172033; }
.teacher { background: #e7f0ff; }
.textarea { min-height: 160rpx; padding: 16rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.primary { background: #2456a6; color: #ffffff; }
.empty { color: #5f6f86; text-align: center; }
</style>
