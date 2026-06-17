<script setup lang="ts">
import type { LessonChangeCreatePayload, OperationLessonChangeType } from '../../../api/operations/lesson-change.ts'
import { createLessonChangeRequest } from '../../../api/operations/lesson-change.ts'

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
    emit('conflict', error?.message ?? 'Lesson change conflict')
  }
  finally {
    submitting.value = false
  }
}
</script>

<template>
  <el-dialog v-model="model" title="Lesson Change" width="560px">
    <el-form label-width="120px">
      <el-form-item label="Lesson ID">
        <el-input-number v-model="form.lesson_id" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item label="Type">
        <el-select v-model="form.change_type" style="width: 220px;">
          <el-option v-for="item in ['reschedule', 'suspend', 'cancel', 'replace_teacher', 'replace_classroom', 'substitute_teacher'] as OperationLessonChangeType[]" :key="item" :label="item" :value="item" />
        </el-select>
      </el-form-item>
      <el-form-item label="Reason">
        <el-input v-model="form.reason" type="textarea" />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="model = false">
        Cancel
      </el-button>
      <el-button type="primary" :loading="submitting" @click="submit">
        Submit
      </el-button>
    </template>
  </el-dialog>
</template>
