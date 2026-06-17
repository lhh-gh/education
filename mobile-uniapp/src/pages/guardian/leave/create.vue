<script setup lang="ts">
import { reactive } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import {
  createGuardianLeave,
  type GuardianLeaveRecord,
  type GuardianLeaveType,
} from '@/api/academic/guardian'
import GuardianStateBlock from '../components/GuardianStateBlock.vue'

const state = reactive({
  status: 'success' as 'loading' | 'success' | 'error' | 'forbidden' | 'conflict',
  message: '',
  lessonStudentId: 0,
  studentId: 0,
  submitting: false,
  leaveType: 'sick' as GuardianLeaveType,
  reason: '',
  makeupRequired: true,
  result: null as GuardianLeaveRecord | null,
})

onLoad((query = {}) => {
  const params = query as Record<string, string>
  state.lessonStudentId = Number(params.lessonStudentId || params.lesson_student_id || 0)
  state.studentId = Number(params.studentId || params.student_id || 0)
  if (state.lessonStudentId <= 0) {
    state.status = 'error'
    state.message = 'Lesson student is required'
  }
})

async function submit(): Promise<void> {
  if (state.submitting) {
    return
  }
  if (state.lessonStudentId <= 0) {
    state.status = 'error'
    state.message = 'Lesson student is required'
    return
  }
  if (!state.reason.trim()) {
    state.status = 'error'
    state.message = 'Reason is required'
    return
  }

  state.submitting = true
  state.message = ''
  try {
    state.result = await createGuardianLeave({
      lesson_student_id: state.lessonStudentId,
      leave_type: state.leaveType,
      reason: state.reason,
      makeup_required: state.makeupRequired,
    })
    state.status = 'success'
    state.message = 'Leave request submitted'
  } catch (error) {
    state.result = null
    state.status = isConflict(error) ? 'conflict' : isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  } finally {
    state.submitting = false
  }
}

function setLeaveType(type: GuardianLeaveType): void {
  state.leaveType = type
}

function toggleMakeup(): void {
  state.makeupRequired = !state.makeupRequired
}

function backToSchedule(): void {
  uni.navigateTo({ url: `/pages/guardian/schedule/index?studentId=${state.studentId}` })
}

function isForbidden(error: unknown): boolean {
  const code = (error as { code?: number })?.code

  return code === 401 || code === 403
}

function isConflict(error: unknown): boolean {
  return (error as { code?: number })?.code === 409
}

function errorMessage(error: unknown): string {
  return (error as { message?: string })?.message || 'Request failed'
}
</script>

<template>
  <view class="page">
    <GuardianStateBlock v-if="state.status === 'forbidden'" state="forbidden" :message="state.message" @retry="submit" />
    <GuardianStateBlock v-else-if="state.status === 'conflict'" state="conflict" :message="state.message" retry-text="Back" @retry="backToSchedule" />

    <view v-else class="form">
      <view class="summary">
        <text class="title">Lesson student</text>
        <text class="meta">{{ state.lessonStudentId || '-' }}</text>
      </view>

      <view class="type-row">
        <button class="type-button" :class="{ active: state.leaveType === 'sick' }" @tap="setLeaveType('sick')">Sick</button>
        <button class="type-button" :class="{ active: state.leaveType === 'personal' }" @tap="setLeaveType('personal')">Personal</button>
        <button class="type-button" :class="{ active: state.leaveType === 'school' }" @tap="setLeaveType('school')">School</button>
        <button class="type-button" :class="{ active: state.leaveType === 'other' }" @tap="setLeaveType('other')">Other</button>
      </view>

      <textarea v-model="state.reason" class="reason" placeholder="Reason" maxlength="500" />

      <button class="toggle" @tap="toggleMakeup">
        Makeup required: {{ state.makeupRequired ? 'Yes' : 'No' }}
      </button>

      <text v-if="state.status === 'error'" class="error">{{ state.message }}</text>
      <text v-if="state.result" class="success">{{ state.message }} · {{ state.result.leave_no }} · {{ state.result.status }}</text>

      <button class="submit" :disabled="state.submitting" @tap="submit">
        {{ state.submitting ? 'Submitting...' : 'Submit' }}
      </button>
    </view>
  </view>
</template>

<style scoped>
.page {
  min-height: 100vh;
  padding: 24rpx;
  background: #f7f8f3;
  color: #172033;
}

.form,
.summary,
.type-row {
  display: flex;
  flex-direction: column;
  gap: 18rpx;
}

.summary,
.reason,
.toggle {
  padding: 20rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
}

.title {
  font-size: 30rpx;
  font-weight: 700;
}

.meta {
  color: #5f6f86;
}

.type-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.type-button,
.toggle,
.submit {
  min-height: 76rpx;
  border-radius: 8rpx;
}

.type-button {
  background: #edf1f6;
  color: #172033;
}

.type-button.active,
.submit {
  background: #2456a6;
  color: #ffffff;
}

.reason {
  width: 100%;
  min-height: 220rpx;
  box-sizing: border-box;
}

.error {
  color: #8a1f1f;
}

.success {
  color: #17623a;
}
</style>
