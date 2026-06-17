<script setup lang="ts">
import { importReconciliationBatch } from '../../../api/finance/reconciliation.ts'

const props = defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean], success: [] }>()
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
  <el-drawer v-model="visible" title="Import Reconciliation" size="420px">
    <el-form label-width="120px" :model="form">
      <el-form-item label="Channel">
        <el-select v-model="form.channel_code">
          <el-option label="WeChat" value="wechat" />
          <el-option label="Cash" value="offline_cash" />
        </el-select>
      </el-form-item>
      <el-form-item label="Business Date">
        <el-date-picker v-model="form.business_date" type="date" value-format="YYYY-MM-DD" />
      </el-form-item>
      <el-form-item label="File URL">
        <el-input v-model="form.file_url" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="submit">
          Import
        </el-button>
      </el-form-item>
    </el-form>
  </el-drawer>
</template>
