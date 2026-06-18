<script setup lang="ts">
import { pageWorkflowTemplates, saveWorkflowTemplate } from '../../api/workflow/template.ts'

defineOptions({ name: 'EducationWorkflowTemplateList' })

const rows = ref<any[]>([])
const total = ref(0)
const form = reactive({ template_code: '', template_name: '', task_type: '', template_json: {} })

async function loadRows() {
  const response = await pageWorkflowTemplates({})
  rows.value = response.data.list
  total.value = response.data.total
}

async function saveTemplate() {
  await saveWorkflowTemplate(form)
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-workflow-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Workflow Templates</span>
          <el-button type="primary" @click="saveTemplate">
            Save
          </el-button>
        </div>
      </template>
      <el-form inline>
        <el-form-item label="Code">
          <el-input v-model="form.template_code" />
        </el-form-item>
        <el-form-item label="Name">
          <el-input v-model="form.template_name" />
        </el-form-item>
        <el-form-item label="Task Type">
          <el-input v-model="form.task_type" />
        </el-form-item>
      </el-form>
      <el-table :data="rows" row-key="template_code">
        <el-table-column prop="template_code" label="Code" />
        <el-table-column prop="template_name" label="Name" />
        <el-table-column prop="task_type" label="Task Type" />
      </el-table>
      <el-pagination class="page-pagination" layout="total" :total="total" />
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-workflow-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }
  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>
