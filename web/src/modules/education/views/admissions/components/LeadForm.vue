<script setup lang="ts">
const emit = defineEmits<{ success: [], submit: [payload: { contact_name: string, contact_mobile: string, student_name: string }] }>()
const model = defineModel<boolean>({ default: false })
const form = reactive({ contact_name: '', contact_mobile: '', student_name: '' })

function submit() {
  emit('submit', { ...form })
  emit('success')
  model.value = false
}
</script>

<template>
  <el-dialog v-model="model" title="Lead" width="520px">
    <el-form :model="form" label-width="110px">
      <el-form-item label="Contact"><el-input v-model="form.contact_name" /></el-form-item>
      <el-form-item label="Mobile"><el-input v-model="form.contact_mobile" /></el-form-item>
      <el-form-item label="Student"><el-input v-model="form.student_name" /></el-form-item>
    </el-form>
    <template #footer><el-button type="primary" @click="submit">Submit</el-button></template>
  </el-dialog>
</template>
