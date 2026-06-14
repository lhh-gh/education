<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { getGuardianContext } from '@/api/foundation/context'
import { guardianPageOptions } from '@/pages/foundation/pageOptions'
import { createFoundationContextPage } from '@/pages/foundation/useFoundationContextPage'

const {
  state,
  enabledFeatureCodes,
  load,
  retry,
  refresh,
} = createFoundationContextPage(() => getGuardianContext(), guardianPageOptions)

onLoad(load)
onPullDownRefresh(refresh)
</script>

<template>
  <view class="page">
    <view v-if="state.status === 'loading'" class="state">
      <text>Loading...</text>
    </view>

    <view v-else-if="state.status === 'forbidden'" class="state blocked">
      <text class="message">{{ state.message }}</text>
      <button class="retry" @tap="retry">Retry</button>
    </view>

    <view v-else-if="state.status === 'error'" class="state">
      <text class="message">{{ state.message }}</text>
      <button class="retry" @tap="retry">Retry</button>
    </view>

    <view v-else-if="state.context" class="content">
      <view class="summary">
        <text class="tenant">{{ state.context.tenant.name }}</text>
        <text class="name">{{ state.context.profile.display_name }}</text>
        <text class="meta">Bound students: {{ state.context.bound_students.length }}</text>
      </view>

      <view v-if="state.status === 'empty'" class="empty">
        <text>{{ state.message }}</text>
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

.state,
.content {
  display: flex;
  flex-direction: column;
  gap: 24rpx;
}

.state {
  min-height: 60vh;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.blocked {
  color: #8a1f1f;
}

.message {
  max-width: 560rpx;
  line-height: 1.6;
}

.retry {
  width: 220rpx;
  border-radius: 8rpx;
  background: #2456a6;
  color: #ffffff;
}

.summary,
.section,
.empty {
  padding: 24rpx;
  border: 1rpx solid #dfe5d5;
  border-radius: 8rpx;
  background: #ffffff;
}

.summary {
  display: flex;
  flex-direction: column;
  gap: 8rpx;
}

.tenant {
  font-size: 30rpx;
  font-weight: 700;
}

.name {
  font-size: 38rpx;
  font-weight: 700;
}

.meta {
  color: #5f6f86;
}

.section {
  display: flex;
  flex-direction: column;
  gap: 16rpx;
}

.section-title {
  font-size: 28rpx;
  font-weight: 700;
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

.empty {
  color: #6b4e00;
  background: #fff8df;
}
</style>
