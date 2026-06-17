<script setup lang="ts">
defineProps<{
  status: 'unread' | 'read'
  priority?: 'normal' | 'important' | 'urgent'
}>()
</script>

<template>
  <text class="notice-badge" :class="[status, priority || 'normal']">
    {{ status }} · {{ priority || 'normal' }}
  </text>
</template>

<style scoped>
.notice-badge {
  min-width: 128rpx;
  padding: 6rpx 12rpx;
  border-radius: 8rpx;
  background: #edf1f6;
  color: #5f6f86;
  text-align: center;
  font-size: 22rpx;
}

.unread {
  background: #fff8df;
  color: #6b4e00;
}

.important {
  background: #e7f6ee;
  color: #17623a;
}

.urgent {
  background: #ffecec;
  color: #8a1f1f;
}
</style>
