<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { MobileWorkflowTask } from '@/api/workflow/operator'
import {
  addOperatorWorkflowTaskComment,
  completeOperatorWorkflowTask,
  getOperatorWorkflowTaskDetail,
} from '@/api/workflow/operator'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  taskId: 0,
  task: null as MobileWorkflowTask | null,
  comment: '',
  result: 'done',
  completeContent: '',
  submitted: false,
})

onLoad((query = {}) => {
  state.taskId = Number((query as Record<string, string>).task_id || 0)
  loadDetail()
})

async function loadDetail(): Promise<void> {
  if (state.taskId <= 0) {
    state.status = 'empty'
    state.message = 'Workflow task is required'
    return
  }
  state.status = 'loading'
  try {
    state.task = await getOperatorWorkflowTaskDetail(state.taskId)
    state.status = 'success'
    state.message = ''
  }
  catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function addComment(): Promise<void> {
  if (!state.comment.trim()) {
    state.message = 'Comment content is required'
    return
  }
  try {
    await addOperatorWorkflowTaskComment(state.taskId, { content: state.comment.trim() })
    state.message = 'Comment submitted'
    state.comment = ''
    await loadDetail()
  }
  catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function completeTask(): Promise<void> {
  if (!state.completeContent.trim()) {
    state.message = 'Completion content is required'
    return
  }
  try {
    await completeOperatorWorkflowTask(state.taskId, { result: state.result, content: state.completeContent.trim() })
    state.submitted = true
    state.message = 'Task completed'
    await loadDetail()
  }
  catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

function isForbidden(error: unknown): boolean {
  const code = (error as { code?: number })?.code

  return code === 401 || code === 403
}

function errorMessage(error: unknown): string {
  return (error as { message?: string })?.message || 'Workflow task request failed'
}
</script>

<template>
  <view class="page">
    <view v-if="state.status !== 'success'" class="state" :class="state.status">
      <text>{{ state.status }}</text>
      <text>{{ state.message || 'Loading workflow task' }}</text>
      <button v-if="state.status === 'error' || state.status === 'forbidden'" class="primary" @tap="loadDetail">Retry</button>
    </view>

    <view v-else-if="state.task" class="content">
      <view class="panel">
        <text class="title">{{ state.task.title }}</text>
        <text class="meta">Status: {{ state.task.status }}</text>
        <text class="meta">Priority: {{ state.task.priority || 'normal' }}</text>
      </view>

      <view class="panel">
        <text class="section-title">Comments</text>
        <text v-if="!state.task.comments?.length" class="meta">No comments</text>
        <view v-for="comment in state.task.comments" :key="comment.id || comment.content" class="row">
          <text>{{ comment.content }}</text>
        </view>
        <textarea v-model="state.comment" class="textarea" placeholder="Add task comment" />
        <button class="primary" @tap="addComment">Submit Comment</button>
      </view>

      <view class="panel">
        <text class="section-title">Complete</text>
        <input v-model="state.result" class="input" placeholder="Result" />
        <textarea v-model="state.completeContent" class="textarea" placeholder="Completion note" />
        <button class="primary" :disabled="state.submitted || state.task.status === 'completed'" @tap="completeTask">
          {{ state.submitted || state.task.status === 'completed' ? 'Submitted' : 'Complete Task' }}
        </button>
        <text v-if="state.message" class="message">{{ state.message }}</text>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.state,
.content { display: flex; flex-direction: column; gap: 18rpx; }
.state { align-items: center; justify-content: center; min-height: 420rpx; padding: 32rpx; border-radius: 8rpx; background: #ffffff; }
.panel { display: flex; flex-direction: column; gap: 14rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 32rpx; font-weight: 700; }
.section-title { font-size: 28rpx; font-weight: 700; }
.meta { color: #5f6f86; }
.row { padding: 14rpx; border-radius: 8rpx; background: #edf1f6; }
.input,
.textarea { min-height: 72rpx; padding: 18rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; }
.textarea { min-height: 160rpx; }
.primary { background: #2456a6; color: #ffffff; }
.message { color: #17623a; }
</style>
