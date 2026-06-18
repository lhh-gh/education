<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { MobileWorkflowTask, WorkflowTaskStatus } from '@/api/workflow/operator'
import { getOperatorWorkflowTasks } from '@/api/workflow/operator'

const statusOptions = ['pending', 'processing', 'overdue', 'completed'] as WorkflowTaskStatus[]

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  filterStatus: 'pending' as WorkflowTaskStatus,
  tasks: [] as MobileWorkflowTask[],
})

onLoad(loadTasks)
onPullDownRefresh(async () => {
  await loadTasks()
  uni.stopPullDownRefresh()
})

async function loadTasks(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getOperatorWorkflowTasks({ status: state.filterStatus })
    state.tasks = result.list
    state.status = result.list.length > 0 ? 'success' : 'empty'
    state.message = result.list.length > 0 ? '' : 'No assigned workflow tasks'
  }
  catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

function changeStatus(event: { detail: { value: number } }): void {
  state.filterStatus = statusOptions[event.detail.value] || 'pending'
  loadTasks()
}

function openTask(task: MobileWorkflowTask): void {
  const taskId = Number(task.task_id || task.id || 0)
  if (taskId > 0) {
    uni.navigateTo({ url: `/pages/operator/workflow/task-detail?task_id=${taskId}` })
  }
}

function isForbidden(error: unknown): boolean {
  const code = (error as { code?: number })?.code

  return code === 401 || code === 403
}

function errorMessage(error: unknown): string {
  return (error as { message?: string })?.message || 'Workflow tasks failed to load'
}
</script>

<template>
  <view class="page">
    <view class="toolbar">
      <picker :range="statusOptions" @change="changeStatus">
        <view class="picker">Status: {{ state.filterStatus }}</view>
      </picker>
      <button class="refresh" @tap="loadTasks">Refresh</button>
    </view>

    <view v-if="state.status !== 'success'" class="state" :class="state.status">
      <text>{{ state.status }}</text>
      <text>{{ state.message || 'Loading assigned tasks' }}</text>
      <button v-if="state.status === 'error' || state.status === 'forbidden'" class="refresh" @tap="loadTasks">Retry</button>
    </view>

    <view v-else class="task-list">
      <view v-for="task in state.tasks" :key="task.task_id || task.id" class="task-card" @tap="openTask(task)">
        <view class="task-header">
          <text class="title">{{ task.title }}</text>
          <text v-if="task.status === 'overdue'" class="overdue">Overdue</text>
        </view>
        <text class="meta">{{ task.task_type || 'workflow' }} / {{ task.status }}</text>
        <text v-if="task.due_at" class="meta">Due {{ task.due_at }}</text>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.toolbar { display: flex; align-items: center; gap: 16rpx; margin-bottom: 20rpx; }
.picker { min-width: 320rpx; min-height: 72rpx; padding: 18rpx; border-radius: 8rpx; background: #ffffff; }
.refresh { width: 180rpx; background: #2456a6; color: #ffffff; }
.state,
.task-list { display: flex; flex-direction: column; gap: 18rpx; }
.state { align-items: center; justify-content: center; min-height: 420rpx; padding: 32rpx; border-radius: 8rpx; background: #ffffff; }
.task-card { display: flex; flex-direction: column; gap: 10rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.task-header { display: flex; align-items: center; justify-content: space-between; gap: 16rpx; }
.title { flex: 1; font-size: 30rpx; font-weight: 700; }
.meta { color: #5f6f86; }
.overdue { padding: 4rpx 12rpx; border-radius: 8rpx; background: #fdecec; color: #8a1f1f; font-size: 22rpx; }
</style>
