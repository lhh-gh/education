<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { SingleLessonSchedulePayload } from '../../../api/academic/classSchedule.ts'
import { checkScheduleConflict, scheduleSingleLesson } from '../../../api/academic/classSchedule.ts'
import { singleSchedulePayload, singleScheduleSuccessSummary } from '../classScheduleRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'

const props = defineProps<{
  modelValue: boolean
  tenantId?: number
  campusId?: number
  classId?: number
  lessonUnits?: number | string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'success': []
  'conflict': [result: Record<string, unknown>]
}>()

const message = useMessage()
const formRef = ref<FormInstance>()
const submitting = ref(false)
const checking = ref(false)
const errorText = ref('')
const model = reactive({
  campus_id: props.campusId ?? 0,
  class_id: props.classId ?? 0,
  teacher_id: 0,
  classroom_id: null as number | null,
  title: '',
  start_at: '',
  end_at: '',
  lesson_units: Number(props.lessonUnits ?? 1),
  remark: '',
})

const rules: FormRules = {
  campus_id: [{ required: true, message: 'Campus is required', trigger: 'blur' }],
  class_id: [{ required: true, message: 'Class is required', trigger: 'blur' }],
  teacher_id: [{ required: true, message: 'Teacher is required', trigger: 'blur' }],
  title: [{ required: true, message: 'Title is required', trigger: 'blur' }],
  start_at: [{ required: true, message: 'Start is required', trigger: 'change' }],
  end_at: [{ required: true, message: 'End is required', trigger: 'change' }],
  lesson_units: [{ required: true, message: 'Lesson units is required', trigger: 'blur' }],
}

watch(() => props.modelValue, (visible) => {
  if (visible) {
    model.campus_id = props.campusId ?? model.campus_id
    model.class_id = props.classId ?? model.class_id
    model.lesson_units = Number(props.lessonUnits ?? model.lesson_units)
  }
})

function close() {
  emit('update:modelValue', false)
}

async function previewConflict() {
  await formRef.value?.validate()
  checking.value = true
  try {
    const response = await checkScheduleConflict({ ...singleSchedulePayload(model), tenant_id: props.tenantId })
    if (response.data.has_conflict) {
      emit('conflict', response.data)
    }
    else {
      message.success('No schedule conflict')
    }
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Conflict check failed'
    message.error(errorText.value)
  }
  finally {
    checking.value = false
  }
}

async function submit() {
  await formRef.value?.validate()
  submitting.value = true
  try {
    const payload = { ...singleSchedulePayload(model), tenant_id: props.tenantId } as SingleLessonSchedulePayload
    const response = await scheduleSingleLesson(payload)
    message.success(singleScheduleSuccessSummary(response.data))
    errorText.value = ''
    emit('success')
    close()
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Single lesson schedule failed'
    message.error(errorText.value)
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ submit, previewConflict, errorText })
</script>

<template>
  <el-drawer :model-value="modelValue" title="Single Lesson" size="560px" @close="close">
    <el-alert v-if="errorText" class="drawer-alert" type="error" show-icon :closable="false" :title="errorText" />
    <el-form ref="formRef" :model="model" :rules="rules" label-width="128px">
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
      <el-form-item label="Remark">
        <el-input v-model="model.remark" type="textarea" maxlength="500" />
      </el-form-item>
    </el-form>
    <div class="drawer-actions">
      <el-button :loading="checking" @click="previewConflict">
        Check Conflict
      </el-button>
      <el-button type="primary" :loading="submitting" @click="submit">
        Schedule
      </el-button>
    </div>
  </el-drawer>
</template>

<style scoped lang="scss">
.drawer-alert {
  margin-bottom: 12px;
}

.drawer-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  margin-top: 16px;
}
</style>
