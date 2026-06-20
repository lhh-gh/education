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
  <el-dialog v-model="visible" title="合同信息" width="640px">
    <el-alert v-if="riskWarning" class="mb-3" type="warning" :title="riskWarning" show-icon />
    <el-form :model="form" label-width="128px">
      <el-form-item label="合同编号">
        <el-input v-model="form.contract_no" />
      </el-form-item>
      <el-form-item label="合同类型">
        <el-input v-model="form.contract_type" />
      </el-form-item>
      <el-form-item label="合同标题">
        <el-input v-model="form.title" />
      </el-form-item>
      <el-form-item label="相对方">
        <el-input v-model="form.counterparty_name" />
      </el-form-item>
      <el-form-item label="金额(分)">
        <el-input-number v-model="form.amount_cents" :min="0" />
      </el-form-item>
      <el-form-item label="风险等级">
        <el-select v-model="form.risk_level">
          <el-option label="正常" value="normal" />
          <el-option label="预警" value="warning" />
          <el-option label="高风险" value="high" />
          <el-option label="严重" value="critical" />
        </el-select>
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="visible = false">
        取消
      </el-button>
      <el-button type="primary" :loading="saving" @click="submit">
        保存
      </el-button>
    </template>
  </el-dialog>
</template>
