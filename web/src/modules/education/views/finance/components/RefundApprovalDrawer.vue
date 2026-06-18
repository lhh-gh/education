<script setup lang="ts">
import type { RefundRequestRecord } from '../../../api/finance/refund.ts'
import { approveRefundRequest } from '../../../api/finance/refund.ts'
import { validateRefundAmount } from '../financeRules.ts'

const props = defineProps<{ modelValue: boolean, row: RefundRequestRecord | null, refundableCents?: number }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean], 'success': [] }>()
const form = reactive({ review_note: '' })
const errorText = computed(() => props.row ? validateRefundAmount(props.row.refund_amount_cents, props.refundableCents ?? props.row.refund_amount_cents) : '')
const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

async function submit() {
  if (!props.row || errorText.value) {
    return
  }
  await approveRefundRequest(props.row.id, { review_note: form.review_note })
  visible.value = false
  emit('success')
}
</script>

<template>
  <el-drawer v-model="visible" title="Refund Approval" size="420px">
    <el-alert v-if="errorText" type="error" show-icon :closable="false" :title="errorText" />
    <el-form class="mt-3" label-width="110px">
      <el-form-item label="Refund">
        <span>{{ row?.refund_no }}</span>
      </el-form-item>
      <el-form-item label="Review Note">
        <el-input v-model="form.review_note" type="textarea" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" :disabled="!!errorText" @click="submit">
          Approve
        </el-button>
      </el-form-item>
    </el-form>
  </el-drawer>
</template>
