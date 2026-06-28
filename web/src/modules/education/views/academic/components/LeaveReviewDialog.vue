<script setup lang="ts">
import type { LeaveRequestRecord } from '../../../api/academic/lessonChange.ts'
import { approveLeaveRequest, rejectLeaveRequest } from '../../../api/academic/lessonChange.ts'
import { useMessage } from '@/hooks/useMessage.ts'

defineOptions({ name: 'EducationLeaveReviewDialog' })

const props = defineProps<{
  modelValue: boolean
  action: 'approve' | 'reject'
  row?: LeaveRequestRecord | null
  tenantId?: number
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: boolean): void
  (event: 'success', row: LeaveRequestRecord): void
}>()

const message = useMessage()
const submitting = ref(false)
const reviewRemark = ref('')
const errorText = ref('')
const title = computed(() => props.action === 'approve' ? 'Approve Leave' : 'Reject Leave')

watch(() => props.modelValue, (visible) => {
  if (visible) {
    reviewRemark.value = ''
    errorText.value = ''
  }
})

async function handleSubmit() {
  if (!props.row) {
    return
  }
  submitting.value = true
  try {
    const request = props.action === 'approve' ? approveLeaveRequest : rejectLeaveRequest
    const response = await request(props.row.id, reviewRemark.value, props.tenantId)
    emit('success', response.data)
    emit('update:modelValue', false)
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Leave review failed'
    message.error(errorText.value)
  }
  finally {
    submitting.value = false
  }
}
</script>

<template>
  <el-dialog :model-value="modelValue" :title="title" width="460px" @update:model-value="emit('update:modelValue', $event)">
    <el-alert v-if="errorText" class="mb-3" type="error" show-icon :closable="false" :title="errorText" />
    <el-form label-width="120px">
      <el-form-item label="Leave No">
        <span>{{ row?.leave_no }}</span>
      </el-form-item>
      <el-form-item label="Remark" required>
        <el-input v-model="reviewRemark" type="textarea" :rows="4" maxlength="500" show-word-limit />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="emit('update:modelValue', false)">
        Cancel
      </el-button>
      <el-button type="primary" :loading="submitting" @click="handleSubmit">
        Confirm
      </el-button>
    </template>
  </el-dialog>
</template>
