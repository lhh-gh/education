<script setup lang="ts">
import type { GuardianStudentRecord } from '@/api/academic/guardian'

defineProps<{
  students: GuardianStudentRecord[]
  selectedStudentId?: number
}>()

defineEmits<{
  (event: 'select', student: GuardianStudentRecord): void
}>()
</script>

<template>
  <view class="student-selector">
    <view
      v-for="student in students"
      :key="student.id"
      class="student-card"
      :class="{ active: student.id === selectedStudentId }"
      @tap="$emit('select', student)"
    >
      <view class="student-main">
        <text class="student-name">{{ student.name }}</text>
        <text class="student-meta">{{ student.student_no }} · {{ student.relation }}</text>
        <text class="student-meta">{{ student.campus_name || 'No campus' }}</text>
      </view>
      <view class="badges">
        <text v-if="student.is_primary" class="badge">Primary</text>
        <text class="badge" :class="{ disabled: !student.can_receive_notice }">Notice</text>
        <text class="badge" :class="{ disabled: !student.can_submit_leave }">Leave</text>
      </view>
    </view>
  </view>
</template>

<style scoped>
.student-selector {
  display: flex;
  flex-direction: column;
  gap: 16rpx;
}

.student-card {
  display: flex;
  justify-content: space-between;
  gap: 18rpx;
  min-height: 132rpx;
  padding: 20rpx;
  border: 1rpx solid #dce4ef;
  border-radius: 8rpx;
  background: #ffffff;
}

.student-card.active {
  border-color: #2456a6;
  background: #f2f6ff;
}

.student-main,
.badges {
  display: flex;
  flex-direction: column;
  gap: 8rpx;
}

.student-main {
  min-width: 0;
  flex: 1;
}

.student-name {
  font-size: 30rpx;
  font-weight: 700;
}

.student-meta {
  color: #5f6f86;
  line-height: 1.4;
}

.badge {
  min-width: 96rpx;
  padding: 4rpx 10rpx;
  border-radius: 8rpx;
  background: #e7f6ee;
  color: #17623a;
  text-align: center;
  font-size: 22rpx;
}

.badge.disabled {
  background: #edf1f6;
  color: #5f6f86;
}
</style>
