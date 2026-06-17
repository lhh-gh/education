<script setup lang="ts">
const emit = defineEmits<{ submit: [payload: Record<string, unknown>] }>()
const form = reactive({ lead_id: undefined as number | undefined, student_name: '', guardian_name: '', lesson_package_id: undefined as number | undefined, paid_amount: '0.00' })
</script>

<template>
  <el-form :model="form" label-width="130px">
    <el-form-item label="Lead"><el-input-number v-model="form.lead_id" :controls="false" /></el-form-item>
    <el-form-item label="Student"><el-input v-model="form.student_name" /></el-form-item>
    <el-form-item label="Guardian"><el-input v-model="form.guardian_name" /></el-form-item>
    <el-form-item label="Package"><el-input-number v-model="form.lesson_package_id" :controls="false" /></el-form-item>
    <el-form-item label="Paid"><el-input v-model="form.paid_amount" /></el-form-item>
    <el-form-item><el-button type="primary" @click="emit('submit', { ...form })">Convert</el-button></el-form-item>
  </el-form>
</template>
