<script setup lang="ts">
import type { LessonChangeRequestRecord } from '../../../api/operations/lesson-change.ts'
import { applyLessonChangeRequest, approveLessonChangeRequest, rejectLessonChangeRequest } from '../../../api/operations/lesson-change.ts'
import { operationStatusLabel, operationTypeLabel } from '../operationRules.ts'

const props = defineProps<{ row: LessonChangeRequestRecord | null, action: 'approve' | 'reject' | 'apply', error?: string }>()
const emit = defineEmits<{ success: [] }>()
const model = defineModel<boolean>({ default: false })
const note = ref('')
const submitting = ref(false)

async function submit() {
  if (!props.row) {
    return
  }
  submitting.value = true
  try {
    if (props.action === 'approve') {
      await approveLessonChangeRequest(props.row.id, { tenant_id: props.row.tenant_id, campus_id: props.row.campus_id ?? undefined, review_note: note.value })
    }
    else if (props.action === 'reject') {
      await rejectLessonChangeRequest(props.row.id, { tenant_id: props.row.tenant_id, campus_id: props.row.campus_id ?? undefined, review_note: note.value })
    }
    else {
      await applyLessonChangeRequest(props.row.id, { tenant_id: props.row.tenant_id, campus_id: props.row.campus_id ?? undefined })
    }
    model.value = false
    emit('success')
  }
  finally {
    submitting.value = false
  }
}
</script>

<template>
  <el-drawer v-model="model" title="调课审批" size="420px">
    <el-alert v-if="error" class="mb-3" type="error" show-icon :closable="false" :title="error" />
    <el-descriptions v-if="row" :column="1" border>
      <el-descriptions-item label="课次">
        {{ row.lesson_id }}
      </el-descriptions-item>
      <el-descriptions-item label="类型">
        {{ operationTypeLabel(row.change_type) }}
      </el-descriptions-item>
      <el-descriptions-item label="状态">
        {{ operationStatusLabel(row.status) }}
      </el-descriptions-item>
      <el-descriptions-item label="原因">
        {{ row.reason }}
      </el-descriptions-item>
    </el-descriptions>
    <el-input v-if="action !== 'apply'" v-model="note" class="mt-3" type="textarea" placeholder="审核备注" />
    <template #footer>
      <el-button @click="model = false">
        取消
      </el-button>
      <el-button type="primary" :loading="submitting" @click="submit">
        确认
      </el-button>
    </template>
  </el-drawer>
</template>
