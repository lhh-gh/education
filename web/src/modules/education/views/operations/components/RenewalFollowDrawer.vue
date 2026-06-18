<script setup lang="ts">
import type { RenewalAlertRecord } from '../../../api/operations/renewal.ts'
import { assignRenewalTask } from '../../../api/operations/renewal.ts'

const props = defineProps<{ row: RenewalAlertRecord | null }>()
const emit = defineEmits<{ success: [] }>()
const model = defineModel<boolean>({ default: false })
const submitting = ref(false)
const form = reactive({ assignee_id: 0, next_follow_at: '' })

async function submit() {
  if (!props.row) {
    return
  }
  submitting.value = true
  try {
    await assignRenewalTask({ tenant_id: props.row.tenant_id, campus_id: props.row.campus_id ?? undefined, renewal_alert_id: props.row.id, assignee_id: form.assignee_id, next_follow_at: form.next_follow_at })
    model.value = false
    emit('success')
  }
  finally {
    submitting.value = false
  }
}
</script>

<template>
  <el-drawer v-model="model" title="Renewal Follow-up" size="420px">
    <el-descriptions v-if="row" :column="1" border>
      <el-descriptions-item label="Student">
        {{ row.student_id }}
      </el-descriptions-item>
      <el-descriptions-item label="Level">
        {{ row.alert_level }}
      </el-descriptions-item>
      <el-descriptions-item label="Due">
        {{ row.due_date }}
      </el-descriptions-item>
    </el-descriptions>
    <el-form class="mt-3" label-width="110px">
      <el-form-item label="Assignee">
        <el-input-number v-model="form.assignee_id" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item label="Next Follow">
        <el-date-picker v-model="form.next_follow_at" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="model = false">
        Cancel
      </el-button>
      <el-button type="primary" :loading="submitting" @click="submit">
        Assign
      </el-button>
    </template>
  </el-drawer>
</template>
