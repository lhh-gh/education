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
  name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
  mobile: [{ required: true, message: 'Mobile is required', trigger: 'blur' }],
  gender: [{ required: true, message: 'Gender is required', trigger: 'change' }],
  status: [{ required: true, message: 'Status is required', trigger: 'change' }],
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
    message.error(error?.message ?? 'Guardian save failed')
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ submit, submitting })
</script>

<template>
  <el-form ref="formRef" :model="model" :rules="rules" label-width="120px">
    <el-form-item label="Name" prop="name">
      <el-input v-model="model.name" maxlength="120" />
    </el-form-item>
    <el-form-item label="Mobile" prop="mobile">
      <el-input v-model="model.mobile" maxlength="30" />
    </el-form-item>
    <el-form-item label="Gender" prop="gender">
      <el-select v-model="model.gender">
        <el-option label="Male" value="male" />
        <el-option label="Female" value="female" />
        <el-option label="Unknown" value="unknown" />
      </el-select>
    </el-form-item>
    <el-form-item label="OpenID">
      <el-input v-model="model.openid" maxlength="80" />
    </el-form-item>
    <el-form-item label="UnionID">
      <el-input v-model="model.unionid" maxlength="80" />
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
