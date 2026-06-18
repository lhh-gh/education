<script setup lang="ts">
import type { AttendanceSubmitResult } from '../../../api/academic/attendanceConsumption.ts'

defineOptions({ name: 'EducationAttendanceResultDrawer' })

defineProps<{
  modelValue: boolean
  result?: AttendanceSubmitResult | null
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: boolean): void
}>()
</script>

<template>
  <el-drawer :model-value="modelValue" title="Attendance Result" size="420px" @update:model-value="emit('update:modelValue', $event)">
    <el-empty v-if="!result" description="No result" />
    <el-descriptions v-else :column="1" border>
      <el-descriptions-item label="Batch No">
        {{ result.attendance_batch_no || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="Attendance Count">
        {{ result.attendance_count }}
      </el-descriptions-item>
      <el-descriptions-item label="Consumed Count">
        {{ result.consumed_count }}
      </el-descriptions-item>
      <el-descriptions-item label="No Consume Count">
        {{ result.no_consume_count ?? 0 }}
      </el-descriptions-item>
      <el-descriptions-item label="Total Units">
        {{ result.total_consumed_units }}
      </el-descriptions-item>
    </el-descriptions>
    <el-table v-if="result?.account_changes?.length" class="mt-4" :data="result.account_changes" size="small">
      <el-table-column prop="account_id" label="Account ID" width="110" />
      <el-table-column prop="before_available_units" label="Before" />
      <el-table-column prop="after_available_units" label="After" />
    </el-table>
  </el-drawer>
</template>
