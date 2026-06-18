<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { FeatureFlagRecord, FeatureFlagSavePayload } from '../../../api/foundation/featureFlag.ts'
import { createFeatureFlag, updateFeatureFlag } from '../../../api/foundation/featureFlag.ts'
import { dictionaryOwnerTypeOptions, extractApiErrorMessage, isSubmitDisabled, parseJsonObjectText } from '../actionRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'

const { mode = 'create', data = null, platformContext = false } = defineProps<{
  mode?: 'create' | 'edit'
  data?: FeatureFlagRecord | null
  platformContext?: boolean
}>()

const emit = defineEmits<{
  success: []
}>()

const message = useMessage()
const formRef = ref<FormInstance>()
const submitting = ref(false)
const ownerOptions = computed(() => dictionaryOwnerTypeOptions(platformContext))
const configText = ref(data?.config ? JSON.stringify(data.config, null, 2) : '')
const model = reactive<FeatureFlagSavePayload>({
  owner_type: platformContext ? data?.owner_type ?? 'system' : 'tenant',
  tenant_id: data?.tenant_id,
  feature_code: data?.feature_code ?? '',
  feature_name: data?.feature_name ?? '',
  description: data?.description ?? '',
  enabled: data?.enabled ?? false,
  effective_from: data?.effective_from,
  effective_to: data?.effective_to,
  status: data?.status ?? 'enabled',
  is_locked: platformContext ? data?.is_locked ?? false : false,
})

const codePattern = /^[a-z][a-z0-9_.-]{1,119}$/
const rules: FormRules = {
  owner_type: [{ required: true, message: '请选择归属类型', trigger: 'change' }],
  tenant_id: [{
    validator: (_rule, value, callback) => {
      if (model.owner_type === 'tenant' && (!value || Number(value) <= 0)) {
        callback(new Error('请输入机构ID'))
        return
      }
      callback()
    },
    trigger: 'blur',
  }],
  feature_code: [
    { required: true, message: '请输入功能编码', trigger: 'blur' },
    { pattern: codePattern, message: '小写字母开头，仅支持小写字母、数字、点、下划线和连字符', trigger: 'blur' },
    { max: 120, message: '最多 120 个字符', trigger: 'blur' },
  ],
  feature_name: [{ required: true, message: '请输入功能名称', trigger: 'blur' }, { max: 120, message: '最多 120 个字符', trigger: 'blur' }],
  description: [{ max: 255, message: '最多 255 个字符', trigger: 'blur' }],
  effective_to: [{
    validator: (_rule, value, callback) => {
      if (model.effective_from && value && new Date(value).getTime() <= new Date(model.effective_from).getTime()) {
        callback(new Error('结束时间必须晚于开始时间'))
        return
      }
      callback()
    },
    trigger: 'change',
  }],
}

watch(() => model.owner_type, (ownerType) => {
  if (ownerType === 'system') {
    model.tenant_id = undefined
  }
})

function parseConfig(): Record<string, unknown> | undefined {
  try {
    return parseJsonObjectText(configText.value, 'config')
  }
  catch {
    message.error('配置 JSON 格式不正确')
    throw new Error('配置 JSON 格式不正确')
  }
}

async function submit() {
  if (!formRef.value) {
    return
  }
  await formRef.value.validate()
  let config: Record<string, unknown> | undefined
  try {
    config = parseConfig()
  }
  catch {
    return
  }

  submitting.value = true
  try {
    const payload: FeatureFlagSavePayload = {
      ...model,
      tenant_id: model.owner_type === 'tenant' ? model.tenant_id : undefined,
      config,
      is_locked: platformContext ? model.is_locked : false,
    }

    if (mode === 'edit' && data?.id) {
      await updateFeatureFlag(data.id, payload)
    }
    else {
      await createFeatureFlag(payload)
    }
    emit('success')
  }
  catch (error: any) {
    message.error(extractApiErrorMessage(error, '功能开关保存失败'))
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ submit })
</script>

<template>
  <el-form ref="formRef" :model="model" :rules="rules" label-width="112px">
    <el-form-item label="归属类型" prop="owner_type">
      <el-select v-model="model.owner_type" :disabled="!platformContext || mode === 'edit'" style="width: 100%;">
        <el-option v-for="option in ownerOptions" :key="option.value" :label="option.label" :value="option.value" />
      </el-select>
    </el-form-item>
    <el-form-item v-if="model.owner_type === 'tenant'" label="机构ID" prop="tenant_id">
      <el-input-number v-model="model.tenant_id" :min="1" :controls="false" style="width: 100%;" />
    </el-form-item>
    <el-form-item label="功能编码" prop="feature_code">
      <el-input v-model="model.feature_code" maxlength="120" :disabled="mode === 'edit'" />
    </el-form-item>
    <el-form-item label="功能名称" prop="feature_name">
      <el-input v-model="model.feature_name" maxlength="120" show-word-limit />
    </el-form-item>
    <el-form-item label="描述" prop="description">
      <el-input v-model="model.description" maxlength="255" show-word-limit />
    </el-form-item>
    <el-form-item label="开关值">
      <el-switch v-model="model.enabled" active-text="开启" inactive-text="关闭" />
    </el-form-item>
    <el-form-item label="配置 JSON">
      <el-input v-model="configText" type="textarea" :autosize="{ minRows: 3, maxRows: 8 }" />
    </el-form-item>
    <el-form-item label="生效开始">
      <el-date-picker v-model="model.effective_from" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" style="width: 100%;" />
    </el-form-item>
    <el-form-item label="生效结束" prop="effective_to">
      <el-date-picker v-model="model.effective_to" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" style="width: 100%;" />
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
    <el-form-item v-if="platformContext" label="锁定">
      <el-switch v-model="model.is_locked" />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" :loading="submitting" :disabled="isSubmitDisabled(submitting)" @click="submit">
        保存
      </el-button>
    </el-form-item>
  </el-form>
</template>
