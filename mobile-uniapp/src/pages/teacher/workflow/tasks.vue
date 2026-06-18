<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { computed, reactive } from 'vue'
import type { MobileWorkflowTask, WorkflowTaskStatus } from '@/api/workflow/teacher'
import { getTeacherWorkflowTasks } from '@/api/workflow/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const statusOptions = ['pending', 'processing', 'overdue', 'completed'] as WorkflowTaskStatus[]

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  filterStatus: 'pending' as WorkflowTaskStatus,
  tasks: [] as MobileWorkflowTask[],
})

const visibleTasks = computed(() => state.tasks)

onLoad(loadTasks)
onPullDownRefresh(async () => {
  await loadTasks()
  uni.stopPullDownRefresh()
})

async function loadTasks(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getTeacherWorkflowTasks({ status: state.filterStatus })
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
    uni.navigateTo({ url: `/pages/teacher/workflow/task-detail?task_id=${taskId}` })
  }
}

function isOverdue(task: MobileWorkflowTask): boolean {
  if (task.status === 'overdue') {
    return true
  }

  return Boolean(task.due_at && new Date(task.due_at).getTime() < Date.now() && !['completed', 'cancelled'].includes(task.status))
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

    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading assigned tasks" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="loadTasks" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="loadTasks" />

    <view v-else class="task-list">
      <view v-for="task in visibleTasks" :key="task.task_id || task.id" class="task-card" @tap="openTask(task)">
        <view class="task-header">
          <text class="title">{{ task.title }}</text>
          <text v-if="isOverdue(task)" class="overdue">Overdue</text>
        </view>
        <text class="meta">{{ task.task_type || 'workflow' }} / {{ task.status }}</text>
        <text v-if="task.due_at" class="meta">Due {{ task.due_at }}</text>
        <view class="chips">
          <text class="chip">{{ task.priority || 'normal' }}</text>
          <text v-if="task.attachments?.length" class="chip">{{ task.attachments.length }} attachments</text>
          <text v-if="task.comments?.length" class="chip">{{ task.comments.length }} comments</text>
        </view>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.toolbar { display: flex; align-items: center; gap: 16rpx; margin-bottom: 20rpx; }
.picker { min-width: 320rpx; min-height: 72rpx; padding: 18rpx; border-radius: 8rpx; background: #ffffff; }
.refresh { width: 180rpx; background: #2456a6; color: #ffffff; }
.task-list { display: flex; flex-direction: column; gap: 18rpx; }
.task-card { display: flex; flex-direction: column; gap: 10rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.task-header { display: flex; align-items: center; justify-content: space-between; gap: 16rpx; }
.title { flex: 1; font-size: 30rpx; font-weight: 700; }
.meta { color: #5f6f86; }
.overdue { padding: 4rpx 12rpx; border-radius: 8rpx; background: #fdecec; color: #8a1f1f; font-size: 22rpx; }
.chips { display: flex; flex-wrap: wrap; gap: 10rpx; }
.chip { padding: 6rpx 12rpx; border-radius: 8rpx; background: #edf1f6; color: #3e4a5f; font-size: 22rpx; }
</style>
