<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { UserProfileListItem } from '../../../api/foundation/userProfile.ts'
import type { TeacherRecord, TeacherSavePayload } from '../../../api/academic/profile.ts'
import { pageUserProfiles } from '../../../api/foundation/userProfile.ts'
import { createTeacher, teacherProfileSelectorParams, updateTeacher } from '../../../api/academic/profile.ts'
import { useMessage } from '@/hooks/useMessage.ts'

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
  campus_id: [{ required: true, message: '请选择校区', trigger: 'blur' }],
  teacher_no: [{ required: true, message: '请输入教师编号', trigger: 'blur' }],
  name: [{ required: true, message: '请输入教师姓名', trigger: 'blur' }],
  gender: [{ required: true, message: '请选择性别', trigger: 'change' }],
  status: [{ required: true, message: '请选择状态', trigger: 'change' }],
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
    message.error(error?.message ?? '教师保存失败')
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
    <el-form-item label="校区ID" prop="campus_id">
      <el-input-number v-model="model.campus_id" :min="1" :controls="false" />
    </el-form-item>
    <el-form-item label="用户档案">
      <el-select v-model="model.user_profile_id" clearable filterable>
        <el-option v-for="profile in profiles" :key="profile.id" :label="profile.display_name" :value="profile.id" />
      </el-select>
    </el-form-item>
    <el-form-item label="教师编号" prop="teacher_no">
      <el-input v-model="model.teacher_no" maxlength="64" :disabled="mode === 'edit'" />
    </el-form-item>
    <el-form-item label="姓名" prop="name">
      <el-input v-model="model.name" maxlength="120" />
    </el-form-item>
    <el-form-item label="手机号">
      <el-input v-model="model.mobile" maxlength="30" />
    </el-form-item>
    <el-form-item label="性别" prop="gender">
      <el-select v-model="model.gender">
        <el-option label="男" value="male" />
        <el-option label="女" value="female" />
        <el-option label="未知" value="unknown" />
      </el-select>
    </el-form-item>
    <el-form-item label="职称">
      <el-input v-model="model.title" maxlength="80" />
    </el-form-item>
    <el-form-item label="状态" prop="status">
      <el-segmented v-model="model.status" :options="[{ label: '启用', value: 'enabled' }, { label: '停用', value: 'disabled' }]" />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" :loading="submitting" @click="submit">
        保存
      </el-button>
    </el-form-item>
  </el-form>
</template>
