<script setup lang="ts">
import type { SalarySlipRecord } from '../../../api/payroll/slip.ts'
import { createSalaryAdjustment } from '../../../api/payroll/slip.ts'
import { centsToYuan } from '../payrollRules.ts'

const props = defineProps<{ modelValue: boolean, row: SalarySlipRecord | null }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean], 'success': [] }>()
const form = reactive({ amount_cents: 0, reason: '' })
const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

async function submit() {
  if (!props.row) {
    return
  }
  await createSalaryAdjustment(props.row.id, form)
  visible.value = false
  emit('success')
}
</script>

<template>
  <el-dialog v-model="visible" title="薪酬调整" width="460px">
    <el-alert v-if="props.row" type="info" :closable="false" :title="`当前实发 ${centsToYuan(props.row.payable_amount_cents)}`" />
    <el-form class="mt-3" label-width="120px">
      <el-form-item label="金额">
        <el-input-number v-model="form.amount_cents" :step="1000" />
      </el-form-item>
      <el-form-item label="原因">
        <el-input v-model="form.reason" type="textarea" />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="visible = false">
        取消
      </el-button>
      <el-button type="primary" @click="submit">
        保存
      </el-button>
    </template>
  </el-dialog>
</template>
