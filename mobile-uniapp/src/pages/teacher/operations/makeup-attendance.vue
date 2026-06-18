<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { MakeupAttendanceStatus } from '@/api/operations/teacher'
import { getMakeupAttendanceDetail, submitMakeupAttendance } from '@/api/operations/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  makeupRecordId: 0,
  attendanceStatus: 'present' as MakeupAttendanceStatus,
  submitted: false,
})

onLoad((query = {}) => {
  state.makeupRecordId = Number((query as Record<string, string>).makeup_record_id || 0)
  loadDetail()
})

async function loadDetail(): Promise<void> {
  if (state.makeupRecordId <= 0) {
    state.status = 'empty'
    state.message = 'Make-up record is required'
    return
  }
  state.status = 'loading'
  try {
    await getMakeupAttendanceDetail(state.makeupRecordId)
    state.status = 'success'
  }
  catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function submit(): Promise<void> {
  try {
    await submitMakeupAttendance({ makeup_record_id: state.makeupRecordId, attendance_status: state.attendanceStatus })
    state.submitted = true
    state.message = 'Attendance submitted'
  }
  catch (error) {
    state.status = 'error'
    state.message = errorMessage(error)
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
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading make-up attendance" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="loadDetail" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="loadDetail" />
    <view v-else class="panel">
      <text class="title">Make-up Record {{ state.makeupRecordId }}</text>
      <picker :range="['present', 'late', 'absent', 'leave']" @change="state.attendanceStatus = ['present', 'late', 'absent', 'leave'][$event.detail.value] as MakeupAttendanceStatus">
        <view class="picker">Status: {{ state.attendanceStatus }}</view>
      </picker>
      <button class="primary" :disabled="state.submitted" @tap="submit">{{ state.submitted ? 'Submitted' : 'Submit' }}</button>
      <text v-if="state.message" class="message">{{ state.message }}</text>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.panel { display: flex; flex-direction: column; gap: 18rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.picker { min-height: 76rpx; padding: 18rpx; border-radius: 8rpx; background: #edf1f6; }
.primary { background: #2456a6; color: #ffffff; }
.message { color: #17623a; }
</style>
