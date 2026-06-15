<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { UserProfileListItem } from '../../../api/foundation/userProfile.ts'
import type { TeacherRecord, TeacherSavePayload } from '../../../api/academic/profile.ts'
import { pageUserProfiles } from '../../../api/foundation/userProfile.ts'
import { createTeacher, teacherProfileSelectorParams, updateTeacher } from '../../../api/academic/profile.ts'

const { mode = 'create', tenantId, data = null } = defineProps<{
  mode?: 'create' | 'edit'
  tenantId?: number
  data?: TeacherRecord | null
}>()

const emit = defineEmits<{ success: [] }>()
const message = useMessage()
const formRef = ref<FormInstance>()
const submitting = ref(false)
const profiles = ref<UserProfileListItem[]>([])
const model = reactive<TeacherSavePayload>({
  tenant_id: tenantId,
  campus_id: data?.campus_id ?? 0,
  user_profile_id: data?.user_profile_id,
  teacher_no: data?.teacher_no ?? '',
  name: data?.name ?? '',
  mobile: data?.mobile ?? '',
  gender: data?.gender ?? 'unknown',
  title: data?.title ?? '',
  status: data?.status ?? 'enabled',
  remark: data?.remark ?? '',
})

const rules: FormRules = {
  campus_id: [{ required: true, message: 'Campus is required', trigger: 'blur' }],
  teacher_no: [{ required: true, message: 'Teacher number is required', trigger: 'blur' }],
  name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
  gender: [{ required: true, message: 'Gender is required', trigger: 'change' }],
  status: [{ required: true, message: 'Status is required', trigger: 'change' }],
}

async function loadTeacherProfiles() {
  const response = await pageUserProfiles({ page: 1, page_size: 100, ...teacherProfileSelectorParams(tenantId) })
  profiles.value = response.data.list
}

async function submit() {
  await formRef.value?.validate()
  submitting.value = true
  try {
    if (mode === 'edit' && data?.id) {
      await updateTeacher(data.id, model)
    }
    else {
      await createTeacher(model)
    }
    emit('success')
  }
  catch (error: any) {
    message.error(error?.message ?? 'Teacher save failed')
  }
  finally {
    submitting.value = false
  }
}

onMounted(loadTeacherProfiles)
defineExpose({ submit, submitting, loadTeacherProfiles })
</script>

<template>
  <el-form ref="formRef" :model="model" :rules="rules" label-width="128px">
    <el-form-item label="Campus ID" prop="campus_id">
      <el-input-number v-model="model.campus_id" :min="1" :controls="false" />
    </el-form-item>
    <el-form-item label="Profile">
      <el-select v-model="model.user_profile_id" clearable filterable>
        <el-option v-for="profile in profiles" :key="profile.id" :label="profile.display_name" :value="profile.id" />
      </el-select>
    </el-form-item>
    <el-form-item label="Teacher No" prop="teacher_no">
      <el-input v-model="model.teacher_no" maxlength="64" :disabled="mode === 'edit'" />
    </el-form-item>
    <el-form-item label="Name" prop="name">
      <el-input v-model="model.name" maxlength="120" />
    </el-form-item>
    <el-form-item label="Mobile">
      <el-input v-model="model.mobile" maxlength="30" />
    </el-form-item>
    <el-form-item label="Gender" prop="gender">
      <el-select v-model="model.gender">
        <el-option label="Male" value="male" />
        <el-option label="Female" value="female" />
        <el-option label="Unknown" value="unknown" />
      </el-select>
    </el-form-item>
    <el-form-item label="Title">
      <el-input v-model="model.title" maxlength="80" />
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
