<script setup lang="ts">
import { saveServiceTemplateSet } from '../../api/standards/template.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

defineOptions({ name: 'EducationStandardsServiceTemplateList' })

const form = reactive({ template_set_code: '', template_set_name: '', items: [] as any[] })
const rows = ref<any[]>([])
const canSave = computed(() => hasAuth('education:standards:template:save'))

async function save() {
  const response = await saveServiceTemplateSet(form)
  rows.value.unshift({ ...form, id: response.data.template_set_id })
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>服务模板</span></template><el-form inline><el-input v-model="form.template_set_code" placeholder="模板编码" /><el-input v-model="form.template_set_name" placeholder="模板名称" /><el-button v-if="canSave" type="primary" @click="save">保存</el-button></el-form><el-table :data="rows"><el-table-column prop="template_set_code" label="编码" /><el-table-column prop="template_set_name" label="名称" /></el-table></el-card></div>
</template>
