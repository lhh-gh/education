<script setup lang="ts">
import { pageEscalationPolicies, saveEscalationPolicy } from '../../api/workflow/policy.ts'

defineOptions({ name: 'EducationWorkflowEscalationPolicyList' })

const rows = ref<any[]>([])
const total = ref(0)
const form = reactive({ policy_code: '', task_type: '', overdue_minutes: 30, escalate_to_user_ids_json: [] as number[] })

async function loadRows() {
  const response = await pageEscalationPolicies({})
  rows.value = response.data.list
  total.value = response.data.total
}

async function savePolicy() {
  await saveEscalationPolicy(form)
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-workflow-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Escalation Policies</span>
          <el-button type="primary" @click="savePolicy">
            Save
          </el-button>
        </div>
      </template>
      <el-form inline>
        <el-form-item label="Code">
          <el-input v-model="form.policy_code" />
        </el-form-item>
        <el-form-item label="Task Type">
          <el-input v-model="form.task_type" />
        </el-form-item>
        <el-form-item label="Overdue">
          <el-input-number v-model="form.overdue_minutes" :min="1" />
        </el-form-item>
      </el-form>
      <el-table :data="rows" row-key="policy_code">
        <el-table-column prop="policy_code" label="Code" />
        <el-table-column prop="task_type" label="Task Type" />
        <el-table-column prop="overdue_minutes" label="Overdue Minutes" />
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
