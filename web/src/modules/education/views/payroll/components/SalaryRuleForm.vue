<script setup lang="ts">
import type { SalaryRulePayload } from '../../../api/payroll/rule.ts'
import { saveSalaryRule } from '../../../api/payroll/rule.ts'

const props = defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean], success: [] }>()
const form = reactive<SalaryRulePayload>({
  rule_name: '',
  rule_type: 'lesson',
  base_amount_cents: 0,
  unit_amount_cents: 0,
  status: 'enabled',
})
const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

async function submit() {
  await saveSalaryRule(form)
  visible.value = false
  emit('success')
}
</script>

<template>
  <el-drawer v-model="visible" title="Salary Rule" size="460px">
    <el-form label-width="130px">
      <el-form-item label="Rule Name">
        <el-input v-model="form.rule_name" />
      </el-form-item>
      <el-form-item label="Rule Type">
        <el-select v-model="form.rule_type">
          <el-option label="Lesson" value="lesson" />
          <el-option label="Workload" value="workload" />
          <el-option label="Performance" value="performance" />
        </el-select>
      </el-form-item>
      <el-form-item label="Base Amount">
        <el-input-number v-model="form.base_amount_cents" :min="0" :step="1000" />
      </el-form-item>
      <el-form-item label="Unit Amount">
        <el-input-number v-model="form.unit_amount_cents" :min="0" :step="1000" />
      </el-form-item>
      <el-form-item label="Status">
        <el-radio-group v-model="form.status">
          <el-radio-button label="enabled" />
          <el-radio-button label="disabled" />
        </el-radio-group>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="submit">
          Save
        </el-button>
      </el-form-item>
    </el-form>
  </el-drawer>
</template>
