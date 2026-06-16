<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { BatchLessonSchedulePayload } from '../../../api/academic/classSchedule.ts'
import { checkScheduleConflict, scheduleBatchLessons } from '../../../api/academic/classSchedule.ts'
import { batchSchedulePayload, batchScheduleSuccessSummary } from '../classScheduleRules.ts'

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
  title_template: '',
  start_date: '',
  end_date: '',
  weekdays: [] as Array<number | string>,
  start_time: '',
  end_time: '',
  lesson_units: Number(props.lessonUnits ?? 1),
  remark: '',
})

const rules: FormRules = {
  campus_id: [{ required: true, message: 'Campus is required', trigger: 'blur' }],
  class_id: [{ required: true, message: 'Class is required', trigger: 'blur' }],
  teacher_id: [{ required: true, message: 'Teacher is required', trigger: 'blur' }],
  title_template: [{ required: true, message: 'Title template is required', trigger: 'blur' }],
  start_date: [{ required: true, message: 'Start date is required', trigger: 'change' }],
  end_date: [{ required: true, message: 'End date is required', trigger: 'change' }],
  weekdays: [{ required: true, message: 'Weekdays are required', trigger: 'change' }],
  start_time: [{ required: true, message: 'Start time is required', trigger: 'change' }],
  end_time: [{ required: true, message: 'End time is required', trigger: 'change' }],
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
    const response = await checkScheduleConflict({ ...batchSchedulePayload(model), tenant_id: props.tenantId })
    if (response.data.has_conflict) {
      emit('conflict', response.data)
    }
    else {
      message.success('No schedule conflict')
    }
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Conflict preview failed'
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
    const payload = { ...batchSchedulePayload(model), tenant_id: props.tenantId } as BatchLessonSchedulePayload
    const response = await scheduleBatchLessons(payload)
    message.success(batchScheduleSuccessSummary(response.data))
    errorText.value = ''
    emit('success')
    close()
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Batch schedule failed'
    message.error(errorText.value)
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ submit, previewConflict, errorText })
</script>

<template>
  <el-drawer :model-value="modelValue" title="Batch Lessons" size="600px" @close="close">
    <el-alert v-if="errorText" class="drawer-alert" type="error" show-icon :closable="false" :title="errorText" />
    <el-form ref="formRef" :model="model" :rules="rules" label-width="132px">
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
      <el-form-item label="Title Template" prop="title_template">
        <el-input v-model="model.title_template" maxlength="160" />
      </el-form-item>
      <el-form-item label="Date Range" prop="start_date">
        <el-date-picker v-model="model.start_date" value-format="YYYY-MM-DD" type="date" placeholder="Start" />
        <el-date-picker v-model="model.end_date" value-format="YYYY-MM-DD" type="date" placeholder="End" class="ml-2" />
      </el-form-item>
      <el-form-item label="Weekdays" prop="weekdays">
        <el-checkbox-group v-model="model.weekdays">
          <el-checkbox :value="1">
            Mon
          </el-checkbox>
          <el-checkbox :value="2">
            Tue
          </el-checkbox>
          <el-checkbox :value="3">
            Wed
          </el-checkbox>
          <el-checkbox :value="4">
            Thu
          </el-checkbox>
          <el-checkbox :value="5">
            Fri
          </el-checkbox>
          <el-checkbox :value="6">
            Sat
          </el-checkbox>
          <el-checkbox :value="0">
            Sun
          </el-checkbox>
        </el-checkbox-group>
      </el-form-item>
      <el-form-item label="Time" prop="start_time">
        <el-time-picker v-model="model.start_time" value-format="HH:mm" format="HH:mm" placeholder="Start" />
        <el-time-picker v-model="model.end_time" value-format="HH:mm" format="HH:mm" placeholder="End" class="ml-2" />
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
        Preview Conflict
      </el-button>
      <el-button type="primary" :loading="submitting" @click="submit">
        Schedule Batch
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
