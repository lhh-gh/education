<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { GuardianGrowthRecord } from '@/api/family/guardian'
import { getGuardianGrowthRecords, markGuardianFamilyRead } from '@/api/family/guardian'
import GuardianStateBlock from '@/pages/guardian/components/GuardianStateBlock.vue'

const readGrowthIds = new Set<number>()
const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  studentId: 0,
  records: [] as GuardianGrowthRecord[],
})

onLoad((query = {}) => {
  state.studentId = Number((query as Record<string, string>).student_id || uni.getStorageSync('guardian_selected_student_id') || 0)
  loadRows()
})
onPullDownRefresh(refresh)

async function loadRows(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getGuardianGrowthRecords({ student_id: state.studentId })
    state.records = result.list.filter(record => record.status === 'published')
    state.status = state.records.length === 0 ? 'empty' : 'success'
    state.message = state.records.length === 0 ? 'No published growth records' : ''
    await markVisibleGrowthRead()
  }
  catch (error) {
    state.records = []
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function markVisibleGrowthRead(): Promise<void> {
  for (const record of state.records) {
    if (readGrowthIds.has(record.id)) {
      continue
    }
    readGrowthIds.add(record.id)
    await markGuardianFamilyRead({ student_id: state.studentId, business_type: 'growth_record', business_id: record.id })
  }
}

async function refresh(): Promise<void> {
  try {
    await loadRows()
  }
  finally {
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
    <GuardianStateBlock v-if="state.status === 'loading'" state="loading" message="Loading growth records" />
    <GuardianStateBlock v-else-if="state.status === 'empty'" state="empty" :message="state.message" />
    <GuardianStateBlock v-else-if="state.status === 'forbidden'" state="forbidden" :message="state.message" @retry="loadRows" />
    <GuardianStateBlock v-else-if="state.status === 'error'" state="error" :message="state.message" @retry="loadRows" />
    <view v-else class="list">
      <view v-for="record in state.records" :key="record.id" class="card">
        <text class="title">{{ record.title }}</text>
        <text class="meta">{{ record.record_type }}</text>
        <text class="content">{{ record.content }}</text>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f7f8f3; color: #172033; }
.list { display: flex; flex-direction: column; gap: 16rpx; }
.card { display: flex; flex-direction: column; gap: 10rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.meta, .content { color: #5f6f86; line-height: 1.5; }
</style>
