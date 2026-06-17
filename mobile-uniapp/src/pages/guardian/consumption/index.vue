<script setup lang="ts">
import { reactive } from 'vue'
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import {
  pageGuardianStudentConsumptions,
  type GuardianConsumptionRecord,
} from '@/api/academic/guardian'
import GuardianStateBlock from '../components/GuardianStateBlock.vue'

const selectedStudentKey = 'guardian_selected_student_id'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  studentId: 0,
  accountId: undefined as number | undefined,
  rows: [] as GuardianConsumptionRecord[],
  page: 1,
  total: 0,
})

onLoad((query = {}) => {
  const params = query as Record<string, string>
  state.studentId = readStudentId(params)
  state.accountId = Number(params.accountId || params.account_id || 0) || undefined
  loadConsumptions()
})
onPullDownRefresh(refresh)

async function loadConsumptions(): Promise<void> {
  if (state.studentId <= 0) {
    state.status = 'empty'
    state.message = 'Select a student first'
    return
  }

  state.status = 'loading'
  state.message = ''
  try {
    const result = await pageGuardianStudentConsumptions(state.studentId, {
      page: state.page,
      pageSize: 20,
      account_id: state.accountId,
    })
    state.rows = result.list
    state.total = result.total
    state.status = state.rows.length === 0 ? 'empty' : 'success'
    state.message = state.rows.length === 0 ? 'No consumption records found' : ''
  } catch (error) {
    state.rows = []
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function refresh(): Promise<void> {
  try {
    state.page = 1
    await loadConsumptions()
  } finally {
    uni.stopPullDownRefresh?.()
  }
}

function readStudentId(query: Record<string, string>): number {
  const queryStudentId = Number(query.studentId || query.student_id || 0)
  if (queryStudentId > 0) {
    return queryStudentId
  }
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
    <view class="filter">
      <text>Account: {{ state.accountId || 'All' }}</text>
      <text>Total: {{ state.total }}</text>
    </view>

    <GuardianStateBlock v-if="state.status === 'loading'" state="loading" message="Loading consumption records" />
    <GuardianStateBlock v-else-if="state.status === 'empty'" state="empty" :message="state.message" />
    <GuardianStateBlock v-else-if="state.status === 'forbidden'" state="forbidden" :message="state.message" @retry="loadConsumptions" />
    <GuardianStateBlock v-else-if="state.status === 'error'" state="error" :message="state.message" @retry="loadConsumptions" />

    <view v-else class="ledger-list">
      <view v-for="row in state.rows" :key="row.id" class="ledger-row">
        <view class="ledger-main">
          <text class="course">{{ row.course_name }}</text>
          <text class="meta">{{ row.lesson_title || 'Manual record' }}</text>
          <text class="meta">{{ row.created_at }}</text>
        </view>
        <view class="ledger-side">
          <text class="units">{{ row.direction }} {{ row.units }}</text>
          <text class="badge" :class="{ reversed: row.status === 'reversed' }">{{ row.status }}</text>
          <text class="meta">{{ row.after_available_units }}</text>
        </view>
      </view>
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

.filter,
.ledger-row {
  display: flex;
  justify-content: space-between;
  gap: 18rpx;
  padding: 20rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
}

.filter {
  margin-bottom: 18rpx;
}

.ledger-list,
.ledger-main,
.ledger-side {
  display: flex;
  flex-direction: column;
  gap: 8rpx;
}

.ledger-list {
  gap: 18rpx;
}

.ledger-main {
  min-width: 0;
  flex: 1;
}

.course,
.units {
  font-size: 28rpx;
  font-weight: 700;
}

.meta {
  color: #5f6f86;
  line-height: 1.4;
}

.badge {
  padding: 4rpx 12rpx;
  border-radius: 8rpx;
  background: #e7f6ee;
  color: #17623a;
  text-align: center;
  font-size: 22rpx;
}

.badge.reversed {
  background: #edf1f6;
  color: #5f6f86;
}
</style>
