<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'success' as 'success' | 'empty' | 'error' | 'forbidden',
  lessonId: 0,
  studentId: 0,
})

onLoad((query = {}) => {
  state.lessonId = Number((query as Record<string, string>).lesson_id || 0)
  state.studentId = Number((query as Record<string, string>).student_id || 0)
  if (state.lessonId <= 0 || state.studentId <= 0) {
    state.status = 'empty'
  }
})

function openForm(): void {
  uni.navigateTo({ url: `/pages/teacher/family/comment-form?lesson_id=${state.lessonId}&student_id=${state.studentId}` })
}
</script>

<template>
  <view class="page">
    <TeacherStateBlock v-if="state.status === 'empty'" status="empty" message="Select an assigned lesson and student" />
    <view v-else class="panel">
      <text class="title">Lesson {{ state.lessonId }}</text>
      <text class="meta">Student {{ state.studentId }}</text>
      <button class="primary" @tap="openForm">Create Comment</button>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.panel { display: flex; flex-direction: column; gap: 18rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.meta { color: #5f6f86; }
.primary { background: #2456a6; color: #ffffff; }
</style>
