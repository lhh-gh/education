<script setup lang="ts">
import type { SalaryRulePayload } from '../../../api/payroll/rule.ts'
import { saveSalaryRule } from '../../../api/payroll/rule.ts'
import { payrollStatusLabel, payrollTypeLabel } from '../payrollRules.ts'

const props = defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean], 'success': [] }>()
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
  <el-drawer v-model="visible" title="薪酬规则" size="460px">
    <el-form label-width="130px">
      <el-form-item label="规则名称">
        <el-input v-model="form.rule_name" />
      </el-form-item>
      <el-form-item label="规则类型">
        <el-select v-model="form.rule_type">
          <el-option :label="payrollTypeLabel('lesson')" value="lesson" />
          <el-option :label="payrollTypeLabel('workload')" value="workload" />
          <el-option :label="payrollTypeLabel('performance')" value="performance" />
        </el-select>
      </el-form-item>
      <el-form-item label="基础金额">
        <el-input-number v-model="form.base_amount_cents" :min="0" :step="1000" />
      </el-form-item>
      <el-form-item label="单价">
        <el-input-number v-model="form.unit_amount_cents" :min="0" :step="1000" />
      </el-form-item>
      <el-form-item label="状态">
        <el-radio-group v-model="form.status">
          <el-radio-button label="enabled">
            {{ payrollStatusLabel('enabled') }}
          </el-radio-button>
          <el-radio-button label="disabled">
            {{ payrollStatusLabel('disabled') }}
          </el-radio-button>
        </el-radio-group>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="submit">
          保存
        </el-button>
      </el-form-item>
    </el-form>
  </el-drawer>
</template>
