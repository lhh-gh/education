<script setup lang="ts">
import type { RescheduleLessonPayload, RescheduleLessonResult } from '../../../api/academic/lessonChange.ts'
import { rescheduleLesson } from '../../../api/academic/lessonChange.ts'
import { conflictMessage, rescheduleSuccessSummary } from '../leaveMakeupRescheduleRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'

defineOptions({ name: 'EducationRescheduleLessonForm' })

const props = defineProps<{
  modelValue: boolean
  tenantId?: number
  sourceLessonId?: number
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: boolean): void
  (event: 'success', result: RescheduleLessonResult): void
}>()

const message = useMessage()
const submitting = ref(false)
const errorText = ref('')
const resultText = ref('')
const form = reactive<RescheduleLessonPayload>({
  tenant_id: props.tenantId,
  source_lesson_id: props.sourceLessonId ?? 0,
  teacher_id: 0,
  classroom_id: undefined,
  title: '',
  start_at: '',
  end_at: '',
  lesson_units: 1,
  reason: '',
})

watch(() => props.modelValue, (visible) => {
  if (visible) {
    form.tenant_id = props.tenantId
    form.source_lesson_id = props.sourceLessonId ?? 0
    errorText.value = ''
    resultText.value = ''
  }
})

async function handleSubmit() {
  submitting.value = true
  try {
    const response = await rescheduleLesson(form)
    resultText.value = rescheduleSuccessSummary(response.data)
    emit('success', response.data)
    emit('update:modelValue', false)
  }
  catch (error: any) {
    errorText.value = conflictMessage(error)
    message.error(errorText.value)
  }
  finally {
    submitting.value = false
  }
}
</script>

<template>
  <el-drawer :model-value="modelValue" title="Reschedule Lesson" size="560px" @update:model-value="emit('update:modelValue', $event)">
    <el-alert v-if="errorText" class="mb-3" type="error" show-icon :closable="false" :title="errorText" />
    <el-alert v-if="resultText" class="mb-3" type="success" show-icon :closable="false" :title="resultText" />
    <el-form label-width="140px" :model="form">
      <el-form-item label="Source Lesson ID" required>
        <el-input-number v-model="form.source_lesson_id" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item label="Teacher ID" required>
        <el-input-number v-model="form.teacher_id" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item label="Classroom ID">
        <el-input-number v-model="form.classroom_id" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item label="Title" required>
        <el-input v-model="form.title" maxlength="160" />
      </el-form-item>
      <el-form-item label="Start" required>
        <el-date-picker v-model="form.start_at" value-format="YYYY-MM-DD HH:mm:ss" type="datetime" />
      </el-form-item>
      <el-form-item label="End" required>
        <el-date-picker v-model="form.end_at" value-format="YYYY-MM-DD HH:mm:ss" type="datetime" />
      </el-form-item>
      <el-form-item label="Units" required>
        <el-input-number v-model="form.lesson_units" :min="0.01" :precision="2" :controls="false" />
      </el-form-item>
      <el-form-item label="Reason" required>
        <el-input v-model="form.reason" type="textarea" :rows="4" maxlength="500" show-word-limit />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="emit('update:modelValue', false)">
        Cancel
      </el-button>
      <el-button type="primary" :loading="submitting" @click="handleSubmit">
        Save
      </el-button>
    </template>
  </el-drawer>
</template>
