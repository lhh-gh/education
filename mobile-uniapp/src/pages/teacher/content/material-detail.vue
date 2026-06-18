<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { TeacherMaterial } from '@/api/content/teacher'
import { getTeacherMaterials } from '@/api/content/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error',
  message: '',
  materialId: 0,
  material: undefined as TeacherMaterial | undefined,
})

onLoad((query = {}) => {
  state.materialId = Number((query as Record<string, string>).material_id || 0)
  loadDetail()
})

async function loadDetail(): Promise<void> {
  state.status = 'loading'
  try {
    const result = await getTeacherMaterials()
    state.material = result.list.find(item => (item.material_id || item.id) === state.materialId)
    state.status = state.material ? 'success' : 'empty'
    state.message = state.material ? '' : 'Material not found'
  }
  catch (error) {
    state.status = 'error'
    state.message = (error as { message?: string })?.message || 'Request failed'
  }
}

function useInLesson(): void {
  uni.navigateTo({ url: `/pages/teacher/content/lesson-material-usage?material_id=${state.materialId}` })
}
</script>

<template>
  <view class="page">
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading material" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="loadDetail" />
    <view v-else-if="state.material" class="card">
      <text class="title">{{ state.material.material_name }}</text>
      <text class="meta">{{ state.material.status }}</text>
      <button class="primary" @tap="useInLesson">Use in lesson</button>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.card { display: flex; flex-direction: column; gap: 16rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 34rpx; font-weight: 700; }
.meta { color: #5f6f86; }
.primary { background: #2456a6; color: #ffffff; }
</style>
