<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { GuardianRecord, GuardianSavePayload } from '../../../api/academic/profile.ts'
import { createGuardian, updateGuardian } from '../../../api/academic/profile.ts'
import { useMessage } from '@/hooks/useMessage.ts'

const { mode = 'create', tenantId, data = null } = defineProps<{
  mode?: 'create' | 'edit'
  tenantId?: number
  data?: GuardianRecord | null
}>()

const emit = defineEmits<{ success: [] }>()
const message = useMessage()
const formRef = ref<FormInstance>()
const submitting = ref(false)
const model = reactive<GuardianSavePayload>({
  tenant_id: tenantId,
  name: data?.name ?? '',
  mobile: data?.mobile ?? '',
  gender: data?.gender ?? 'unknown',
  openid: data?.openid ?? '',
  unionid: data?.unionid ?? '',
  status: data?.status ?? 'enabled',
  remark: data?.remark ?? '',
})

const rules: FormRules = {
  name: [{ required: true, message: '请输入监护人姓名', trigger: 'blur' }],
  mobile: [{ required: true, message: '请输入手机号', trigger: 'blur' }],
  gender: [{ required: true, message: '请选择性别', trigger: 'change' }],
  status: [{ required: true, message: '请选择状态', trigger: 'change' }],
}

async function submit() {
  await formRef.value?.validate()
  submitting.value = true
  try {
    if (mode === 'edit' && data?.id) {
      await updateGuardian(data.id, model)
    }
    else {
      await createGuardian(model)
    }
    emit('success')
  }
  catch (error: any) {
    message.error(error?.message ?? '监护人保存失败')
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ submit, submitting })
</script>

<template>
  <el-form ref="formRef" :model="model" :rules="rules" label-width="120px">
    <el-form-item label="姓名" prop="name">
      <el-input v-model="model.name" maxlength="120" />
    </el-form-item>
    <el-form-item label="手机号" prop="mobile">
      <el-input v-model="model.mobile" maxlength="30" />
    </el-form-item>
    <el-form-item label="性别" prop="gender">
      <el-select v-model="model.gender">
        <el-option label="男" value="male" />
        <el-option label="女" value="female" />
        <el-option label="未知" value="unknown" />
      </el-select>
    </el-form-item>
    <el-form-item label="OpenID">
      <el-input v-model="model.openid" maxlength="80" />
    </el-form-item>
    <el-form-item label="UnionID">
      <el-input v-model="model.unionid" maxlength="80" />
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
