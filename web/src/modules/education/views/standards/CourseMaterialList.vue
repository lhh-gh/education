<script setup lang="ts">
import { saveCourseMaterial } from '../../api/standards/material.ts'

defineOptions({ name: 'EducationStandardsCourseMaterialList' })

const form = reactive({ material_code: '', material_name: '', material_type: 'file', guardian_visible: false })
const rows = ref<any[]>([])

async function save() {
  const response = await saveCourseMaterial(form)
  rows.value.unshift({ ...form, id: response.data.material_id })
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>Course Materials</span></template><el-form inline><el-input v-model="form.material_code" placeholder="Code" /><el-input v-model="form.material_name" placeholder="Name" /><el-switch v-model="form.guardian_visible" /><el-button type="primary" @click="save">Save</el-button></el-form><el-table :data="rows"><el-table-column prop="material_code" label="Code" /><el-table-column prop="material_name" label="Name" /></el-table></el-card></div>
</template>
