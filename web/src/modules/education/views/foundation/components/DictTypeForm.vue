<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { DictTypeRecord, DictTypeSavePayload } from '../../../api/foundation/dictionary.ts'
import { createDictType, updateDictType } from '../../../api/foundation/dictionary.ts'
import { dictionaryOwnerTypeOptions, extractApiErrorMessage, isSubmitDisabled } from '../actionRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'

const { mode = 'create', data = null, platformContext = false } = defineProps<{
  mode?: 'create' | 'edit'
  data?: DictTypeRecord | null
  platformContext?: boolean
}>()

const emit = defineEmits<{
  success: []
}>()

const formRef = ref<FormInstance>()
const message = useMessage()
const submitting = ref(false)
const ownerOptions = computed(() => dictionaryOwnerTypeOptions(platformContext))
const model = reactive<DictTypeSavePayload>({
  owner_type: platformContext ? data?.owner_type ?? 'system' : 'tenant',
  tenant_id: data?.tenant_id,
  code: data?.code ?? '',
  name: data?.name ?? '',
  description: data?.description ?? '',
  status: data?.status ?? 'enabled',
  is_locked: platformContext ? data?.is_locked ?? false : false,
  sort_order: data?.sort_order ?? 0,
})

const codePattern = /^[a-z][a-z0-9_.-]{1,79}$/
const rules: FormRules = {
  owner_type: [{ required: true, message: '请选择归属类型', trigger: 'change' }],
  tenant_id: [{
    validator: (_rule, value, callback) => {
      if (model.owner_type === 'tenant' && (!value || Number(value) <= 0)) {
        callback(new Error('请输入租户 ID'))
        return
      }
      callback()
    },
    trigger: 'blur',
  }],
  code: [
    { required: true, message: '请输入字典编码', trigger: 'blur' },
    { pattern: codePattern, message: '小写字母开头，仅支持小写字母、数字、点、下划线和连字符', trigger: 'blur' },
    { max: 80, message: '最多 80 个字符', trigger: 'blur' },
  ],
  name: [{ required: true, message: '请输入字典名称', trigger: 'blur' }, { max: 120, message: '最多 120 个字符', trigger: 'blur' }],
  description: [{ max: 255, message: '最多 255 个字符', trigger: 'blur' }],
  sort_order: [{ type: 'number', min: 0, message: '排序值不能小于 0', trigger: 'blur' }],
}

watch(() => model.owner_type, (ownerType) => {
  if (ownerType === 'system') {
    model.tenant_id = undefined
  }
})

async function submit() {
  if (!formRef.value) {
    return
  }
  await formRef.value.validate()
  submitting.value = true
  try {
    const payload: DictTypeSavePayload = {
      ...model,
      tenant_id: model.owner_type === 'tenant' ? model.tenant_id : undefined,
      is_locked: platformContext ? model.is_locked : false,
    }

    if (mode === 'edit' && data?.id) {
      await updateDictType(data.id, payload)
    }
    else {
      await createDictType(payload)
    }
    emit('success')
  }
  catch (error: any) {
    message.error(extractApiErrorMessage(error, 'dictionary save failed'))
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ submit })
</script>

<template>
  <el-form ref="formRef" :model="model" :rules="rules" label-width="104px">
    <el-form-item label="归属类型" prop="owner_type">
      <el-select v-model="model.owner_type" :disabled="!platformContext || mode === 'edit'" style="width: 100%;">
        <el-option v-for="option in ownerOptions" :key="option.value" :label="option.label" :value="option.value" />
      </el-select>
    </el-form-item>
    <el-form-item v-if="model.owner_type === 'tenant'" label="租户 ID" prop="tenant_id">
      <el-input-number v-model="model.tenant_id" :min="1" :controls="false" style="width: 100%;" />
    </el-form-item>
    <el-form-item label="字典编码" prop="code">
      <el-input v-model="model.code" maxlength="80" :disabled="mode === 'edit'" />
    </el-form-item>
    <el-form-item label="字典名称" prop="name">
      <el-input v-model="model.name" maxlength="120" show-word-limit />
    </el-form-item>
    <el-form-item label="描述" prop="description">
      <el-input v-model="model.description" maxlength="255" show-word-limit />
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
    <el-form-item label="排序" prop="sort_order">
      <el-input-number v-model="model.sort_order" :min="0" :controls="false" style="width: 100%;" />
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
