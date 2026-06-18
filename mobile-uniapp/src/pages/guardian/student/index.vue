<script setup lang="ts">
import { reactive } from 'vue'
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import {
  getGuardianStudents,
  type GuardianStudentRecord,
} from '@/api/academic/guardian'
import GuardianStateBlock from '../components/GuardianStateBlock.vue'
import StudentSelector from '../components/StudentSelector.vue'

const selectedStudentKey = 'guardian_selected_student_id'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  students: [] as GuardianStudentRecord[],
  selectedStudentId: 0,
})

onLoad(loadStudents)
onPullDownRefresh(refresh)

async function loadStudents(): Promise<void> {
  state.status = 'loading'
  state.message = ''
  state.selectedStudentId = readSelectedStudentId()
  try {
    const result = await getGuardianStudents()
    state.students = result.list
    state.status = state.students.length === 0 ? 'empty' : 'success'
    state.message = state.students.length === 0 ? 'No bound students' : ''
  } catch (error) {
    state.students = []
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function refresh(): Promise<void> {
  try {
    await loadStudents()
  } finally {
    uni.stopPullDownRefresh?.()
  }
}

function selectStudent(student: GuardianStudentRecord): void {
  uni.setStorageSync(selectedStudentKey, student.id)
  state.selectedStudentId = student.id
  uni.navigateBack()
}

function readSelectedStudentId(): number {
  try {
    return Number(uni.getStorageSync(selectedStudentKey) || 0)
  } catch {
    return 0
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
    <GuardianStateBlock v-if="state.status === 'loading'" state="loading" message="Loading students" />
    <GuardianStateBlock v-else-if="state.status === 'empty'" state="empty" :message="state.message" />
    <GuardianStateBlock v-else-if="state.status === 'forbidden'" state="forbidden" :message="state.message" @retry="loadStudents" />
    <GuardianStateBlock v-else-if="state.status === 'error'" state="error" :message="state.message" @retry="loadStudents" />
    <StudentSelector
      v-else
      :students="state.students"
      :selected-student-id="state.selectedStudentId"
      @select="selectStudent"
    />
  </view>
</template>

<style scoped>
.page {
  min-height: 100vh;
  padding: 24rpx;
  background: #f7f8f3;
}
</style>
