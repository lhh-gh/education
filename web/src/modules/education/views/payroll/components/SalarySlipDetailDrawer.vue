<script setup lang="ts">
import type { SalarySlipRecord } from '../../../api/payroll/slip.ts'
import { centsToYuan, payrollTagType } from '../payrollRules.ts'

const props = defineProps<{ modelValue: boolean, row: SalarySlipRecord | null }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()
const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})
</script>

<template>
  <el-drawer v-model="visible" title="Salary Slip Detail" size="460px">
    <el-descriptions v-if="props.row" :column="1" border>
      <el-descriptions-item label="Teacher">
        {{ props.row.teacher_name || props.row.teacher_id }}
      </el-descriptions-item>
      <el-descriptions-item label="Month">
        {{ props.row.salary_month }}
      </el-descriptions-item>
      <el-descriptions-item label="Gross">
        {{ centsToYuan(props.row.gross_amount_cents) }}
      </el-descriptions-item>
      <el-descriptions-item label="Payable">
        {{ centsToYuan(props.row.payable_amount_cents) }}
      </el-descriptions-item>
      <el-descriptions-item label="Status">
        <el-tag :type="payrollTagType(props.row.status)">
          {{ props.row.status }}
        </el-tag>
      </el-descriptions-item>
    </el-descriptions>
  </el-drawer>
</template>
