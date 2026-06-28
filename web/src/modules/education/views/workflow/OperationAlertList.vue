<script setup lang="ts">
import type { OperationAlertRecord } from '../../api/workflow/alert.ts'
import { convertAlertToTask, pageOperationAlerts } from '../../api/workflow/alert.ts'
import { alertConvertState, workflowStatusLabel } from './workflowRules.ts'

defineOptions({ name: 'EducationWorkflowOperationAlertList' })

const loading = ref(false)
const rows = ref<OperationAlertRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20 })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageOperationAlerts(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function convert(row: OperationAlertRecord) {
  await convertAlertToTask(row.id, { assignee_user_id: 1 })
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-workflow-page pt-3">
    <el-card shadow="never">
      <template #header>
        <span>运营告警</span>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="title" label="告警标题" min-width="180" />
        <el-table-column prop="level" label="级别" width="120" />
        <el-table-column label="状态" width="130">
          <template #default="{ row }">
            {{ workflowStatusLabel(row.status) }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="120">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="alertConvertState(row).disabled" @click="convert(row)">
              {{ alertConvertState(row).label }}
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无运营告警" />
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
