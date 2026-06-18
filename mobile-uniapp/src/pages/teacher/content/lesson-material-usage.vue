<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import { createTeacherLessonMaterialUsage } from '@/api/content/teacher'

const state = reactive({
  lessonId: 0,
  materialId: 0,
  usageType: 'pre_class',
  remark: '',
  message: '',
  status: 'idle' as 'idle' | 'submitted' | 'error',
})

onLoad((query = {}) => {
  state.materialId = Number((query as Record<string, string>).material_id || 0)
  state.lessonId = Number((query as Record<string, string>).lesson_id || 0)
})

async function submitUsage(): Promise<void> {
  try {
    await createTeacherLessonMaterialUsage({
      lesson_id: state.lessonId,
      material_id: state.materialId,
      usage_type: state.usageType,
      remark: state.remark,
    })
    state.status = 'submitted'
    state.message = 'Usage submitted'
  }
  catch (error) {
    state.status = 'error'
    state.message = (error as { message?: string })?.message || 'Request failed'
  }
}
</script>

<template>
  <view class="page">
    <view class="card">
      <text class="title">Lesson Material Usage</text>
      <input v-model.number="state.lessonId" class="input" type="number" placeholder="Lesson ID" />
      <input v-model.number="state.materialId" class="input" type="number" placeholder="Material ID" />
      <picker :range="['pre_class', 'in_class', 'after_class']" @change="state.usageType = ['pre_class', 'in_class', 'after_class'][Number($event.detail.value)]">
        <text class="input">{{ state.usageType }}</text>
      </picker>
      <textarea v-model="state.remark" class="textarea" placeholder="Remark" />
      <button class="primary" @tap="submitUsage">Submit</button>
      <text v-if="state.message" class="meta">{{ state.message }}</text>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.card { display: flex; flex-direction: column; gap: 16rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.input, .textarea { padding: 16rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.textarea { min-height: 160rpx; }
.primary { background: #2456a6; color: #ffffff; }
.meta { color: #5f6f86; }
</style>
