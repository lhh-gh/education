<script setup lang="ts">
import type { WorkflowRuleRecord } from '../../api/workflow/rule.ts'
import { pageWorkflowRules, saveWorkflowRule } from '../../api/workflow/rule.ts'

defineOptions({ name: 'EducationWorkflowRuleList' })

const loading = ref(false)
const rows = ref<WorkflowRuleRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20 })
const form = reactive({ rule_code: '', rule_name: '', event_type: '', actions: [{ action_type: 'create_task', action_config_json: { task_type: 'renewal_follow' } }] })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageWorkflowRules(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function saveRule() {
  await saveWorkflowRule(form)
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-workflow-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Workflow Rules</span>
          <el-button type="primary" @click="saveRule">
            Save
          </el-button>
        </div>
      </template>
      <el-form inline>
        <el-form-item label="Code">
          <el-input v-model="form.rule_code" />
        </el-form-item>
        <el-form-item label="Name">
          <el-input v-model="form.rule_name" />
        </el-form-item>
        <el-form-item label="Event">
          <el-input v-model="form.event_type" />
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="rule_code" label="Code" />
        <el-table-column prop="rule_name" label="Name" />
        <el-table-column prop="event_type" label="Event" />
        <el-table-column prop="status" label="Status" />
        <template #empty>
          <el-empty description="No workflow rules" />
        </template>
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
