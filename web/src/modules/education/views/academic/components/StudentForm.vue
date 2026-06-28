<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { StudentRecord, StudentSavePayload } from '../../../api/academic/profile.ts'
import { createStudent, updateStudent } from '../../../api/academic/profile.ts'
import { useMessage } from '@/hooks/useMessage.ts'

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
  campus_id: [{ required: true, message: '请选择校区', trigger: 'blur' }],
  student_no: [{ required: true, message: '请输入学员编号', trigger: 'blur' }],
  name: [{ required: true, message: '请输入学员姓名', trigger: 'blur' }],
  gender: [{ required: true, message: '请选择性别', trigger: 'change' }],
  status: [{ required: true, message: '请选择状态', trigger: 'change' }],
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
    message.error(error?.message ?? '学员保存失败')
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ submit, submitting })
</script>

<template>
  <el-form ref="formRef" :model="model" :rules="rules" label-width="120px">
    <el-form-item label="校区ID" prop="campus_id">
      <el-input-number v-model="model.campus_id" :min="1" :controls="false" />
    </el-form-item>
    <el-form-item label="学员编号" prop="student_no">
      <el-input v-model="model.student_no" maxlength="64" :disabled="mode === 'edit'" />
    </el-form-item>
    <el-form-item label="姓名" prop="name">
      <el-input v-model="model.name" maxlength="120" />
    </el-form-item>
    <el-form-item label="性别" prop="gender">
      <el-select v-model="model.gender">
        <el-option label="男" value="male" />
        <el-option label="女" value="female" />
        <el-option label="未知" value="unknown" />
      </el-select>
    </el-form-item>
    <el-form-item label="手机号">
      <el-input v-model="model.mobile" maxlength="30" />
    </el-form-item>
    <el-form-item label="学校">
      <el-input v-model="model.school" maxlength="120" />
    </el-form-item>
    <el-form-item label="年级">
      <el-input v-model="model.grade" maxlength="60" />
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
