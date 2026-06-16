<script setup lang="ts">
import type { TeacherLessonStatus } from '@/api/academic/teacher'

defineProps<{
  status: TeacherLessonStatus
}>()
</script>

<template>
  <text class="lesson-status" :class="status">{{ status }}</text>
</template>

<style scoped>
.lesson-status {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 144rpx;
  min-height: 44rpx;
  padding: 0 14rpx;
  border-radius: 8rpx;
  font-size: 22rpx;
  font-weight: 700;
}

.scheduled {
  background: #e8f5ff;
  color: #155c93;
}

.completed {
  background: #e7f6ee;
  color: #17623a;
}

.cancelled {
  background: #f1f3f7;
  color: #5f6f86;
}
</style>
