<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { CourseRecord, LessonPackageRecord, LessonPackageSavePayload } from '../../../api/academic/courseAccount.ts'
import { createLessonPackage, pageCourses, updateLessonPackage } from '../../../api/academic/courseAccount.ts'
import { computePackageTotal } from '../courseAccountRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'

const { mode = 'create', tenantId, campusId, courseId, data = null } = defineProps<{
  mode?: 'create' | 'edit'
  tenantId?: number
  campusId?: number
  courseId?: number
  data?: LessonPackageRecord | null
}>()

const emit = defineEmits<{ success: [] }>()
const message = useMessage()
const formRef = ref<FormInstance>()
const submitting = ref(false)
const courses = ref<CourseRecord[]>([])
const model = reactive<LessonPackageSavePayload>({
  tenant_id: tenantId,
  campus_id: data?.campus_id ?? campusId ?? 0,
  course_id: data?.course_id ?? courseId ?? 0,
  code: data?.code ?? '',
  name: data?.name ?? '',
  lesson_units: data?.lesson_units ?? '0.00',
  bonus_units: data?.bonus_units ?? '0.00',
  list_price: data?.list_price ?? '0.00',
  sale_price: data?.sale_price ?? '0.00',
  validity_days: data?.validity_days,
  status: data?.status ?? 'enabled',
  sort_order: data?.sort_order ?? 0,
  remark: data?.remark ?? '',
})
const totalUnits = computed(() => computePackageTotal(model.lesson_units, model.bonus_units))

const rules: FormRules = {
  campus_id: [{ required: true, message: '请选择校区', trigger: 'blur' }],
  course_id: [{ required: true, message: '请选择课程', trigger: 'change' }],
  code: [{ required: true, message: '请输入课包编码', trigger: 'blur' }],
  name: [{ required: true, message: '请输入课包名称', trigger: 'blur' }],
  lesson_units: [{ required: true, message: '请输入课时数', trigger: 'blur' }],
  bonus_units: [{ required: true, message: '请输入赠送课时', trigger: 'blur' }],
  list_price: [{ required: true, message: '请输入标价', trigger: 'blur' }],
  sale_price: [{ required: true, message: '请输入售价', trigger: 'blur' }],
  status: [{ required: true, message: '请选择状态', trigger: 'change' }],
}

async function loadCourses() {
  const response = await pageCourses({ page: 1, page_size: 100, tenant_id: tenantId, campus_id: campusId, status: 'enabled' })
  courses.value = response.data.list
}

async function submit() {
  await formRef.value?.validate()
  submitting.value = true
  try {
    if (mode === 'edit' && data?.id) {
      await updateLessonPackage(data.id, model)
    }
    else {
      await createLessonPackage(model)
    }
    emit('success')
  }
  catch (error: any) {
    message.error(error?.message ?? '课包保存失败')
  }
  finally {
    submitting.value = false
  }
}

onMounted(loadCourses)
defineExpose({ submit, submitting, totalUnits })
</script>

<template>
  <el-form ref="formRef" :model="model" :rules="rules" label-width="128px">
    <el-form-item label="校区ID" prop="campus_id">
      <el-input-number v-model="model.campus_id" :min="1" :controls="false" />
    </el-form-item>
    <el-form-item label="课程" prop="course_id">
      <el-select v-model="model.course_id" filterable>
        <el-option v-for="course in courses" :key="course.id" :label="`${course.name} ${course.code}`" :value="course.id" />
      </el-select>
    </el-form-item>
    <el-form-item label="课包编码" prop="code">
      <el-input v-model="model.code" maxlength="64" :disabled="mode === 'edit'" />
    </el-form-item>
    <el-form-item label="课包名称" prop="name">
      <el-input v-model="model.name" maxlength="120" />
    </el-form-item>
    <el-form-item label="课时数" prop="lesson_units">
      <el-input v-model="model.lesson_units" inputmode="decimal" />
    </el-form-item>
    <el-form-item label="赠送课时" prop="bonus_units">
      <el-input v-model="model.bonus_units" inputmode="decimal" />
    </el-form-item>
    <el-form-item label="总课时">
      <el-tag>{{ totalUnits }}</el-tag>
    </el-form-item>
    <el-form-item label="标价" prop="list_price">
      <el-input v-model="model.list_price" inputmode="decimal" />
    </el-form-item>
    <el-form-item label="售价" prop="sale_price">
      <el-input v-model="model.sale_price" inputmode="decimal" />
    </el-form-item>
    <el-form-item label="有效期(天)">
      <el-input-number v-model="model.validity_days" :min="1" :max="3650" />
    </el-form-item>
    <el-form-item label="状态" prop="status">
      <el-segmented v-model="model.status" :options="[{ label: '启用', value: 'enabled' }, { label: '停用', value: 'disabled' }]" />
    </el-form-item>
    <el-form-item label="排序">
      <el-input-number v-model="model.sort_order" :min="-9999" :max="9999" />
    </el-form-item>
    <el-form-item label="备注">
      <el-input v-model="model.remark" type="textarea" maxlength="500" />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" :loading="submitting" @click="submit">
        保存
      </el-button>
    </el-form-item>
  </el-form>
</template>
