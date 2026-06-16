<script setup lang="ts">
import type { ConsumptionRecord, RollbackResult } from '../../../api/academic/attendanceConsumption.ts'
import { rollbackConsumption } from '../../../api/academic/attendanceConsumption.ts'

defineOptions({ name: 'EducationConsumptionRollbackDialog' })

const props = defineProps<{
  modelValue: boolean
  row?: ConsumptionRecord | null
  tenantId?: number
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: boolean): void
  (event: 'success', result: RollbackResult<ConsumptionRecord>): void
}>()

const message = useMessage()
const loading = ref(false)
const reason = ref('')
const errorText = ref('')

watch(() => props.modelValue, (visible) => {
  if (visible) {
    reason.value = ''
    errorText.value = ''
  }
})

async function handleSubmit() {
  if (!props.row) {
    return
  }
  loading.value = true
  try {
    const response = await rollbackConsumption(props.row.id, reason.value, props.tenantId)
    emit('success', response.data)
    emit('update:modelValue', false)
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Consumption rollback failed'
    message.error(errorText.value)
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <el-dialog :model-value="modelValue" title="Rollback Consumption" width="480px" @update:model-value="emit('update:modelValue', $event)">
    <el-alert v-if="errorText" class="mb-3" type="error" show-icon :closable="false" :title="errorText" />
    <el-form label-width="90px">
      <el-form-item label="No">
        <span>{{ row?.consumption_no }}</span>
      </el-form-item>
      <el-form-item label="Reason" required>
        <el-input v-model="reason" type="textarea" :rows="3" maxlength="500" show-word-limit />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="emit('update:modelValue', false)">
        Cancel
      </el-button>
      <el-button type="primary" :loading="loading" :disabled="!reason.trim()" @click="handleSubmit">
        Rollback
      </el-button>
    </template>
  </el-dialog>
</template>
