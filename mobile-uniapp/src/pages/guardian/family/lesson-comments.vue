<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { GuardianLessonComment } from '@/api/family/guardian'
import { getGuardianLessonComments, markGuardianFamilyRead } from '@/api/family/guardian'
import GuardianStateBlock from '@/pages/guardian/components/GuardianStateBlock.vue'

const readCommentIds = new Set<number>()
const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  studentId: 0,
  rows: [] as GuardianLessonComment[],
})

onLoad((query = {}) => {
  state.studentId = Number((query as Record<string, string>).student_id || uni.getStorageSync('guardian_selected_student_id') || 0)
  loadRows()
})
onPullDownRefresh(refresh)

async function loadRows(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getGuardianLessonComments({ student_id: state.studentId })
    state.rows = result.list.filter(row => row.status === 'published')
    state.status = state.rows.length === 0 ? 'empty' : 'success'
    state.message = state.rows.length === 0 ? 'No published comments' : ''
    await markVisibleCommentsRead()
  }
  catch (error) {
    state.rows = []
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function markVisibleCommentsRead(): Promise<void> {
  for (const row of state.rows) {
    if (readCommentIds.has(row.id)) {
      continue
    }
    readCommentIds.add(row.id)
    await markGuardianFamilyRead({ student_id: state.studentId, business_type: 'lesson_comment', business_id: row.id })
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
    <GuardianStateBlock v-if="state.status === 'loading'" state="loading" message="Loading comments" />
    <GuardianStateBlock v-else-if="state.status === 'empty'" state="empty" :message="state.message" />
    <GuardianStateBlock v-else-if="state.status === 'forbidden'" state="forbidden" :message="state.message" @retry="loadRows" />
    <GuardianStateBlock v-else-if="state.status === 'error'" state="error" :message="state.message" @retry="loadRows" />
    <view v-else class="list">
      <view v-for="row in state.rows" :key="row.id" class="card">
        <text class="title">Lesson {{ row.lesson_id }}</text>
        <text class="content">{{ row.content }}</text>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f7f8f3; color: #172033; }
.list { display: flex; flex-direction: column; gap: 16rpx; }
.card { display: flex; flex-direction: column; gap: 10rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.content { color: #5f6f86; line-height: 1.5; }
</style>
