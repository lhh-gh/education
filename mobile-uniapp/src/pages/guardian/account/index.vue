<script setup lang="ts">
import { reactive } from 'vue'
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import {
  pageGuardianStudentAccounts,
  type GuardianAccountRecord,
} from '@/api/academic/guardian'
import AccountBalanceCard from '../components/AccountBalanceCard.vue'
import GuardianStateBlock from '../components/GuardianStateBlock.vue'

const selectedStudentKey = 'guardian_selected_student_id'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  studentId: 0,
  accounts: [] as GuardianAccountRecord[],
})

onLoad((query = {}) => {
  state.studentId = readStudentId(query as Record<string, string>)
  loadAccounts()
})
onPullDownRefresh(refresh)

async function loadAccounts(): Promise<void> {
  if (state.studentId <= 0) {
    state.status = 'empty'
    state.message = 'Select a student first'
    return
  }

  state.status = 'loading'
  state.message = ''
  try {
    const result = await pageGuardianStudentAccounts(state.studentId, { page: 1, pageSize: 50, status: 'active' })
    state.accounts = result.list
    state.status = state.accounts.length === 0 ? 'empty' : 'success'
    state.message = state.accounts.length === 0 ? 'No course accounts found' : ''
  } catch (error) {
    state.accounts = []
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function refresh(): Promise<void> {
  try {
    await loadAccounts()
  } finally {
    uni.stopPullDownRefresh?.()
  }
}

function openConsumption(account: GuardianAccountRecord): void {
  uni.navigateTo({
    url: `/pages/guardian/consumption/index?studentId=${state.studentId}&accountId=${account.id}`,
  })
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
    <GuardianStateBlock v-if="state.status === 'loading'" state="loading" message="Loading accounts" />
    <GuardianStateBlock v-else-if="state.status === 'empty'" state="empty" :message="state.message" />
    <GuardianStateBlock v-else-if="state.status === 'forbidden'" state="forbidden" :message="state.message" @retry="loadAccounts" />
    <GuardianStateBlock v-else-if="state.status === 'error'" state="error" :message="state.message" @retry="loadAccounts" />
    <view v-else class="account-list">
      <view v-for="account in state.accounts" :key="account.id" @tap="openConsumption(account)">
        <AccountBalanceCard :account="account" />
      </view>
    </view>
  </view>
</template>

<style scoped>
.page {
  min-height: 100vh;
  padding: 24rpx;
  background: #f7f8f3;
}

.account-list {
  display: flex;
  flex-direction: column;
  gap: 18rpx;
}
</style>
