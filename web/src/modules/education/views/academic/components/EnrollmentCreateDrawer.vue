<script setup lang="ts">
import type { StudentRecord } from '../../../api/academic/profile.ts'
import { pageStudents } from '../../../api/academic/profile.ts'
import type { CourseRecord, EnrollmentCreatePayload, EnrollmentCreateResult, LessonPackageRecord } from '../../../api/academic/courseAccount.ts'
import { createEnrollment, pageCourses, pageLessonPackages } from '../../../api/academic/courseAccount.ts'
import { computePackageTotal, enrollmentSuccessSummary } from '../courseAccountRules.ts'

const props = defineProps<{
  modelValue: boolean
  tenantId?: number
  campusId?: number
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'success': [result: EnrollmentCreateResult]
}>()

const message = useMessage()
const loading = ref(false)
const submitting = ref(false)
const students = ref<StudentRecord[]>([])
const courses = ref<CourseRecord[]>([])
const packages = ref<LessonPackageRecord[]>([])
const errorText = ref('')
const successText = ref('')
const model = reactive<EnrollmentCreatePayload>({
  tenant_id: props.tenantId,
  campus_id: props.campusId ?? 0,
  student_id: 0,
  course_id: 0,
  lesson_package_id: 0,
  deal_amount: undefined,
  enrolled_at: '',
  remark: '',
})
const selectedPackage = computed(() => packages.value.find(item => item.id === model.lesson_package_id))
const packageSummary = computed(() => {
  if (!selectedPackage.value) {
    return 'No package selected'
  }

  return `${selectedPackage.value.name}: ${computePackageTotal(selectedPackage.value.lesson_units, selectedPackage.value.bonus_units)} units / ${selectedPackage.value.sale_price}`
})

watch(() => props.modelValue, (visible) => {
  if (visible) {
    model.tenant_id = props.tenantId
    model.campus_id = props.campusId ?? model.campus_id
    successText.value = ''
    loadOptions()
  }
})

watch(() => model.course_id, () => {
  model.lesson_package_id = 0
  loadPackages()
})

async function loadOptions() {
  loading.value = true
  try {
    const [studentResponse, courseResponse] = await Promise.all([
      pageStudents({ page: 1, page_size: 100, tenant_id: props.tenantId, campus_id: model.campus_id, status: 'enabled' }),
      pageCourses({ page: 1, page_size: 100, tenant_id: props.tenantId, campus_id: model.campus_id, status: 'enabled' }),
    ])
    students.value = studentResponse.data.list
    courses.value = courseResponse.data.list
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Enrollment options loading failed'
  }
  finally {
    loading.value = false
  }
}

async function loadPackages() {
  if (!model.course_id) {
    packages.value = []
    return
  }
  const response = await pageLessonPackages({ page: 1, page_size: 100, tenant_id: props.tenantId, campus_id: model.campus_id, course_id: model.course_id, status: 'enabled' })
  packages.value = response.data.list
}

function close() {
  emit('update:modelValue', false)
}

async function submit() {
  submitting.value = true
  try {
    const response = await createEnrollment(model)
    successText.value = enrollmentSuccessSummary(response.data)
    message.success(successText.value)
    emit('success', response.data)
    close()
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Enrollment create failed'
    message.error(errorText.value)
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ loadOptions, submit, model, packageSummary })
</script>

<template>
  <el-drawer :model-value="modelValue" title="Create Enrollment" size="620px" @close="close">
    <el-alert v-if="errorText" class="drawer-alert" type="error" show-icon :closable="false" :title="errorText" />
    <el-alert v-if="successText" class="drawer-alert" type="success" show-icon :closable="false" :title="successText" />
    <el-skeleton v-if="loading" :rows="6" animated />
    <el-form v-else :model="model" label-width="140px">
      <el-form-item label="Campus ID">
        <el-input-number v-model="model.campus_id" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item label="Student">
        <el-select v-model="model.student_id" filterable>
          <el-option v-for="student in students" :key="student.id" :label="`${student.name} ${student.student_no}`" :value="student.id" />
        </el-select>
      </el-form-item>
      <el-form-item label="Course">
        <el-select v-model="model.course_id" filterable>
          <el-option v-for="course in courses" :key="course.id" :label="`${course.name} ${course.code}`" :value="course.id" />
        </el-select>
      </el-form-item>
      <el-form-item label="Package">
        <el-select v-model="model.lesson_package_id" filterable>
          <el-option v-for="item in packages" :key="item.id" :label="`${item.name} ${item.total_units} units`" :value="item.id" />
        </el-select>
      </el-form-item>
      <el-form-item label="Package Summary">
        <el-tag>{{ packageSummary }}</el-tag>
      </el-form-item>
      <el-form-item label="Deal Amount">
        <el-input v-model="model.deal_amount" inputmode="decimal" placeholder="Use package sale price when empty" />
      </el-form-item>
      <el-form-item label="Enrolled At">
        <el-date-picker v-model="model.enrolled_at" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" />
      </el-form-item>
      <el-form-item label="Remark">
        <el-input v-model="model.remark" type="textarea" maxlength="500" />
      </el-form-item>
    </el-form>
    <div class="drawer-actions">
      <el-button @click="close">
        Close
      </el-button>
      <el-button type="primary" :loading="submitting" @click="submit">
        Create
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
