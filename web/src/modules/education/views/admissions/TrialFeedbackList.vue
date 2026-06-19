<script setup lang="ts">
import { saveTrialFeedback } from '../../api/admissions/trial.ts'
import { admissionErrorText } from './admissionRules.ts'

defineOptions({ name: 'EducationAdmissionTrialFeedbackList' })

const errorText = ref('')
const successText = ref('')
const form = reactive({ tenant_id: undefined as number | undefined, campus_id: undefined as number | undefined, trial_lesson_id: undefined as number | undefined, feedback_type: 'teacher', score: 4, content: '' })

async function submitFeedback() {
  try {
    await saveTrialFeedback(form as any)
    successText.value = '反馈已保存'
  }
  catch (error: any) {
    errorText.value = admissionErrorText(error)
  }
}
</script>

<template>
  <div class="mine-layout admission-page pt-3">
    <el-card shadow="never">
      <template #header><div class="page-header"><span>试听反馈</span><el-button type="primary" @click="submitFeedback">保存反馈</el-button></div></template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" />
      <el-form :model="form" label-width="120px">
        <el-form-item label="试听课次"><el-input-number v-model="form.trial_lesson_id" :controls="false" /></el-form-item>
        <el-form-item label="评分"><el-input-number v-model="form.score" :min="0" :max="5" /></el-form-item>
        <el-form-item label="内容"><el-input v-model="form.content" type="textarea" /></el-form-item>
      </el-form>
    </el-card>
  </div>
</template>
