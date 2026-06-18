<script setup lang="ts">
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { TeacherMaterial } from '@/api/content/teacher'
import { getTeacherMaterials, saveTeacherMaterialFavorite } from '@/api/content/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  keyword: '',
  courseId: 0,
  rows: [] as TeacherMaterial[],
})

onLoad((query = {}) => {
  state.courseId = Number((query as Record<string, string>).course_id || 0)
  loadRows()
})
onPullDownRefresh(refresh)

async function loadRows(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getTeacherMaterials({ course_id: state.courseId || undefined, keyword: state.keyword || undefined })
    state.rows = result.list
    state.status = state.rows.length === 0 ? 'empty' : 'success'
    state.message = state.rows.length === 0 ? 'No materials' : ''
  }
  catch (error) {
    state.rows = []
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

async function toggleFavorite(row: TeacherMaterial): Promise<void> {
  const materialId = row.material_id || row.id || 0
  await saveTeacherMaterialFavorite({ material_id: materialId, favorited: !row.favorite })
  row.favorite = !row.favorite
}

function openDetail(row: TeacherMaterial): void {
  uni.navigateTo({ url: `/pages/teacher/content/material-detail?material_id=${row.material_id || row.id}` })
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
    <view class="search">
      <input v-model="state.keyword" class="input" placeholder="Search materials" />
      <button class="primary" @tap="loadRows">Search</button>
    </view>
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading materials" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="loadRows" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="loadRows" />
    <view v-else class="list">
      <view v-for="row in state.rows" :key="row.material_id || row.id" class="card">
        <text class="title" @tap="openDetail(row)">{{ row.material_name }}</text>
        <text class="meta">{{ row.material_type || 'material' }} / {{ row.status }}</text>
        <button class="secondary" @tap="toggleFavorite(row)">{{ row.favorite ? 'Unfavorite' : 'Favorite' }}</button>
      </view>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.search, .list, .card { display: flex; flex-direction: column; gap: 16rpx; }
.input { padding: 16rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.card { padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.meta { color: #5f6f86; }
.primary { background: #2456a6; color: #ffffff; }
.secondary { background: #eef4ff; color: #2456a6; }
</style>
