<script setup lang="ts">
import type { TeacherRecord } from '../../../api/academic/profile.ts'
import { pageTeachers } from '../../../api/academic/profile.ts'
import type { TeacherCourseRecord } from '../../../api/academic/courseAccount.ts'
import { getCourseTeachers, saveCourseTeachers } from '../../../api/academic/courseAccount.ts'
import { normalizeTeacherSelection } from '../courseAccountRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'

const props = defineProps<{
  modelValue: boolean
  courseId?: number
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
const teachers = ref<TeacherRecord[]>([])
const selectedTeacherIds = ref<number[]>([])
const authorizedRows = ref<TeacherCourseRecord[]>([])
const errorText = ref('')

watch(() => props.modelValue, (visible) => {
  if (visible && props.courseId) {
    load()
  }
})

async function load() {
  if (!props.courseId) {
    return
  }
  loading.value = true
  try {
    const [teacherResponse, authResponse] = await Promise.all([
      pageTeachers({ page: 1, page_size: 100, tenant_id: props.tenantId, campus_id: props.campusId, status: 'enabled' }),
      getCourseTeachers(props.courseId, props.tenantId),
    ])
    const authData = authResponse.data
    authorizedRows.value = Array.isArray(authData) ? authData : authData.list
    selectedTeacherIds.value = normalizeTeacherSelection(authorizedRows.value.map(item => item.teacher_id))
    teachers.value = teacherResponse.data.list
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Teacher authorization loading failed'
  }
  finally {
    loading.value = false
  }
}

function close() {
  emit('update:modelValue', false)
}

async function submit() {
  if (!props.courseId || props.readonly) {
    return
  }
  submitting.value = true
  try {
    await saveCourseTeachers(props.courseId, normalizeTeacherSelection(selectedTeacherIds.value), props.tenantId)
    emit('success')
    close()
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Teacher authorization save failed'
    message.error(errorText.value)
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ load, submit, selectedTeacherIds })
</script>

<template>
  <el-drawer :model-value="modelValue" title="Course Teachers" size="560px" @close="close">
    <el-alert v-if="errorText" class="drawer-alert" type="error" show-icon :closable="false" :title="errorText" />
    <el-skeleton v-if="loading" :rows="5" animated />
    <template v-else>
      <el-form label-width="120px">
        <el-form-item label="Teachers">
          <el-select v-model="selectedTeacherIds" multiple filterable clearable :disabled="readonly" placeholder="Select enabled teachers">
            <el-option v-for="teacher in teachers" :key="teacher.id" :label="`${teacher.name} ${teacher.teacher_no}`" :value="teacher.id" />
          </el-select>
        </el-form-item>
      </el-form>
      <el-table :data="authorizedRows" row-key="teacher_id">
        <el-table-column prop="teacher_no" label="Teacher No" width="140" />
        <el-table-column prop="teacher_name" label="Name" min-width="140" />
        <el-table-column prop="teacher_mobile" label="Mobile" width="140" />
        <el-table-column prop="status" label="Status" width="100" />
        <template #empty>
          <el-empty description="No authorized teachers" />
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
