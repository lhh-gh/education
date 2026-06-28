<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { TenantRecord, TenantSavePayload } from '../../../api/foundation/tenant.ts'
import { createTenant, updateTenant } from '../../../api/foundation/tenant.ts'
import { useMessage } from '@/hooks/useMessage.ts'

const { mode = 'create', data = null } = defineProps<{
  mode?: 'create' | 'edit'
  data?: TenantRecord | null
}>()

const emit = defineEmits<{
  success: []
}>()

const message = useMessage()
const formRef = ref<FormInstance>()
const submitting = ref(false)
const model = reactive<TenantSavePayload>({
  name: data?.name ?? '',
  code: data?.code ?? '',
  short_name: data?.short_name ?? '',
  contact_name: data?.contact_name ?? '',
  contact_phone: data?.contact_phone ?? '',
  status: data?.status ?? 'enabled',
})

const codePattern = /^[a-z][a-z0-9_-]{1,63}$/
const rules: FormRules = {
  name: [{ required: true, message: '请输入机构名称', trigger: 'blur' }, { max: 120, message: '最多 120 个字符', trigger: 'blur' }],
  code: [
    { required: true, message: '请输入机构编码', trigger: 'blur' },
    { pattern: codePattern, message: '小写字母开头，仅支持小写字母、数字、下划线和连字符', trigger: 'blur' },
  ],
  short_name: [{ max: 60, message: '最多 60 个字符', trigger: 'blur' }],
  contact_name: [{ max: 60, message: '最多 60 个字符', trigger: 'blur' }],
  contact_phone: [{ max: 30, message: '最多 30 个字符', trigger: 'blur' }],
}

async function submit() {
  if (!formRef.value) {
    return
  }
  await formRef.value.validate()
  submitting.value = true
  try {
    if (mode === 'edit' && data?.id) {
      await updateTenant(data.id, model)
    }
    else {
      await createTenant(model)
    }
    emit('success')
  }
  catch (error: any) {
    message.error(error?.message ?? '机构保存失败')
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ submit })
</script>

<template>
  <el-form ref="formRef" :model="model" :rules="rules" label-width="96px">
    <el-form-item label="机构名称" prop="name">
      <el-input v-model="model.name" maxlength="120" show-word-limit />
    </el-form-item>
    <el-form-item label="机构编码" prop="code">
      <el-input v-model="model.code" maxlength="64" :disabled="mode === 'edit'" />
    </el-form-item>
    <el-form-item label="机构简称" prop="short_name">
      <el-input v-model="model.short_name" maxlength="60" show-word-limit />
    </el-form-item>
    <el-form-item label="联系人" prop="contact_name">
      <el-input v-model="model.contact_name" maxlength="60" />
    </el-form-item>
    <el-form-item label="联系电话" prop="contact_phone">
      <el-input v-model="model.contact_phone" maxlength="30" />
    </el-form-item>
    <el-form-item label="状态" prop="status">
      <el-segmented
        v-model="model.status"
        :options="[
          { label: '启用', value: 'enabled' },
          { label: '停用', value: 'disabled' },
        ]"
      />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" :loading="submitting" @click="submit">
        保存
      </el-button>
    </el-form-item>
  </el-form>
</template>
