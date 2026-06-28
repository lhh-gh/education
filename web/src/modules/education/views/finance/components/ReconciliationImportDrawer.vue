<script setup lang="ts">
import { importReconciliationBatch } from '../../../api/finance/reconciliation.ts'
import { financePageText, paymentChannelTypeLabel } from '../financeRules.ts'

const props = defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean], 'success': [] }>()
const form = reactive({ channel_code: 'wechat', business_date: '', file_url: '' })
const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

async function submit() {
  await importReconciliationBatch({ ...form })
  visible.value = false
  emit('success')
}
</script>

<template>
  <el-drawer v-model="visible" :title="financePageText.drawers.reconciliationImport.title" size="420px">
    <el-form label-width="120px" :model="form">
      <el-form-item :label="financePageText.drawers.reconciliationImport.fields.channel">
        <el-select v-model="form.channel_code">
          <el-option :label="paymentChannelTypeLabel('wechat')" value="wechat" />
          <el-option :label="paymentChannelTypeLabel('offline_cash')" value="offline_cash" />
        </el-select>
      </el-form-item>
      <el-form-item :label="financePageText.drawers.reconciliationImport.fields.businessDate">
        <el-date-picker v-model="form.business_date" type="date" value-format="YYYY-MM-DD" />
      </el-form-item>
      <el-form-item :label="financePageText.drawers.reconciliationImport.fields.fileUrl">
        <el-input v-model="form.file_url" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="submit">
          {{ financePageText.drawers.reconciliationImport.import }}
        </el-button>
      </el-form-item>
    </el-form>
  </el-drawer>
</template>
