<script setup lang="ts">
import { saveServiceTemplateSet } from '../../api/standards/template.ts'

defineOptions({ name: 'EducationStandardsServiceTemplateList' })

const form = reactive({ template_set_code: '', template_set_name: '', items: [] as any[] })
const rows = ref<any[]>([])

async function save() {
  const response = await saveServiceTemplateSet(form)
  rows.value.unshift({ ...form, id: response.data.template_set_id })
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>Service Templates</span></template><el-form inline><el-input v-model="form.template_set_code" placeholder="Code" /><el-input v-model="form.template_set_name" placeholder="Name" /><el-button type="primary" @click="save">Save</el-button></el-form><el-table :data="rows"><el-table-column prop="template_set_code" label="Code" /><el-table-column prop="template_set_name" label="Name" /></el-table></el-card></div>
</template>
