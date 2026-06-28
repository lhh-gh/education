<script setup lang="ts">
import type { WorkloadDisputeRecord } from '../../../api/payroll/dispute.ts'
import { reviewWorkloadDispute } from '../../../api/payroll/dispute.ts'
import { disputeReviewPayload, payrollStatusLabel } from '../payrollRules.ts'

const props = defineProps<{ modelValue: boolean, row: WorkloadDisputeRecord | null }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean], 'success': [] }>()
const form = reactive({ status: 'approved' as 'approved' | 'rejected', review_note: '' })
const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

async function submit() {
  if (!props.row) {
    return
  }
  await reviewWorkloadDispute(props.row.id, disputeReviewPayload(form.status, form.review_note))
  visible.value = false
  emit('success')
}
</script>

<template>
  <el-drawer v-model="visible" title="申诉复核" size="420px">
    <el-form label-width="110px">
      <el-form-item label="复核结果">
        <el-radio-group v-model="form.status">
          <el-radio-button label="approved">
            {{ payrollStatusLabel('approved') }}
          </el-radio-button>
          <el-radio-button label="rejected">
            {{ payrollStatusLabel('rejected') }}
          </el-radio-button>
        </el-radio-group>
      </el-form-item>
      <el-form-item label="复核备注">
        <el-input v-model="form.review_note" type="textarea" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="submit">
          提交
        </el-button>
      </el-form-item>
    </el-form>
  </el-drawer>
</template>
