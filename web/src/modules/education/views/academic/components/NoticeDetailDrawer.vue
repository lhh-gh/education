<script setup lang="ts">
import type { NoticeRecord } from '../../../api/academic/notice.ts'
import { noticePriorityType, noticeStatusType } from '../noticeRules.ts'

defineOptions({ name: 'EducationNoticeDetailDrawer' })

defineProps<{
  modelValue: boolean
  row?: NoticeRecord | null
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: boolean): void
}>()
</script>

<template>
  <el-drawer :model-value="modelValue" title="Notice Detail" size="640px" @update:model-value="emit('update:modelValue', $event)">
    <el-empty v-if="!row" description="No notice selected" />
    <el-descriptions v-else :column="1" border>
      <el-descriptions-item label="Title">
        {{ row.title }}
      </el-descriptions-item>
      <el-descriptions-item label="Content">
        {{ row.content }}
      </el-descriptions-item>
      <el-descriptions-item label="Type">
        {{ row.notice_type }}
      </el-descriptions-item>
      <el-descriptions-item label="Priority">
        <el-tag :type="noticePriorityType(row.priority)">
          {{ row.priority }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="Target">
        {{ row.target_type }} / {{ row.target_id ?? '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="Status">
        <el-tag :type="noticeStatusType(row.status)">
          {{ row.status }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="Published At">
        {{ row.published_at ?? '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="Withdrawn At">
        {{ row.withdrawn_at ?? '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="Withdraw Reason">
        {{ row.withdraw_reason ?? '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="Receipts">
        {{ row.read_count }} / {{ row.receipt_count }}
      </el-descriptions-item>
    </el-descriptions>
  </el-drawer>
</template>
