<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { DictItemRecord, DictItemSavePayload } from '../../../api/foundation/dictionary.ts'
import { createDictItem, updateDictItem } from '../../../api/foundation/dictionary.ts'

const { mode = 'create', dictTypeId, data = null } = defineProps<{
  mode?: 'create' | 'edit'
  dictTypeId: number
  data?: DictItemRecord | null
}>()

const emit = defineEmits<{
  success: []
}>()

const message = useMessage()
const formRef = ref<FormInstance>()
const submitting = ref(false)
const extraText = ref(data?.extra ? JSON.stringify(data.extra, null, 2) : '')
const model = reactive<DictItemSavePayload>({
  dict_type_id: dictTypeId,
  label: data?.label ?? '',
  value: data?.value ?? '',
  color: data?.color ?? '',
  sort_order: data?.sort_order ?? 0,
  status: data?.status ?? 'enabled',
  is_default: data?.is_default ?? false,
})

const valuePattern = /^[\w.-]+$/
const rules: FormRules = {
  dict_type_id: [{ required: true, message: '缺少字典类型', trigger: 'blur' }],
  label: [{ required: true, message: '请输入显示名称', trigger: 'blur' }, { max: 120, message: '最多 120 个字符', trigger: 'blur' }],
  value: [
    { required: true, message: '请输入字典值', trigger: 'blur' },
    { pattern: valuePattern, message: '仅支持字母、数字、点、下划线和连字符', trigger: 'blur' },
    { max: 120, message: '最多 120 个字符', trigger: 'blur' },
  ],
  color: [{ max: 40, message: '最多 40 个字符', trigger: 'blur' }],
  sort_order: [{ type: 'number', min: 0, message: '排序值不能小于 0', trigger: 'blur' }],
}

function parseExtra(): Record<string, unknown> | undefined {
  const text = extraText.value.trim()
  if (!text) {
    return undefined
  }

  try {
    const parsed = JSON.parse(text)
    if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) {
      throw new Error('extra must be an object')
    }

    return parsed
  }
  catch {
    message.error('扩展 JSON 格式不正确')
    throw new Error('invalid extra json')
  }
}

async function submit() {
  if (!formRef.value) {
    return
  }
  await formRef.value.validate()
  const extra = parseExtra()
  submitting.value = true
  try {
    const payload: DictItemSavePayload = {
      ...model,
      extra,
    }

    if (mode === 'edit' && data?.id) {
      await updateDictItem(data.id, payload)
    }
    else {
      await createDictItem(payload)
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
  <el-form ref="formRef" :model="model" :rules="rules" label-width="104px">
    <el-form-item label="字典类型" prop="dict_type_id">
      <el-input-number v-model="model.dict_type_id" disabled :controls="false" style="width: 100%;" />
    </el-form-item>
    <el-form-item label="显示名称" prop="label">
      <el-input v-model="model.label" maxlength="120" show-word-limit />
    </el-form-item>
    <el-form-item label="字典值" prop="value">
      <el-input v-model="model.value" maxlength="120" :disabled="mode === 'edit'" />
    </el-form-item>
    <el-form-item label="颜色标记" prop="color">
      <el-input v-model="model.color" maxlength="40" placeholder="success / #67c23a" />
    </el-form-item>
    <el-form-item label="扩展 JSON">
      <el-input v-model="extraText" type="textarea" :autosize="{ minRows: 3, maxRows: 8 }" />
    </el-form-item>
    <el-form-item label="排序" prop="sort_order">
      <el-input-number v-model="model.sort_order" :min="0" :controls="false" style="width: 100%;" />
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
    <el-form-item label="默认项">
      <el-switch v-model="model.is_default" />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" :loading="submitting" @click="submit">
        保存
      </el-button>
    </el-form-item>
  </el-form>
</template>
