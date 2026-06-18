<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { ClassroomRecord, ClassroomSavePayload } from '../../../api/academic/profile.ts'
import { createClassroom, updateClassroom } from '../../../api/academic/profile.ts'
import { useMessage } from '@/hooks/useMessage.ts'

const { mode = 'create', tenantId, data = null } = defineProps<{
  mode?: 'create' | 'edit'
  tenantId?: number
  data?: ClassroomRecord | null
}>()

const emit = defineEmits<{ success: [] }>()
const message = useMessage()
const formRef = ref<FormInstance>()
const submitting = ref(false)
const model = reactive<ClassroomSavePayload>({
  tenant_id: tenantId,
  campus_id: data?.campus_id ?? 0,
  code: data?.code ?? '',
  name: data?.name ?? '',
  capacity: data?.capacity ?? 0,
  location: data?.location ?? '',
  equipment: data?.equipment ?? {},
  status: data?.status ?? 'enabled',
  sort_order: data?.sort_order ?? 0,
  remark: data?.remark ?? '',
})

const rules: FormRules = {
  campus_id: [{ required: true, message: '请选择校区', trigger: 'blur' }],
  code: [{ required: true, message: '请输入教室编码', trigger: 'blur' }],
  name: [{ required: true, message: '请输入教室名称', trigger: 'blur' }],
  status: [{ required: true, message: '请选择状态', trigger: 'change' }],
}

async function submit() {
  await formRef.value?.validate()
  submitting.value = true
  try {
    if (mode === 'edit' && data?.id) {
      await updateClassroom(data.id, model)
    }
    else {
      await createClassroom(model)
    }
    emit('success')
  }
  catch (error: any) {
    message.error(error?.message ?? '教室保存失败')
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
    <el-form-item label="教室编码" prop="code">
      <el-input v-model="model.code" maxlength="64" :disabled="mode === 'edit'" />
    </el-form-item>
    <el-form-item label="教室名称" prop="name">
      <el-input v-model="model.name" maxlength="120" />
    </el-form-item>
    <el-form-item label="容量">
      <el-input-number v-model="model.capacity" :min="0" :max="9999" />
    </el-form-item>
    <el-form-item label="位置">
      <el-input v-model="model.location" maxlength="120" />
    </el-form-item>
    <el-form-item label="状态" prop="status">
      <el-segmented v-model="model.status" :options="[{ label: '启用', value: 'enabled' }, { label: '停用', value: 'disabled' }]" />
    </el-form-item>
    <el-form-item label="排序">
      <el-input-number v-model="model.sort_order" :min="-9999" :max="9999" />
    </el-form-item>
    <el-form-item label="备注">
      <el-input v-model="model.remark" type="textarea" maxlength="500" />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" :loading="submitting" @click="submit">
        保存
      </el-button>
    </el-form-item>
  </el-form>
</template>
