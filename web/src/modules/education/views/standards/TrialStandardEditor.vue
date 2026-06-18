<script setup lang="ts">
import { saveTrialStandard } from '../../api/standards/trial-standard.ts'

defineOptions({ name: 'EducationStandardsTrialStandardEditor' })

const form = reactive({ course_id: 0, standard_code: '', standard_name: '', guardian_visible: false, items: [{ item_name: '', item_content: '', sort_order: 0 }] })
const status = ref('')

async function save() {
  const response = await saveTrialStandard(form)
  status.value = response.data.status ?? 'draft'
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>Trial Standards</span></template><el-form label-width="120px"><el-form-item label="Course"><el-input-number v-model="form.course_id" :min="0" /></el-form-item><el-form-item label="Code"><el-input v-model="form.standard_code" /></el-form-item><el-form-item label="Name"><el-input v-model="form.standard_name" /></el-form-item><el-form-item label="Guardian"><el-switch v-model="form.guardian_visible" /></el-form-item><el-button type="primary" @click="save">Save</el-button><el-tag v-if="status">{{ status }}</el-tag></el-form></el-card></div>
</template>
