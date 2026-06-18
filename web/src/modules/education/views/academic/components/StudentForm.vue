<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { StudentRecord, StudentSavePayload } from '../../../api/academic/profile.ts'
import { createStudent, updateStudent } from '../../../api/academic/profile.ts'

const { mode = 'create', tenantId, data = null } = defineProps<{
  mode?: 'create' | 'edit'
  tenantId?: number
  data?: StudentRecord | null
}>()

const emit = defineEmits<{ success: [] }>()
const message = useMessage()
const formRef = ref<FormInstance>()
const submitting = ref(false)
const model = reactive<StudentSavePayload>({
  tenant_id: tenantId,
  campus_id: data?.campus_id ?? 0,
  student_no: data?.student_no ?? '',
  name: data?.name ?? '',
  gender: data?.gender ?? 'unknown',
  birthday: data?.birthday ?? '',
  mobile: data?.mobile ?? '',
  school: data?.school ?? '',
  grade: data?.grade ?? '',
  source: data?.source ?? '',
  avatar: data?.avatar ?? '',
  enrolled_at: data?.enrolled_at ?? '',
  status: data?.status ?? 'enabled',
  remark: data?.remark ?? '',
})

const rules: FormRules = {
  campus_id: [{ required: true, message: 'Campus is required', trigger: 'blur' }],
  student_no: [{ required: true, message: 'Student number is required', trigger: 'blur' }],
  name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
  gender: [{ required: true, message: 'Gender is required', trigger: 'change' }],
  status: [{ required: true, message: 'Status is required', trigger: 'change' }],
}

async function submit() {
  await formRef.value?.validate()
  submitting.value = true
  try {
    if (mode === 'edit' && data?.id) {
      await updateStudent(data.id, model)
    }
    else {
      await createStudent(model)
    }
    emit('success')
  }
  catch (error: any) {
    message.error(error?.message ?? 'Student save failed')
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ submit, submitting })
</script>

<template>
  <el-form ref="formRef" :model="model" :rules="rules" label-width="120px">
    <el-form-item label="Campus ID" prop="campus_id">
      <el-input-number v-model="model.campus_id" :min="1" :controls="false" />
    </el-form-item>
    <el-form-item label="Student No" prop="student_no">
      <el-input v-model="model.student_no" maxlength="64" :disabled="mode === 'edit'" />
    </el-form-item>
    <el-form-item label="Name" prop="name">
      <el-input v-model="model.name" maxlength="120" />
    </el-form-item>
    <el-form-item label="Gender" prop="gender">
      <el-select v-model="model.gender">
        <el-option label="Male" value="male" />
        <el-option label="Female" value="female" />
        <el-option label="Unknown" value="unknown" />
      </el-select>
    </el-form-item>
    <el-form-item label="Mobile">
      <el-input v-model="model.mobile" maxlength="30" />
    </el-form-item>
    <el-form-item label="School">
      <el-input v-model="model.school" maxlength="120" />
    </el-form-item>
    <el-form-item label="Grade">
      <el-input v-model="model.grade" maxlength="60" />
    </el-form-item>
    <el-form-item label="Status" prop="status">
      <el-segmented v-model="model.status" :options="[{ label: 'Enabled', value: 'enabled' }, { label: 'Disabled', value: 'disabled' }]" />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" :loading="submitting" @click="submit">
        Save
      </el-button>
    </el-form-item>
  </el-form>
</template>
