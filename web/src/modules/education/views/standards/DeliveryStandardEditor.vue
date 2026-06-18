<script setup lang="ts">
import { saveDeliveryStandard } from '../../api/standards/delivery-standard.ts'

defineOptions({ name: 'EducationStandardsDeliveryStandardEditor' })

const form = reactive({ course_id: 0, standard_code: '', standard_name: '', lesson_type: 'regular', content: '' })

async function save() {
  await saveDeliveryStandard(form)
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>Delivery Standards</span></template><el-form label-width="120px"><el-form-item label="Course"><el-input-number v-model="form.course_id" :min="0" /></el-form-item><el-form-item label="Code"><el-input v-model="form.standard_code" /></el-form-item><el-form-item label="Name"><el-input v-model="form.standard_name" /></el-form-item><el-form-item label="Lesson Type"><el-input v-model="form.lesson_type" /></el-form-item><el-form-item label="Content"><el-input v-model="form.content" type="textarea" /></el-form-item><el-button type="primary" @click="save">Save</el-button></el-form></el-card></div>
</template>
