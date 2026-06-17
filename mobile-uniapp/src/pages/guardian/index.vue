<script setup lang="ts">
import { reactive } from 'vue'
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import {
  getGuardianStudents,
  type GuardianStudentRecord,
} from '@/api/academic/guardian'
import { getGuardianContext } from '@/api/foundation/context'
import { guardianPageOptions } from '@/pages/foundation/pageOptions'
import { createFoundationContextPage } from '@/pages/foundation/useFoundationContextPage'
import GuardianStateBlock from './components/GuardianStateBlock.vue'
import StudentSelector from './components/StudentSelector.vue'

const selectedStudentKey = 'guardian_selected_student_id'

const {
  state,
  enabledFeatureCodes,
  load,
} = createFoundationContextPage(() => getGuardianContext(), guardianPageOptions)

const guardian = reactive({
  loading: false,
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  students: [] as GuardianStudentRecord[],
  selectedStudentId: 0,
})

onLoad(loadAll)
onPullDownRefresh(refreshAll)

async function loadAll(): Promise<void> {
  await load()
  guardian.selectedStudentId = readSelectedStudentId()
  if (state.status === 'success' || state.status === 'empty') {
    await loadStudents()
  } else {
    guardian.status = state.status === 'forbidden' ? 'forbidden' : state.status === 'error' ? 'error' : 'loading'
    guardian.message = state.message
  }
}

async function loadStudents(): Promise<void> {
  guardian.loading = true
  guardian.status = 'loading'
  guardian.message = ''
  try {
    const result = await getGuardianStudents()
    guardian.students = result.list
    if (guardian.students.length === 0) {
      guardian.status = 'empty'
      guardian.message = 'No bound students'
      guardian.selectedStudentId = 0
      return
    }

    if (!guardian.students.some((student) => student.id === guardian.selectedStudentId)) {
      selectStudent(guardian.students[0])
    }
    guardian.status = 'success'
  } catch (error) {
    guardian.students = []
    guardian.status = isForbidden(error) ? 'forbidden' : 'error'
    guardian.message = errorMessage(error)
  } finally {
    guardian.loading = false
  }
}

async function retryAll(): Promise<void> {
  await loadAll()
}

async function refreshAll(): Promise<void> {
  try {
    await loadAll()
  } finally {
    uni.stopPullDownRefresh?.()
  }
}

function selectStudent(student: GuardianStudentRecord): void {
  guardian.selectedStudentId = student.id
  uni.setStorageSync(selectedStudentKey, student.id)
}

function openStudentSelector(): void {
  uni.navigateTo({ url: '/pages/guardian/student/index' })
}

function openStudentPage(path: string): void {
  if (guardian.selectedStudentId <= 0) {
    return
  }
  uni.navigateTo({ url: `${path}?studentId=${guardian.selectedStudentId}` })
}

function openNotice(): void {
  uni.navigateTo({ url: '/pages/guardian/notice/index' })
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
    <GuardianStateBlock
      v-if="guardian.status === 'loading'"
      state="loading"
      message="Loading guardian dashboard"
    />
    <GuardianStateBlock
      v-else-if="guardian.status === 'forbidden'"
      state="forbidden"
      :message="guardian.message"
      @retry="retryAll"
    />
    <GuardianStateBlock
      v-else-if="guardian.status === 'error'"
      state="error"
      :message="guardian.message"
      @retry="retryAll"
    />

    <view v-else class="content">
      <view v-if="state.context" class="summary">
        <text class="tenant">{{ state.context.tenant.name }}</text>
        <text class="name">{{ state.context.profile.display_name }}</text>
        <text class="meta">Bound students: {{ guardian.students.length }}</text>
      </view>

      <GuardianStateBlock
        v-if="guardian.status === 'empty'"
        state="empty"
        :message="guardian.message"
        @retry="retryAll"
      />

      <view v-else class="section">
        <text class="section-title">Selected student</text>
        <StudentSelector
          :students="guardian.students"
          :selected-student-id="guardian.selectedStudentId"
          @select="selectStudent"
        />
      </view>

      <view class="section">
        <text class="section-title">Actions</text>
        <button class="entry-button" @tap="openStudentSelector">Students</button>
        <button class="entry-button" :disabled="!guardian.selectedStudentId" @tap="openStudentPage('/pages/guardian/schedule/index')">Schedule</button>
        <button class="entry-button" :disabled="!guardian.selectedStudentId" @tap="openStudentPage('/pages/guardian/account/index')">Accounts</button>
        <button class="entry-button" :disabled="!guardian.selectedStudentId" @tap="openStudentPage('/pages/guardian/consumption/index')">Consumption</button>
        <button class="entry-button secondary" @tap="openNotice">Notices</button>
        <button class="entry-button" :disabled="!guardian.selectedStudentId" @tap="openStudentPage('/pages/guardian/leave/create')">Leave</button>
        <button class="entry-button" :disabled="!guardian.selectedStudentId" @tap="openStudentPage('/pages/guardian/finance/orders')">Finance Orders</button>
        <button class="entry-button" :disabled="!guardian.selectedStudentId" @tap="openStudentPage('/pages/guardian/finance/receipts')">Receipts</button>
        <button class="entry-button" :disabled="!guardian.selectedStudentId" @tap="openStudentPage('/pages/guardian/finance/refunds')">Refunds</button>
      </view>

      <view class="section">
        <text class="section-title">Features</text>
        <view class="chips">
          <text v-for="feature in enabledFeatureCodes" :key="feature" class="chip">{{ feature }}</text>
        </view>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page {
  min-height: 100vh;
  padding: 32rpx;
  background: #f7f8f3;
  color: #172033;
}

.content,
.summary,
.section {
  display: flex;
  flex-direction: column;
  gap: 18rpx;
}

.summary,
.section {
  padding: 24rpx;
  border: 1rpx solid #dfe5d5;
  border-radius: 8rpx;
  background: #ffffff;
}

.tenant,
.section-title {
  font-size: 28rpx;
  font-weight: 700;
}

.name {
  font-size: 38rpx;
  font-weight: 700;
}

.meta {
  color: #5f6f86;
}

.entry-button {
  min-height: 76rpx;
  border-radius: 8rpx;
  background: #2456a6;
  color: #ffffff;
}

.entry-button[disabled] {
  background: #c9d1db;
  color: #5f6f86;
}

.secondary {
  background: #17623a;
}

.chips {
  display: flex;
  flex-wrap: wrap;
  gap: 12rpx;
}

.chip {
  padding: 8rpx 14rpx;
  border-radius: 8rpx;
  background: #e7f6ee;
  color: #17623a;
  font-size: 22rpx;
}
</style>
