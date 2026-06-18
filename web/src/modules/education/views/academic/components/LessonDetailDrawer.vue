<script setup lang="ts">
import type { LessonRecord, LessonStudentRecord } from '../../../api/academic/classSchedule.ts'
import { getLesson } from '../../../api/academic/classSchedule.ts'
import { lessonDetailStudentRows } from '../classScheduleRules.ts'

const props = defineProps<{
  modelValue: boolean
  lessonId?: number
  tenantId?: number
}>()

const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()
const loading = ref(false)
const detail = ref<(LessonRecord & { students?: LessonStudentRecord[] }) | null>(null)
const errorText = ref('')
const studentRows = computed(() => lessonDetailStudentRows(detail.value))

watch(() => props.modelValue, (visible) => {
  if (visible && props.lessonId) {
    load()
  }
})

async function load() {
  if (!props.lessonId) {
    return
  }
  loading.value = true
  try {
    const response = await getLesson(props.lessonId, props.tenantId)
    detail.value = response.data
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Lesson detail loading failed'
  }
  finally {
    loading.value = false
  }
}

function close() {
  emit('update:modelValue', false)
}
</script>

<template>
  <el-drawer :model-value="modelValue" title="Lesson Detail" size="720px" @close="close">
    <el-alert v-if="errorText" class="drawer-alert" type="error" show-icon :closable="false" :title="errorText" />
    <el-skeleton v-if="loading" :rows="8" animated />
    <template v-else-if="detail">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="Lesson No">
          {{ detail.lesson_no }}
        </el-descriptions-item>
        <el-descriptions-item label="Status">
          {{ detail.status }}
        </el-descriptions-item>
        <el-descriptions-item label="Title">
          {{ detail.title }}
        </el-descriptions-item>
        <el-descriptions-item label="Class">
          {{ detail.class_name_snapshot }}
        </el-descriptions-item>
        <el-descriptions-item label="Course">
          {{ detail.course_name_snapshot }}
        </el-descriptions-item>
        <el-descriptions-item label="Teacher">
          {{ detail.teacher_name_snapshot }}
        </el-descriptions-item>
        <el-descriptions-item label="Classroom">
          {{ detail.classroom_name_snapshot || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="Time">
          {{ detail.start_at }} - {{ detail.end_at }}
        </el-descriptions-item>
        <el-descriptions-item v-if="detail.cancel_reason" label="Cancel Reason">
          {{ detail.cancel_reason }}
        </el-descriptions-item>
      </el-descriptions>
      <el-table class="student-table" :data="studentRows" row-key="student_id">
        <el-table-column prop="student_no_snapshot" label="Student No" width="140" />
        <el-table-column prop="student_name_snapshot" label="Name" min-width="160" />
        <el-table-column prop="lesson_units" label="Units" width="100" />
        <el-table-column prop="status" label="Status" width="120" />
        <template #empty>
          <el-empty description="No lesson students" />
        </template>
      </el-table>
    </template>
  </el-drawer>
</template>

<style scoped lang="scss">
.drawer-alert {
  margin-bottom: 12px;
}

.student-table {
  margin-top: 16px;
}
</style>
