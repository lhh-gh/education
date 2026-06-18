<script setup lang="ts">
defineProps<{
  status: 'loading' | 'empty' | 'error' | 'forbidden'
  message: string
  actionText?: string
}>()

defineEmits<{
  (event: 'retry'): void
}>()
</script>

<template>
  <view class="state-block" :class="status">
    <text class="state-title">{{ status }}</text>
    <text class="state-message">{{ message }}</text>
    <button v-if="status === 'error' || status === 'forbidden'" class="state-action" @tap="$emit('retry')">
      {{ actionText || 'Retry' }}
    </button>
  </view>
</template>

<style scoped>
.state-block {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 18rpx;
  min-height: 420rpx;
  padding: 32rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
  text-align: center;
}

.state-title {
  min-width: 160rpx;
  font-size: 28rpx;
  font-weight: 700;
  text-transform: capitalize;
  color: #172033;
}

.state-message {
  max-width: 560rpx;
  color: #5f6f86;
  line-height: 1.6;
}

.state-action {
  width: 220rpx;
  min-height: 72rpx;
  border-radius: 8rpx;
  background: #2456a6;
  color: #ffffff;
}

.forbidden .state-title,
.error .state-title {
  color: #8a1f1f;
}

.empty .state-title {
  color: #6b4e00;
}
</style>
