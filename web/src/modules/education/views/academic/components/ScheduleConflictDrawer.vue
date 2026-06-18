<script setup lang="ts">
import { conflictDrawerRows } from '../classScheduleRules.ts'

const props = defineProps<{
  modelValue: boolean
  result?: Record<string, any> | null
}>()

const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()
const rows = computed(() => conflictDrawerRows(props.result))

function close() {
  emit('update:modelValue', false)
}
</script>

<template>
  <el-drawer :model-value="modelValue" title="Schedule Conflicts" size="560px" @close="close">
    <el-alert class="drawer-alert" type="error" show-icon :closable="false" title="Schedule conflict detected" />
    <el-table :data="rows" row-key="lesson_id">
      <el-table-column prop="conflict_type" label="Type" width="140" />
      <el-table-column prop="lesson_id" label="Lesson ID" width="120" />
      <el-table-column prop="student_id" label="Student ID" width="120" />
      <el-table-column prop="message" label="Message" min-width="160" />
      <template #empty>
        <el-empty description="No conflict rows" />
      </template>
    </el-table>
  </el-drawer>
</template>

<style scoped lang="scss">
.drawer-alert {
  margin-bottom: 12px;
}
</style>
