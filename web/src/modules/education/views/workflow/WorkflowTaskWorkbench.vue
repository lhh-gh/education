<script setup lang="ts">
import type { WorkflowTaskRecord } from '../../api/workflow/task.ts'
import { completeWorkflowTask, pageWorkflowTasks } from '../../api/workflow/task.ts'
import { isOverdueTask, workflowStatusLabel } from './workflowRules.ts'

defineOptions({ name: 'EducationWorkflowTaskWorkbench' })

const loading = ref(false)
const rows = ref<WorkflowTaskRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, status: '' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageWorkflowTasks(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function complete(row: WorkflowTaskRecord) {
  await completeWorkflowTask(row.id, { result: 'done', content: '已完成' })
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-workflow-page pt-3">
    <el-card shadow="never">
      <template #header>
        <span>待办任务</span>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="title" label="任务标题" min-width="180" />
        <el-table-column prop="task_type" label="任务类型" width="150" />
        <el-table-column label="状态" width="130">
          <template #default="{ row }">
            <el-tag :type="isOverdueTask(row) ? 'danger' : 'info'">
              {{ workflowStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="120">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="row.status === 'completed'" @click="complete(row)">
              完成
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无待办任务" />
        </template>
      </el-table>
      <el-pagination class="page-pagination" layout="total" :total="total" />
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-workflow-page {
  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>
