<script setup lang="ts">
import { convertLead } from '../../api/admissions/lead.ts'
import { admissionErrorText } from './admissionRules.ts'
import { useEducationScope } from '@/composables/education/useEducationScope.ts'

defineOptions({ name: 'EducationAdmissionConversionWorkbench' })

const { scope } = useEducationScope()
const errorText = ref('')
const result = ref<Record<string, number> | null>(null)
const form = reactive({ lead_id: undefined as number | undefined, student_name: '', guardian_name: '', lesson_package_id: undefined as number | undefined, paid_amount: '0.00' })

async function submitConversion() {
  if (!form.lead_id || !form.lesson_package_id) {
    errorText.value = '线索和课包必填'
    return
  }
  try {
    const response = await convertLead(form.lead_id, { ...form, tenant_id: scope.tenant_id, campus_id: scope.campus_id } as any)
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
      <template #header><div class="page-header"><span>线索转化</span><el-button type="primary" @click="submitConversion">转化</el-button></div></template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :model="form" label-width="130px">
        <el-form-item label="线索"><el-input-number v-model="form.lead_id" :controls="false" /></el-form-item>
        <el-form-item label="学员"><el-input v-model="form.student_name" /></el-form-item>
        <el-form-item label="家长"><el-input v-model="form.guardian_name" /></el-form-item>
        <el-form-item label="课包"><el-input-number v-model="form.lesson_package_id" :controls="false" /></el-form-item>
      </el-form>
      <el-result v-if="result" icon="success" title="转化成功" :sub-title="`学员 #${result.student_id}，报名 #${result.enrollment_id}`" />
    </el-card>
  </div>
</template>
