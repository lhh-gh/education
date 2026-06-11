<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { CampusRecord, CampusSavePayload } from '../../../api/foundation/campus.ts'
import { createCampus, updateCampus } from '../../../api/foundation/campus.ts'

const { mode = 'create', tenantId, data = null } = defineProps<{
  mode?: 'create' | 'edit'
  tenantId: number
  data?: CampusRecord | null
}>()

const emit = defineEmits<{
  success: []
}>()

const formRef = ref<FormInstance>()
const submitting = ref(false)
const model = reactive<CampusSavePayload>({
  name: data?.name ?? '',
  code: data?.code ?? '',
  contact_name: data?.contact_name ?? '',
  contact_phone: data?.contact_phone ?? '',
  address: data?.address ?? '',
  status: data?.status ?? 'enabled',
})

const codePattern = /^[a-z][a-z0-9_-]{1,63}$/
const rules: FormRules = {
  name: [{ required: true, message: '请输入校区名称', trigger: 'blur' }, { max: 120, message: '最多 120 个字符', trigger: 'blur' }],
  code: [
    { required: true, message: '请输入校区编码', trigger: 'blur' },
    { pattern: codePattern, message: '小写字母开头，仅支持小写字母、数字、下划线和连字符', trigger: 'blur' },
  ],
  contact_name: [{ max: 60, message: '最多 60 个字符', trigger: 'blur' }],
  contact_phone: [{ max: 30, message: '最多 30 个字符', trigger: 'blur' }],
  address: [{ max: 255, message: '最多 255 个字符', trigger: 'blur' }],
}

async function submit() {
  if (!formRef.value) return
  await formRef.value.validate()
  submitting.value = true
  try {
    if (mode === 'edit' && data?.id) {
      await updateCampus(tenantId, data.id, model)
    }
    else {
      await createCampus(tenantId, model)
    }
    emit('success')
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ submit })
</script>

<template>
  <el-form ref="formRef" :model="model" :rules="rules" label-width="96px">
    <el-form-item label="校区名称" prop="name">
      <el-input v-model="model.name" maxlength="120" show-word-limit />
    </el-form-item>
    <el-form-item label="校区编码" prop="code">
      <el-input v-model="model.code" maxlength="64" :disabled="mode === 'edit'" />
    </el-form-item>
    <el-form-item label="联系人" prop="contact_name">
      <el-input v-model="model.contact_name" maxlength="60" />
    </el-form-item>
    <el-form-item label="联系电话" prop="contact_phone">
      <el-input v-model="model.contact_phone" maxlength="30" />
    </el-form-item>
    <el-form-item label="地址" prop="address">
      <el-input v-model="model.address" type="textarea" maxlength="255" show-word-limit />
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
