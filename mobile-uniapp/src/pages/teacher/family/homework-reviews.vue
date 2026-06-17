<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { TeacherHomeworkReviewItem } from '@/api/family/teacher'
import { getTeacherHomeworkSubmissions, reviewTeacherHomework } from '@/api/family/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  rows: [] as TeacherHomeworkReviewItem[],
  activeSubmissionId: 0,
  score: 100,
  reviewContent: '',
})

onLoad(loadRows)
onPullDownRefresh(refresh)

async function loadRows(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getTeacherHomeworkSubmissions({ status: 'submitted' })
    state.rows = result.list
    state.status = state.rows.length === 0 ? 'empty' : 'success'
    state.message = state.rows.length === 0 ? 'No homework submissions' : ''
  }
  catch (error) {
    state.rows = []
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function submitReview(row: TeacherHomeworkReviewItem): Promise<void> {
  try {
    await reviewTeacherHomework({
      homework_submission_id: row.homework_submission_id,
      score: state.score,
      content: state.reviewContent || 'Reviewed',
    })
    state.activeSubmissionId = row.homework_submission_id
    state.message = 'Review submitted'
    await loadRows()
  }
  catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function refresh(): Promise<void> {
  try {
    await loadRows()
  }
  finally {
    uni.stopPullDownRefresh?.()
  }
}

function isForbidden(error: unknown): boolean {
  const code = (error as { code?: number })?.code

  return code === 401 || code === 403
}

function errorMessage(error: unknown): string {
  return (error as { message?: string })?.message || 'Request failed'
}
</script>

<template>
  <view class="page">
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading submissions" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="loadRows" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="loadRows" />
    <view v-else class="list">
      <view v-for="row in state.rows" :key="row.homework_submission_id" class="card">
        <text class="title">{{ row.title || `Submission ${row.homework_submission_id}` }}</text>
        <text class="meta">Student {{ row.student_name || row.student_id }}</text>
        <input v-model.number="state.score" class="input" type="number" />
        <textarea v-model="state.reviewContent" class="textarea" placeholder="Review content" />
        <button class="primary" @tap="submitReview(row)">Review</button>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.list { display: flex; flex-direction: column; gap: 16rpx; }
.card { display: flex; flex-direction: column; gap: 12rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.meta { color: #5f6f86; }
.input, .textarea { padding: 16rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; }
.textarea { min-height: 160rpx; }
.primary { background: #2456a6; color: #ffffff; }
</style>
