<script setup lang="ts">
import type { GuardianAccountRecord } from '@/api/academic/guardian'

defineProps<{
  account: GuardianAccountRecord
}>()
</script>

<template>
  <view class="account-card">
    <view class="account-head">
      <text class="course">{{ account.course_name }}</text>
      <text class="status">{{ account.status }}</text>
    </view>
    <view class="balance">
      <text class="balance-value">{{ account.available_units }}</text>
      <text class="balance-label">Available units</text>
    </view>
    <view class="rows">
      <text>Purchased: {{ account.purchased_units }}</text>
      <text>Bonus: {{ account.bonus_units }}</text>
      <text>Consumed: {{ account.consumed_units }}</text>
      <text>Adjusted: {{ account.adjusted_units }}</text>
      <text>Refunded: {{ account.refunded_units }}</text>
      <text>Frozen: {{ account.frozen_units }}</text>
      <text>Expires: {{ account.expires_at || '-' }}</text>
    </view>
  </view>
</template>

<style scoped>
.account-card {
  display: flex;
  flex-direction: column;
  gap: 16rpx;
  padding: 24rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
}

.account-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16rpx;
}

.course {
  font-size: 30rpx;
  font-weight: 700;
}

.status {
  padding: 4rpx 12rpx;
  border-radius: 8rpx;
  background: #e7f6ee;
  color: #17623a;
  font-size: 22rpx;
}

.balance {
  display: flex;
  flex-direction: column;
  gap: 4rpx;
}

.balance-value {
  font-size: 42rpx;
  font-weight: 700;
}

.balance-label,
.rows {
  color: #5f6f86;
}

.rows {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10rpx 16rpx;
  line-height: 1.4;
}
</style>
