<script setup lang="ts">
import { saveApprovalTemplate } from '../../../api/group/approval.ts'

const props = defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean], 'success': [] }>()

const saving = ref(false)
const form = reactive({
  template_code: '',
  template_name: '',
  business_type: 'contract',
  assignee_user_id: undefined as number | undefined,
})
const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

async function submit() {
  saving.value = true
  try {
    await saveApprovalTemplate({
      template_code: form.template_code,
      template_name: form.template_name,
      business_type: form.business_type,
      nodes: [{ node_code: 'manager', node_name: 'Manager', sort_order: 1, assignee_user_id: Number(form.assignee_user_id) }],
    })
    visible.value = false
    emit('success')
  }
  finally {
    saving.value = false
  }
}
</script>

<template>
  <el-dialog v-model="visible" title="Approval Template" width="600px">
    <el-form :model="form" label-width="128px">
      <el-form-item label="Code">
        <el-input v-model="form.template_code" />
      </el-form-item>
      <el-form-item label="Name">
        <el-input v-model="form.template_name" />
      </el-form-item>
      <el-form-item label="Business">
        <el-input v-model="form.business_type" />
      </el-form-item>
      <el-form-item label="Assignee User">
        <el-input-number v-model="form.assignee_user_id" :min="1" />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="visible = false">
        Cancel
      </el-button>
      <el-button type="primary" :loading="saving" @click="submit">
        Save
      </el-button>
    </template>
  </el-dialog>
</template>
