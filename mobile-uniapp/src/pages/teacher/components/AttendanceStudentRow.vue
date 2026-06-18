<script setup lang="ts">
import type { TeacherAttendanceRecord } from '@/api/academic/teacher'

defineProps<{
  record: TeacherAttendanceRecord
  disabled?: boolean
}>()

defineEmits<{
  (event: 'setStatus', value: TeacherAttendanceRecord['default_attendance_status']): void
}>()
</script>

<template>
  <view class="attendance-row">
    <view class="student">
      <text class="name">{{ record.student_name_snapshot }}</text>
      <text class="meta">{{ record.default_consumed_units }} units</text>
    </view>
    <view class="status-actions">
      <button
        v-for="status in ['present', 'late', 'absent', 'leave']"
        :key="status"
        class="status-button"
        :class="{ active: (record.attendance_status || record.default_attendance_status) === status }"
        :disabled="disabled"
        @tap="$emit('setStatus', status as any)"
      >
        {{ status }}
      </button>
    </view>
  </view>
</template>

<style scoped>
.attendance-row {
  display: flex;
  min-height: 156rpx;
  flex-direction: column;
  gap: 16rpx;
  padding: 22rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
}

.student {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16rpx;
}

.name {
  font-size: 30rpx;
  font-weight: 700;
}

.meta {
  color: #5f6f86;
}

.status-actions {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 10rpx;
}

.status-button {
  min-height: 60rpx;
  padding: 0;
  border-radius: 8rpx;
  background: #f1f3f7;
  color: #334155;
  font-size: 22rpx;
}

.active {
  background: #2456a6;
  color: #ffffff;
}
</style>
