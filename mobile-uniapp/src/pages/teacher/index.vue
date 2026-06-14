<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { getTeacherContext } from '@/api/foundation/context'
import { teacherPageOptions } from '@/pages/foundation/pageOptions'
import { createFoundationContextPage } from '@/pages/foundation/useFoundationContextPage'

const {
  state,
  enabledFeatureCodes,
  currentCampusName,
  load,
  retry,
  refresh,
} = createFoundationContextPage(() => getTeacherContext(), teacherPageOptions)

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
        <text class="meta">{{ currentCampusName || 'No current campus' }}</text>
      </view>

      <view v-if="state.status === 'empty'" class="empty">
        <text>{{ state.message }}</text>
      </view>

      <view class="section">
        <text class="section-title">Campuses</text>
        <view v-for="campus in state.context.campus_scopes" :key="campus.campus_id" class="row">
          <text>{{ campus.campus_name }}</text>
          <text v-if="campus.is_current" class="tag">Current</text>
        </view>
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
  background: #f6f8fb;
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
  border: 1rpx solid #dce4ef;
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

.row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 56rpx;
}

.tag,
.chip {
  border-radius: 8rpx;
  background: #e7f6ee;
  color: #17623a;
}

.tag {
  padding: 4rpx 12rpx;
  font-size: 22rpx;
}

.chips {
  display: flex;
  flex-wrap: wrap;
  gap: 12rpx;
}

.chip {
  padding: 8rpx 14rpx;
  font-size: 22rpx;
}

.empty {
  color: #6b4e00;
  background: #fff8df;
}
</style>
