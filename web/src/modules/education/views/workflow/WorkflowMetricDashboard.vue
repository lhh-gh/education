<script setup lang="ts">
import type { WorkflowMetricSummary } from '../../api/workflow/metric.ts'
import { getWorkflowMetrics } from '../../api/workflow/metric.ts'
import { metricFilterPayload } from './workflowRules.ts'

defineOptions({ name: 'EducationWorkflowMetricDashboard' })

const metrics = ref<WorkflowMetricSummary>({ created_count: 0, completed_count: 0, overdue_count: 0, alert_count: 0 })
const filters = reactive({ dateRange: undefined as [string, string] | undefined, task_type: '' })

async function loadMetrics() {
  const response = await getWorkflowMetrics(metricFilterPayload(filters))
  metrics.value = response.data
}

onMounted(loadMetrics)
</script>

<template>
  <div class="mine-layout education-workflow-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Workflow Metrics</span>
          <el-button type="primary" @click="loadMetrics">
            Refresh
          </el-button>
        </div>
      </template>
      <el-row :gutter="16">
        <el-col :span="6">
          <el-statistic title="Created" :value="metrics.created_count" />
        </el-col>
        <el-col :span="6">
          <el-statistic title="Completed" :value="metrics.completed_count" />
        </el-col>
        <el-col :span="6">
          <el-statistic title="Overdue" :value="metrics.overdue_count" />
        </el-col>
        <el-col :span="6">
          <el-statistic title="Alerts" :value="metrics.alert_count" />
        </el-col>
      </el-row>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-workflow-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }
}
</style>
