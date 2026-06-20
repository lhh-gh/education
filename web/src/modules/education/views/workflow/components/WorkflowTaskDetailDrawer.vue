<script setup lang="ts">
import type { WorkflowTaskRecord } from '../../../api/workflow/task.ts'
import { workflowStatusLabel } from '../workflowRules.ts'

defineOptions({ name: 'EducationWorkflowTaskDetailDrawer' })

defineProps<{ task: WorkflowTaskRecord | null }>()
const visible = defineModel<boolean>('visible', { default: false })
</script>

<template>
  <el-drawer v-model="visible" title="任务详情" size="40%">
    <el-descriptions v-if="task" :column="1" border>
      <el-descriptions-item label="任务标题">
        {{ task.title }}
      </el-descriptions-item>
      <el-descriptions-item label="状态">
        {{ workflowStatusLabel(task.status) }}
      </el-descriptions-item>
      <el-descriptions-item label="到期时间">
        {{ task.due_at || '-' }}
      </el-descriptions-item>
    </el-descriptions>
  </el-drawer>
</template>
