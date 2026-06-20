<script setup lang="ts">
import { saveDeliveryStandard } from '../../api/standards/delivery-standard.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

defineOptions({ name: 'EducationStandardsDeliveryStandardEditor' })

const form = reactive({ course_id: 0, standard_code: '', standard_name: '', lesson_type: 'regular', content: '' })
const canSave = computed(() => hasAuth('education:standards:delivery:save'))

async function save() {
  await saveDeliveryStandard(form)
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>交付标准</span></template><el-form label-width="120px"><el-form-item label="课程 ID"><el-input-number v-model="form.course_id" :min="0" /></el-form-item><el-form-item label="标准编码"><el-input v-model="form.standard_code" /></el-form-item><el-form-item label="标准名称"><el-input v-model="form.standard_name" /></el-form-item><el-form-item label="课型"><el-input v-model="form.lesson_type" /></el-form-item><el-form-item label="交付内容"><el-input v-model="form.content" type="textarea" /></el-form-item><el-button v-if="canSave" type="primary" @click="save">保存</el-button></el-form></el-card></div>
</template>
