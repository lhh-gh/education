<script setup lang="ts">
import type { AttendanceLessonDetail, AttendanceSubmitRecord, AttendanceSubmitResult } from '../../../api/academic/attendanceConsumption.ts'
import { getAttendanceLesson, submitAttendance } from '../../../api/academic/attendanceConsumption.ts'
import { accountBalanceWarning, defaultAttendanceRecords } from '../attendanceConsumptionRules.ts'

defineOptions({ name: 'EducationAttendanceSubmitDrawer' })

const props = defineProps<{
  modelValue: boolean
  lessonId?: number
  tenantId?: number
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: boolean): void
  (event: 'submitted', result: AttendanceSubmitResult): void
}>()

const message = useMessage()
const loading = ref(false)
const submitting = ref(false)
const errorText = ref('')
const submittedAt = ref('')
const detail = ref<AttendanceLessonDetail | null>(null)
const records = ref<AttendanceSubmitRecord[]>([])

watch(() => [props.modelValue, props.lessonId] as const, async ([visible, lessonId]) => {
  if (visible && lessonId) {
    await loadDetail(lessonId)
  }
})

async function loadDetail(lessonId: number) {
  loading.value = true
  try {
    const response = await getAttendanceLesson(lessonId, props.tenantId)
    detail.value = response.data
    records.value = defaultAttendanceRecords(response.data.lesson_students)
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Attendance detail loading failed'
  }
  finally {
    loading.value = false
  }
}

function warningFor(record: AttendanceSubmitRecord): string {
  const student = detail.value?.lesson_students.find(item => (item.lesson_student_id ?? item.id) === record.lesson_student_id)
  return student ? accountBalanceWarning(student, Number(record.consumed_units)) : ''
}

async function handleSubmit() {
  if (!props.lessonId) {
    return
  }
  submitting.value = true
  try {
    const response = await submitAttendance(props.lessonId, {
      tenant_id: props.tenantId,
      submitted_at: submittedAt.value || null,
      records: records.value,
    })
    errorText.value = ''
    emit('submitted', response.data)
    emit('update:modelValue', false)
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Attendance submit failed'
    message.error(errorText.value)
  }
  finally {
    submitting.value = false
  }
}
</script>

<template>
  <el-drawer :model-value="modelValue" title="Submit Attendance" size="820px" @update:model-value="emit('update:modelValue', $event)">
    <el-alert v-if="errorText" class="mb-3" type="error" show-icon :closable="false" :title="errorText" />
    <div v-loading="loading">
      <el-descriptions v-if="detail?.lesson" :column="2" border>
        <el-descriptions-item label="Lesson No">
          {{ detail.lesson.lesson_no }}
        </el-descriptions-item>
        <el-descriptions-item label="Title">
          {{ detail.lesson.title }}
        </el-descriptions-item>
        <el-descriptions-item label="Class">
          {{ detail.lesson.class_name_snapshot }}
        </el-descriptions-item>
        <el-descriptions-item label="Teacher">
          {{ detail.lesson.teacher_name_snapshot }}
        </el-descriptions-item>
      </el-descriptions>
      <el-form class="mt-4">
        <el-form-item label="Submitted At">
          <el-date-picker v-model="submittedAt" value-format="YYYY-MM-DD HH:mm:ss" type="datetime" />
        </el-form-item>
      </el-form>
      <el-table :data="records" row-key="lesson_student_id">
        <el-table-column label="Student" min-width="160">
          <template #default="{ row }">
            {{ detail?.lesson_students.find(item => (item.lesson_student_id ?? item.id) === row.lesson_student_id)?.student_name_snapshot || row.lesson_student_id }}
          </template>
        </el-table-column>
        <el-table-column label="Status" width="140">
          <template #default="{ row }">
            <el-select v-model="row.attendance_status">
              <el-option label="Present" value="present" />
              <el-option label="Late" value="late" />
              <el-option label="Absent" value="absent" />
              <el-option label="Leave" value="leave" />
            </el-select>
          </template>
        </el-table-column>
        <el-table-column label="Policy" width="150">
          <template #default="{ row }">
            <el-select v-model="row.consume_policy">
              <el-option label="Consume" value="consume" />
              <el-option label="No Consume" value="no_consume" />
            </el-select>
          </template>
        </el-table-column>
        <el-table-column label="Units" width="150">
          <template #default="{ row }">
            <el-input-number v-model="row.consumed_units" :min="0" :precision="2" :controls="false" />
            <div v-if="warningFor(row)" class="row-warning">
              {{ warningFor(row) }}
            </div>
          </template>
        </el-table-column>
        <el-table-column label="Remark" min-width="180">
          <template #default="{ row }">
            <el-input v-model="row.remark" clearable />
          </template>
        </el-table-column>
      </el-table>
    </div>
    <template #footer>
      <el-button @click="emit('update:modelValue', false)">
        Cancel
      </el-button>
      <el-button type="primary" :loading="submitting" @click="handleSubmit">
        Submit
      </el-button>
    </template>
  </el-drawer>
</template>

<style scoped lang="scss">
.row-warning {
  margin-top: 4px;
  font-size: 12px;
  color: var(--el-color-danger);
}
</style>
