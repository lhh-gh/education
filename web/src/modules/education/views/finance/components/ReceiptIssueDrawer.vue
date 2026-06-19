<script setup lang="ts">
import type { FinanceOrderRecord } from '../../../api/finance/order.ts'
import { issueReceipt } from '../../../api/finance/receipt.ts'
import { financePageText } from '../financeRules.ts'

const props = defineProps<{ modelValue: boolean, order: FinanceOrderRecord | null }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean], 'success': [] }>()
const form = reactive({ amount_cents: 0, pdf_url: '' })
const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

watch(() => props.order, (order) => {
  form.amount_cents = Math.max(0, (order?.paid_amount_cents ?? 0) - (order?.refund_amount_cents ?? 0))
})

async function submit() {
  if (!props.order?.id && !props.order?.order_id) {
    return
  }
  await issueReceipt({
    order_id: props.order.order_id ?? props.order.id!,
    amount_cents: form.amount_cents,
    pdf_url: form.pdf_url || undefined,
    tenant_id: props.order.tenant_id,
    campus_id: props.order.campus_id ?? undefined,
  } as any)
  visible.value = false
  emit('success')
}
</script>

<template>
  <el-drawer v-model="visible" :title="financePageText.drawers.receiptIssue.title" size="420px">
    <el-form label-width="110px" :model="form">
      <el-form-item :label="financePageText.drawers.receiptIssue.fields.amount">
        <el-input-number v-model="form.amount_cents" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item :label="financePageText.drawers.receiptIssue.fields.pdfUrl">
        <el-input v-model="form.pdf_url" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="submit">
          {{ financePageText.drawers.receiptIssue.issue }}
        </el-button>
      </el-form-item>
    </el-form>
  </el-drawer>
</template>
