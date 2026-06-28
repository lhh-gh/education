<script setup lang="ts">
import type { FinanceOrderRecord } from '../../../api/finance/order.ts'
import { confirmOfflinePayment } from '../../../api/finance/payment.ts'
import { financePageText, paymentChannelTypeLabel } from '../financeRules.ts'

const props = defineProps<{ modelValue: boolean, order: FinanceOrderRecord | null }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean], 'success': [] }>()

const form = reactive({ channel_code: 'offline_cash', payment_no: '', amount_cents: 0, payer_name: '', remark: '' })
const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

watch(() => props.order, (order) => {
  form.amount_cents = Math.max(0, (order?.total_amount_cents ?? 0) - (order?.paid_amount_cents ?? 0))
})

async function submit() {
  if (!props.order?.id && !props.order?.order_id) {
    return
  }
  await confirmOfflinePayment({
    order_id: props.order.order_id ?? props.order.id!,
    channel_code: form.channel_code,
    payment_no: form.payment_no || undefined,
    amount_cents: form.amount_cents,
    payer_name: form.payer_name || undefined,
    remark: form.remark || undefined,
    tenant_id: props.order.tenant_id,
    campus_id: props.order.campus_id ?? undefined,
  } as any)
  visible.value = false
  emit('success')
}
</script>

<template>
  <el-drawer v-model="visible" :title="financePageText.drawers.offlineCollection.title" size="420px">
    <el-form label-width="120px" :model="form">
      <el-form-item :label="financePageText.drawers.offlineCollection.fields.channel">
        <el-select v-model="form.channel_code">
          <el-option :label="paymentChannelTypeLabel('offline_cash')" value="offline_cash" />
          <el-option :label="paymentChannelTypeLabel('offline_bank')" value="offline_bank" />
          <el-option label="POS" value="offline_pos" />
        </el-select>
      </el-form-item>
      <el-form-item :label="financePageText.drawers.offlineCollection.fields.paymentNo">
        <el-input v-model="form.payment_no" />
      </el-form-item>
      <el-form-item :label="financePageText.drawers.offlineCollection.fields.amount">
        <el-input-number v-model="form.amount_cents" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item :label="financePageText.drawers.offlineCollection.fields.payer">
        <el-input v-model="form.payer_name" />
      </el-form-item>
      <el-form-item :label="financePageText.drawers.offlineCollection.fields.remark">
        <el-input v-model="form.remark" type="textarea" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="submit">
          {{ financePageText.drawers.offlineCollection.confirm }}
        </el-button>
      </el-form-item>
    </el-form>
  </el-drawer>
</template>
