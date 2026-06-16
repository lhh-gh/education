<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { CourseRecord, CourseSavePayload } from '../../../api/academic/courseAccount.ts'
import { createCourse, updateCourse } from '../../../api/academic/courseAccount.ts'

const { mode = 'create', tenantId, data = null } = defineProps<{
  mode?: 'create' | 'edit'
  tenantId?: number
  data?: CourseRecord | null
}>()

const emit = defineEmits<{ success: [] }>()
const message = useMessage()
const formRef = ref<FormInstance>()
const submitting = ref(false)
const model = reactive<CourseSavePayload>({
  tenant_id: tenantId,
  campus_id: data?.campus_id ?? 0,
  code: data?.code ?? '',
  name: data?.name ?? '',
  category: data?.category ?? '',
  subject: data?.subject ?? '',
  unit_minutes: data?.unit_minutes ?? 45,
  cover_url: data?.cover_url ?? '',
  description: data?.description ?? '',
  status: data?.status ?? 'enabled',
  sort_order: data?.sort_order ?? 0,
  remark: data?.remark ?? '',
})

const rules: FormRules = {
  campus_id: [{ required: true, message: 'Campus is required', trigger: 'blur' }],
  code: [{ required: true, message: 'Code is required', trigger: 'blur' }],
  name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
  unit_minutes: [{ required: true, message: 'Unit minutes is required', trigger: 'blur' }],
  status: [{ required: true, message: 'Status is required', trigger: 'change' }],
}

async function submit() {
  await formRef.value?.validate()
  submitting.value = true
  try {
    if (mode === 'edit' && data?.id) {
      await updateCourse(data.id, model)
    }
    else {
      await createCourse(model)
    }
    emit('success')
  }
  catch (error: any) {
    message.error(error?.message ?? 'Course save failed')
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ submit, submitting })
</script>

<template>
  <el-form ref="formRef" :model="model" :rules="rules" label-width="128px">
    <el-form-item label="Campus ID" prop="campus_id">
      <el-input-number v-model="model.campus_id" :min="1" :controls="false" />
    </el-form-item>
    <el-form-item label="Code" prop="code">
      <el-input v-model="model.code" maxlength="64" :disabled="mode === 'edit'" />
    </el-form-item>
    <el-form-item label="Name" prop="name">
      <el-input v-model="model.name" maxlength="120" />
    </el-form-item>
    <el-form-item label="Category">
      <el-input v-model="model.category" maxlength="80" />
    </el-form-item>
    <el-form-item label="Subject">
      <el-input v-model="model.subject" maxlength="80" />
    </el-form-item>
    <el-form-item label="Unit Minutes" prop="unit_minutes">
      <el-input-number v-model="model.unit_minutes" :min="1" :max="1440" />
    </el-form-item>
    <el-form-item label="Cover URL">
      <el-input v-model="model.cover_url" maxlength="255" />
    </el-form-item>
    <el-form-item label="Description">
      <el-input v-model="model.description" type="textarea" maxlength="5000" />
    </el-form-item>
    <el-form-item label="Status" prop="status">
      <el-segmented v-model="model.status" :options="[{ label: 'Enabled', value: 'enabled' }, { label: 'Disabled', value: 'disabled' }]" />
    </el-form-item>
    <el-form-item label="Sort">
      <el-input-number v-model="model.sort_order" :min="-9999" :max="9999" />
    </el-form-item>
    <el-form-item label="Remark">
      <el-input v-model="model.remark" type="textarea" maxlength="500" />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" :loading="submitting" @click="submit">
        Save
      </el-button>
    </el-form-item>
  </el-form>
</template>
