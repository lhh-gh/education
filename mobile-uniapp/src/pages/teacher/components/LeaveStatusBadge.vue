<script setup lang="ts">
import type { TeacherLeaveStatus } from '@/api/academic/teacher'

defineProps<{
  status: TeacherLeaveStatus
}>()
</script>

<template>
  <text class="leave-status" :class="status">{{ status }}</text>
</template>

<style scoped>
.leave-status {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 160rpx;
  min-height: 44rpx;
  padding: 0 14rpx;
  border-radius: 8rpx;
  font-size: 22rpx;
  font-weight: 700;
}

.pending {
  background: #fff4d6;
  color: #7a4b00;
}

.approved,
.makeup_scheduled,
.closed {
  background: #e7f6ee;
  color: #17623a;
}

.rejected,
.cancelled {
  background: #fbe9e7;
  color: #8a1f1f;
}
</style>
