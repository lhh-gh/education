<script setup lang="ts">
import { reactive } from 'vue'
import { createGuardianConsultation } from '@/api/admissions/guardian'
import GuardianStateBlock from '@/pages/guardian/components/GuardianStateBlock.vue'

const state = reactive({
  status: 'form' as 'form' | 'submitting' | 'success' | 'conflict' | 'error',
  message: '',
  leadId: undefined as number | undefined,
  form: {
    contact_name: '',
    contact_mobile: '',
    student_name: '',
    student_age: undefined as number | undefined,
    interested_course: '',
  },
})

async function submit(): Promise<void> {
  const validation = validateForm()
  if (validation !== '') {
    state.status = 'error'
    state.message = validation
    return
  }

  state.status = 'submitting'
  state.message = ''
  try {
    const result = await createGuardianConsultation({ ...state.form })
    state.status = 'success'
    state.leadId = result.lead_id || result.id
    state.message = 'Consultation submitted'
  }
  catch (error) {
    state.status = isConflict(error) ? 'conflict' : 'error'
    state.message = errorMessage(error)
  }
}

function validateForm(): string {
  if (state.form.contact_mobile.trim() === '') {
    return 'Contact mobile is required'
  }
  if (state.form.contact_name.trim() === '') {
    return 'Contact name is required'
  }
  if (state.form.student_name.trim() === '') {
    return 'Student name is required'
  }

  return ''
}

function resetForm(): void {
  state.status = 'form'
  state.message = ''
}

function isConflict(error: unknown): boolean {
  return (error as { code?: number })?.code === 409
}

function errorMessage(error: unknown): string {
  return (error as { message?: string })?.message || 'Request failed'
}
</script>

<template>
  <view class="page">
    <GuardianStateBlock v-if="state.status === 'submitting'" state="loading" message="Submitting consultation" />
    <GuardianStateBlock v-else-if="state.status === 'conflict'" state="conflict" :message="state.message" retry-text="Edit" @retry="resetForm" />
    <view v-else-if="state.status === 'success'" class="panel success">
      <text class="title">Consultation Submitted</text>
      <text class="meta">Lead {{ state.leadId || '-' }}</text>
      <button class="primary" @tap="resetForm">New Consultation</button>
    </view>
    <view v-else class="panel">
      <text class="title">Admission Consultation</text>
      <input v-model="state.form.contact_name" class="input" placeholder="Contact name" />
      <input v-model="state.form.contact_mobile" class="input" placeholder="Contact mobile" type="number" />
      <input v-model="state.form.student_name" class="input" placeholder="Student name" />
      <input v-model.number="state.form.student_age" class="input" placeholder="Student age" type="number" />
      <input v-model="state.form.interested_course" class="input" placeholder="Interested course" />
      <text v-if="state.message" class="error">{{ state.message }}</text>
      <button class="primary" @tap="submit">Submit</button>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f7f8f3; color: #172033; }
.panel { display: flex; flex-direction: column; gap: 18rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 32rpx; font-weight: 700; }
.input { min-height: 78rpx; padding: 0 18rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #f8fafc; }
.primary { min-height: 76rpx; border-radius: 8rpx; background: #2456a6; color: #ffffff; }
.error { color: #8a1f1f; line-height: 1.5; }
.meta { color: #17623a; }
.success { border-color: #b8e4cc; }
</style>
