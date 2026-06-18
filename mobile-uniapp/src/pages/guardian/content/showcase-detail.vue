<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { GuardianShowcase } from '@/api/content/guardian'
import { getGuardianShowcaseDetail } from '@/api/content/guardian'
import GuardianStateBlock from '@/pages/guardian/components/GuardianStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'error',
  message: '',
  showcase: undefined as GuardianShowcase | undefined,
})

onLoad((query = {}) => {
  const data = query as Record<string, string>
  loadDetail(Number(data.showcase_id || 0), Number(data.student_id || 0))
})

async function loadDetail(showcaseId: number, studentId?: number): Promise<void> {
  state.status = 'loading'
  try {
    state.showcase = await getGuardianShowcaseDetail(showcaseId, { student_id: studentId })
    state.status = 'success'
  }
  catch (error) {
    state.status = 'error'
    state.message = (error as { message?: string })?.message || 'Request failed'
  }
}
</script>

<template>
  <view class="page">
    <GuardianStateBlock v-if="state.status === 'loading'" state="loading" message="Loading showcase" />
    <GuardianStateBlock v-else-if="state.status === 'error'" state="error" :message="state.message" />
    <view v-else-if="state.showcase" class="card">
      <text class="title">{{ state.showcase.title }}</text>
      <text class="meta">{{ state.showcase.summary || state.showcase.status }}</text>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f7f8f3; color: #172033; }
.card { display: flex; flex-direction: column; gap: 16rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 34rpx; font-weight: 700; }
.meta { color: #5f6f86; }
</style>
