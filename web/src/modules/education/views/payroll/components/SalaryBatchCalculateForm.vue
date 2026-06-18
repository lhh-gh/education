<script setup lang="ts">
import type { SalaryBatchCalculatePayload } from '../../../api/payroll/batch.ts'
import { calculateSalaryBatch } from '../../../api/payroll/batch.ts'

const props = defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean], 'success': [] }>()
const form = reactive<SalaryBatchCalculatePayload>({ salary_month: '', campus_id: undefined })
const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

async function submit() {
  await calculateSalaryBatch(form)
  visible.value = false
  emit('success')
}
</script>

<template>
  <el-dialog v-model="visible" title="Calculate Salary Batch" width="460px">
    <el-form label-width="120px">
      <el-form-item label="Salary Month">
        <el-date-picker v-model="form.salary_month" type="month" value-format="YYYY-MM" />
      </el-form-item>
      <el-form-item label="Campus ID">
        <el-input-number v-model="form.campus_id" :min="1" />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="visible = false">
        Cancel
      </el-button>
      <el-button type="primary" @click="submit">
        Calculate
      </el-button>
    </template>
  </el-dialog>
</template>
