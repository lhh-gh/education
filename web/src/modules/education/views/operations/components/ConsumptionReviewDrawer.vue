<script setup lang="ts">
import type { ConsumptionReviewRecord } from '../../../api/operations/consumption-review.ts'
import { approveConsumptionReview, rejectConsumptionReview } from '../../../api/operations/consumption-review.ts'

const props = defineProps<{ row: ConsumptionReviewRecord | null, action: 'approve' | 'reject', error?: string }>()
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
    const payload = { tenant_id: props.row.tenant_id, campus_id: props.row.campus_id ?? undefined, review_note: note.value }
    if (props.action === 'approve') {
      await approveConsumptionReview(props.row.id, payload)
    }
    else {
      await rejectConsumptionReview(props.row.id, payload)
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
  <el-drawer v-model="model" title="Consumption Review" size="420px">
    <el-alert v-if="error" class="mb-3" type="error" show-icon :closable="false" :title="error" />
    <el-descriptions v-if="row" :column="1" border>
      <el-descriptions-item label="Lesson">
        {{ row.lesson_id }}
      </el-descriptions-item>
      <el-descriptions-item label="Status">
        {{ row.status }}
      </el-descriptions-item>
      <el-descriptions-item label="Submitted">
        {{ row.submitted_at }}
      </el-descriptions-item>
    </el-descriptions>
    <el-input v-model="note" class="mt-3" type="textarea" placeholder="Review note" />
    <template #footer>
      <el-button @click="model = false">
        Cancel
      </el-button>
      <el-button type="primary" :loading="submitting" @click="submit">
        Submit
      </el-button>
    </template>
  </el-drawer>
</template>
