<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { EducationRoleCode, UserProfileRecord, UserProfileSavePayload } from '../../../api/foundation/userProfile.ts'
import { createUserProfile, updateUserProfile } from '../../../api/foundation/userProfile.ts'
import { educationRoleOptions, foundationStatusOptions, isPlatformRole } from '../actionRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'

const { mode = 'create', data = null } = defineProps<{
  mode?: 'create' | 'edit'
  data?: UserProfileRecord | null
}>()

const emit = defineEmits<{
  success: []
}>()

const message = useMessage()
const formRef = ref<FormInstance>()
const submitting = ref(false)
const roleOptions: Array<{ label: string, value: EducationRoleCode }> = educationRoleOptions()
const statusOptions = foundationStatusOptions()

const model = reactive<UserProfileSavePayload>({
  tenant_id: data?.tenant_id,
  user_id: data?.user_id ?? 0,
  role_code: data?.role_code ?? 'teacher',
  display_name: data?.display_name ?? '',
  mobile: data?.mobile ?? '',
  avatar: data?.avatar ?? '',
  openid: data?.openid ?? '',
  unionid: data?.unionid ?? '',
  status: data?.status ?? 'enabled',
  current_campus_id: data?.current_campus_id,
})
const platformRole = computed(() => isPlatformRole(model.role_code))

function validateTenant(_rule: unknown, value: unknown, callback: (error?: Error) => void) {
  if (platformRole.value && value) {
    callback(new Error('平台角色无需填写机构ID'))
    return
  }
  if (!platformRole.value && !value) {
    callback(new Error('请选择机构'))
    return
  }
  callback()
}

function validateCampus(_rule: unknown, value: unknown, callback: (error?: Error) => void) {
  if (platformRole.value && value) {
    callback(new Error('平台角色无需填写当前校区'))
    return
  }
  callback()
}

const rules: FormRules = {
  tenant_id: [{ validator: validateTenant, trigger: 'change' }],
  user_id: [{ required: true, type: 'number', min: 1, message: '请选择 MineAdmin 用户', trigger: 'change' }],
  role_code: [{ required: true, message: '请选择角色', trigger: 'change' }],
  display_name: [{ required: true, message: '请输入姓名', trigger: 'blur' }, { max: 80, message: '最多 80 个字符', trigger: 'blur' }],
  mobile: [{ max: 30, message: '最多 30 个字符', trigger: 'blur' }],
  avatar: [{ max: 255, message: '最多 255 个字符', trigger: 'blur' }],
  openid: [{ max: 80, message: '最多 80 个字符', trigger: 'blur' }],
  unionid: [{ max: 80, message: '最多 80 个字符', trigger: 'blur' }],
  current_campus_id: [{ validator: validateCampus, trigger: 'change' }],
}

watch(platformRole, (value) => {
  if (value) {
    model.tenant_id = undefined
    model.current_campus_id = undefined
  }
})

function payload(): UserProfileSavePayload {
  const data = { ...model }
  if (platformRole.value) {
    delete data.tenant_id
    delete data.current_campus_id
  }
  if (!data.mobile) {
    delete data.mobile
  }
  if (!data.avatar) {
    delete data.avatar
  }
  if (!data.openid) {
    delete data.openid
  }
  if (!data.unionid) {
    delete data.unionid
  }
  if (!data.current_campus_id) {
    delete data.current_campus_id
  }

  return data
}

async function submit() {
  if (!formRef.value) {
    return
  }
  await formRef.value.validate()
  submitting.value = true
  try {
    if (mode === 'edit' && data?.id) {
      await updateUserProfile(data.id, payload())
    }
    else {
      await createUserProfile(payload())
    }
    emit('success')
  }
  catch (error: any) {
    message.error(error?.message ?? '教育用户档案保存失败')
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ submit })
</script>

<template>
  <el-form ref="formRef" :model="model" :rules="rules" label-width="132px">
    <el-form-item label="机构ID" prop="tenant_id">
      <el-input-number v-model="model.tenant_id" :controls="false" :disabled="platformRole" :min="1" />
    </el-form-item>
    <el-form-item label="用户ID" prop="user_id">
      <el-input-number v-model="model.user_id" :controls="false" :min="1" />
    </el-form-item>
    <el-form-item label="角色" prop="role_code">
      <el-select v-model="model.role_code" filterable>
        <el-option v-for="item in roleOptions" :key="item.value" :label="item.label" :value="item.value" />
      </el-select>
    </el-form-item>
    <el-form-item label="姓名" prop="display_name">
      <el-input v-model="model.display_name" maxlength="80" show-word-limit />
    </el-form-item>
    <el-form-item label="手机号" prop="mobile">
      <el-input v-model="model.mobile" maxlength="30" />
    </el-form-item>
    <el-form-item label="头像" prop="avatar">
      <el-input v-model="model.avatar" maxlength="255" />
    </el-form-item>
    <el-form-item label="OpenID" prop="openid">
      <el-input v-model="model.openid" maxlength="80" />
    </el-form-item>
    <el-form-item label="UnionID" prop="unionid">
      <el-input v-model="model.unionid" maxlength="80" />
    </el-form-item>
    <el-form-item label="当前校区" prop="current_campus_id">
      <el-input-number v-model="model.current_campus_id" :controls="false" :disabled="platformRole" :min="1" />
    </el-form-item>
    <el-form-item label="状态" prop="status">
      <el-segmented
        v-model="model.status"
        :options="statusOptions"
      />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" :loading="submitting" @click="submit">
        保存
      </el-button>
    </el-form-item>
  </el-form>
</template>
