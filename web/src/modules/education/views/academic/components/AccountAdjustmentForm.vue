<script setup lang="ts">
import type { AccountAdjustmentRecord } from '../../../api/academic/attendanceConsumption.ts'
import { createSupplementDeduction } from '../../../api/academic/attendanceConsumption.ts'
import { buildSupplementDeductionPayload } from '../attendanceConsumptionRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'

defineOptions({ name: 'EducationAccountAdjustmentForm' })

const props = defineProps<{
  modelValue: boolean
  tenantId?: number
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: boolean): void
  (event: 'success', adjustment: AccountAdjustmentRecord): void
}>()

const message = useMessage()
const loading = ref(false)
const errorText = ref('')
const form = reactive({
  account_id: undefined as number | undefined,
  units: 1,
  reason: '',
})

watch(() => props.modelValue, (visible) => {
  if (visible) {
    form.account_id = undefined
    form.units = 1
    form.reason = ''
    errorText.value = ''
  }
})

async function handleSubmit() {
  if (!form.account_id) {
    errorText.value = 'account_id is required'
    return
  }
  loading.value = true
  try {
    const response = await createSupplementDeduction({
      tenant_id: props.tenantId,
      ...buildSupplementDeductionPayload(form.account_id, form.units, form.reason),
    })
    emit('success', response.data.adjustment)
    emit('update:modelValue', false)
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Account adjustment failed'
    message.error(errorText.value)
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <el-dialog :model-value="modelValue" title="Supplement Deduction" width="520px" @update:model-value="emit('update:modelValue', $event)">
    <el-alert v-if="errorText" class="mb-3" type="error" show-icon :closable="false" :title="errorText" />
    <el-form label-width="100px" :model="form">
      <el-form-item label="Account ID" required>
        <el-input-number v-model="form.account_id" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item label="Units" required>
        <el-input-number v-model="form.units" :min="0.01" :precision="2" :controls="false" />
      </el-form-item>
      <el-form-item label="Reason" required>
        <el-input v-model="form.reason" type="textarea" :rows="3" maxlength="500" show-word-limit />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="emit('update:modelValue', false)">
        Cancel
      </el-button>
      <el-button type="primary" :loading="loading" :disabled="!form.account_id || !form.reason.trim()" @click="handleSubmit">
        Save
      </el-button>
    </template>
  </el-dialog>
</template>
