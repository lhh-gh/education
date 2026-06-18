<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { TeacherMaterial } from '@/api/content/teacher'
import { getTeacherMaterials } from '@/api/content/teacher'

const state = reactive({ rows: [] as TeacherMaterial[], message: '' })

onLoad(loadRows)

async function loadRows(): Promise<void> {
  const result = await getTeacherMaterials()
  state.rows = result.list.filter(item => item.favorite)
  state.message = state.rows.length === 0 ? 'No favorites' : ''
}
</script>

<template>
  <view class="page">
    <text v-if="state.message" class="meta">{{ state.message }}</text>
    <view v-for="row in state.rows" :key="row.material_id || row.id" class="card">
      <text class="title">{{ row.material_name }}</text>
      <text class="meta">{{ row.status }}</text>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.card { display: flex; flex-direction: column; gap: 8rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.meta { color: #5f6f86; }
</style>
