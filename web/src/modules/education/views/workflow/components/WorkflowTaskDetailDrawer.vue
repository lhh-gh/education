<script setup lang="ts">
import type { WorkflowTaskRecord } from '../../../api/workflow/task.ts'

defineOptions({ name: 'EducationWorkflowTaskDetailDrawer' })

defineProps<{ task: WorkflowTaskRecord | null }>()
const visible = defineModel<boolean>('visible', { default: false })
</script>

<template>
  <el-drawer v-model="visible" title="Task Detail" size="40%">
    <el-descriptions v-if="task" :column="1" border>
      <el-descriptions-item label="Title">
        {{ task.title }}
      </el-descriptions-item>
      <el-descriptions-item label="Status">
        {{ task.status }}
      </el-descriptions-item>
      <el-descriptions-item label="Due">
        {{ task.due_at || '-' }}
      </el-descriptions-item>
    </el-descriptions>
  </el-drawer>
</template>
