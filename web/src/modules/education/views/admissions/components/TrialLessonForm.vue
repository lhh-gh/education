<script setup lang="ts">
const emit = defineEmits<{ submit: [payload: Record<string, unknown>] }>()
const model = defineModel<boolean>({ default: false })
const form = reactive({ lead_id: undefined as number | undefined, lead_student_id: undefined as number | undefined, course_id: undefined as number | undefined, teacher_id: undefined as number | undefined, start_time: '', end_time: '' })
</script>

<template>
  <el-dialog v-model="model" title="Trial Lesson" width="560px">
    <el-form :model="form" label-width="120px">
      <el-form-item label="Lead"><el-input-number v-model="form.lead_id" :controls="false" /></el-form-item>
      <el-form-item label="Student"><el-input-number v-model="form.lead_student_id" :controls="false" /></el-form-item>
      <el-form-item label="Course"><el-input-number v-model="form.course_id" :controls="false" /></el-form-item>
      <el-form-item label="Teacher"><el-input-number v-model="form.teacher_id" :controls="false" /></el-form-item>
      <el-form-item label="Start"><el-input v-model="form.start_time" /></el-form-item>
      <el-form-item label="End"><el-input v-model="form.end_time" /></el-form-item>
    </el-form>
    <template #footer><el-button type="primary" @click="emit('submit', { ...form })">Submit</el-button></template>
  </el-dialog>
</template>
