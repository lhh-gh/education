<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import { submitTeacherWorkloadDispute } from '@/api/payroll/teacher'

const state = reactive({
  submitting: false,
  message: '',
  sourceWorkloadId: 0,
  salarySlipId: undefined as number | undefined,
  disputeType: 'lesson_count',
  content: '',
})

onLoad((query = {}) => {
  state.sourceWorkloadId = Number((query as Record<string, string>).source_workload_id || 0)
  const slipId = Number((query as Record<string, string>).salary_slip_id || 0)
  state.salarySlipId = slipId > 0 ? slipId : undefined
})

async function submit(): Promise<void> {
  state.submitting = true
  state.message = ''
  try {
    await submitTeacherWorkloadDispute({
      source_workload_id: state.sourceWorkloadId,
      salary_slip_id: state.salarySlipId,
      dispute_type: state.disputeType,
      content: state.content,
    })
    uni.navigateBack()
  }
  catch (error) {
    state.message = (error as { message?: string })?.message || 'Submit failed'
  }
  finally {
    state.submitting = false
  }
}
</script>

<template>
  <view class="page">
    <view class="form-card">
      <label class="field">
        <text>Workload ID</text>
        <input v-model.number="state.sourceWorkloadId" type="number" placeholder="Required" />
      </label>
      <label class="field">
        <text>Salary Slip ID</text>
        <input v-model.number="state.salarySlipId" type="number" placeholder="Optional" />
      </label>
      <label class="field">
        <text>Type</text>
        <input v-model="state.disputeType" placeholder="lesson_count" />
      </label>
      <label class="field">
        <text>Content</text>
        <textarea v-model="state.content" placeholder="Describe the workload issue" />
      </label>
      <text v-if="state.message" class="error-text">{{ state.message }}</text>
      <button class="submit-button" :disabled="state.submitting" @tap="submit">
        Submit
      </button>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.form-card { display: flex; flex-direction: column; gap: 20rpx; padding: 28rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.field { display: flex; flex-direction: column; gap: 10rpx; color: #5f6f86; }
input, textarea { min-height: 76rpx; padding: 0 18rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; color: #172033; }
textarea { min-height: 180rpx; padding-top: 18rpx; }
.error-text { color: #8a1f1f; }
.submit-button { min-height: 76rpx; border-radius: 8rpx; background: #2456a6; color: #ffffff; }
</style>
