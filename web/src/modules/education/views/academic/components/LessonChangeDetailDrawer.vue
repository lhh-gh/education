<script setup lang="ts">
import type { LessonChangeRecord } from '../../../api/academic/lessonChange.ts'
import { detailDrawerTitle, lessonChangeTypeLabel } from '../leaveMakeupRescheduleRules.ts'

defineOptions({ name: 'EducationLessonChangeDetailDrawer' })

defineProps<{
  modelValue: boolean
  row?: LessonChangeRecord | null
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: boolean): void
}>()
</script>

<template>
  <el-drawer :model-value="modelValue" :title="detailDrawerTitle(row)" size="620px" @update:model-value="emit('update:modelValue', $event)">
    <el-empty v-if="!row" description="No lesson change selected" />
    <el-descriptions v-else :column="2" border>
      <el-descriptions-item label="Change No">
        {{ row.change_no }}
      </el-descriptions-item>
      <el-descriptions-item label="Type">
        {{ lessonChangeTypeLabel(row.change_type) }}
      </el-descriptions-item>
      <el-descriptions-item label="Status">
        {{ row.status }}
      </el-descriptions-item>
      <el-descriptions-item label="Leave Request">
        {{ row.leave_request_id || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="Source Lesson">
        {{ row.source_lesson_id }}
      </el-descriptions-item>
      <el-descriptions-item label="Target Lesson">
        {{ row.target_lesson_id || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="Source Time">
        {{ row.source_start_at }} / {{ row.source_end_at }}
      </el-descriptions-item>
      <el-descriptions-item label="Target Time">
        {{ row.target_start_at }} / {{ row.target_end_at }}
      </el-descriptions-item>
      <el-descriptions-item label="Units">
        {{ row.lesson_units }}
      </el-descriptions-item>
      <el-descriptions-item label="Reason">
        {{ row.reason }}
      </el-descriptions-item>
    </el-descriptions>
  </el-drawer>
</template>
