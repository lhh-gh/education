<script setup lang="ts">
import { convertLead } from '../../api/admissions/lead.ts'
import { admissionErrorText } from './admissionRules.ts'

defineOptions({ name: 'EducationAdmissionConversionWorkbench' })

const errorText = ref('')
const result = ref<Record<string, number> | null>(null)
const form = reactive({ tenant_id: undefined as number | undefined, campus_id: undefined as number | undefined, lead_id: undefined as number | undefined, student_name: '', guardian_name: '', lesson_package_id: undefined as number | undefined, paid_amount: '0.00' })

async function submitConversion() {
  if (!form.lead_id || !form.lesson_package_id) {
    errorText.value = 'Lead and package are required'
    return
  }
  try {
    const response = await convertLead(form.lead_id, form as any)
    result.value = response.data
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = admissionErrorText(error)
  }
}
</script>

<template>
  <div class="mine-layout admission-page pt-3">
    <el-card shadow="never">
      <template #header><div class="page-header"><span>Conversion Workbench</span><el-button type="primary" @click="submitConversion">Convert</el-button></div></template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :model="form" label-width="130px">
        <el-form-item label="Lead"><el-input-number v-model="form.lead_id" :controls="false" /></el-form-item>
        <el-form-item label="Student"><el-input v-model="form.student_name" /></el-form-item>
        <el-form-item label="Guardian"><el-input v-model="form.guardian_name" /></el-form-item>
        <el-form-item label="Package"><el-input-number v-model="form.lesson_package_id" :controls="false" /></el-form-item>
      </el-form>
      <el-result v-if="result" icon="success" title="Conversion succeeded" :sub-title="`Student #${result.student_id}, Enrollment #${result.enrollment_id}`" />
    </el-card>
  </div>
</template>
