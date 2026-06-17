<script setup lang="ts">
import { saveContract } from '../../../api/group/contract.ts'
import { activeContractRiskWarning } from '../groupRules.ts'

const props = defineProps<{ modelValue: boolean, editing?: { status?: string, amount_cents?: number, risk_level?: string } | null }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean], 'success': [] }>()

const saving = ref(false)
const form = reactive({ contract_no: '', contract_type: 'lease', title: '', counterparty_name: '', amount_cents: 0, risk_level: 'normal' })
const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})
const riskWarning = computed(() => props.editing ? activeContractRiskWarning(props.editing, form) : '')

async function submit() {
  saving.value = true
  try {
    await saveContract(form)
    visible.value = false
    emit('success')
  }
  finally {
    saving.value = false
  }
}
</script>

<template>
  <el-dialog v-model="visible" title="Contract" width="640px">
    <el-alert v-if="riskWarning" class="mb-3" type="warning" :title="riskWarning" show-icon />
    <el-form :model="form" label-width="128px">
      <el-form-item label="No.">
        <el-input v-model="form.contract_no" />
      </el-form-item>
      <el-form-item label="Type">
        <el-input v-model="form.contract_type" />
      </el-form-item>
      <el-form-item label="Title">
        <el-input v-model="form.title" />
      </el-form-item>
      <el-form-item label="Counterparty">
        <el-input v-model="form.counterparty_name" />
      </el-form-item>
      <el-form-item label="Amount Cents">
        <el-input-number v-model="form.amount_cents" :min="0" />
      </el-form-item>
      <el-form-item label="Risk">
        <el-select v-model="form.risk_level">
          <el-option label="Normal" value="normal" />
          <el-option label="Warning" value="warning" />
          <el-option label="High" value="high" />
          <el-option label="Critical" value="critical" />
        </el-select>
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="visible = false">
        Cancel
      </el-button>
      <el-button type="primary" :loading="saving" @click="submit">
        Save
      </el-button>
    </template>
  </el-dialog>
</template>
