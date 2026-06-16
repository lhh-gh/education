<script setup lang="ts">
import type { ClassStudentRecord } from '../../../api/academic/classSchedule.ts'
import { getClassStudents, saveClassStudents } from '../../../api/academic/classSchedule.ts'
import { pageStudents } from '../../../api/academic/profile.ts'
import type { StudentRecord } from '../../../api/academic/profile.ts'
import { classStudentSavePayload } from '../classScheduleRules.ts'

const props = defineProps<{
  modelValue: boolean
  classId?: number
  tenantId?: number
  campusId?: number
  readonly?: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'success': []
}>()

const message = useMessage()
const loading = ref(false)
const submitting = ref(false)
const rows = ref<ClassStudentRecord[]>([])
const students = ref<StudentRecord[]>([])
const selectedStudentIds = ref<number[]>([])
const errorText = ref('')

watch(() => props.modelValue, (visible) => {
  if (visible && props.classId) {
    load()
  }
})

async function load() {
  if (!props.classId) {
    return
  }
  loading.value = true
  try {
    const [studentResponse, classResponse] = await Promise.all([
      pageStudents({ page: 1, page_size: 100, tenant_id: props.tenantId, campus_id: props.campusId, status: 'enabled' }),
      getClassStudents(props.classId, props.tenantId),
    ])
    students.value = studentResponse.data.list
    rows.value = classResponse.data.list
    selectedStudentIds.value = rows.value.map(row => row.student_id)
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Class students loading failed'
  }
  finally {
    loading.value = false
  }
}

function close() {
  emit('update:modelValue', false)
}

async function submit() {
  if (!props.classId || props.readonly) {
    return
  }
  submitting.value = true
  try {
    await saveClassStudents(props.classId, classStudentSavePayload(selectedStudentIds.value), props.tenantId)
    emit('success')
    close()
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Class students save failed'
    message.error(errorText.value)
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ load, submit, selectedStudentIds, errorText })
</script>

<template>
  <el-drawer :model-value="modelValue" title="Class Students" size="640px" @close="close">
    <el-alert v-if="errorText" class="drawer-alert" type="error" show-icon :closable="false" :title="errorText" />
    <el-skeleton v-if="loading" :rows="5" animated />
    <template v-else>
      <el-form label-width="120px">
        <el-form-item label="Students">
          <el-select v-model="selectedStudentIds" multiple filterable clearable :disabled="readonly" placeholder="Select active students">
            <el-option v-for="student in students" :key="student.id" :label="`${student.name} ${student.student_no}`" :value="student.id" />
          </el-select>
        </el-form-item>
      </el-form>
      <el-table :data="rows" row-key="student_id">
        <el-table-column prop="student_no" label="Student No" width="140" />
        <el-table-column prop="student_name" label="Name" min-width="140" />
        <el-table-column prop="account_id" label="Account" width="100" />
        <el-table-column prop="status" label="Status" width="100" />
        <template #empty>
          <el-empty description="No class students" />
        </template>
      </el-table>
    </template>
    <div class="drawer-actions">
      <el-button @click="close">
        Close
      </el-button>
      <el-button v-if="!readonly" type="primary" :loading="submitting" @click="submit">
        Save
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
