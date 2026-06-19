<script setup lang="ts">
import type { LessonChangeCreatePayload, OperationLessonChangeType } from '../../../api/operations/lesson-change.ts'
import { createLessonChangeRequest } from '../../../api/operations/lesson-change.ts'
import { conflictErrorText, operationTypeLabel } from '../operationRules.ts'

const props = defineProps<{ tenantId?: number, campusId?: number }>()
const emit = defineEmits<{ success: [id: number], conflict: [message: string] }>()
const model = defineModel<boolean>({ default: false })
const submitting = ref(false)
const form = reactive<LessonChangeCreatePayload>({
  tenant_id: undefined,
  campus_id: undefined,
  lesson_id: 0,
  change_type: 'reschedule',
  new_values_json: {},
  reason: '',
})

async function submit() {
  submitting.value = true
  try {
    const response = await createLessonChangeRequest({ ...form, tenant_id: props.tenantId, campus_id: props.campusId })
    model.value = false
    emit('success', response.data.id)
  }
  catch (error: any) {
    emit('conflict', conflictErrorText(error))
  }
  finally {
    submitting.value = false
  }
}
</script>

<template>
  <el-dialog v-model="model" title="调课申请" width="560px">
    <el-form label-width="120px">
      <el-form-item label="课次 ID">
        <el-input-number v-model="form.lesson_id" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item label="类型">
        <el-select v-model="form.change_type" style="width: 220px;">
          <el-option v-for="item in ['reschedule', 'suspend', 'cancel', 'replace_teacher', 'replace_classroom', 'substitute_teacher'] as OperationLessonChangeType[]" :key="item" :label="operationTypeLabel(item)" :value="item" />
        </el-select>
      </el-form-item>
      <el-form-item label="原因">
        <el-input v-model="form.reason" type="textarea" />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="model = false">
        取消
      </el-button>
      <el-button type="primary" :loading="submitting" @click="submit">
        提交
      </el-button>
    </template>
  </el-dialog>
</template>
