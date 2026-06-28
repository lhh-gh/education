<script setup lang="ts">
import type { SalarySlipRecord } from '../../../api/payroll/slip.ts'
import { centsToYuan, payrollStatusLabel, payrollTagType } from '../payrollRules.ts'

const props = defineProps<{ modelValue: boolean, row: SalarySlipRecord | null }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()
const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})
</script>

<template>
  <el-drawer v-model="visible" title="工资条详情" size="460px">
    <el-descriptions v-if="props.row" :column="1" border>
      <el-descriptions-item label="教师">
        {{ props.row.teacher_name || props.row.teacher_id }}
      </el-descriptions-item>
      <el-descriptions-item label="月份">
        {{ props.row.salary_month }}
      </el-descriptions-item>
      <el-descriptions-item label="应发">
        {{ centsToYuan(props.row.gross_amount_cents) }}
      </el-descriptions-item>
      <el-descriptions-item label="实发">
        {{ centsToYuan(props.row.payable_amount_cents) }}
      </el-descriptions-item>
      <el-descriptions-item label="状态">
        <el-tag :type="payrollTagType(props.row.status)">
          {{ payrollStatusLabel(props.row.status) }}
        </el-tag>
      </el-descriptions-item>
    </el-descriptions>
  </el-drawer>
</template>
