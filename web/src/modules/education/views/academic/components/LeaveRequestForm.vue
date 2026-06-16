<script setup lang="ts">
import type { LeaveRequestCreatePayload, LeaveRequestRecord } from '../../../api/academic/lessonChange.ts'
import { createLeaveRequest } from '../../../api/academic/lessonChange.ts'

defineOptions({ name: 'EducationLeaveRequestForm' })

const props = defineProps<{
  modelValue: boolean
  tenantId?: number
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: boolean): void
  (event: 'created', row: LeaveRequestRecord): void
}>()

const message = useMessage()
const submitting = ref(false)
const errorText = ref('')
const form = reactive<LeaveRequestCreatePayload>({
  tenant_id: props.tenantId,
  lesson_student_id: 0,
  source: 'staff',
  leave_type: 'sick',
  reason: '',
  makeup_required: true,
  remark: '',
})

watch(() => props.modelValue, (visible) => {
  if (visible) {
    form.tenant_id = props.tenantId
    form.lesson_student_id = 0
    form.source = 'staff'
    form.leave_type = 'sick'
    form.guardian_id = undefined
    form.teacher_id = undefined
    form.reason = ''
    form.makeup_required = true
    form.remark = ''
    errorText.value = ''
  }
})

async function handleSubmit() {
  submitting.value = true
  try {
    const response = await createLeaveRequest(form)
    emit('created', response.data)
    emit('update:modelValue', false)
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Leave request creation failed'
    message.error(errorText.value)
  }
  finally {
    submitting.value = false
  }
}
</script>

<template>
  <el-drawer :model-value="modelValue" title="Create Leave Request" size="520px" @update:model-value="emit('update:modelValue', $event)">
    <el-alert v-if="errorText" class="mb-3" type="error" show-icon :closable="false" :title="errorText" />
    <el-form label-width="140px" :model="form">
      <el-form-item label="Lesson Student ID" required>
        <el-input-number v-model="form.lesson_student_id" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item label="Source" required>
        <el-segmented v-model="form.source" :options="[{ label: 'Staff', value: 'staff' }, { label: 'Guardian', value: 'guardian' }, { label: 'Teacher', value: 'teacher' }]" />
      </el-form-item>
      <el-form-item label="Leave Type" required>
        <el-select v-model="form.leave_type">
          <el-option label="Sick" value="sick" />
          <el-option label="Personal" value="personal" />
          <el-option label="School" value="school" />
          <el-option label="Other" value="other" />
        </el-select>
      </el-form-item>
      <el-form-item v-if="form.source === 'guardian'" label="Guardian ID">
        <el-input-number v-model="form.guardian_id" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item v-if="form.source === 'teacher'" label="Teacher ID">
        <el-input-number v-model="form.teacher_id" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item label="Make-up Required">
        <el-switch v-model="form.makeup_required" />
      </el-form-item>
      <el-form-item label="Reason" required>
        <el-input v-model="form.reason" type="textarea" :rows="4" maxlength="500" show-word-limit />
      </el-form-item>
      <el-form-item label="Remark">
        <el-input v-model="form.remark" type="textarea" :rows="3" maxlength="500" show-word-limit />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="emit('update:modelValue', false)">
        Cancel
      </el-button>
      <el-button type="primary" :loading="submitting" @click="handleSubmit">
        Save
      </el-button>
    </template>
  </el-drawer>
</template>
