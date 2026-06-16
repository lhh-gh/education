<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { LessonRecord, SingleLessonSchedulePayload } from '../../../api/academic/classSchedule.ts'
import { updateLesson } from '../../../api/academic/classSchedule.ts'

const props = defineProps<{
  tenantId?: number
  data?: LessonRecord | null
}>()

const emit = defineEmits<{ success: [lesson: LessonRecord] }>()
const message = useMessage()
const formRef = ref<FormInstance>()
const submitting = ref(false)
const errorText = ref('')
const model = reactive<SingleLessonSchedulePayload>(defaultModel())

const rules: FormRules = {
  campus_id: [{ required: true, message: 'Campus is required', trigger: 'blur' }],
  class_id: [{ required: true, message: 'Class is required', trigger: 'blur' }],
  teacher_id: [{ required: true, message: 'Teacher is required', trigger: 'blur' }],
  title: [{ required: true, message: 'Title is required', trigger: 'blur' }],
  start_at: [{ required: true, message: 'Start is required', trigger: 'change' }],
  end_at: [{ required: true, message: 'End is required', trigger: 'change' }],
  lesson_units: [{ required: true, message: 'Lesson units is required', trigger: 'blur' }],
}

function defaultModel(): SingleLessonSchedulePayload {
  return {
    tenant_id: props.tenantId,
    campus_id: props.data?.campus_id ?? 0,
    class_id: props.data?.class_id ?? 0,
    teacher_id: props.data?.teacher_id ?? 0,
    classroom_id: props.data?.classroom_id ?? null,
    title: props.data?.title ?? '',
    start_at: props.data?.start_at ?? '',
    end_at: props.data?.end_at ?? '',
    lesson_units: Number(props.data?.lesson_units ?? 1),
    remark: '',
  }
}

watch(() => props.data, () => Object.assign(model, defaultModel()))

async function submit() {
  if (!props.data?.id) {
    return
  }
  await formRef.value?.validate()
  submitting.value = true
  try {
    const response = await updateLesson(props.data.id, model)
    errorText.value = ''
    emit('success', response.data)
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Lesson update failed'
    message.error(errorText.value)
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ submit, errorText })
</script>

<template>
  <el-form ref="formRef" :model="model" :rules="rules" label-width="128px">
    <el-alert v-if="errorText" class="form-alert" type="error" show-icon :closable="false" :title="errorText" />
    <el-form-item label="Campus ID" prop="campus_id">
      <el-input-number v-model="model.campus_id" :min="1" :controls="false" />
    </el-form-item>
    <el-form-item label="Class ID" prop="class_id">
      <el-input-number v-model="model.class_id" :min="1" :controls="false" />
    </el-form-item>
    <el-form-item label="Teacher ID" prop="teacher_id">
      <el-input-number v-model="model.teacher_id" :min="1" :controls="false" />
    </el-form-item>
    <el-form-item label="Classroom ID">
      <el-input-number v-model="model.classroom_id" :min="1" :controls="false" />
    </el-form-item>
    <el-form-item label="Title" prop="title">
      <el-input v-model="model.title" maxlength="160" />
    </el-form-item>
    <el-form-item label="Time" prop="start_at">
      <el-date-picker v-model="model.start_at" value-format="YYYY-MM-DD HH:mm:ss" type="datetime" placeholder="Start" />
      <el-date-picker v-model="model.end_at" value-format="YYYY-MM-DD HH:mm:ss" type="datetime" placeholder="End" class="ml-2" />
    </el-form-item>
    <el-form-item label="Lesson Units" prop="lesson_units">
      <el-input-number v-model="model.lesson_units" :min="0.25" :step="0.25" />
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
