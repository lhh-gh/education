<script setup lang="ts">
import type { MakeupEntitlementRecord } from '../../../api/operations/makeup.ts'
import { arrangeMakeup } from '../../../api/operations/makeup.ts'

const props = defineProps<{ row: MakeupEntitlementRecord | null }>()
const emit = defineEmits<{ success: [] }>()
const model = defineModel<boolean>({ default: false })
const submitting = ref(false)
const form = reactive({ makeup_lesson_id: 0, arranged_at: '' })

async function submit() {
  if (!props.row) {
    return
  }
  submitting.value = true
  try {
    await arrangeMakeup(props.row.id, { ...form, tenant_id: props.row.tenant_id, campus_id: props.row.campus_id ?? undefined })
    model.value = false
    emit('success')
  }
  finally {
    submitting.value = false
  }
}
</script>

<template>
  <el-dialog v-model="model" title="安排补课" width="480px">
    <el-form label-width="130px">
      <el-form-item label="补课权益">
        <span>{{ row?.id }}</span>
      </el-form-item>
      <el-form-item label="补课课次">
        <el-input-number v-model="form.makeup_lesson_id" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item label="安排时间">
        <el-date-picker v-model="form.arranged_at" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="model = false">
        取消
      </el-button>
      <el-button type="primary" :loading="submitting" @click="submit">
        安排
      </el-button>
    </template>
  </el-dialog>
</template>
