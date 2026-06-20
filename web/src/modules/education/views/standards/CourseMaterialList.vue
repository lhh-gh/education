<script setup lang="ts">
import { saveCourseMaterial } from '../../api/standards/material.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

defineOptions({ name: 'EducationStandardsCourseMaterialList' })

const form = reactive({ material_code: '', material_name: '', material_type: 'file', guardian_visible: false })
const rows = ref<any[]>([])
const canSave = computed(() => hasAuth('education:standards:material:save'))

async function save() {
  const response = await saveCourseMaterial(form)
  rows.value.unshift({ ...form, id: response.data.material_id })
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>课程资料</span></template><el-form inline><el-input v-model="form.material_code" placeholder="资料编码" /><el-input v-model="form.material_name" placeholder="资料名称" /><el-form-item label="家长可见"><el-switch v-model="form.guardian_visible" /></el-form-item><el-button v-if="canSave" type="primary" @click="save">保存</el-button></el-form><el-table :data="rows"><el-table-column prop="material_code" label="编码" /><el-table-column prop="material_name" label="名称" /></el-table></el-card></div>
</template>
