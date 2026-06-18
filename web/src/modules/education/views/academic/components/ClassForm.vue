<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { ClassRecord, ClassSavePayload } from '../../../api/academic/classSchedule.ts'
import { createClass, updateClass } from '../../../api/academic/classSchedule.ts'

const props = withDefaults(defineProps<{
  mode?: 'create' | 'edit'
  tenantId?: number
  campusId?: number
  data?: ClassRecord | null
}>(), {
  mode: 'create',
  data: null,
})

const emit = defineEmits<{ success: [] }>()
const message = useMessage()
const formRef = ref<FormInstance>()
const submitting = ref(false)
const errorText = ref('')
const model = reactive<ClassSavePayload>(defaultModel())

const rules: FormRules = {
  campus_id: [{ required: true, message: 'Campus is required', trigger: 'blur' }],
  course_id: [{ required: true, message: 'Course is required', trigger: 'blur' }],
  code: [{ required: true, message: 'Code is required', trigger: 'blur' }],
  name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
  class_type: [{ required: true, message: 'Class type is required', trigger: 'change' }],
  max_students: [{ required: true, message: 'Max students is required', trigger: 'blur' }],
  lesson_units: [{ required: true, message: 'Lesson units is required', trigger: 'blur' }],
  status: [{ required: true, message: 'Status is required', trigger: 'change' }],
}

function defaultModel(): ClassSavePayload {
  return {
    tenant_id: props.tenantId,
    campus_id: props.data?.campus_id ?? props.campusId ?? 0,
    course_id: props.data?.course_id ?? 0,
    main_teacher_id: props.data?.main_teacher_id ?? null,
    classroom_id: props.data?.classroom_id ?? null,
    code: props.data?.code ?? '',
    name: props.data?.name ?? '',
    class_type: props.data?.class_type ?? 'group',
    max_students: props.data?.max_students ?? 20,
    start_date: props.data?.start_date ?? null,
    end_date: props.data?.end_date ?? null,
    lesson_units: Number(props.data?.lesson_units ?? 1),
    status: props.data?.status ?? 'enabled',
    schedule_note: props.data?.schedule_note ?? '',
    remark: props.data?.remark ?? '',
  }
}

watch(() => props.data, () => Object.assign(model, defaultModel()))

async function submit() {
  await formRef.value?.validate()
  submitting.value = true
  try {
    if (props.mode === 'edit' && props.data?.id) {
      await updateClass(props.data.id, model)
    }
    else {
      await createClass(model)
    }
    errorText.value = ''
    emit('success')
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Class save failed'
    message.error(errorText.value)
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ submit, submitting, errorText })
</script>

<template>
  <el-form ref="formRef" :model="model" :rules="rules" label-width="132px">
    <el-alert v-if="errorText" class="form-alert" type="error" show-icon :closable="false" :title="errorText" />
    <el-form-item label="Campus ID" prop="campus_id">
      <el-input-number v-model="model.campus_id" :min="1" :controls="false" />
    </el-form-item>
    <el-form-item label="Course ID" prop="course_id">
      <el-input-number v-model="model.course_id" :min="1" :controls="false" />
    </el-form-item>
    <el-form-item label="Main Teacher ID">
      <el-input-number v-model="model.main_teacher_id" :min="1" :controls="false" />
    </el-form-item>
    <el-form-item label="Classroom ID">
      <el-input-number v-model="model.classroom_id" :min="1" :controls="false" />
    </el-form-item>
    <el-form-item label="Code" prop="code">
      <el-input v-model="model.code" maxlength="64" :disabled="mode === 'edit'" />
    </el-form-item>
    <el-form-item label="Name" prop="name">
      <el-input v-model="model.name" maxlength="120" />
    </el-form-item>
    <el-form-item label="Class Type" prop="class_type">
      <el-segmented v-model="model.class_type" :options="[{ label: 'Group', value: 'group' }, { label: '1:1', value: 'one_to_one' }]" />
    </el-form-item>
    <el-form-item label="Max Students" prop="max_students">
      <el-input-number v-model="model.max_students" :min="1" :max="999" />
    </el-form-item>
    <el-form-item label="Date Range">
      <el-date-picker v-model="model.start_date" value-format="YYYY-MM-DD" type="date" placeholder="Start" />
      <el-date-picker v-model="model.end_date" value-format="YYYY-MM-DD" type="date" placeholder="End" class="ml-2" />
    </el-form-item>
    <el-form-item label="Lesson Units" prop="lesson_units">
      <el-input-number v-model="model.lesson_units" :min="0.25" :step="0.25" />
    </el-form-item>
    <el-form-item label="Status" prop="status">
      <el-segmented v-model="model.status" :options="[{ label: 'Enabled', value: 'enabled' }, { label: 'Disabled', value: 'disabled' }]" />
    </el-form-item>
    <el-form-item label="Schedule Note">
      <el-input v-model="model.schedule_note" type="textarea" maxlength="1000" />
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

<style scoped lang="scss">
.form-alert {
  margin-bottom: 12px;
}
</style>
