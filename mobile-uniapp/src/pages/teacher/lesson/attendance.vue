<script setup lang="ts">
import { reactive } from 'vue'
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import {
  getTeacherAttendanceResult,
  getTeacherAttendanceSheet,
  submitTeacherAttendance,
  type TeacherAttendanceRecord,
  type TeacherAttendanceResult,
  type TeacherAttendanceSheet,
  type TeacherAttendanceStatus,
} from '@/api/academic/teacher'
import AttendanceStudentRow from '@/pages/teacher/components/AttendanceStudentRow.vue'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

type PageStatus = 'loading' | 'success' | 'empty' | 'error' | 'forbidden' | 'conflict'

const state = reactive({
  status: 'loading' as PageStatus,
  message: '',
  lessonId: 0,
  sheet: null as TeacherAttendanceSheet | null,
  records: [] as TeacherAttendanceRecord[],
  result: null as TeacherAttendanceResult | null,
  submitting: false,
})

onLoad((query = {}) => {
  state.lessonId = Number((query as Record<string, string>).lesson_id || 0)
  loadSheet()
})
onPullDownRefresh(refresh)

async function loadSheet(): Promise<void> {
  state.status = 'loading'
  state.message = ''
  try {
    if (state.lessonId <= 0) {
      throw new Error('Lesson id is required')
    }
    state.sheet = await getTeacherAttendanceSheet(state.lessonId)
    state.records = state.sheet.records.map((record) => ({
      ...record,
      attendance_status: record.attendance_status || record.default_attendance_status,
      consume_policy: record.consume_policy || record.default_consume_policy,
      consumed_units: record.consumed_units || record.default_consumed_units,
    }))
    state.status = state.records.length === 0 ? 'empty' : 'success'
    state.message = state.records.length === 0 ? 'No students to attend' : ''
  } catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

function setStatus(record: TeacherAttendanceRecord, status: TeacherAttendanceStatus): void {
  record.attendance_status = status
  if (status === 'leave') {
    record.consume_policy = 'no_consume'
    record.consumed_units = '0.00'
  }
}

async function submit(): Promise<void> {
  if (state.submitting) {
    return
  }
  state.submitting = true
  state.message = ''
  try {
    state.result = await submitTeacherAttendance(state.lessonId, {
      records: state.records.map((record) => ({
        lesson_student_id: record.lesson_student_id,
        attendance_status: record.attendance_status || record.default_attendance_status,
        consume_policy: record.consume_policy || record.default_consume_policy,
        consumed_units: record.consumed_units || record.default_consumed_units,
        remark: record.remark || undefined,
      })),
    })
    state.status = 'success'
  } catch (error) {
    if ((error as { code?: number })?.code === 409) {
      state.status = 'conflict'
    } else {
      state.status = isForbidden(error) ? 'forbidden' : 'error'
    }
    state.message = errorMessage(error)
  } finally {
    state.submitting = false
  }
}

async function reloadResult(): Promise<void> {
  state.result = await getTeacherAttendanceResult(state.lessonId)
  state.status = 'success'
}

async function retry(): Promise<void> {
  await loadSheet()
}

async function refresh(): Promise<void> {
  try {
    await loadSheet()
  } finally {
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
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading attendance" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="retry" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="retry" />

    <view v-else class="content">
      <view v-if="state.status === 'conflict'" class="conflict">
        <text>{{ state.message }}</text>
        <button class="secondary-action" @tap="reloadResult">Reload result</button>
      </view>

      <AttendanceStudentRow
        v-for="record in state.records"
        :key="record.lesson_student_id"
        :record="record"
        :disabled="state.submitting"
        @setStatus="setStatus(record, $event)"
      />

      <button class="primary-action" :disabled="state.submitting" @tap="submit">
        {{ state.submitting ? 'Submitting' : 'Submit attendance' }}
      </button>

      <view v-if="state.result" class="result">
        <text>Submitted: {{ state.result.attendance_count }}</text>
        <text>Consumed: {{ state.result.total_consumed_units }}</text>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page {
  min-height: 100vh;
  padding: 24rpx;
  background: #f6f8fb;
  color: #172033;
}

.content {
  display: flex;
  flex-direction: column;
  gap: 18rpx;
}

.primary-action,
.secondary-action {
  min-height: 80rpx;
  border-radius: 8rpx;
  color: #ffffff;
}

.primary-action {
  background: #2456a6;
}

.secondary-action {
  width: 260rpx;
  background: #17623a;
}

.conflict,
.result {
  display: flex;
  flex-direction: column;
  gap: 14rpx;
  padding: 22rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
}
</style>
